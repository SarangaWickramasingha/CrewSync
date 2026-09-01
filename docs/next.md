`=============================================
 ISSUES FOUND DURING FINAL REPORT REVIEW
=============================================

--- 3.4 Search and Filtering Module ---

MISSING FEATURES (not implemented in code):
1.~~ _No city filter - city is collected in supplier registration UI but never
  saved to DB or used in search. users table has no city column. -done
2. No rating filter - rating is only used for sorting (ORDER BY avg_rating DESC),
   not for filtering. Users cannot filter by min/max rating.~~- done
3. No price range filter - charge_per_day (providers) and unit_price (materials)
   have no min/max filter. Only displayed in results.-done
4. No pagination - both search endpoints return ALL matching results with no
   LIMIT/OFFSET. No server-side pagination exists.-done
5. No sorting options - sorting is hardcoded by avg_rating DESC. Users cannot 
   choose to sort by price, name, or distance.
6. No full-text search index - provider text search uses LIKE %term% which is
   slow on large datasets and cannot use indexes. MySQL FULLTEXT indexes
   are not used.
7. No free-text search for materials - service provider search has a q parameter
   for text search by name/skill. Materials search has no equivalent.

--- 3.5 Ratings, Reviews, and Notifications Module ---

CRITICAL BUGS:
1. avg_rating column NEVER updated programmatically.
   - service_providers.avg_rating and supplier_profiles.avg_rating are only
     set by seed data in sampledata.sql.
   - SearchController computes live AVG(r.rating) from reviews table.
   - FIXED: ProviderController::getPublicProfile() now computes the live average
     directly from the reviews table (same as search + dashboard), instead of
     reading the stale stored service_providers.avg_rating column. Verified:
     provider 1 now returns 4.7 (was 4.50), provider 3 returns 4.0 (was 4.80).
   - NOTE: supplier_profiles.avg_rating is still seed-only. The reviews table
     only references service_providers (provider_id FK), so there is no live
     supplier rating source to reconcile against. Add supplier reviews + a
     live AVG source if supplier ratings are needed.

2. Property owner review submission is localStorage-only. - DONE (implemented)
   - ✅ WriteReviewModal now uses a DYNAMIC provider list fetched from
     GET /api/reviews/assigned-providers (providers assigned to the owner's
     project tasks) instead of 4 hardcoded names.
   - ✅ handleAddReview() now POSTs to backend (POST /api/reviews) and only
     adds to the list on success; no longer depends on localStorage.
   - ✅ Backend ReviewController::create() INSERTs into the reviews table.
   - ✅ Reviews persist in the DB and providers see user-submitted ones.
   - ✅ Owner can only review providers assigned to a task on their projects (403
     otherwise); duplicate reviews blocked (409).

3. No backend review creation endpoint exists. - DONE (implemented)
   - ✅ POST /api/reviews (ReviewController::create) — property_owner role.
   - ✅ GET /api/reviews/mine — owner's own submitted reviews.
   - ✅ GET /api/reviews/assigned-providers — providers assigned to owner's tasks.
   - ✅ service_providers.avg_rating recomputed and persisted after each insert.
   - ✅ Verified end-to-end (Kamala → provider 2, avg_rating 4.20 → 4.00; review
     then visible to that provider's recent-reviews view; all cleaned back up).

4. Notification creation is frontend-only (property owner side only). - DONE (implemented)
   - ✅ Added shared helper `backend/helpers/notify.php::notify_user($db,$userId,$title,$msg)`
     that inserts a notification row for ANY user_id (works cross-user, not just auth).
   - ✅ Backend events now create notifications for the correct recipient:
     * Service request accept/reject (ProviderController::respondToJobRequest) -> notify owner
       ("<Provider> accepted/declined your request for <Task>")
     * Order status changes (SupplierController::updateOrderStatus) -> notify owner
       ("Your order for <Material> has been accepted/declined/delivered")
     * New reviews (ReviewController::create) -> notify provider
       ("<Owner> left you a N-star review")
     * Material orders (SupplierController::createOrder) -> notify supplier
       ("<Owner> ordered <Material> × qty")
   - ✅ Provider + supplier notification UI added: shared `NotificationsPage` component,
     React Query hooks (`src/hooks/useNotifications.js`), pages at
     /dashboard/serviceprovider/notifications and /dashboard/supplier/notifications,
     and sidebar nav items with unread-count badges in app/dashboard/layout.jsx.
   - ✅ Verified end-to-end (all 4 flows) then cleaned up test data.

MODERATE ISSUES:
5. provider_reply column queried but not in schema.
   - ProviderController::getAllReviews() runs SHOW COLUMNS to check for
     provider_reply, but it is NOT in crewsync_db_final.sql schema.
   - Will always be false unless manually added to the database.✅ done no needed

6. Admin Reviews tab is a dead stub.
   - src/components/admin/admin-tabs/Reviews.jsx returns hardcoded [].
   - Has a TODO comment. The actual admin reviews page works via a
     different route (app/dashboard/admin/reviews/page.jsx).

7. RequestServiceModal triggers notification locally but does not reflect
   backend response. Notification says "Request sent to..." but is a
   side-effect of context update, not from the backend.

8. Notification time formatting is broken.
   - NotificationController::getNotifications() hardcodes "Today," prefix
     regardless of when the notification was created.
   - Yesterday's or last week's notifications all display "Today, ...".

9. No notification system for service providers or material suppliers.
   - Provider sidebar and supplier sidebar have no Notifications link.
   - Only property owners have a notifications page.

10. Reviews only visible to providers from seed data.
    - Provider reviews page queries reviews table (seed data only).
    - Property owner writes reviews to localStorage (never to DB).
    - These two systems are completely disconnected.

SECURITY:
11. XSS vulnerability in notifications page.
    - page.jsx uses dangerouslySetInnerHTML to render notification messages.
    - Notification text contains HTML (strong tags, anchor tags).
    - Backend stores HTML as-is in the message TEXT column.

--- 3.3 Service and Material Booking Module ---

IMPLEMENTED:
1. Material ordering is now fully implemented.
   - POST /api/supplier/orders → SupplierController::createOrder() creates a
     pending order (owner role, resolves owner_id, validates supplier_material_id,
     checks stock & duplicate pending order, computes total_cost = unit_price × qty).
   - RequestMaterialModal now calls the backend API and only shows "Request Sent"
     on success; errors (over-stock, duplicate, unavailable) are displayed inline.
   - Resulting orders appear in the Supplier Orders page via getOrders().
   ✅ Verified end-to-end (created order #ORD-004, visible to supplier, then cleaned up).

REMAINING / MEDIUM:
3. Property owners have no "My Requests" page to see status of sent
   material/service requests (accepted/rejected/expired). Owners receive a local
   notification when the order is placed, but there is no owner-facing status UI.
4. Service request auto-expiration (72 hours) not mentioned in report.

--- 3.6 Documentation and Reporting Module ---

ACCURATE CLAIMS:
- Backend aggregates data for reports ✅ (Report.php taskFacts/projectFacts)
- Reports include project costs and task data ✅
- Property owners can download PDF reports ✅
- Backend uses FPDF library for PDF generation ✅

INACCURATE/EXAGGERATED CLAIMS:
1. "ongoing and completed projects" - Reports are ONLY available for
   COMPLETED tasks (is_finished=1) and COMPLETED projects.
   Ongoing projects cannot generate reports.

2. "at each project stage" - Reports are only generated post-completion,
   not at each stage. Incomplete items show "Pending" status.

3. "financial transparency" - No financial-specific reports exist.
   Database schema originally had ENUM('financial') but was narrowed
   to just 'task' and 'project'. No expense reports or budget
   tracking reports as distinct features.

4. "summaries and reports" (implied variety) - Only 2 report types exist:
   - Task Report (single task: dates, worked days, providers, budget vs cost)
   - Project Report (project overview + per-task breakdown table)

5. Frontend lists "Project Cost Summary Report" as separate entry but it
   is a DUPLICATE of "Full Project Completion Report" (same type:'project',
   same API call, same PDF output).

MISSING FEATURES:
6. No CSV or Excel export functionality.- no needed.
7. No print functionality (no window.print, no print-friendly CSS).
8. No financial/expense report type.
9. No "general" report type (was in original schema ENUM but removed).
10. docs/docRM.txt is empty (0 lines) - documentation was planned but
    never written.

NOTES:
- PDF files are stored in backend/reports/ directory
- 4 actual PDF files exist (2 task, 2 project reports)
- Reports include CREWSYNC branding, page numbers, "Generated by" footer
- Task reports show budget vs actual cost comparison (red if over, green if under)
- Report caching: backend checks for existing PDF before regenerating

--- 3.7 Interface and Navigation ---

FABRICATED COMPONENT NAMES (all 3 cited examples DO NOT EXIST):
1. "TaskTimelineCard" - DOES NOT EXIST anywhere in codebase.
   Actual component: ProjectTimelineCard
   (src/components/serviceProvider/ProjectTimelineCard.jsx)

2. "ProviderProfile" - DOES NOT EXIST as a reusable component.
   Actual: ServiceProviderProfilePage (a PAGE, not a component)
   (app/dashboard/serviceprovider/profile/page.jsx)
   Also: ServiceProviderCard (a card component)
   (src/components/propertyOwner/ServiceProviderCard.jsx)

3. "BookingModal" - DOES NOT EXIST anywhere in codebase.
   Actual component: RequestServiceModal
   (src/components/propertyOwner/RequestServiceModal.jsx)

ACCURATE CLAIMS:
4. "developed using Next.js" - Confirmed: Next.js 16.3.0 with App Router
5. "styled with Tailwind CSS" - Confirmed: Tailwind CSS v4.3.1
6. "Routing handled natively by Next.js" - Confirmed: App Router with
   file-system routing, useRouter, Link from next/navigation

PARTIALLY ACCURATE:
7. "responsive...experience across laptops and mobile devices"
   - 23 responsive breakpoint instances found (sm:, md:, lg:)
   - Hamburger menus exist for mobile on all 3 sidebar implementations
   - BUT: Some components use ONLY inline styles with NO responsive
     breakpoints (ProjectTimelineCard, ServiceProviderProfilePage)
   - No xl: or 2xl: breakpoints used anywhere
   - Mix of Tailwind classes and inline styles undermines consistency

8. "accessible" - WEAK accessibility implementation:
   - Only 8 aria-label instances in entire codebase
   - ZERO ARIA roles (no role="button", role="dialog", role="navigation")
   - No focus trap in any modal component
   - Only 5 keyboard navigation instances (onKeyDown/tabIndex)
   - Most <label> elements lack htmlFor attributes
   - No skip navigation links for keyboard users

9. "modularized into reusable elements"
   - UI primitives are genuinely reusable: StatusPill, PasswordInput,
     DistrictSelect (used across multiple files)
   - Domain components exist but are role-specific, not cross-domain
   - The 3 specific examples cited in the report are fabricated

NOT MENTIONED IN REPORT (but exists in code):
- State management: TanStack React Query v5.101.4 for server state
- React Context for auth state (AuthContext) and task state (TasksContext)
- 57 component files across 8 subdirectories
- 41 routes/pages
- 4 role-specific sidebar implementations
- Multi-step registration form (6 step components)
- Navbar with 5 context-specific variants

--- General ---

1. Inconsistent terminology: "clients" vs "Property owners" used interchangeably.
2. Reports claims are overstated for partially-implemented features.