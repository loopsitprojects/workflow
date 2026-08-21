# Loops Work — GEMINI.md

## Overview

**Loops Work** is a Laravel 12 creative project management and workflow platform built for marketing and creative agencies. It orchestrates the full lifecycle of deliverables — from initial client brief and copywriting through multi-tier review, design production, client proofing, and final archiving — across client brands using a role-based approval pipeline.

---

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend Framework** | Laravel 12 (PHP 8.2+) |
| **Frontend Templates** | Blade templates + Alpine.js |
| **Styling** | Tailwind CSS + Custom Theme Variables (Dark / Light mode via Alpine store & `localStorage`) |
| **Real-time Events** | Laravel Reverb / Echo (`DeliverablesUpdated` broadcast event) |
| **Authentication** | Laravel session auth (Username or Email login, tokenized invitation registration) |
| **File Storage** | AWS S3 with local disk fallback (`public` disk), S3 presigned URLs for large assets |
| **Database** | MySQL |
| **Notifications** | Laravel database notifications (`notifications` table) |
| **Document Exports** | PhpWord (`.docx`), PhpPresentation (`.pptx`), DomPDF / PDF generation |
| **Avatars** | UI Avatars API |

---

## Directory Structure

```
app/
  Events/
    DeliverablesUpdated.php       — Broadcasts real-time deliverable updates
  Http/
    Controllers/
      Admin/
        MaintenanceController.php — Maintenance mode toggling and messaging
      ArtworkReviewController.php — Client-facing review & annotation portal
      AuthController.php          — User authentication & session management
      BrandController.php         — Brand CRUD, retainer board, member sync
      DashboardController.php     — Role-filtered active task dashboard
      DeliverableController.php   — Workflow state transitions, batch ops, S3 presigning, exports
      ProjectController.php       — Project CRUD, brief uploads, subtask generation
      RegisterController.php      — Tokenized invitation user registration
      SettingsController.php      — User profile & password settings
      SubtaskTypeController.php   — Subtask taxonomy management
      UserController.php          — User CRUD, invitation links, admin settings
    Middleware/
      AdminMiddleware.php         — Enforces Admin/Owner access on /admin/*
      CheckMaintenanceMode.php    — Displays 503 maintenance screen to non-admins
    Requests/
      StoreDeliverableRequest.php — Form validation for deliverable creation
  Models/
    ArtworkAnnotation.php         — Pin, drawing, and text annotations on client proofs
    ArtworkReview.php             — Tokenized client review sessions
    Brand.php                     — Client brands (route key: `slug`)
    Deliverable.php               — Core deliverable engine (stages, progress, relationships)
    DeliverableApproval.php       — Stage approval audit trail
    DeliverableReassignment.php   — Reassignment audit log
    DeliverableRevision.php       — Revision requests & markup image attachments
    Invitation.php                — 7-day role onboarding tokens
    Project.php                   — Projects under brands (workflow_type drives stage pipeline)
    SubtaskType.php               — Workflow-specific subtask types (Carousel, Reel, etc.)
    User.php                      — Roles, `isAdmin()`, `avatar_url` accessor
  Notifications/
    ArtworkReviewSubmitted.php    — Alert when client submits proof markup
    BriefUploaded.php             — Alert when project brief is uploaded
    DeliverableUpdated.php        — Generic stage progression & revision alerts
  Providers/
    AppServiceProvider.php        — Gates (`create-deliverable`, admin bypass), HTTPS enforcement

resources/
  css/
    app.css                       — Global styles, design tokens & theme classes
  js/
    app.js, bootstrap.js, echo.js — Alpine initialization, Laravel Echo listener
  views/
    admin/                        — Settings, user management, subtask types
    artwork/                      — Client review tool (`review.blade.php`, `team_view.blade.php`)
    auth/                         — Login (`login.blade.php`), registration (`register.blade.php`)
    brands/                       — Brand views & retainer Kanban board
    components/                   — Layout, nav bar, toast alerts, notifications panel, brand card
    deliverables/                 — Single/batch views, creation, editing, PDF exports
    errors/                       — Custom error templates (`503.blade.php`)
    projects/                     — Project details, deliverable matrix, creation/editing
    settings/                     — User profile settings

database/
  migrations/                     — Full schema definitions
  seeders/
    DatabaseSeeder.php            — Default Admin & role accounts
    BrandDeliverableSeeder.php    — Demonstration brands & deliverables
    SubtaskTypeSeeder.php         — Default retainer & campaign subtask types
```

---

## User Roles & Permissions

Roles are stored directly in `users.role` (string column).

| Role | Primary Stage Responsibility | Can Create Deliverables | Access Scope |
|---|---|---|---|
| **Admin** / **Owner** | All Stages (Bypass) | Yes | Full system & `/admin/*` |
| **Operations Manager** | All Stages / Management | Yes | Full project & brand management |
| **Brand Manager** | Brand Manager / AM/BD / Final Approval | Yes (Gate: `create-deliverable`) | Assigned brands & projects |
| **Approver** / **Approver Coordinator** | Approver / Further Approver / Review | No | Assigned brands & projects |
| **Coordinator** | Coordinator stage | No | Assigned brands & projects |
| **Writer** | Writer / Assignee / Review | Yes (Gate: `create-deliverable`) | Assigned deliverables |
| **Designer** | Designer stage (Artwork uploads) | No | Assigned deliverables |

