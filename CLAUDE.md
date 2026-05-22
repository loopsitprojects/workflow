# Loops Work — CLAUDE.md

## What Is This App?

**Loops Work** is a Laravel 12 creative project management platform for marketing/creative agencies. It manages the full lifecycle of creative deliverables — from brief through final delivery — across multiple client brands, using a structured role-based approval workflow.

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 12 (PHP 8.2+) |
| Frontend | Blade templates + Alpine.js |
| Styling | Tailwind CSS + custom CSS variables |
| Auth | Laravel session auth (no OAuth/SSO) |
| Database | MySQL |
| Notifications | Laravel database notifications |
| Avatars | UI Avatars API (generated from user name) |
| Theme | Dark/light via Alpine.js store + `localStorage` |

---

## Key Directories

```
app/
  Http/
    Controllers/       — AuthController, BrandController, ProjectController,
                         DeliverableController, UserController, SubtaskTypeController
    Middleware/
      AdminMiddleware.php  — Enforces admin-only access on /admin/* routes
  Models/
    User.php           — Roles, isAdmin(), avatarUrl accessor
    Brand.php          — Route key: slug
    Project.php        — workflow_type drives deliverable stage set
    Deliverable.php    — Core workflow engine (constants, stage helpers)
    DeliverableRevision.php
    DeliverableApproval.php
    SubtaskType.php
  Providers/
    AppServiceProvider.php  — Gates: admin bypass + create-deliverable

resources/views/
  auth/login.blade.php
  dashboard.blade.php
  brands/               — index, show, create, edit, retainer_board
  projects/             — index, show, create, edit
  deliverables/         — create, edit
  admin/
    settings.blade.php
    users/              — index, create, edit
    subtask-types/      — index
  components/           — layout, navigation, toast, notification-panel, brand-card

database/migrations/    — Full schema history (see Schema section below)
database/seeders/
  DatabaseSeeder.php        — Default Owner + 2 users per role
  BrandDeliverableSeeder.php — Sample brands, projects, deliverables
  SubtaskTypeSeeder.php     — Default retainer + campaign subtask types
```

---

## User Roles

Roles are stored in `users.role` (string column). No separate permissions table.

| Role | Workflow Stage | Can Create Deliverables |
|---|---|---|
| Owner | Any (admin bypass) | Yes |
| Admin | Any (admin bypass) | Yes |
| Brand Manager | Brand Manager / AM/BD stage | Yes (Gate: `create-deliverable`) |
| Approver | Approver stage | No |
| Coordinator | Coordinator stage | No |
| Traffic Coordinator | Coordinator stage | No |
| Writer | Writer / Assignee stage | No |
| Designer | Designer stage | No |

**`isAdmin()`** — returns true if role is `Admin` or `Owner`. Used in views and was previously used in controllers (now handled by `AdminMiddleware`).

**Gates** (defined in `AppServiceProvider::boot()`):
- Admin bypass: all gates return `true` for admins/owners
- `create-deliverable`: only `Brand Manager` (or admin)

---

## Workflow Engine

### Retainer Workflow (7 stages)

```
Writer → Approver → Brand Manager → Coordinator → Designer → Final Approval → Closed
  0%       20%          40%             60%           80%          90%          100%
```

### Campaign / Pitch Workflow (4 stages)

```
Assignee → AM/BD → Final Approval → Closed
   10%      50%        90%          100%
```

Stage constants live in `app/Models/Deliverable.php`:
- `Deliverable::STAGES` — retainer
- `Deliverable::CAMPAIGN_STAGES` — campaign/pitch

Stage logic lives in `app/Http/Controllers/DeliverableController.php`:
- `submitStage()` — advances deliverable to next stage
- `requestRevisions()` — pushes back to start (or Designer from Final Approval)
- `batchSubmit()` / `batchRevisions()` — parent + all subtasks together (wrapped in DB transaction)
- `internallyAdvanceStage()` (private) — core logic: validates next assignee, handles file uploads for Designer stage, skips stages, creates approval history, sends notifications

**Notification fallback chain:** stage assignee → project lead → first Admin/Owner

---

## Database Schema (Key Tables)

| Table | Purpose |
|---|---|
| `users` | Team members; `role` column drives all access |
| `brands` | Client accounts; route key is `slug` |
| `brand_user` | Many-to-many brands ↔ users |
| `projects` | Projects under a brand; `workflow_type` = retainer/campaign/pitch |
| `project_user` | Many-to-many projects ↔ users |
| `deliverables` | Content pieces; formerly called "tasks" |
| `deliverable_revisions` | Revision request history |
| `deliverable_approvals` | Stage approval history |
| `subtask_types` | Admin-managed content categories (e.g. Carousel, Reels) |

### Projects Table — Important Columns

