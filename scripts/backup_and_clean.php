<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

/**
 * CLI Maintenance Utility: Database SQL Backup & Clean Messages
 *
 * Usage via CLI:
 *   php scripts/backup_and_clean.php
 *   php scripts/backup_and_clean.php --dry-run
 *   php scripts/backup_and_clean.php --backup-only
 *   php scripts/backup_and_clean.php --clean-only
 *   php scripts/backup_and_clean.php --clean-uploads
 */

require_once dirname(__DIR__) . '/src/bootstrap.php';

// Parse CLI options
$options = getopt('', ['dry-run', 'backup-only', 'clean-only', 'purge-expired', 'retention-days:', 'clean-uploads', 'out-dir:', 'help']);
if (isset($options['help'])) {
    echo <<<HELP
WhatsGateway Maintenance Utility: Database SQL Backup & Clean

Usage:
  php scripts/backup_and_clean.php [options]

Options:
  --dry-run               Simulate cleanup without deleting data
  --backup-only           Generate SQL dump only, skip purging tables
  --purge-expired         Purge records older than retention period (DB-06)
  --retention-days=<days> Number of days of history to retain (default: 30)
  --clean-only            Purge all messages and queues without creating backup (caution!)
  --clean-uploads         Also delete orphaned media files in public/uploads/
  --out-dir=<path>        Specify directory for SQL dump file (default: storage/backups)
  --help                  Show this help message

HELP;
    exit(0);
}

$dryRun = isset($options['dry-run']);
$backupOnly = isset($options['backup-only']);
$cleanOnly = isset($options['clean-only']);
$purgeExpired = isset($options['purge-expired']) || isset($options['retention-days']);
$retentionDays = isset($options['retention-days']) ? max(1, (int) $options['retention-days']) : 30;
$cleanUploads = isset($options['clean-uploads']);
$outDir = isset($options['out-dir']) ? (string) $options['out-dir'] : null;

echo "====================================================================\n";
echo "          WHATSGATEWAY DATABASE BACKUP & SAFE CLEANUP               \n";
echo "====================================================================\n";
if ($dryRun) {
    echo "[!] DRY RUN MODE ACTIVATED - No database modifications will occur.\n";
}
echo "\n";

// -----------------------------------------------------------------------------
// STEP 1: CREATE SQL BACKUP (unless --clean-only is requested)
// -----------------------------------------------------------------------------
if (!$cleanOnly) {
    echo ">>> [1/2] Creating full SQL backup...\n";
    try {
        $backupResult = DatabaseMaintenance::createSqlBackup($outDir);
        echo "    [✔] Backup successfully generated:\n";
        echo "        File:      {$backupResult['file']}\n";
        echo "        Size:      {$backupResult['size_formatted']} ({$backupResult['size_bytes']} bytes)\n";
        echo "        Tables:    {$backupResult['tables_dumped']}\n";
        echo "        Rows:      {$backupResult['rows_dumped']}\n";
        echo "        Time:      {$backupResult['duration_sec']}s\n\n";
    } catch (Throwable $e) {
        fwrite(STDERR, "    [✖] BACKUP FAILED: " . $e->getMessage() . "\n");
        fwrite(STDERR, "    Cleanup aborted for safety to prevent data loss.\n");
        exit(1);
    }
}

// -----------------------------------------------------------------------------
// STEP 2: CLEAN MESSAGES, LOGS, QUEUES & HISTORY (unless --backup-only)
// -----------------------------------------------------------------------------
if (!$backupOnly) {
    if ($purgeExpired) {
        echo ">>> [2/2] Running automated rolling retention cleanup (DB-06)...\n";
        echo "    (Retaining last {$retentionDays} days; preserving accounts, configurations, and active queues)\n\n";
        try {
            $purgeResult = DatabaseMaintenance::purgeExpiredRecords($retentionDays, $dryRun);
            echo "    --- Rolling Purge Breakdown ---\n";
            foreach ($purgeResult['tables'] as $table => $info) {
                printf("    • %-25s : %-12s (%d records)\n", $table, $info['status'], $info['purged']);
            }
            echo "\n    [✔] Total expired records purged: {$purgeResult['total_purged']}\n";
            echo "    [✔] Execution time:               {$purgeResult['duration_ms']}ms\n\n";
        } catch (Throwable $e) {
            fwrite(STDERR, "    [✖] ROLLING PURGE ERROR: " . $e->getMessage() . "\n");
            exit(1);
        }
    } else {
        echo ">>> [2/2] Cleaning message tables and transient history...\n";
        echo "    (Preserving user accounts, credentials, AI configurations, and migrations)\n\n";
        try {
            $cleanResult = DatabaseMaintenance::cleanDatabase($dryRun);

        echo "    --- Cleaned Tables Breakdown ---\n";
        foreach ($cleanResult['cleaned_tables'] as $table => $info) {
            $status = $info['status'];
            $count = $info['rows_cleared'] ?? 0;
            printf("    • %-25s : %-10s (%d rows)\n", $table, $status, $count);
        }

        echo "\n    --- Preserved User & System Tables ---\n";
        foreach ($cleanResult['preserved_tables'] as $table => $info) {
            printf("    • %-25s : %-10s (%d rows intact)\n", $table, $info['status'], $info['rows']);
        }

        echo "\n    [✔] Total rows purged: {$cleanResult['total_rows_cleared']}\n";
        echo "    [✔] Execution time:    {$cleanResult['duration_sec']}s\n\n";
    } catch (Throwable $e) {
        fwrite(STDERR, "    [✖] CLEANUP ENCOUNTERED ERROR: " . $e->getMessage() . "\n");
        exit(1);
    }
    }

    if ($cleanUploads) {
        echo ">>> Cleaning orphaned uploads in public/uploads/...\n";
        $uploadsResult = DatabaseMaintenance::cleanUploadFiles($dryRun);
        echo "    [✔] Deleted {$uploadsResult['files_deleted']} files ({$uploadsResult['bytes_formatted']} freed)\n\n";
    }
}

echo "====================================================================\n";
echo "                      MAINTENANCE COMPLETE                          \n";
echo "====================================================================\n";
