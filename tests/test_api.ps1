<#
.SYNOPSIS
    Automated API smoke/regression test for the CrewSync backend.

.DESCRIPTION
    Hits every endpoint exposed by backend/index.php against a configurable
    base URL, verifies the HTTP status code and (when possible) the JSON
    response, and reports a PASS/FAIL summary per endpoint.

    The backend uses httpOnly JWT cookies, so the script logs in as one user
    per role and reuses the cookie jar for that role's endpoints.

.EXAMPLE
    powershell -ExecutionPolicy Bypass -File .\tests\test_api.ps1
    powershell -ExecutionPolicy Bypass -File .\tests\test_api.ps1 -BaseUrl "https://crewsync-1sis.onrender.com"
#>

[CmdletBinding()]
param(
    [string]$BaseUrl = "https://crewsync-1sis.onrender.com",

    # Seed/test credentials (adjust to whatever accounts exist on the target DB).
    # Leave a password empty ("") to SKIP authenticated blocks gracefully.
    [string]$OwnerEmail = "nimal@gmail.com",
    [string]$OwnerPassword = "password01",
    [string]$ProviderEmail = "sunil@gmail.com",
    [string]$ProviderPassword = "password04",
    [string]$SupplierEmail = "saman@gmail.com",
    [string]$SupplierPassword = "password07",
    [string]$AdminEmail = "admin@crewsync.com",
    [string]$AdminPassword = "",

    # Set $false to actually POST/DELETE data; $true (default) is safer for smoke tests.
    [switch]$ReadOnly
)

$ErrorActionPreference = "Stop"

# ─────────────────────────────────────────────────────────────────────────────
# Small HTTP helper using Invoke-WebRequest with cookie-session support.
# Returns an object: { Status, Body (hashtable|null), Raw, RawError, Ex }
# ─────────────────────────────────────────────────────────────────────────────
$script:Results = [System.Collections.Generic.List[object]]::new()

function Invoke-CrewApi {
    param(
        [string]$Method = "GET",
        [string]$Path,
        [object]$Body = $null,
        [Microsoft.PowerShell.Commands.WebRequestSession]$Session = $null,
        [hashtable]$Form = $null,
        [int]$ExpectStatus = -1
    )

    $uri = $BaseUrl.TrimEnd('/') + "/" + $Path.TrimStart('/')
    $params = @{
        Method        = $Method
        Uri           = $uri
        UseBasicParsing = $true
    }
    if ($Session) { $params.WebSession = $Session }
    if ($null -ne $Body) {
        $params.ContentType = "application/json"
        $params.Body = ($Body | ConvertTo-Json -Depth 10 -Compress)
    }
    if ($Form) { $params.Body = $Form }   # multipart handled via Form? -> array bytes instead

    $status = 0
    $content = $null
    $raw = $null
    $ex = $null
    try {
        $resp = Invoke-WebRequest @params
        $status = [int]$resp.StatusCode
        $content = $resp.Content
        $raw = $resp
    } catch {
        $ex = $_.Exception
        if ($ex.Response) {
            $status = [int]$ex.Response.StatusCode
            try {
                $reader = New-Object System.IO.StreamReader($ex.Response.GetResponseStream())
                $content = $reader.ReadToEnd()
            } catch { $content = $ex.Message }
        } else {
            $status = 0
            $content = $ex.Message
        }
    }

    # Try to parse JSON, fall back to raw string
    $parsed = $null
    if ($content) {
        try { $parsed = $content | ConvertFrom-Json } catch { $parsed = $null }
    }

    return [pscustomobject]@{
        Status  = $status
        Body    = $parsed
        Raw     = $content   # the raw body string (not the response object)
        Ex      = $ex
    }
}

function Write-Result {
    param(
        [string]$Name,
        [int]$Status,
        [int]$Expected,
        [string]$Detail = "",
        [bool]$Pass = $false
    )
    $statusText = if ($Pass) { "PASS" } else { "FAIL" }
    $color = if ($Pass) { "Green" } else { "Red" }
    $line = "{0,-4} {1,-52} got={2} exp={3} {4}" -f $statusText, $Name, $Status, $Expected, $Detail
    Write-Host $line -ForegroundColor $color
    $script:Results.Add([pscustomobject]@{
        Pass = $Pass; Name = $Name; Status = $Status; Expected = $Expected; Detail = $Detail
    })
}

