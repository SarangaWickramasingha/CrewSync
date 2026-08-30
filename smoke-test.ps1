# CrewSync Backend Smoke Test  (PowerShell 5.1+)
# Hits every API endpoint and prints the HTTP status code.
# Protected endpoints are tested as the matching role IF a login for that
# role succeeds; otherwise they are reported as SKIP.
#
# Usage:
#   powershell -ExecutionPolicy Bypass -File smoke-test.ps1
#
# Before running, set the test credentials in $Accounts below.

$BaseUrl = 'https://crewsync-1sis.onrender.com'

# ── TEST ACCOUNTS ─────────────────────────────────────────────────────────────
# Fill in ONE real account per role you want to smoke-test.
# Roles marked by their API role string. Leave password empty to skip that role.
$Accounts = [ordered]@{
    property_owner    = @{ email = 'owner@example.com';    password = '' }
    service_provider  = @{ email = 'provider@example.com'; password = '' }
    material_supplier = @{ email = 'supplier@example.com'; password = '' }
    admin             = @{ email = 'admin@example.com';    password = '' }
}

# ── Candidate user IDs used inside URL paths (placeholder; replace as needed) ──
$SampleIds = @{
    projectId = 1
    taskId    = 1
    userId    = 1
    reviewId  = 1
    photoId   = 1
    requestId = 1
    notifId   = 1
    skillId   = 1
    productId = 1
    orderId   = 1
    feedbackId = 1
}

# Expand a path like /api/projects/{projectId} using the sample ids
function Expand-Path([string]$path) {
    foreach ($k in $SampleIds.Keys) {
        $path = $path -replace "\{$k\}", ([string]$SampleIds[$k])
    }
    return $path
}

# ── LOGIN: returns a WebSession carrying the auth cookie, or $null on failure ──
function Test-Login([string]$email, [string]$password) {
    if (-not $email -or -not $password) { return $null }
    $body = @{ email = $email; password = $password } | ConvertTo-Json
    $session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    try {
        $resp = Invoke-WebRequest -Uri "$BaseUrl/api/auth/login" `
            -Method Post -ContentType 'application/json' -Body $body `
            -WebSession $session -UseBasicParsing -ErrorAction Stop
        # cookie is stored in $session automatically by Invoke-WebRequest
        return $session
    } catch {
        return $null
    }
}

# ── Make ONE request and return a result record ───────────────────────────────
function Invoke-Endpoint([string]$method, [string]$path, $session, [string]$bodyJson, [hashtable]$query) {
    $url = "$BaseUrl$(Expand-Path $path)"
    if ($query -and $query.Count -gt 0) {
        $pairs = foreach ($k in $query.Keys) { "$($k)=$([uri]::EscapeDataString("$($query[$k])"))" }
        $url += '?' + ($pairs -join '&')
    }

    $params = @{
        Uri = $url
        Method = $method
        UseBasicParsing = $true
        ErrorAction = 'SilentlyContinue'
        WebSession = $session
    }
    if ($null -ne $session) { $params.WebSession = $session }
    # always send empty session if none so unauthenticated calls behave
    if (-not $session) { $params.WebSession = (New-Object Microsoft.PowerShell.Commands.WebRequestSession) }
    if ($bodyJson) {
        $params.ContentType = 'application/json'
        $params.Body = $bodyJson
    }

    try {
        $r = Invoke-WebRequest @params
        return [pscustomobject]@{ Code = [int]$r.StatusCode; Ok = $true }
    } catch {
        $code = try { [int]$_.Exception.Response.StatusCode.value__ } catch { 0 }
        return [pscustomobject]@{ Code = $code; Ok = $false }
    }
}

