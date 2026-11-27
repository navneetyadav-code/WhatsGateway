-- 026_workspace_mode.sql
-- Add workspace_mode to users table to tailor navigation and features (api_only, personal, support)

ALTER TABLE `users`
  ADD COLUMN `workspace_mode` enum('personal','support','api_only') NOT NULL DEFAULT 'support';

UPDATE `users` u
LEFT JOIN `ai_agent_settings` a ON a.user_id = u.id
SET u.workspace_mode = CASE
    WHEN a.agent_mode = 'personal' THEN 'personal'
    ELSE 'support'
END;