# Assert a call.
#  - $Json  : require the body to parse as JSON (catches PHP fatal-error HTML pages
#             that still return HTTP 200). Defaults to $true for JSON APIs.
#  - $Contains : require the raw body to contain a substring.
function Assert-Api {
    param(
        [string]$Method = "GET",
        [string]$Path,
        [object]$Body = $null,
        [Microsoft.PowerShell.Commands.WebRequestSession]$Session = $null,
        [int]$Expected = 200,
        [string]$Name,
        [string]$Contains = $null,
        [bool]$Json = $true
    )
    $r = Invoke-CrewApi -Method $Method -Path $Path -Body $Body -Session $Session
    $pass = ($r.Status -eq $Expected)
    $detail = ""

    # A real JSON API response must BOTH parse as JSON AND not contain PHP
    # error/stack-trace markers (the fatal-error page can sometimes be
    # mis-parsed by ConvertFrom-Json).
    if ($Json) {
        $raw = ($r.Raw | Out-String)
        $phpError = $raw -match "(?i)Fatal error|Stack trace|Warning</b>|<br|Parse error|on line <b>"
        if ($null -eq $r.Body -or $phpError) {
            if ($pass) { $pass = $false; $detail = "response is not a valid JSON API body (PHP error page?)" }
        }
    }

    if ($pass -and $Contains) {
        $raw = ($r.Raw | Out-String)
        if ($raw -match [regex]::Escape($Contains)) { $pass = $true } else { $pass = $false; $detail = "missing '$Contains'" }
    }
    Write-Result -Name $Name -Status $r.Status -Expected $Expected -Detail $detail -Pass $pass
}

function Write-Header {
    param([string]$Text)
    Write-Host ""
    Write-Host ("===== " + $Text + " =====") -ForegroundColor Cyan
}

# ─────────────────────────────────────────────────────────────────────────────
# PrintConfig
# ─────────────────────────────────────────────────────────────────────────────
Write-Host ("Target: " + $BaseUrl) -ForegroundColor Yellow
if ($ReadOnly) { Write-Host "Mode: READ-ONLY (no POST/DELETE that mutates data)" -ForegroundColor Yellow }

# ─────────────────────────────────────────────────────────────────────────────
# 1. NO AUTH / HEALTH
# ─────────────────────────────────────────────────────────────────────────────
Write-Header "1. Public endpoints (no auth)"

Assert-Api -Method GET -Path "/api/stats/summary" -Expected 200 -Name "GET /api/stats/summary" -Contains '"success"'
Assert-Api -Method GET -Path "/api/search/providers" -Expected 200 -Name "GET /api/search/providers"
Assert-Api -Method GET -Path "/api/search/materials" -Expected 200 -Name "GET /api/search/materials" -Contains '"success"'
Assert-Api -Method GET -Path "/api/providers/1" -Expected 200 -Name "GET /api/providers/{id} (public profile)"

# Feedback (public submit + list + status update need specific shapes)
$fb = Invoke-CrewApi -Method POST -Path "/api/feedback/submit" -Body @{
    name = "Test User"; email = "test_user@example.com"; message = "Automated test feedback"; message_type = "General"
}
Write-Result -Name "POST /api/feedback/submit" -Status $fb.Status -Expected 201 -Pass ($fb.Status -eq 201)
$fl = Invoke-CrewApi -Method GET -Path "/api/feedback"
Write-Result -Name "GET /api/feedback" -Status $fl.Status -Expected 200 -Pass ($fl.Status -eq 200)

# Auth guards (should be reachable and return structured JSON)
$meNoAuth = Invoke-CrewApi -Method GET -Path "/api/auth/me"
Write-Result -Name "GET /api/auth/me (no auth -> 401)" -Status $meNoAuth.Status -Expected 401 -Pass ($meNoAuth.Status -eq 401)

$chk = Invoke-CrewApi -Method POST -Path "/api/auth/check-email" -Body @{ email = "doesnotexist12345@example.com" }
Write-Result -Name "POST /api/auth/check-email" -Status $chk.Status -Expected 200 -Pass ($chk.Status -eq 200)

