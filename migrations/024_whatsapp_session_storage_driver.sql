-- 024_whatsapp_session_storage_driver.sql
-- Configurable session memory storage driver: database_encrypted vs persistent_disk

ALTER TABLE `whatsapp_sessions`
  ADD COLUMN `storage_driver` enum('database_encrypted', 'persistent_disk') NOT NULL DEFAULT 'database_encrypted';
