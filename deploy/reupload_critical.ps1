# Re-upload critical packages that failed in reupload_vendor.ps1
# Focus: nesbot/carbon, monolog/monolog, league/flysystem, masterminds/html5, nette/*

$FTP_HOST = "poweyb.ftp.tb-hosting.com"
$FTP_USER = "powerhelpes@powerhelpes"
$FTP_PASS = "RS1kFpcTjQdAWYIz"
$creds    = New-Object System.Net.NetworkCredential($FTP_USER, $FTP_PASS)

$vendorLocal  = "c:/Users/Usuario/Projects/Invoicing_app/vendor"
$vendorRemote = "laravel_factura/vendor"
$ok = 0; $err = 0; $skip = 0

function Ftp-Exists($remote) {
    try {
        $r = [System.Net.FtpWebRequest]::Create("ftp://$FTP_HOST/$remote")
        $r.Credentials = $creds; $r.Method = [System.Net.WebRequestMethods+Ftp]::GetFileSize
        $r.UsePassive = $true; $r.KeepAlive = $false; $r.Timeout = 10000
        $resp = $r.GetResponse(); $resp.Close(); return $true
    } catch { return $false }
}

function Ftp-MkDir($remote) {
    try {
        $r = [System.Net.FtpWebRequest]::Create("ftp://$FTP_HOST/$remote")
        $r.Credentials = $creds; $r.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $r.UsePassive = $true; $r.UseBinary = $true; $r.KeepAlive = $false; $r.Timeout = 10000
        $r.GetResponse().Close()
    } catch {}
}

function Ftp-Upload($local, $remote) {
    $maxRetries = 5
    for ($i = 1; $i -le $maxRetries; $i++) {
        try {
            $r = [System.Net.FtpWebRequest]::Create("ftp://$FTP_HOST/$remote")
            $r.Credentials = $creds; $r.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $r.UsePassive = $true; $r.UseBinary = $true; $r.KeepAlive = $false
            $r.Timeout = 60000; $r.ReadWriteTimeout = 60000
            $buf = [System.IO.File]::ReadAllBytes($local)
            $r.ContentLength = $buf.Length
            $s = $r.GetRequestStream(); $s.Write($buf, 0, $buf.Length); $s.Close()
            $r.GetResponse().Close()
            return $true
        } catch {
            if ($i -eq $maxRetries) { return $false }
            Start-Sleep -Milliseconds (500 * $i)
        }
    }
    return $false
}

function Upload-Package($localDir, $remoteDir) {
    Ftp-MkDir $remoteDir
    Start-Sleep -Milliseconds 100
    Get-ChildItem $localDir | ForEach-Object {
        $name = $_.Name
        if ($_.PSIsContainer) {
            Upload-Package $_.FullName "$remoteDir/$name"
        } else {
            $remote = "$remoteDir/$name"
            if (Ftp-Exists $remote) {
                $script:skip++
                return
            }
            if (Ftp-Upload $_.FullName $remote) {
                $script:ok++
                if ($script:ok % 50 -eq 0) { Write-Host "  ... $($script:ok) uploaded, $($script:skip) skipped, $($script:err) errors" }
            } else {
                $script:err++
                Write-Host "  ERR $remote" -ForegroundColor Red
            }
        }
    }
}

# Critical production packages that had upload errors
$criticalPkgs = @(
    "nesbot/carbon",
    "carbonphp/carbon-doctrine-types",
    "monolog/monolog",
    "league/flysystem",
    "league/flysystem-local",
    "league/mime-type-detection",
    "league/commonmark",
    "league/config",
    "league/uri",
    "league/uri-interfaces",
    "masterminds/html5",
    "nette/utils",
    "nette/schema",
    "nikic/php-parser"
)

Write-Host "=== Re-uploading critical packages ===" -ForegroundColor Cyan

foreach ($pkg in $criticalPkgs) {
    $parts = $pkg -split "/"
    $vendor = $parts[0]; $name = $parts[1]
    $localPath = "$vendorLocal/$vendor/$name"
    if (Test-Path $localPath) {
        Write-Host "  Uploading $pkg ..." -ForegroundColor White
        Ftp-MkDir "$vendorRemote/$vendor"
        Upload-Package $localPath "$vendorRemote/$vendor/$name"
        Write-Host "  Done $pkg" -ForegroundColor Green
    } else {
        Write-Host "  MISSING locally: $pkg" -ForegroundColor Yellow
    }
}

# Re-apply patched autoloader files and termwind stub
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