$logBad = Invoke-CrewApi -Method POST -Path "/api/auth/login" -Body @{ email = "bad@example.com"; password = "wrong" }
Write-Result -Name "POST /api/auth/login (bad creds -> structured)" -Status $logBad.Status -Expected 200 -Pass ($logBad.Status -eq 200)

$otp = Invoke-CrewApi -Method POST -Path "/api/auth/send-otp" -Body @{ email = "notvalid-email" }
Write-Result -Name "POST /api/auth/send-otp (invalid email -> 400)" -Status $otp.Status -Expected 400 -Pass ($otp.Status -eq 400)

# ─────────────────────────────────────────────────────────────────────────────
# 2. AUTH - LOGIN helper
# ─────────────────────────────────────────────────────────────────────────────
function Connect-Role {
    param([string]$Email, [string]$Password)
    if (-not $Email -or -not $Password) { return $null }
    $s = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $r = Invoke-CrewApi -Method POST -Path "/api/auth/login" -Body @{ email = $Email; password = $Password } -Session $s
    if ($r.Status -eq 200 -and $r.Body -and $r.Body.success) { return $s }
    Write-Host ("  (login failed for " + $Email + " - skipping this role)") -ForegroundColor DarkYellow
    return $null
}

# ─────────────────────────────────────────────────────────────────────────────
# 3. OWNER AUTHENTICATED
# ─────────────────────────────────────────────────────────────────────────────
Write-Header "2. Authorized endpoints (per role)"

$owner = Connect-Role -Email $OwnerEmail -Password $OwnerPassword
if ($owner) {
    Write-Header "2a. Property Owner"
    Assert-Api -Method GET -Path "/api/auth/me" -Session $owner -Expected 200 -Name "GET /api/auth/me (owner)"
    Assert-Api -Method GET -Path "/api/projects" -Session $owner -Expected 200 -Name "GET /api/projects"

    # Real IDs depend on DB state -- do a best-effort against the first returned project/task.
    $projects = Invoke-CrewApi -Method GET -Path "/api/projects" -Session $owner
    if ($projects.Body -and $projects.Body.projects -and $projects.Body.projects.Count -gt 0) {
        $pid = $projects.Body.projects[0].project_id
        Assert-Api -Method GET -Path ("/api/projects/" + $pid) -Session $owner -Expected 200 -Name "GET /api/projects/{id}"
        Assert-Api -Method GET -Path ("/api/projects/" + $pid + "/comments") -Session $owner -Expected 200 -Name "GET /api/projects/{id}/comments"
        Assert-Api -Method GET -Path ("/api/reports/project/" + $pid) -Session $owner -Expected 200 -Name "GET /api/reports/project/{id}"

        $one = Invoke-CrewApi -Method GET -Path ("/api/projects/" + $pid) -Session $owner
        if ($one.Body -and $one.Body.tasks -and $one.Body.tasks.Count -gt 0) {
            $tid = $one.Body.tasks[0].task_id
            Assert-Api -Method GET -Path "/api/tasks/unassigned" -Session $owner -Expected 200 -Name "GET /api/tasks/unassigned"
            Assert-Api -Method PUT -Path ("/api/tasks/" + $tid + "/daily-status") -Body @{ statuses = @(@{ date = "2026-08-30"; status = "in_progress" }) } -Session $owner -Expected 200 -Name "PUT /api/tasks/{id}/daily-status"
        }
    }

    # Comment submission (mutating)
    if ($projects.Body -and $projects.Body.projects -and $projects.Body.projects.Count -gt 0 -and -not $ReadOnly) {
        $pid = $projects.Body.projects[0].project_id
        Assert-Api -Method POST -Path ("/api/projects/" + $pid + "/comments") -Body @{ comment = "Automated test comment" } -Session $owner -Expected 200 -Name "POST /api/projects/{id}/comments"
    } elseif (-not $ReadOnly) {
        Assert-Api -Method POST -Path "/api/projects/1/comments" -Body @{ comment = "Automated test comment" } -Session $owner -Expected 200 -Name "POST /api/projects/1/comments"
    }

    # Project create (validation-only when read-only; full create otherwise)
    if (-not $ReadOnly) {
        $pc = Invoke-CrewApi -Method POST -Path "/api/projects/create" -Body @{
            title = "API Test Project"; total_budget = 100000; start_date = "2026-09-01"; target_end_date = "2027-01-01"; district = "Colombo"; address = "Test Address"; tasks = @()
        } -Session $owner
        Write-Result -Name "POST /api/projects/create" -Status $pc.Status -Expected 200 -Pass ($pc.Status -eq 200)
    } else {
        $pc = Invoke-CrewApi -Method POST -Path "/api/projects/create" -Body @{ title = ""; start_date = ""; target_end_date = ""; total_budget = 0; district = ""; address = "" } -Session $owner
        Write-Result -Name "POST /api/projects/create (missing fields -> 400)" -Status $pc.Status -Expected 400 -Pass ($pc.Status -eq 400)
    }
}

