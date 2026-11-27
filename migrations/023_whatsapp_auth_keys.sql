-- 023_whatsapp_auth_keys.sql
-- WhatsApp Baileys authentication state persistence in MySQL
-- Ensures sessions survive container restarts, Render redeployments, and ephemeral filesystem resets.

CREATE TABLE IF NOT EXISTS `whatsapp_auth_keys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `key_id` varchar(191) NOT NULL,
  `value` longtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_key` (`user_id`, `key_id`),
  KEY `idx_auth_keys_user` (`user_id`),
  CONSTRAINT `fk_whatsapp_auth_keys_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
