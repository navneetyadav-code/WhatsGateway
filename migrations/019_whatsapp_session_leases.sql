-- 019_whatsapp_session_leases.sql
-- Distributed WhatsApp Session Leases (DIST-02)
-- Prevents multi-worker socket collisions and cascading WhatsApp bans in multi-instance / containerized deployments.

ALTER TABLE `whatsapp_sessions` ADD COLUMN `locked_by` varchar(80) DEFAULT NULL;
ALTER TABLE `whatsapp_sessions` ADD COLUMN `lease_expires_at` datetime DEFAULT NULL;
ALTER TABLE `whatsapp_sessions` ADD KEY `idx_sessions_lease` (`locked_by`, `lease_expires_at`);