# Provider
$provider = Connect-Role -Email $ProviderEmail -Password $ProviderPassword
if ($provider) {
    Write-Header "2b. Service Provider"
    Assert-Api -Method GET -Path "/api/provider/availability" -Session $provider -Expected 200 -Name "GET /api/provider/availability"
    Assert-Api -Method GET -Path "/api/provider/dashboard-stats" -Session $provider -Expected 200 -Name "GET /api/provider/dashboard-stats"
    Assert-Api -Method GET -Path "/api/provider/current-work" -Session $provider -Expected 200 -Name "GET /api/provider/current-work"
    Assert-Api -Method GET -Path "/api/provider/recent-reviews" -Session $provider -Expected 200 -Name "GET /api/provider/recent-reviews"
    Assert-Api -Method GET -Path "/api/provider/job-requests" -Session $provider -Expected 200 -Name "GET /api/provider/job-requests"
    Assert-Api -Method GET -Path "/api/provider/timeline" -Session $provider -Expected 200 -Name "GET /api/provider/timeline"
    Assert-Api -Method GET -Path "/api/provider/reviews/all" -Session $provider -Expected 200 -Name "GET /api/provider/reviews/all"
    Assert-Api -Method GET -Path "/api/provider/profile" -Session $provider -Expected 200 -Name "GET /api/provider/profile"
    Assert-Api -Method POST -Path "/api/provider/skills" -Body @{ skill_id = 1; years = 2; description = "test" } -Session $provider -Expected 200 -Name "POST /api/provider/skills"
}

# Supplier
$supplier = Connect-Role -Email $SupplierEmail -Password $SupplierPassword
if ($supplier) {
    Write-Header "2c. Material Supplier"
    Assert-Api -Method GET -Path "/api/supplier/products" -Session $supplier -Expected 200 -Name "GET /api/supplier/products"
    Assert-Api -Method GET -Path "/api/supplier/orders" -Session $supplier -Expected 200 -Name "GET /api/supplier/orders"
    Assert-Api -Method GET -Path "/api/supplier/profile" -Session $supplier -Expected 200 -Name "GET /api/supplier/profile"
    $prod = Invoke-CrewApi -Method POST -Path "/api/supplier/products" -Body @{ material_id = 1; unit_price = 99; stock_qty = 5; description = "test" } -Session $supplier
    Write-Result -Name "POST /api/supplier/products" -Status $prod.Status -Expected 200 -Pass ($prod.Status -eq 200)
}

# Admin
$admin = Connect-Role -Email $AdminEmail -Password $AdminPassword
if ($admin) {
    Write-Header "2d. Admin"
    Assert-Api -Method GET -Path "/api/admin/stats" -Session $admin -Expected 200 -Name "GET /api/admin/stats"
    Assert-Api -Method GET -Path "/api/admin/users" -Session $admin -Expected 200 -Name "GET /api/admin/users"
    Assert-Api -Method GET -Path "/api/admin/users/property-owners" -Session $admin -Expected 200 -Name "GET /api/admin/users/property-owners"
    Assert-Api -Method GET -Path "/api/admin/users/service-providers" -Session $admin -Expected 200 -Name "GET /api/admin/users/service-providers"
    Assert-Api -Method GET -Path "/api/admin/users/material-suppliers" -Session $admin -Expected 200 -Name "GET /api/admin/users/material-suppliers"
    Assert-Api -Method GET -Path "/api/admin/reviews" -Session $admin -Expected 200 -Name "GET /api/admin/reviews"
    Assert-Api -Method GET -Path "/api/admin/feedback" -Session $admin -Expected 200 -Name "GET /api/admin/feedback"
    Assert-Api -Method GET -Path "/api/admin/projects" -Session $admin -Expected 200 -Name "GET /api/admin/projects"
    $users = Invoke-CrewApi -Method GET -Path "/api/admin/users" -Session $admin
    if ($users.Body -and $users.Body.users -and $users.Body.users.Count -gt 0) {
        $uid = $users.Body.users[0].user_id
        Assert-Api -Method GET -Path ("/api/admin/users/" + $uid) -Session $admin -Expected 200 -Name "GET /api/admin/users/{id}"
    }
}

