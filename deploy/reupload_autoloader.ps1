# Quick fix: re-upload patched autoloader files + termwind stub
# Run this when autoloader issues appear on production

$FTP_HOST = "poweyb.ftp.tb-hosting.com"
$FTP_USER = "powerhelpes@powerhelpes"
$FTP_PASS = "RS1kFpcTjQdAWYIz"
$creds    = New-Object System.Net.NetworkCredential($FTP_USER, $FTP_PASS)
$vendorRemote = "laravel_factura/vendor"

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

$base = "c:/Users/Usuario/Projects/Invoicing_app"

Write-Host "=== Uploading patched autoloader + termwind stub ===" -ForegroundColor Cyan

$files = @(
    @{ local = "$base/vendor/composer/autoload_real.prod.php";   remote = "$vendorRemote/composer/autoload_real.php" },
    @{ local = "$base/vendor/composer/autoload_files.prod.php";  remote = "$vendorRemote/composer/autoload_files.php" },
    @{ local = "$base/vendor/composer/autoload_static.prod.php"; remote = "$vendorRemote/composer/autoload_static.php" },
    @{ local = "$base/vendor/termwind_stub.php";                  remote = "$vendorRemote/termwind_stub.php" }
)

foreach ($f in $files) {
    $name = Split-Path $f.remote -Leaf
    Write-Host "  Uploading $name ..." -ForegroundColor White -NoNewline
    if (Ftp-Upload $f.local $f.remote) {
        Write-Host " OK" -ForegroundColor Green
    } else {
        Write-Host " FAILED" -ForegroundColor Red
    }
}

Write-Host "`n=== DONE ===" -ForegroundColor Green
Write-Host "Visit: https://powerhelp.es/factura/setup.php?token=inmo2025setup"
