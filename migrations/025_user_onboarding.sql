-- 025_user_onboarding.sql
-- Track whether newly registered users have completed initial workspace onboarding

ALTER TABLE `users`
  ADD COLUMN `onboarding_completed` tinyint(1) NOT NULL DEFAULT 0;

UPDATE `users` SET `onboarding_completed` = 1;
