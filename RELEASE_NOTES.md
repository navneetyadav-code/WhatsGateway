# WhatsGateway v2.0.0 Release Notes

**Release Date:** November 8, 2026  
**Commit Range:** `v1.2.0...HEAD` (86 commits, 297 files changed, +40,241 / -24,215 lines)  
**Database Migrations:** `004` through `021` (18 schema migrations)

---

## 🚀 Features

### 1. Distributed Systems & High-Throughput Worker Engine
* **Distributed Session Leasing (`DIST-01`, `DIST-02`, Migration `019`)**: Added distributed session locking via `whatsapp_session_leases` table. Guarantees that only one worker process claims an active WhatsApp session, preventing split-brain socket disconnections and message corruption. Includes automatic stale-lease recovery and heartbeat renewals.
* **Per-User Concurrency & Adaptive Queue Engine (`DIST-03`, `5cecc7a`, `e49e4ef`)**:
  * Implemented per-tenant queue claiming with priority queues (`idx_queue_priority_tenant`), ensuring high-volume users cannot starve smaller tenants.
  * Adaptive polling mechanism: worker automatically adjusts from low-frequency idle checks to instant burst dispatch upon message arrival notifications.
  * Integrated exponential backoff retries with deterministic message deduplication.
* **Elimination of Phantom WAMIDs (`e22bc06`, `d6cf81e`)**: Realigned API message tracking directly with database queue IDs and native Baileys protocol message IDs (`wamid`), eliminating phantom message failures and status reporting desynchronization.
* **Concurrent Webhook Dispatch Pool (`DIST-03`)**: Replaced blocking single-threaded webhooks with an asynchronous, non-blocking execution pool.

### 2. Autonomous AI Agent Core & Dual-Tier Memory
* **Multi-Provider AI Router & Automatic Failover (`eee0f7a`, `b087654`)**: Native load routing across Google Gemini, Groq, and OpenAI models. Includes 503/429 status code detection with automated fallback cascading.
* **Dual-Tier Cognitive Memory Engine (`aa382fa`, `07d7dee`, Migrations `013`, `017`)**:
  * **Short-Term Context**: Session-scoped conversational memory with a 45-minute idle TTL decay.
  * **Persistent Long-Term Memory**: Contact-scoped facts notebook using local hybrid semantic search (BM25 + TF-IDF) scaling across 20+ persistent facts without requiring an external vector database.
* **Human-in-the-Loop (HITL) Action Confirmation (`ec22038`, `9ce25c2`, Migration `012`)**: State-aware confirmation workflow for critical actions (e.g. automated email sending or CRM updates) requiring explicit user confirmation before external API execution.
* **AI Chatbot Studio Enhancements (`0145e9e`, `f87fa6a`)**: Added AI Persona Rewrite & Polish tool, temporal anchoring (injecting real-world time and date context), and anti-hallucination domain boundaries.

### 3. Universal Multi-Tenant Custom Tools Engine
* **Dynamic REST API Builder & Action Routes (`956a3a5`, `d76f64d`, Migration `010`)**: Build, test, and register custom REST APIs with dynamic header mapping, URL parameters, and JSON/form-urlencoded body templates.
* **1-Click cURL Importer & Preset Catalog (`03163c0`)**: Instantly parse raw cURL requests into production-ready AI tools, complete with an 8-app preset catalog.
* **Shared-Hosting Anti-Bot Challenge Resolver (`a225a87`, `df66770`)**: Autonomous cryptographic AES-128-CBC challenge solver in both Node.js and PHP, enabling continuous API execution on free shared hosting platforms subject to daily 24-hour cookie salt rotations.
* **Audit Logging & Portability (`adedb84`, Migration `018`)**: Real-time logging of tool invocations, execution latencies, caller metadata, and responses, plus secure Zip export and import of tool packs.

### 4. Native Live Chat Studio & Communications
* **Three-Tab Triage Inbox (`f5577c5`, `029a521`, Migration `005`)**: Segment conversations into **Unread (Human Intervention Required)**, **Bot Handling**, and **Closed**.
* **Real-Time Desktop & Audio Alerts (`a7dccc1`)**: In-dashboard WebAudio buzzer alarm and desktop push notifications for human agent escalations.
* **Automated CSAT Customer Feedback Loop (`f5577c5`, Migration `006`)**: Automated 5-star customer feedback rating dispatched upon conversation resolution.
* **Modernized WaStudio WYSIWYG Composer (`aa03618`, `65e3db5`)**: Visual message editor supporting rich text styling, instant mobile preview, and multi-file attachments (PDF, PPTX, JPG, PNG).
* **Modular Campaigns (`c159dfe`, `2c3ca00`)**: 3-step campaign manager with dedicated sandbox mode, batch scheduling, and contact tag segmentation.