### Authorization Gates (`AppServiceProvider.php`)
- **Admin Bypass**: `Gate::before()` grants all permissions to users where `$user->isAdmin() === true`.
- **`create-deliverable` Gate**: Allows `Admin`, `Owner`, `Brand Manager`, `Writer`, and `Operations Manager`.

---

## Workflow Engine & Lifecycles

### 1. Retainer Workflow (Multi-Stage Pipeline)
```
Writer → Approver → Further Approver → Brand Manager → Coordinator → Designer → Writer Review → Approver Review → Final Approval → Scheduled → Closed
```
*Note: Depending on deliverable configuration, optional stages (e.g. Further Approver, Writer Review, Scheduled) can be bypassed or assigned dynamically.*

### 2. Campaign / Pitch Workflow (Lean 4-Stage Pipeline)
```
Assignee → AM/BD → Final Approval → Closed
 (10%)     (50%)       (90%)        (100%)
```

### Core Workflow Actions (`DeliverableController.php`)
- **`submitStage()`**: Validates completion requirements, handles artwork uploads if in Designer stage, records an entry in `deliverable_approvals`, advances the stage, and dispatches notifications to the next role.
- **`batchSubmit()`**: Processes the parent deliverable and all associated child subtasks simultaneously within a database transaction.
- **`requestRevisions()`**: Records revision instructions, optional annotated screenshot/markup, logs to `deliverable_revisions`, and routes back to Writer or Designer.
- **`batchRevisions()`**: Pushes revision requests across all child deliverables in a batch.
- **`reassignDesigner()`**: Reassigns active designer with full reason logging in `deliverable_reassignments`.
- **`updateClientStatus()`**: Updates external status (`Sent to Client`, `Client Approved`, `Client Revisions`, etc.).

---

## Client Artwork Review & Annotation System

- **Public Review Link**: Accessible without login via `/artwork-review/{token}`.
- **Annotation Canvas**:
  - Interactive Pin markers with numbered badges.
  - Freehand drawing annotations.
  - Text commentary with coordinate mapping (`x_percent`, `y_percent`).
- **Internal Dashboard**:
  - Located at `/deliverables/{deliverable}/artwork-review`.
  - Creative teams can view annotations, add internal replies, resolve comments, and toggle status.
- **Automatic Status Sync**: Submitting client feedback automatically updates deliverable client status to `Client Revisions` and notifies the Brand Manager.

---

## Key Database Tables

| Table | Purpose |
|---|---|
| `users` | User accounts, roles, credentials, and usernames. |
| `brands` | Client accounts with route key `slug`, logos, and health metrics. |
| `brand_user` | Pivot table for user access to brands. |
| `projects` | Projects under a brand (`workflow_type`: `retainer`, `campaign`, `pitch`). |
| `project_user` | Pivot table for project assignees. |
| `deliverables` | Creative items / subtasks with stage progression, copy, links, and assets. |
| `deliverable_revisions` | History of requested changes, instructions, and markup attachments. |
| `deliverable_approvals` | Audit trail of stage approvals with user IDs, timestamps, and hours spent. |
| `deliverable_reassignments`| Audit log of reassignments between team members. |
| `artwork_reviews` | External proofing sessions with tokenized URLs and expiration timestamps. |
| `artwork_annotations` | Pin, drawing, and text annotations submitted during proofing. |
| `invitations` | Pending role invitations with 7-day expiration tokens. |
| `subtask_types` | Dynamic deliverable types categorized by workflow (`Carousel`, `Static Post`, `Reels`, etc.). |
| `notifications` | Database notifications for workflow alerts and reviews. |

---

## Storage & File Handling

- **Dual Storage Strategy**: S3 is utilized for production assets with automatic local `storage/app/public` fallback.
- **Presigned URLs**: Direct client-to-S3 upload capability via `/presigned-url` for large media files (video/high-res assets).
- **Directory Paths**:
  - Brand Logos: `brand_logos/`
  - Project Briefs: `briefs/`
  - Deliverable References: `references/`
  - Final Artwork: `artwork/`
  - Revision Annotations: `revision_images/`

---

## Maintenance Mode

- Managed from `/admin/settings` (persisted to `storage/app/maintenance.json`).
- When enabled, `CheckMaintenanceMode` middleware displays `resources/views/errors/503.blade.php` to all standard users.
- Admins bypass maintenance mode to perform updates and maintenance.

---

## Key Development Commands

```bash
# Run database migrations
php artisan migrate

# Fresh migration with database seeders (Development only)
php artisan migrate:fresh --seed

# Run database seeders
php artisan db:seed

# Link public storage disk
php artisan storage:link

# List registered application routes
php artisan route:list

# Start Vite asset compilation
npm run dev

# Build production assets
npm run build
```

---

## Code & Architecture Guidelines

1. **Keep Workflow Logic Centralized**: When modifying workflow stages or progress calculation, update `app/Models/Deliverable.php` constants (`STAGES`, `CAMPAIGN_STAGES`) and `app/Http/Controllers/DeliverableController.php`.
2. **Atomic Batch Operations**: Deliverable batch submissions and revisions must always be wrapped in database transactions (`DB::transaction`).
3. **Preserve Fallbacks**: Keep S3 storage operations wrapped in `try-catch` blocks with local public disk fallback.
4. **Real-time Synchronization**: Broadcast `DeliverablesUpdated` when modifying deliverable stage states to keep active team boards synchronized.
