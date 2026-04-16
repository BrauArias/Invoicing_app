$FTP_HOST = "poweyb.ftp.tb-hosting.com"
$FTP_USER = "powerhelpes@powerhelpes"
$FTP_PASS = "RS1kFpcTjQdAWYIz"
$creds = New-Object System.Net.NetworkCredential($FTP_USER, $FTP_PASS)

function Ftp-MkDir($remote) {
    try {
        $r = [System.Net.FtpWebRequest]::Create("ftp://$FTP_HOST/$remote")
        $r.Credentials = $creds; $r.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $r.UsePassive = $true; $r.UseBinary = $true; $r.KeepAlive = $false; $r.Timeout = 15000
        $r.GetResponse().Close()
    } catch {}
}

function Ftp-Upload($local, $remote) {
    for ($i = 1; $i -le 5; $i++) {
        try {
            $r = [System.Net.FtpWebRequest]::Create("ftp://$FTP_HOST/$remote")
            $r.Credentials = $creds; $r.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
            $r.UsePassive = $true; $r.UseBinary = $true; $r.KeepAlive = $false
            $r.Timeout = 60000; $r.ReadWriteTimeout = 60000
            $buf = [System.IO.File]::ReadAllBytes($local)
            $r.ContentLength = $buf.Length
            $s = $r.GetRequestStream(); $s.Write($buf, 0, $buf.Length); $s.Close()
            $r.GetResponse().Close()
            Write-Host "  OK $remote" -ForegroundColor Green
            return $true
        } catch {
            $msg = $_.Exception.Message
            if ($i -eq 5) { Write-Host "  ERR ${remote}: $msg" -ForegroundColor Red }
            Start-Sleep -Milliseconds (500 * $i)
        }
    }
    return $false
}

function Upload-Dir($localDir, $remoteDir) {
    Ftp-MkDir $remoteDir
    Get-ChildItem $localDir | ForEach-Object {
        if ($_.PSIsContainer) {
            Upload-Dir $_.FullName "$remoteDir/$($_.Name)"
        } else {
            Ftp-Upload $_.FullName "$remoteDir/$($_.Name)"
        }
    }
}

$base = "c:/Users/Usuario/Projects/Invoicing_app/vendor"
$remote = "laravel_factura/vendor"

Write-Host "Uploading phpoption/phpoption..." -ForegroundColor Cyan
Ftp-MkDir "$remote/phpoption"
Upload-Dir "$base/phpoption/phpoption" "$remote/phpoption/phpoption"

Write-Host "Uploading myclabs/deep-copy..." -ForegroundColor Cyan
Ftp-MkDir "$remote/myclabs"
Upload-Dir "$base/myclabs/deep-copy" "$remote/myclabs/deep-copy"

# Re-apply patched autoloader files
Write-Host "Re-uploading patched composer autoloader files..." -ForegroundColor Yellow
$deploy = "c:/Users/Usuario/Projects/Invoicing_app/deploy"
Ftp-Upload "$deploy/../vendor/composer/autoload_real.prod.php"   "$remote/composer/autoload_real.php"
Ftp-Upload "$deploy/../vendor/composer/autoload_files.prod.php"  "$remote/composer/autoload_files.php"
Ftp-Upload "$deploy/../vendor/composer/autoload_static.prod.php" "$remote/composer/autoload_static.php"
Write-Host "Done." -ForegroundColor Green
