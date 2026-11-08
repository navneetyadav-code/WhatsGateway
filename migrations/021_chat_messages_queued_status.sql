-- 021_chat_messages_queued_status.sql
-- Add 'pending' and 'queued' to chat_messages.status enum for accurate message lifecycle (API-01)

ALTER TABLE `chat_messages` MODIFY COLUMN `status` enum('pending','queued','sent','delivered','read','failed') NOT NULL DEFAULT 'queued';
