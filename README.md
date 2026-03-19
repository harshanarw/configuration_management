# ConfigVault — Configuration Management System

> Laravel 10 implementation of the **Configuration Management Policy**
> Student: Sooriyarathna W.A.R. | Student ID: 2023mis025

A full-stack secure configuration management platform built with **Laravel 10** and **Bootstrap 5.3**, demonstrating all features described in the Configuration Management Policy document.

---

## Features Implemented

| Module | Policy Reference | Description |
|---|---|---|
| Version-Controlled Repository | FR1.1, FR1.2, FR1.3 | Git-style config versioning with history |
| RBAC Management | FR2.1, FR2.2, FR2.3 | 5 roles, permission matrix, access logs |
| Approval Workflow | FR3.1, FR3.2, FR3.3 | 2-level review → auto-deploy |
| Immutable Audit Logs | NFR3.1, FR2.3 | SHA-256 hash-chained WORM log entries |
| STRIDE Threat Model | Section 7 | 12 threats with CIA + NR matrix |
| Secrets Policy | Security | Vault references — no plaintext secrets |

## Demo Accounts

| Email | Password | Role |
|---|---|---|
| admin@configvault.com | password | Admin |
| dev@configvault.com | password | Developer |
| reviewer@configvault.com | password | Reviewer |
| approver@configvault.com | password | Approver |
| auditor@configvault.com | password | Auditor |

---

## Quick Setup (5 minutes)

### Requirements
- PHP 8.1+
- Composer
- Node.js (optional, Bootstrap loaded via CDN)

### Steps

```bash
# 1. Clone the repository
git clone https://github.com/harshanarw/configuration_management.git
cd configuration_management

# 2. Install PHP dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Create SQLite database (easiest — no MySQL needed)
touch database/database.sqlite

# 6. Run migrations
php artisan migrate

# 7. Seed demo data (users + sample configs + audit logs)
php artisan db:seed

# 8. Start development server
php artisan serve
```

Open **http://localhost:8000** and log in with any demo account above.

---

## Using MySQL Instead of SQLite

Edit `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=configvault
DB_USERNAME=root
DB_PASSWORD=your_password
```

Then run:
```bash
php artisan migrate --fresh --seed
```

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php          # Login / logout + audit
│   │   ├── DashboardController.php     # Stats overview
│   │   ├── ConfigurationController.php # FR1.1-1.3 CRUD + versioning
│   │   ├── ApprovalController.php      # FR3.1-3.3 two-stage workflow
│   │   ├── AuditController.php         # FR2.3 immutable logs
│   │   ├── RbacController.php          # FR2.1-2.2 role management
│   │   └── ThreatController.php        # STRIDE threat model
│   ├── Middleware/
│   │   └── RoleMiddleware.php          # Role-based route protection
├── Models/
│   ├── User.php                        # 5-role user model
│   ├── Configuration.php               # Config file with versioning
│   ├── ChangeRequest.php               # Approval workflow state
│   ├── ConfigVersion.php               # Immutable version snapshot
│   └── AuditLog.php                    # SHA-256 hash-chained log
├── Policies/
│   ├── ConfigurationPolicy.php         # RBAC for configs
│   └── ChangeRequestPolicy.php         # RBAC for approval actions

resources/views/
├── layouts/app.blade.php               # Dark sidebar layout
├── auth/login.blade.php                # Split-panel login
├── dashboard/index.blade.php           # Stats + activity
├── configurations/                     # Repo CRUD views
├── approvals/                          # Workflow views
├── audit/index.blade.php               # Audit log viewer
├── rbac/index.blade.php               # User + role management
└── threats/index.blade.php            # STRIDE threat model

database/
├── migrations/                         # All 3 migration files
└── seeders/DatabaseSeeder.php          # 5 users + demo data

routes/web.php                          # All application routes
```

---

## Security Architecture

```
External Entities (Developer, Reviewer, Approver, Auditor, Admin)
        ↓  JWT Auth + MFA
Application Tier
  ├── Web Console / UI    (Bootstrap 5.3 dark theme)
  ├── API Gateway         (Laravel middleware, CSRF)
  ├── RBAC Module         (Policies + Role middleware)
  ├── Approval Engine     (2-level workflow)
  └── Audit & Logging     (SHA-256 hash chains)
        ↓
Data Layer
  ├── Config Repository   (MySQL/SQLite + SoftDeletes)
  ├── Audit Logs          (Immutable, hash-chained)
  └── Secrets Vault       (Referenced via ${vault:path})
```

---

## Workflow Demo

1. Log in as **Developer** → Create a config → Submit change request
2. Log in as **Reviewer** → Go to Approval Queue → Review and approve (Level 1)
3. Log in as **Approver** → Go to Approval Queue → Final approve + MFA code (Level 2)
4. Watch status change to **Deployed** automatically (FR3.3)
5. Log in as **Auditor** → View full audit trail with hash chain
6. Log in as **Admin** → Manage RBAC → View Threat Model

---

## License

MIT — for educational/demonstration purposes.
