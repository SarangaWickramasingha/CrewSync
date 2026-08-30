# CrewSync API — Postman Testing Endpoints

**Base URL (Render):** `https://crewsync-1sis.onrender.com`

All endpoints below are **relative** to the base URL. So `POST /api/auth/login` = `https://crewsync-1sis.onrender.com/api/auth/login`.

> **Authentication:** Login sets an **httpOnly cookie** (`crewsync_token`). In Postman, enable `withCredentials` and keep the Cookie Jar active so the cookie is sent on subsequent requests. Role-based routes require the logged-in user to have the matching role. All bodies are **JSON** unless noted (one multipart endpoint).

---

## AUTH — public

| Method | Endpoint | Body (JSON) |
|--------|----------|-------------|
| POST | `/api/auth/register` | `email`, `password` (≥8), `role` (`property_owner` / `service_provider` / `material_supplier`), `fname`, `lname`, `contact_no`, `district`, `otp_token`; `service_provider` also needs `charge_per_day`; `material_supplier` also needs `business_name` |
| POST | `/api/auth/check-email` | `email` |
| POST | `/api/auth/send-otp` | `email` |
| POST | `/api/auth/verify-otp` | `email`, `otp` → returns `otp_token` |
| POST | `/api/auth/login` | `email`, `password` |
| GET | `/api/auth/me` | — |
| POST | `/api/auth/logout` | — |
| POST | `/api/auth/forgot-password/send-otp` | `email` |
| POST | `/api/auth/forgot-password/reset` | `email`, `otp`, `newPassword` (≥8 chars) |

**Register flow order:** `send-otp` → `verify-otp` (grab `otp_token`) → `register`.

**Forgot-password flow:** `forgot-password/send-otp` (email must already be registered) → `forgot-password/reset`.

---

## PROJECTS — role: property_owner

| Method | Endpoint | Body (JSON) |
|--------|----------|-------------|
| POST | `/api/projects/create` | `title`, `total_budget`, `start_date`, `target_end_date`, `district`, `address`, `tasks` (array of names), `task_budgets` (object, e.g. `{"Piling":5000}`) |
| GET | `/api/projects` | — |
| GET | `/api/projects/{projectId}` | — |
| PUT | `/api/projects/{projectId}/toggle-finish` | — |

---

## TASKS — role: property_owner

| Method | Endpoint | Body (JSON) |
|--------|----------|-------------|
| POST | `/api/tasks` | `project_id`, `task_name`, `task_budget` (opt) |
| GET | `/api/tasks/unassigned` | — |
| PUT | `/api/tasks/{taskId}` | any of: `task_name`, `add_cost`, `task_budget` |
| PUT | `/api/tasks/{taskId}/toggle-finish` | — |
| PUT | `/api/tasks/{taskId}/daily-status` | `statuses`: `[{"date":"YYYY-MM-DD","status":"not_started"\|"in_progress"\|"done"\|"blocked"}]` |
| DELETE | `/api/tasks/{taskId}` | — |

---

## COMMENTS — any logged-in + owner / assigned-provider / admin

| Method | Endpoint | Body (JSON) |
|--------|----------|-------------|
| GET | `/api/projects/{projectId}/comments` | — |
| POST | `/api/projects/{projectId}/comments` | `comment` |

---

## SERVICE REQUESTS — role: property_owner

| Method | Endpoint | Body (JSON) |
|--------|----------|-------------|
| POST | `/api/service-requests` | `provider_id`, `task_id` (int **or** array, e.g. `5` or `[5,6]`) |

---

## STATS — public

| Method | Endpoint |
|--------|----------|
| GET | `/api/stats/summary` |

---

## REPORTS — role: property_owner

| Method | Endpoint |
|--------|----------|
| GET | `/api/reports/project/{projectId}` |
| POST | `/api/reports/task/{taskId}/generate` |
| POST | `/api/reports/project/{projectId}/generate` |

---

## ADMIN — role: admin

