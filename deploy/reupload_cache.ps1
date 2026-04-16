$FTP_HOST = "poweyb.ftp.tb-hosting.com"
$FTP_USER = "powerhelpes@powerhelpes"
$FTP_PASS = "RS1kFpcTjQdAWYIz"
$creds = New-Object System.Net.NetworkCredential($FTP_USER, $FTP_PASS)

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

$app = "c:/Users/Usuario/Projects/Invoicing_app"

Write-Host "Uploading bootstrap/cache files..." -ForegroundColor Cyan
Ftp-Upload "$app/bootstrap/cache/packages.php" "laravel_factura/bootstrap/cache/packages.php"
Ftp-Upload "$app/bootstrap/cache/services.php" "laravel_factura/bootstrap/cache/services.php"
Write-Host "Done." -ForegroundColor Green
