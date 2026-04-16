# Re-upload vendor/ skipping dev-only packages
# Runs in background — only uploads, no deletions

$FTP_HOST = "poweyb.ftp.tb-hosting.com"
$FTP_USER = "powerhelpes@powerhelpes"
$FTP_PASS = "RS1kFpcTjQdAWYIz"
$creds    = New-Object System.Net.NetworkCredential($FTP_USER, $FTP_PASS)

# Dev packages to skip (not needed on production web server)
$SKIP = @(
    "nunomaduro/termwind", "nunomaduro/collision",
    "psy/psysh", "phpunit/phpunit", "phpunit/php-code-coverage",
    "phpunit/php-file-iterator", "phpunit/php-invoker",
    "phpunit/php-text-template", "phpunit/php-timer",
    "mockery/mockery", "fakerphp/faker",
    "symfony/yaml", "brianium/paratest",
    "laravel/pint", "laravel/sail"
)

$vendorLocal  = "c:/Users/Usuario/Projects/Invoicing_app/vendor"
$vendorRemote = "laravel_factura/vendor"
$ok = 0; $err = 0; $skip = 0

function Ftp-Exists($remote) {
    try {
        $r = [System.Net.FtpWebRequest]::Create("ftp://$FTP_HOST/$remote")
        $r.Credentials = $creds; $r.Method = [System.Net.WebRequestMethods+Ftp]::GetFileSize
        $r.UsePassive = $true; $r.KeepAlive = $false
        $resp = $r.GetResponse(); $resp.Close(); return $true
    } catch { return $false }
}

function Ftp-MkDir($remote) {
    try {
        $r = [System.Net.FtpWebRequest]::Create("ftp://$FTP_HOST/$remote")
        $r.Credentials = $creds; $r.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $r.UsePassive = $true; $r.UseBinary = $true; $r.KeepAlive = $false
        $r.GetResponse().Close()
    } catch {}
}

function Ftp-Upload($local, $remote) {
    $maxRetries = 3
    for ($i = 1; $i -le $maxRetries; $i++) {
        try {
            $r = [System.Net.FtpWebRequest]::Create("ftp://$FTP_HOST/$remote")
            $r.Credentials = $creds; $r.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $r.UsePassive = $true; $r.UseBinary = $true; $r.KeepAlive = $false
            $r.Timeout = 30000; $r.ReadWriteTimeout = 30000
            $buf = [System.IO.File]::ReadAllBytes($local)
            $r.ContentLength = $buf.Length
            $s = $r.GetRequestStream(); $s.Write($buf, 0, $buf.Length); $s.Close()
            $r.GetResponse().Close()
            return $true
        } catch {
            if ($i -eq $maxRetries) { return $false }
            Start-Sleep -Milliseconds 500
        }
    }
    return $false
}

function Upload-Vendor($localDir, $remoteDir) {
    Ftp-MkDir $remoteDir
    Get-ChildItem $localDir | ForEach-Object {
        $name = $_.Name
        if ($_.PSIsContainer) {
            Upload-Vendor $_.FullName "$remoteDir/$name"
        } else {
            $remote = "$remoteDir/$name"
            # Skip if already exists and same size
            if (Ftp-Exists $remote) {
                $script:skip++
                return
            }
            if (Ftp-Upload $_.FullName $remote) {
                $script:ok++
                if ($script:ok % 100 -eq 0) { Write-Host "  ... $($script:ok) uploaded, $($script:skip) skipped, $($script:err) errors" }
            } else {
                $script:err++
                Write-Host "  ERR $remote"
            }
        }
    }
}

Write-Host "=== Re-uploading vendor/ (skipping dev packages and existing files) ===" -ForegroundColor Cyan

Get-ChildItem $vendorLocal | ForEach-Object {
    $pkg = $_.Name
    # Check top-level vendor packages (e.g. "laravel", "symfony")
    $fullPkg = $pkg

    # Check against skip list
    $shouldSkip = $false
    foreach ($s in $SKIP) {
        if ($s -like "$pkg/*" -or $s -eq $pkg) { $shouldSkip = $true; break }
    }

    if ($_.PSIsContainer) {
        # Check sub-packages (e.g. laravel/prompts)
        $subPkgs = Get-ChildItem $_.FullName -Directory
        $hasAnySkip = $false
        foreach ($sub in $subPkgs) {
            $fullSub = "$pkg/$($sub.Name)"
            $subSkip = $SKIP -contains $fullSub
            if ($subSkip) {
                Write-Host "  SKIP $fullSub (dev)" -ForegroundColor DarkGray
                $script:skip++
            } else {
                Write-Host "  Uploading $fullSub ..." -ForegroundColor White
                Upload-Vendor $sub.FullName "$vendorRemote/$pkg/$($sub.Name)"
            }
        }
        # Also upload root-level files in pkg dir (composer.json etc)
        Get-ChildItem $_.FullName -File | ForEach-Object {
            $remote = "$vendorRemote/$pkg/$($_.Name)"
            if (-not (Ftp-Exists $remote)) {
                Ftp-Upload $_.FullName $remote | Out-Null
            }
        }
    } else {
        # Root vendor files (autoload.php etc)
        $remote = "$vendorRemote/$pkg"
        if (-not (Ftp-Exists $remote)) {
            Ftp-Upload $_.FullName $remote | Out-Null
        }
    }
}

# Always re-upload the patched composer files and the termwind stub
Write-Host "`nRe-uploading patched composer autoloader files..." -ForegroundColor Yellow
$deploy = "c:/Users/Usuario/Projects/Invoicing_app/deploy"
Ftp-Upload "$deploy/../vendor/composer/autoload_real.prod.php"   "$vendorRemote/composer/autoload_real.php"  | Out-Null
Ftp-Upload "$deploy/../vendor/composer/autoload_files.prod.php"  "$vendorRemote/composer/autoload_files.php" | Out-Null
Ftp-Upload "$deploy/../vendor/composer/autoload_static.prod.php" "$vendorRemote/composer/autoload_static.php"| Out-Null
Ftp-Upload "$deploy/../vendor/termwind_stub.php"                  "$vendorRemote/termwind_stub.php"           | Out-Null
Write-Host "Patched autoloader files restored." -ForegroundColor Green

Write-Host "`n=== DONE ===" -ForegroundColor Green
Write-Host "Uploaded: $ok | Skipped (exists): $skip | Errors: $err" -ForegroundColor Cyan
Write-Host "Now visit: https://powerhelp.es/factura/setup.php?token=inmo2025setup"