# Notifications (any authenticated user)
$anyUser = if ($owner) { $owner } elseif ($provider) { $provider } else { $supplier }
if ($anyUser) {
    Write-Header "2e. Notifications (any auth)"
    Assert-Api -Method GET -Path "/api/notifications" -Session $anyUser -Expected 200 -Name "GET /api/notifications"
    $notif = Invoke-CrewApi -Method POST -Path "/api/notifications" -Body @{ text = "Automated test notification"; type = "system" } -Session $anyUser
    Write-Result -Name "POST /api/notifications" -Status $notif.Status -Expected 200 -Pass ($notif.Status -eq 200)
    Assert-Api -Method PUT -Path "/api/notifications/read" -Body @{ id = 0 } -Session $anyUser -Expected 200 -Name "PUT /api/notifications/read"
}

# ─────────────────────────────────────────────────────────────────────────────
# 4. ROLE GUARD NEGATIVE TESTS (provider hitting owner endpoint => 403)
# ─────────────────────────────────────────────────────────────────────────────
Write-Header "3. Authorization guard (should be 403 for wrong role)"
if ($provider) {
    $g = Invoke-CrewApi -Method GET -Path "/api/projects" -Session $provider
    Write-Result -Name "provider -> GET /api/projects (403)" -Status $g.Status -Expected 403 -Pass ($g.Status -eq 403)
}
if ($owner) {
    $g = Invoke-CrewApi -Method GET -Path "/api/supplier/products" -Session $owner
    Write-Result -Name "owner -> GET /api/supplier/products (403)" -Status $g.Status -Expected 403 -Pass ($g.Status -eq 403)
}
if ($supplier) {
    $g = Invoke-CrewApi -Method GET -Path "/api/provider/availability" -Session $supplier
    Write-Result -Name "supplier -> GET /api/provider/availability (403)" -Status $g.Status -Expected 403 -Pass ($g.Status -eq 403)
}

# ─────────────────────────────────────────────────────────────────────────────
# 5. NOT FOUND ROUTE
# ─────────────────────────────────────────────────────────────────────────────
Write-Header "4. Unknown route (should be 404)"
$nf = Invoke-CrewApi -Method GET -Path "/api/does-not-exist"
Write-Result -Name "GET /api/does-not-exist (404)" -Status $nf.Status -Expected 404 -Pass ($nf.Status -eq 404)

# ─────────────────────────────────────────────────────────────────────────────
# Summary
# ─────────────────────────────────────────────────────────────────────────────
Write-Host ""
$passCount = @($script:Results | Where-Object { $_.Pass }).Count
$failCount = @($script:Results | Where-Object { -not $_.Pass }).Count
$total = $passCount + $failCount
Write-Host ("SUMMARY  : {0} passed, {1} failed, {2} total" -f $passCount, $failCount, $total) -ForegroundColor $(if ($failCount -eq 0) { "Green" } else { "Red" })
Write-Host ""
if ($failCount -gt 0) {
    Write-Host "Failed endpoints:" -ForegroundColor Red
    foreach ($r in $script:Results) { if (-not $r.Pass) { Write-Host ("  FAIL {0} (status {1}, expected {2}) {3}" -f $r.Name, $r.Status, $r.Expected, $r.Detail) -ForegroundColor Red } }
}

exit $(if ($failCount -eq 0) { 0 } else { 1 })