| Method | Endpoint | Body (JSON) |
|--------|----------|-------------|
| GET | `/api/admin/stats` | — |
| GET | `/api/admin/users` | — |
| GET | `/api/admin/users/{userId}` | — |
| POST | `/api/admin/users` | `fname`, `lname`, `email`, `password`, `contact_no`, `district`, `role` |
| PUT | `/api/admin/users/{userId}` | any user fields |
| DELETE | `/api/admin/users/{userId}` | — |
| GET | `/api/admin/users/property-owners` | — |
| GET | `/api/admin/users/service-providers` | — |
| GET | `/api/admin/users/material-suppliers` | — |
| GET | `/api/admin/reviews` | — |
| DELETE | `/api/admin/reviews/{reviewId}` | — |
| GET | `/api/admin/feedback` | — |
| PUT | `/api/admin/feedback/{feedbackId}` | `is_handled` (`0`/`1`) |
| GET | `/api/admin/projects` | — |
| GET | `/api/admin/projects/{projectId}` | — |

---

## SERVICE PROVIDER — role: service_provider (unless noted)

| Method | Endpoint | Body |
|--------|----------|------|
| PUT | `/api/provider/toggle-availability` | — |
| GET | `/api/provider/availability` | — |
| GET | `/api/provider/dashboard-stats` | — |
| GET | `/api/provider/current-work` | — |
| GET | `/api/provider/recent-reviews` | — |
| GET | `/api/provider/job-requests` | — |
| PUT | `/api/provider/job-requests/{requestId}/respond` | `action`: `accept` or `decline` |
| GET | `/api/provider/timeline` | — |
| GET | `/api/provider/reviews/all` | — |
| GET | `/api/provider/profile` | — |
| PUT | `/api/provider/profile` | `full_name`, `district`; opt: `daily_rate`, `bio`, `out_region` |
| POST | `/api/provider/skills` | `skill_id`; opt: `years`, `description` |
| DELETE | `/api/provider/skills/{skillId}` | — |
| GET | `/api/providers/{providerId}` | **public** — no auth |
| POST | `/api/reviews/{reviewId}/photos` | **multipart/form-data** (`$_FILES['photos']`) |
| DELETE | `/api/review-photos/{photoId}` | — |

---

## MATERIAL SUPPLIER — role: material_supplier

| Method | Endpoint | Body (JSON) |
|--------|----------|-------------|
| GET | `/api/supplier/products` | — |
| POST | `/api/supplier/products` | `material_id`, `unit_price` (>0); opt: `stock_qty`, `description`, `is_available` |
| DELETE | `/api/supplier/products/{productId}` | — |
| GET | `/api/supplier/orders` | — |
| PUT | `/api/supplier/orders/{orderId}/status` | `status`: `accepted` / `rejected` / `delivered` |
| GET | `/api/supplier/profile` | — |
| PUT | `/api/supplier/profile` | `section` (`personal` / `business` / `hardware`) + `data` object (keys vary by section) |

---

## SEARCH — public (query params, no body)

| Method | Endpoint | Query Params |
|--------|----------|--------------|
| GET | `/api/search/providers` | `skill_id`, `district`, `q` (all optional) |
| GET | `/api/search/materials` | `material_id`, `district`, `hardware` (all optional) |

---

## FEEDBACK

| Method | Endpoint | Role | Body (JSON) |
|--------|----------|------|-------------|
| POST | `/api/feedback/submit` | any logged-in or guest | `message` (req); `name`/`email` (guests only); opt `message_type` |
| GET | `/api/feedback` | no auth guard | — |
| PUT | `/api/feedback/status` | no auth guard | `feedback_id`, `is_handled` (`0`/`1`) |

---

## NOTIFICATIONS — any logged-in

| Method | Endpoint | Body (JSON) |
|--------|----------|-------------|
| GET | `/api/notifications` | — |
| POST | `/api/notifications` | `text` (req); opt `type` |
| PUT | `/api/notifications/read` | `id` (opt — omit = mark all read) |
| DELETE | `/api/notifications/{notifId}` | — |

---

## Suggested Test Order (happy path)

1. **Register** a property_owner: `send-otp` → `verify-otp` → `register`
2. **Login** → cookie saved → `me` (verify session)
3. Create project → list projects → get one → toggle-finish
4. Create task → daily-status → update → delete
5. Service request → provider responds
6. Admin endpoints (separate admin login)
7. Search + stats (public)