```
brand_id, job_number, name, description, workflow_type (retainer|campaign|pitch),
status, deadline, priority, type (primary|secondary), sub_type,
writer_id, approver_id, brand_manager_id, coordinator_id, designer_id, lead_id,
brief_file_path, progress
```

> `coordinator_id` was added via migration `2026_05_20_045326`. The `Project` model has a `coordinator()` relationship.

### Deliverables Table — Important Columns

```
project_id, parent_deliverable_id (self-ref for subtasks),
title, status, approval_stage, priority, task_type, post_type,
concept, caption, post_copy, reference, reference_file, notes,
deadline, start_date, end_date, progress_percent, is_ready,
writer_id, approver_id, brand_manager_id, coordinator_id, designer_id,
final_designs, final_designs_link, revisions, revision_instructions
```

---

## Routes Summary

```
GET  /login                         AuthController@showLogin
POST /login                         AuthController@login
POST /logout                        AuthController@logout

GET  /                              DashboardController@index
resource /brands                    BrandController (full CRUD)
GET  /brands/{brand}/retainer-board BrandController@retainerBoard
resource /projects                  ProjectController (full CRUD)
resource /deliverables              DeliverableController (except index, show)

POST /deliverables/{id}/submit           DeliverableController@submitStage
POST /deliverables/{id}/revisions        DeliverableController@requestRevisions
POST /deliverables/{id}/batch-submit     DeliverableController@batchSubmit
POST /deliverables/{id}/batch-revisions  DeliverableController@batchRevisions

# Admin routes — protected by AdminMiddleware (admin + owner only)
GET  /admin/settings                UserController@settings
resource /admin/users               UserController (full CRUD)
resource /admin/subtask-types       SubtaskTypeController (index, store, destroy only)

POST /notifications/mark-all-read
POST /notifications/archive-all
```

---

## Middleware

**`AdminMiddleware`** (`app/Http/Middleware/AdminMiddleware.php`)
- Registered as alias `admin` in `bootstrap/app.php`
- Applied to the entire `/admin/*` route group in `routes/web.php`
- Returns 403 for any authenticated non-admin user

---

## File Uploads

| Type | Storage Path |
|---|---|
| Brand logos | `storage/brand_logos` |
| Project briefs | `storage/briefs` |
| Reference files | `storage/references` |
| Final designs | `storage/deliveries` |

Uses Laravel's `public` disk. Run `php artisan storage:link` if uploads aren't serving.

---

## Subtask Logic

- Deliverables can have one level of children via `parent_deliverable_id`
- **1 subtask submitted** → consolidated into a single standalone deliverable (no parent created)
- **2+ subtasks** → parent deliverable created + N child deliverables
- Subtask types are managed at `/admin/subtask-types` and are workflow-type-specific

---

## Seeded Default Credentials

| Account | Email | Password |
|---|---|---|
| Owner (default admin) | admin@loops.com | password |
| Role test users | `{roleslug}{1|2}@loops.com` | password |

Examples: `writer1@loops.com`, `brandmanager2@loops.com`, `designer1@loops.com`

---

## Common Development Commands

```bash
php artisan migrate              # Run pending migrations
php artisan migrate:fresh --seed # Wipe and reseed (dev only)
php artisan db:seed              # Run seeders on existing DB
php artisan storage:link         # Link public storage disk
php artisan route:list           # List all routes
php artisan tinker               # REPL for the app
npm run dev                      # Start Vite dev server (Tailwind)
npm run build                    # Build assets for production
```

---

## Important Notes for Development

1. **Workflow type is set at project creation and drives everything.** Changing `workflow_type` on an existing project after deliverables have been created will break their stage progression — the `approval_stage` values won't match `STAGES` or `CAMPAIGN_STAGES`.

2. **`tasks` → `deliverables` rename.** The database table was renamed in migration `2026_04_17_050908_rename_tasks_to_deliverables`. Some older migrations still reference the `tasks` table name — this is expected and correct for those historical migrations.

3. **Project members vs. role assignees.** `project_user` (many-to-many) tracks who is on the project team. The `writer_id`, `approver_id`, etc. FK columns are the *default assignees* for deliverable stage routing. Deliverables can override these with their own per-deliverable assignee fields.

4. **`is_ready` flag.** Set automatically when a deliverable's stage is submitted. Used by batch operations to check whether all subtasks are ready before batch-advancing.

5. **Admin navigation link** in `components/navigation.blade.php` is gated with `@if(auth()->user()->isAdmin())` — only visible to Admin/Owner.

6. **`@can('create-deliverable')`** in `projects/show.blade.php` gates the "New Deliverable" button. Only Brand Managers (and admins) see it.

7. **No password reset flow exists.** The "Forgot password?" link on the login page is a placeholder. Admins must reset passwords via `/admin/users/{user}/edit`.

8. **Migration table was historically out of sync.** Migrations prior to batch 2 were marked manually via `tinker` on 2026-05-20. If running `migrate:fresh`, the full history will run cleanly from scratch.
