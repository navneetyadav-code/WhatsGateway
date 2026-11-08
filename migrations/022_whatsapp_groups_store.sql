-- 022_whatsapp_groups_store.sql
-- WhatsApp groups persistence store for fast group discovery, Group ID lookup, and Quick Code snippets

CREATE TABLE IF NOT EXISTS `whatsapp_groups` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `group_jid` varchar(80) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `participants_count` int(11) DEFAULT 0,
  `is_admin` tinyint(1) DEFAULT 0,
  `announce` tinyint(1) DEFAULT 0,
  `description` text DEFAULT NULL,
  `created_time` datetime DEFAULT NULL,
  `last_synced_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_group` (`user_id`, `group_jid`),
  KEY `idx_groups_user` (`user_id`),
  CONSTRAINT `fk_whatsapp_groups_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