# ── Endpoint manifest ─────────────────────────────────────────────────────────
# role: 'public' | role-string | $null(skip-login tmp)
# body: JSON string or $null   query: hashtable or $null
$Endpoints = @(
    # AUTH (public)
    @{ m='POST'; p='/api/auth/send-otp';      role='public'; body='{"email":"smoke@example.com"}'; q=$null },
    @{ m='POST'; p='/api/auth/check-email';   role='public'; body='{"email":"smoke@example.com"}'; q=$null },
    @{ m='GET';  p='/api/auth/me';            role='property_owner'; body=$null; q=$null },
    @{ m='POST'; p='/api/auth/logout';        role='public'; body=$null; q=$null },
    @{ m='POST'; p='/api/auth/forgot-password/send-otp'; role='public'; body='{"email":"smoke@example.com"}'; q=$null },
    @{ m='POST'; p='/api/auth/forgot-password/reset';    role='public'; body='{"email":"smoke@example.com","otp":"123456","newPassword":"newpassword123"}'; q=$null },

    # PROJECTS (property_owner)
    @{ m='GET';  p='/api/projects';                 role='property_owner'; body=$null; q=$null },
    @{ m='GET';  p='/api/projects/{projectId}';     role='property_owner'; body=$null; q=$null },
    @{ m='POST'; p='/api/projects/create';          role='property_owner'; body='{"title":"Smoke","total_budget":1000,"start_date":"2026-09-01","target_end_date":"2026-10-01","district":"Colombo","address":"1 Main St","tasks":["Piling"]}'; q=$null },
    @{ m='PUT';  p='/api/projects/{projectId}/toggle-finish'; role='property_owner'; body=$null; q=$null },

    # TASKS (property_owner)
    @{ m='GET';  p='/api/tasks/unassigned';     role='property_owner'; body=$null; q=$null },
    @{ m='POST'; p='/api/tasks';                role='property_owner'; body='{"project_id":"{projectId}","task_name":"Smoke Task"}'; q=$null },
    @{ m='PUT';  p='/api/tasks/{taskId}';       role='property_owner'; body='{"task_name":"Updated"}'; q=$null },
    @{ m='PUT';  p='/api/tasks/{taskId}/toggle-finish'; role='property_owner'; body=$null; q=$null },
    @{ m='PUT';  p='/api/tasks/{taskId}/daily-status';  role='property_owner'; body='{"statuses":[{"date":"2026-09-01","status":"in_progress"}]}'; q=$null },
    @{ m='DELETE'; p='/api/tasks/{taskId}';     role='property_owner'; body=$null; q=$null },

    # COMMENTS (owner / assigned provider / admin)
    @{ m='GET';  p='/api/projects/{projectId}/comments'; role='property_owner'; body=$null; q=$null },
    @{ m='POST'; p='/api/projects/{projectId}/comments'; role='property_owner'; body='{"comment":"Smoke comment"}'; q=$null },

    # SERVICE REQUESTS (property_owner)
    @{ m='POST'; p='/api/service-requests'; role='property_owner'; body='{"provider_id":1,"task_id":"{taskId}"}'; q=$null },

    # STATS (public)
    @{ m='GET'; p='/api/stats/summary'; role='public'; body=$null; q=$null },

    # REPORTS (property_owner)
    @{ m='GET';  p='/api/reports/project/{projectId}';         role='property_owner'; body=$null; q=$null },
    @{ m='POST'; p='/api/reports/task/{taskId}/generate';      role='property_owner'; body=$null; q=$null },
    @{ m='POST'; p='/api/reports/project/{projectId}/generate'; role='property_owner'; body=$null; q=$null },

    # ADMIN (admin)
    @{ m='GET';    p='/api/admin/stats';             role='admin'; body=$null; q=$null },
    @{ m='GET';    p='/api/admin/users';             role='admin'; body=$null; q=$null },
    @{ m='GET';    p='/api/admin/users/{userId}';    role='admin'; body=$null; q=$null },
    @{ m='POST';   p='/api/admin/users';             role='admin'; body='{"fname":"Smoke","lname":"User","email":"smoke@example.com","password":"password123","contact_no":"0770000000","district":"Colombo","role":"property_owner"}'; q=$null },
    @{ m='PUT';    p='/api/admin/users/{userId}';    role='admin'; body='{"contact_no":"0771111111"}'; q=$null },
    @{ m='DELETE'; p='/api/admin/users/{userId}';    role='admin'; body=$null; q=$null },
    @{ m='GET';    p='/api/admin/users/property-owners';   role='admin'; body=$null; q=$null },
    @{ m='GET';    p='/api/admin/users/service-providers'; role='admin'; body=$null; q=$null },
    @{ m='GET';    p='/api/admin/users/material-suppliers'; role='admin'; body=$null; q=$null },
    @{ m='GET';    p='/api/admin/reviews';           role='admin'; body=$null; q=$null },
    @{ m='DELETE'; p='/api/admin/reviews/{reviewId}'; role='admin'; body=$null; q=$null },
    @{ m='GET';    p='/api/admin/feedback';          role='admin'; body=$null; q=$null },
    @{ m='PUT';    p='/api/admin/feedback/{feedbackId}'; role='admin'; body='{"is_handled":1}'; q=$null },
    @{ m='GET';    p='/api/admin/projects';          role='admin'; body=$null; q=$null },
    @{ m='GET';    p='/api/admin/projects/{projectId}'; role='admin'; body=$null; q=$null },

    # SERVICE PROVIDER
    @{ m='PUT';    p='/api/provider/toggle-availability'; role='service_provider'; body=$null; q=$null },
    @{ m='GET';    p='/api/provider/availability';        role='service_provider'; body=$null; q=$null },
    @{ m='GET';    p='/api/provider/dashboard-stats';     role='service_provider'; body=$null; q=$null },
    @{ m='GET';    p='/api/provider/current-work';        role='service_provider'; body=$null; q=$null },
    @{ m='GET';    p='/api/provider/recent-reviews';      role='service_provider'; body=$null; q=$null },
    @{ m='GET';    p='/api/provider/job-requests';        role='service_provider'; body=$null; q=$null },
    @{ m='PUT';    p='/api/provider/job-requests/{requestId}/respond'; role='service_provider'; body='{"action":"accept"}'; q=$null },
    @{ m='GET';    p='/api/provider/timeline';            role='service_provider'; body=$null; q=$null },
    @{ m='GET';    p='/api/provider/reviews/all';         role='service_provider'; body=$null; q=$null },
    @{ m='GET';    p='/api/provider/profile';             role='service_provider'; body=$null; q=$null },
    @{ m='PUT';    p='/api/provider/profile';             role='service_provider'; body='{"full_name":"Smoke Provider","district":"Colombo"}'; q=$null },
    @{ m='POST';   p='/api/provider/skills';              role='service_provider'; body='{"skill_id":1}'; q=$null },
    @{ m='DELETE'; p='/api/provider/skills/{skillId}';    role='service_provider'; body=$null; q=$null },
    @{ m='GET';    p='/api/providers/{providerId}';       role='public'; body=$null; q=$null },
    # note: photos = multipart, skipped in this smoke test (no file)
    @{ m='DELETE'; p='/api/review-photos/{photoId}';      role='service_provider'; body=$null; q=$null },

    # MATERIAL SUPPLIER
    @{ m='GET';    p='/api/supplier/products';                role='material_supplier'; body=$null; q=$null },
    @{ m='POST';   p='/api/supplier/products';                role='material_supplier'; body='{"material_id":1,"unit_price":100}'; q=$null },
    @{ m='DELETE'; p='/api/supplier/products/{productId}';    role='material_supplier'; body=$null; q=$null },
    @{ m='GET';    p='/api/supplier/orders';                  role='material_supplier'; body=$null; q=$null },
    @{ m='PUT';    p='/api/supplier/orders/{orderId}/status'; role='material_supplier'; body='{"status":"accepted"}'; q=$null },
    @{ m='GET';    p='/api/supplier/profile';                 role='material_supplier'; body=$null; q=$null },
    @{ m='PUT';    p='/api/supplier/profile';                 role='material_supplier'; body='{"section":"personal","data":{"firstName":"A","lastName":"B","contactNumber":"0770000000","district":"Colombo"}}'; q=$null },

    # SEARCH (public)
    @{ m='GET'; p='/api/search/providers'; role='public'; body=$null; q=@{ district='Colombo' } },
    @{ m='GET'; p='/api/search/materials'; role='public'; body=$null; q=@{ district='Colombo' } },

    # FEEDBACK
    @{ m='POST'; p='/api/feedback/submit'; role='public'; body='{"message":"Smoke feedback","name":"Smoke","email":"smoke@example.com"}'; q=$null },
    @{ m='GET';  p='/api/feedback';        role='public'; body=$null; q=$null },
    @{ m='PUT';  p='/api/feedback/status'; role='public'; body='{"feedback_id":1,"is_handled":1}'; q=$null },

    # NOTIFICATIONS (any logged-in)
    @{ m='GET';    p='/api/notifications';            role='property_owner'; body=$null; q=$null },
    @{ m='POST';   p='/api/notifications';            role='property_owner'; body='{"text":"Smoke notification"}'; q=$null },
    @{ m='PUT';    p='/api/notifications/read';       role='property_owner'; body='{}'; q=$null },
    @{ m='DELETE'; p='/api/notifications/{notifId}';  role='property_owner'; body=$null; q=$null }
)

