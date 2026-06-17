$base = "http://127.0.0.1:8000"
$s = New-Object Microsoft.PowerShell.Commands.WebRequestSession
$results = [System.Collections.ArrayList]@()

function Add-Result($name, $ok, $note = "") {
    [void]$script:results.Add([PSCustomObject]@{ Test = $name; OK = $ok; Note = $note })
}

function Get-Token($html) {
    if ($html -match 'name="_token" value="([^"]+)"') { return $Matches[1] }
    return $null
}

Start-Sleep -Seconds 2

# --- Public GET pages ---
foreach ($path in @('/', '/board', '/report', '/admin/login')) {
    try {
        $r = Invoke-WebRequest "$base$path" -WebSession $s -UseBasicParsing -TimeoutSec 15
        $ok = $r.StatusCode -eq 200
        $note = if ($r.Content -match 'Exception|SQLSTATE|500 Server Error') { 'ERROR in HTML' } else { '' }
        Add-Result "GET $path" ($ok -and $note -eq '') $note
    } catch {
        Add-Result "GET $path" $false $_.Exception.Message
    }
}

# --- Navbar links on home ---
try {
    $home = Invoke-WebRequest "$base/" -WebSession $s -UseBasicParsing
    Add-Result "Navbar: Board link" ($home.Content -match 'href="[^"]+/board"') ""
    Add-Result "Navbar: Report link" ($home.Content -match '/report') ""
    Add-Result "Navbar: Admin link" ($home.Content -match '/admin/login') ""
    Add-Result "Home: Browse Board btn" ($home.Content -match 'Browse Board') ""
    Add-Result "Home: Report btn" ($home.Content -match 'Report Item') ""
    Add-Result "Home: Recent items" ($home.Content -match 'Recent Activity') ""
} catch {
    Add-Result "Home content check" $false $_.Exception.Message
}

# --- Board filters ---
foreach ($q in @('?status=lost', '?status=found', '?search=umbrella', '?sort=asc')) {
    try {
        $r = Invoke-WebRequest "$base/board$q" -WebSession $s -UseBasicParsing
        Add-Result "Board filter $q" ($r.StatusCode -eq 200) ""
    } catch {
        Add-Result "Board filter $q" $false $_.Exception.Message
    }
}

# --- POST report ---
try {
    $form = Invoke-WebRequest "$base/report" -WebSession $s -UseBasicParsing
    $token = Get-Token $form.Content
    $post = Invoke-WebRequest "$base/report" -Method POST -WebSession $s -UseBasicParsing -Body @{
        _token = $token
        title = 'Web Check Item'
        status = 'lost'
        created_at = (Get-Date -Format 'yyyy-MM-ddTHH:mm')
        location = 'Test Building'
        contact_info = '099-000-111'
        description = 'Automated test'
    } -MaximumRedirection 5
    $redirectOk = $post.StatusCode -in 200, 302
    Add-Result "POST /report (submit)" $redirectOk "status $($post.StatusCode)"
    $find = Invoke-WebRequest "$base/board?search=Web+Check" -WebSession $s -UseBasicParsing
    Add-Result "Board shows new item" ($find.Content -match 'Web Check Item') ""
} catch {
    Add-Result "POST /report" $false $_.Exception.Message
}

# --- Admin login + dashboard ---
try {
    $loginPage = Invoke-WebRequest "$base/admin/login" -WebSession $s -UseBasicParsing
    $token = Get-Token $loginPage.Content
    $bad = Invoke-WebRequest "$base/admin/login" -Method POST -WebSession $s -UseBasicParsing -Body @{ _token = $token; password = 'wrong' }
    Add-Result "Admin: wrong password rejected" ($bad.Content -match 'Invalid|denied|error|Access' -or $bad.StatusCode -eq 302) ""
    $loginPage2 = Invoke-WebRequest "$base/admin/login" -WebSession $s -UseBasicParsing
    $token2 = Get-Token $loginPage2.Content
    $adminEmail = $env:CAMPUSFOUND_CHECK_ADMIN_EMAIL
    $adminPassword = $env:CAMPUSFOUND_CHECK_ADMIN_PASSWORD
    if (-not $adminEmail) {
        $adminEmail = Read-Host "CAMPUSFOUND_CHECK_ADMIN_EMAIL"
    }
    if (-not $adminPassword) {
        $adminPassword = Read-Host "CAMPUSFOUND_CHECK_ADMIN_PASSWORD"
    }
    Invoke-WebRequest "$base/admin/login" -Method POST -WebSession $s -UseBasicParsing -Body @{ _token = $token2; email = $adminEmail; password = $adminPassword } | Out-Null
    $dash = Invoke-WebRequest "$base/admin/dashboard" -WebSession $s -UseBasicParsing
    Add-Result "GET /admin/dashboard" ($dash.StatusCode -eq 200) ""
    Add-Result "Dashboard: stats cards" ($dash.Content -match 'Total Reports') ""
    Add-Result "Dashboard: table rows" ($dash.Content -match 'Web Check Item|Blue Student|btn-outline-danger') ""
    Add-Result "Dashboard: logout form" ($dash.Content -match 'admin/logout') ""
    Add-Result "Dashboard: View modal btn" ($dash.Content -match 'data-bs-toggle="modal"') ""
} catch {
    Add-Result "Admin flow" $false $_.Exception.Message
}

# --- Protected route without login ---
try {
    $s2 = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    $r = Invoke-WebRequest "$base/admin/dashboard" -WebSession $s2 -UseBasicParsing -MaximumRedirection 0 -ErrorAction SilentlyContinue
} catch {
    $loc = $_.Exception.Response.Headers.Location
    Add-Result "Dashboard redirects if guest" ($loc -match 'admin/login') $loc
}

# --- Static assets ---
foreach ($asset in @('/assets/bootstrap-5.3.3/css/bootstrap.min.css', '/assets/bootstrap-5.3.3/js/bootstrap.bundle.min.js')) {
    try {
        $a = Invoke-WebRequest "$base$asset" -UseBasicParsing
        Add-Result "Asset $asset" ($a.StatusCode -eq 200) ""
    } catch {
        Add-Result "Asset $asset" $false "missing"
    }
}

# --- API ---
try {
    $apiEmail = $env:LOSTFOUND_CHECK_EMAIL
    $apiPassword = $env:LOSTFOUND_CHECK_PASSWORD
    if (-not $apiEmail) {
        $apiEmail = Read-Host "API check email"
    }
    if (-not $apiPassword) {
        $apiPassword = Read-Host "API check password"
    }
    $apiBody = @{ email = $apiEmail; password = $apiPassword } | ConvertTo-Json
    $login = Invoke-RestMethod "$base/api/login" -Method POST -ContentType 'application/json' -Body $apiBody
    Add-Result "API login" ($null -ne $login.token) ""
    $items = Invoke-RestMethod "$base/api/items"
    Add-Result "API GET items (public)" ($items.Count -ge 1) "$($items.Count) items"
} catch {
    Add-Result "API" $false $_.Exception.Message
}

$results | Format-Table -AutoSize
$failed = @($results | Where-Object { -not $_.OK })
Write-Host "`n=== SUMMARY: $($results.Count) tests, $($failed.Count) failed ==="
if ($failed.Count -gt 0) { $failed | Format-Table -AutoSize }