---

## 🔒 Security

### 1. SSRF & DNS Rebinding Elimination (`SEC-01`, `3548c52`)
* Enforced strict IP address parsing on all outbound HTTP requests (custom tools, webhooks, and cURL imports).
* Explicitly blocks:
  * Private RFC 1918 ranges (`10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`)
  * Loopback addresses (`127.0.0.0/8`, `::1`)
  * Link-Local addresses (`169.254.0.0/16`, `fe80::/10`)
  * Carrier-Grade NAT (`100.64.0.0/10`)
  * Cloud metadata endpoints (`169.254.169.254`)
  * IPv4-mapped IPv6 representations (`::ffff:127.0.0.1`, `::ffff:10.0.0.1`, etc.)

### 2. Client IP Spoofing Prevention (`SEC-07`)
* Replaced insecure left-most `X-Forwarded-For` inspection with secure reverse-chain traversal.
* `client_ip()` verifies `REMOTE_ADDR` against trusted reverse proxy CIDRs before processing proxy headers, preventing attackers from bypassing rate limiters or IP whitelists.

### 3. Password Reset OTP Hashing (`SEC-09`, Migration `001` update)
* Schema updated to store password reset tokens as SHA-256 digests (`VARCHAR(64)`).
* Plaintext recovery OTPs are never stored in the database, protecting user accounts against database dump compromises.

### 4. Authentication, Anti-CSRF & Tenant Isolation (`SEC-08`, `SEC-10`, `3548c52`)
* Added anti-CSRF token verification across all WhatsApp device pairing, connection resets, and session management endpoints.
* Restricted global system AI API keys (`canUseGlobalAiFallback`) so unprivileged tenants cannot consume host credentials without authorization.
* Eliminated vulnerable loose admin phone matching (`p.endsWith(cleanPhone)`).
* Encrypted sensitive tenant credentials, SMTP passwords, and webhook secrets at rest using AES-256-GCM.
* Quarantined destructive database maintenance endpoints (`/api/maintenance/purge`) strictly to CLI execution (`PHP_SAPI === 'cli'`).

---

## ⚙️ Other (Performance, Operations & Migration)

### 1. Database & High-Scale Performance (`DB-01` to `DB-06`, `PERF-01`, `PERF-02`)
* **Composite Query Indexing**: Added `idx_queue_priority_tenant` on `message_queue` and `idx_media_assets_user_storage` on `media_assets`, completely removing query filesorts during queue claiming.
* **Physical Relational Tracking**: Converted unindexed campaign message JSON arrays to physical `campaign_id` foreign keys with batch multi-row inserts (`DB-04`).
* **Non-Blocking Worker I/O**: Migrated Baileys session credential resolution from synchronous `fs.readFileSync` to asynchronous `fs/promises`.
* **Lightweight Health Checks (`OPS-01`)**: Optimized `/api/health.php` to use `SELECT 1` ping and cached schema checks instead of running costly database introspection on every heartbeat.
* **Removed Production `opcache_reset()`**: Eliminated accidental opcache clearing in request paths.
* **Database Maintenance CLI**: Added `scripts/backup_and_clean.php` with automated SQL dump generation and retention purging (`--dry-run`, `--retention-days`).

### 2. Docker & Environment
* **Persistent Named Volumes**: Docker configuration updated to persist media uploads and database backups across container rebuilds.
* **Environment Synchronization**: Audited and synchronized `.env.example` with all configuration keys across the PHP application and Node.js worker.
* **Clean Vulnerability Audit**: Worker Node.js packages updated with 0 known vulnerabilities.

### 3. Upgrade & Migration Guide (from v1.2.0)
Existing installations must perform the following upgrade steps:

1. **Pull the latest code**:
   ```bash
   git pull origin main
   ```
2. **Apply Database Migrations (`004` to `021`)**:
   Run the migration script:
   ```bash
   php scripts/migrate.php
   ```
   *(Or import `migrations/004_*.sql` through `migrations/021_*.sql` sequentially into your MySQL/MariaDB database).*
3. **Update Worker Dependencies**:
   ```bash
   cd worker
   npm install
   ```
4. **Restart Services**:
   ```bash
   # Restart the Node.js worker
   pm2 restart whatsapp-worker # or systemctl restart whatsapp-worker
   # Reload PHP-FPM / Apache
   systemctl reload php8.2-fpm # or systemctl reload apache2
   ```
