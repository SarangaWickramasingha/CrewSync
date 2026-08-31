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
   have no min/max filter. Only displayed in results.
4. No pagination - both search endpoints return ALL matching results with no
   LIMIT/OFFSET. No server-side pagination exists.
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
   - But ProviderController::getPublicProfile() reads the stale stored column.
   - RESULT: Public provider profile shows outdated rating while search
     results show correct one.

2. Property owner review submission is localStorage-only.
   - WriteReviewModal uses a HARDCODED provider list (4 static names).
   - handleAddReview() stores to localStorage, NOT to the database.
   - No backend API call is made. No INSERT INTO reviews exists in PHP.
   - Reviews are LOST on browser clear and never appear in the database.
   - Providers only see seed-data reviews, never actual user-submitted ones.

3. No backend review creation endpoint exists.
   - The entire PHP codebase has zero INSERT INTO reviews.
   - The only review data comes from sampledata.sql seed data.
   - The rating/review write path is fundamentally incomplete.

4. Notification creation is frontend-only (property owner side only).
   - All event triggers originate from property owner frontend TasksContext.
   - Backend events do NOT trigger notifications:
     * Service request accept/reject -> no notification to owner
     * Order status changes -> no notification to owner
     * New reviews -> no notification to provider or owner
     * Material orders -> no backend order is created at all

MODERATE ISSUES:
5. provider_reply column queried but not in schema.
   - ProviderController::getAllReviews() runs SHOW COLUMNS to check for
     provider_reply, but it is NOT in crewsync_db_final.sql schema.
   - Will always be false unless manually added to the database.

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

CRITICAL:
1. Material ordering is NOT fully implemented.
   - RequestMaterialModal only creates a local notification.
   - No POST /api/material-orders endpoint exists in backend.
   - SupplierController has getOrders() and updateOrderStatus() but NO
     createOrder() method.
   - Supplier Orders page will always be empty unless data is manually
     inserted into the material_orders table.

2. Material request modal sends misleading "Your request has been sent"
   notification but no request is actually sent to the backend.

MEDIUM:
3. Property owners have no "My Requests" page to see status of sent
   service requests (accepted/rejected/expired).
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
6. No CSV or Excel export functionality.
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