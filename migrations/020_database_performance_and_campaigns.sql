-- 020_database_performance_and_campaigns.sql
-- Database Performance, Indexing, and Campaign Optimization (DB-01, DB-02, DB-03)

-- 1. DB-02: Add physical campaign_id column to message_queue and index it
ALTER TABLE `message_queue` ADD COLUMN `campaign_id` bigint(20) unsigned DEFAULT NULL;
ALTER TABLE `message_queue` ADD KEY `idx_message_queue_campaign` (`campaign_id`, `status`);
ALTER TABLE `message_queue` ADD KEY `idx_message_queue_user_campaign` (`user_id`, `campaign_id`);

-- 2. DB-01: Add composite priority queue index matching tenant queue claim queries
ALTER TABLE `message_queue` ADD KEY `idx_queue_priority_tenant` (`status`, `user_id`, `priority`, `available_at`, `id`);

-- 3. DB-03: Add composite indexes on media_assets for direct owner and storage lookups
ALTER TABLE `media_assets` ADD KEY `idx_media_assets_user_storage` (`user_id`, `storage_key`);
ALTER TABLE `media_assets` ADD KEY `idx_media_assets_user_path` (`user_id`, `file_path`(191));

-- 4. Backfill existing campaign_id values from payload JSON
UPDATE `message_queue`
SET `campaign_id` = CAST(JSON_UNQUOTE(JSON_EXTRACT(`payload`, '$.campaign_id')) AS UNSIGNED)
WHERE `payload` LIKE '%"campaign_id"%' AND `campaign_id` IS NULL;
