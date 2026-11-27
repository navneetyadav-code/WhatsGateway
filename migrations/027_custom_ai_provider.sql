-- Migration 027: Replace OpenRouter with Custom API (OpenAI Compatible) provider
-- Supports arbitrary endpoints (OpenAI, DeepSeek, Together, Ollama, vLLM, LocalAI)

ALTER TABLE `ai_provider_keys` 
  MODIFY COLUMN `provider` VARCHAR(32) NOT NULL;

ALTER TABLE `ai_provider_keys` 
  ADD COLUMN `endpoint` VARCHAR(255) DEFAULT NULL;

ALTER TABLE `ai_agent_settings` 
  MODIFY COLUMN `provider_priority` VARCHAR(32) NOT NULL DEFAULT 'gemini';

ALTER TABLE `ai_agent_settings` 
  ADD COLUMN `custom_api_key` TEXT DEFAULT NULL;

ALTER TABLE `ai_agent_settings` 
  ADD COLUMN `custom_model` VARCHAR(100) NOT NULL DEFAULT 'gpt-4o';

ALTER TABLE `ai_agent_settings` 
  ADD COLUMN `custom_endpoint` VARCHAR(255) NOT NULL DEFAULT 'https://api.openai.com/v1';

UPDATE `ai_agent_settings` 
  SET `provider_priority` = 'custom' 
  WHERE `provider_priority` = 'openrouter';

UPDATE `ai_provider_keys` 
  SET `provider` = 'custom', `endpoint` = 'https://openrouter.ai/api/v1' 
  WHERE `provider` = 'openrouter';
