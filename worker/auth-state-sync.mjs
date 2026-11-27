// worker/auth-state-sync.mjs
// Configurable persistent storage & synchronization for WhatsApp Baileys authentication state.
// Supports:
// 1. 'database_encrypted' - AES-256-GCM encrypted persistence in MySQL (ideal for Render/cloud containers)
// 2. 'persistent_disk'     - Local filesystem storage only (ideal for dedicated VPS / Render with Persistent Disk)

import { existsSync, mkdirSync, writeFileSync } from 'fs';
import { readdir, readFile, rm } from 'fs/promises';
import { join } from 'path';
import { BufferJSON } from '@whiskeysockets/baileys';
import { encryptCredential, decryptCredential } from './crypto-helper.mjs';

/**
 * Resolves the configured storage driver for a user ('database_encrypted' | 'persistent_disk').
 */
export async function getStorageDriver(userId, db) {
  const uid = Number(userId);
  try {
    const [rows] = await db.execute(
      'SELECT storage_driver FROM whatsapp_sessions WHERE user_id = ? LIMIT 1',
      [uid]
    );
    if (rows?.[0]?.storage_driver) {
      return rows[0].storage_driver;
    }
  } catch (_) {}
  return 'database_encrypted';
}

/**
 * Restores WhatsApp auth session keys from MySQL to disk if the local container directory is empty.
 * If driver is 'persistent_disk', skips database restore and uses local disk exclusively.
 */