# ── Sessions per role ─────────────────────────────────────────────────────────
$Sessions = @{}
$LoginOk = @{}
foreach ($role in $Accounts.Keys) {
    $acc = $Accounts[$role]
    $s = Test-Login $acc.email $acc.password
    if ($s) {
        $Sessions[$role] = $s
        $LoginOk[$role] = $true
        Write-Host "[login OK ] $role  ($($acc.email))" -ForegroundColor Green
    } else {
        $LoginOk[$role] = $false
        $hint = if ($acc.password) { 'login FAILED' } else { 'no credentials' }
        Write-Host "[skip     ] $role  ($hint)" -ForegroundColor Yellow
    }
}

# ── Run ──────────────────────────────────────────────────────────────────────
Write-Host ""
Write-Host ("Endpoint smoke test against: " + $BaseUrl) -ForegroundColor Cyan
Write-Host ""

# sub in body placeholders too
function Resolve-Body([string]$b) {
    foreach ($k in $SampleIds.Keys) { $b = $b -replace "\{$k\}", ([string]$SampleIds[$k]) }
    return $b
}

$pass = 0; $fail = 0; $skip = 0; $total = $Endpoints.Count
$results = foreach ($ep in $Endpoints) {
    $role  = $ep.role
    $method = $ep.m
    $path  = $ep.p

    if ($role -eq 'public') {
        $session = $null
        $okToRun = $true
    } elseif ($LoginOk[$role]) {
        $session = $Sessions[$role]
        $okToRun = $true
    } else {
        $okToRun = $false
    }

    if (-not $okToRun) {
        $skip++
        [pscustomobject]@{ Method=$method; Path=$path; Role=$role; Code='SKIP'; Status='skip' }
        continue
    }

    $body = if ($ep.body) { Resolve-Body $ep.body } else { $null }
    $r = Invoke-Endpoint $method $path $session $body $ep.q
    $status = 'ok'
    if ($r.Code -ge 200 -and $r.Code -lt 300) { $pass++ } else { $fail++; $status = 'FAIL' }
    [pscustomobject]@{ Method=$method; Path=$path; Role=$role; Code=$r.Code; Status=$status }
}

$results | Format-Table -AutoSize Method, Path, Role, Code, Status

Write-Host ""
Write-Host ("TOTAL: $total   PASS: $pass   FAIL: $fail   SKIP: $skip") -ForegroundColor Cyan
if ($fail -gt 0) { Write-Host "Some endpoints returned non-2xx. Check table 'FAIL' rows (may be expected for writes without valid FK data)." -ForegroundColor Yellow }