export async function restoreSessionFromDb(userId, authDir, db, logger) {
  const uid = Number(userId);
  const driver = await getStorageDriver(uid, db);

  if (driver === 'persistent_disk') {
    logger?.info?.({ userId: uid }, 'Persistent Disk driver active; skipping database restore');
    return existsSync(join(authDir, 'creds.json'));
  }

  const credsPath = join(authDir, 'creds.json');
  if (existsSync(credsPath)) {
    try {
      const [rows] = await db.execute(
        'SELECT COUNT(*) as cnt FROM whatsapp_auth_keys WHERE user_id = ?',
        [uid]
      );
      if (rows?.[0]?.cnt === 0) {
        await backupSessionToDb(uid, authDir, db, logger);
      }
    } catch (err) {
      logger?.warn?.({ err: err.message, userId: uid }, 'Non-fatal check for session backup failed');
    }
    return true;
  }

  try {
    const [rows] = await db.execute(
      'SELECT key_id, value FROM whatsapp_auth_keys WHERE user_id = ?',
      [uid]
    );

    if (!rows || rows.length === 0) {
      return false;
    }

    if (!existsSync(authDir)) {
      mkdirSync(authDir, { recursive: true });
    }

    let restoredCount = 0;
    for (const row of rows) {
      if (!row.key_id || !row.value) continue;
      const safeFileName = row.key_id.replace(/\//g, '__').replace(/:/g, '-');
      const filePath = join(authDir, safeFileName);
      // Decrypt AES-256 payload (or passthrough legacy unencrypted data)
      const decrypted = decryptCredential(row.value);
      writeFileSync(filePath, decrypted, 'utf8');
      restoredCount++;
    }

    logger?.info?.(
      { userId: uid, restoredCount, driver: 'database_encrypted' },
      'Successfully restored and decrypted WhatsApp session auth keys from database'
    );
    return true;
  } catch (err) {
    logger?.error?.(
      { err: err.message, userId: uid },
      'Failed to restore WhatsApp session auth keys from database'
    );
    return false;
  }
}

/**
 * Upserts a single auth key file content into MySQL with AES-256-GCM encryption.
 * Skips write if driver is 'persistent_disk'.
 */
export async function syncKeyFileToDb(userId, keyId, content, db, logger) {
  if (!keyId || typeof content !== 'string') return;
  const uid = Number(userId);

  try {
    const driver = await getStorageDriver(uid, db);
    if (driver === 'persistent_disk') {
      return; // Isolated to filesystem only
    }

    const payload = encryptCredential(content);
    await db.execute(
      `INSERT INTO whatsapp_auth_keys (user_id, key_id, value, updated_at)
       VALUES (?, ?, ?, NOW())
       ON DUPLICATE KEY UPDATE value = VALUES(value), updated_at = NOW()`,
      [uid, keyId, payload]
    );
  } catch (err) {
    logger?.warn?.({ err: err.message, userId: uid, keyId }, 'Failed to sync WhatsApp auth key to database');
  }
}

/**
 * Deletes a single auth key file from MySQL.
 */
export async function deleteKeyFileFromDb(userId, keyId, db, logger) {
  if (!keyId) return;
  const uid = Number(userId);
  try {
    await db.execute(
      'DELETE FROM whatsapp_auth_keys WHERE user_id = ? AND key_id = ?',
      [uid, keyId]
    );
  } catch (err) {
    logger?.warn?.({ err: err.message, userId: uid, keyId }, 'Failed to delete WhatsApp auth key from database');
  }
}

/**
 * Completely clears all stored auth keys for a user from MySQL (used on logout or driver switch).
 */
export async function clearSessionFromDb(userId, db, logger) {
  const uid = Number(userId);
  try {
    const [result] = await db.execute(
      'DELETE FROM whatsapp_auth_keys WHERE user_id = ?',
      [uid]
    );
    logger?.info?.({ userId: uid, deletedRows: result?.affectedRows || 0 }, 'Cleared WhatsApp auth keys from database');
  } catch (err) {
    logger?.warn?.({ err: err.message, userId: uid }, 'Failed to clear WhatsApp auth keys from database');
  }
}

/**
 * Scans the local session directory and encrypts all .json auth files into MySQL.
 */
export async function backupSessionToDb(userId, authDir, db, logger) {
  const uid = Number(userId);
  if (!existsSync(authDir)) return 0;

  try {
    const driver = await getStorageDriver(uid, db);
    if (driver === 'persistent_disk') {
      return 0; // Persistent disk mode strictly avoids DB storage
    }

    const files = await readdir(authDir).catch(() => []);
    const jsonFiles = files.filter(f => f.endsWith('.json'));
    if (jsonFiles.length === 0 || !jsonFiles.includes('creds.json')) {
      return 0;
    }

    let synced = 0;
    for (const file of jsonFiles) {
      const content = await readFile(join(authDir, file), 'utf8').catch(() => null);
      if (content) {
        await syncKeyFileToDb(uid, file, content, db, logger);
        synced++;
      }
    }

    logger?.info?.({ userId: uid, synced }, 'Encrypted and backed up active WhatsApp session auth keys to database');
    return synced;
  } catch (err) {
    logger?.warn?.({ err: err.message, userId: uid }, 'Failed to back up WhatsApp session auth keys to database');
    return 0;
  }
}

/**
 * Wraps Baileys auth state saveCreds and keys.set to automatically mirror all updates into MySQL if encrypted DB driver is enabled.
 */
export function wrapAuthStateWithDbSync(userId, authDir, state, saveCreds, db, logger) {
  const uid = Number(userId);

  const wrappedSaveCreds = async () => {
    await saveCreds();
    try {
      if (state.creds) {
        const serialized = JSON.stringify(state.creds, BufferJSON.replacer);
        await syncKeyFileToDb(uid, 'creds.json', serialized, db, logger);
      }
    } catch (err) {
      logger?.warn?.({ err: err.message, userId: uid }, 'Non-fatal error syncing creds.json to DB');
    }
  };

  const originalKeysSet = state.keys.set;
  state.keys.set = async (data) => {
    await originalKeysSet(data);
    try {
      const tasks = [];
      for (const category in data) {
        for (const id in data[category]) {
          const value = data[category][id];
          const keyId = `${category}-${id}.json`.replace(/\//g, '__').replace(/:/g, '-');
          if (value) {
            const serialized = JSON.stringify(value, BufferJSON.replacer);
            tasks.push(syncKeyFileToDb(uid, keyId, serialized, db, logger));
          } else {
            tasks.push(deleteKeyFileFromDb(uid, keyId, db, logger));
          }
        }
      }
      if (tasks.length > 0) {
        await Promise.all(tasks);
      }
    } catch (err) {
      logger?.warn?.({ err: err.message, userId: uid }, 'Non-fatal error syncing keys to DB');
    }
  };

  return { state, saveCreds: wrappedSaveCreds };
}
