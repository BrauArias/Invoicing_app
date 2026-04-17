# PowerHelp Landing Page — FTP Deploy Script
# Sube index.html + assets/ a www/ en el hosting de Nominalia
# Uso: powershell -ExecutionPolicy Bypass -File deploy\upload_landing.ps1

$FTP_HOST = "poweyb.ftp.tb-hosting.com"
$FTP_USER = "powerhelpes@powerhelpes"
$FTP_PASS = "YOUR_FTP_PASSWORD"
$FTP_BASE = "ftp://$FTP_HOST"

$DEPLOY_DIR = $PSScriptRoot
$credentials = New-Object System.Net.NetworkCredential($FTP_USER, $FTP_PASS)

# ── Helpers ──────────────────────────────────────────────────

function Ftp-Upload($localFile, $remotePath) {
    try {
        $bytes = [System.IO.File]::ReadAllBytes($localFile)
        $req = [System.Net.FtpWebRequest]::Create("$FTP_BASE/$remotePath")
        $req.Credentials  = $credentials
        $req.Method       = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $req.UseBinary    = $true
        $req.UsePassive   = $true
        $req.KeepAlive    = $false
        $req.ContentLength = $bytes.Length
        $stream = $req.GetRequestStream()
        $stream.Write($bytes, 0, $bytes.Length)
        $stream.Close()
        $req.GetResponse().Close()
        $size = [math]::Round($bytes.Length / 1KB, 1)
        Write-Host "  [OK]  /$remotePath  ($size KB)" -ForegroundColor Green
    } catch {
        Write-Host "  [ERR] /$remotePath : $($_.Exception.Message)" -ForegroundColor Red
    }
}

function Ftp-MkDir($remotePath) {
    try {
        $req = [System.Net.FtpWebRequest]::Create("$FTP_BASE/$remotePath")
        $req.Credentials = $credentials
        $req.Method      = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $req.UsePassive  = $true
        $req.KeepAlive   = $false
        $req.GetResponse().Close()
        Write-Host "  [DIR] /$remotePath" -ForegroundColor Cyan
    } catch {
        if ($_.Exception.Message -notmatch "550") {
            Write-Host "  [WARN] mkdir /$remotePath : $($_.Exception.Message)" -ForegroundColor Yellow
        }
    }
}

# ── Upload ────────────────────────────────────────────────────

Write-Host ""
Write-Host "=== PowerHelp Landing -- Deploy a www/ ===" -ForegroundColor Magenta
Write-Host ""

# 1. front-page.php → tema extendable (WordPress template hierarchy)
Write-Host "→ Subiendo front-page.php al tema WordPress..." -ForegroundColor White
$fpSrc = Join-Path $DEPLOY_DIR "public\front-page.php"
Ftp-Upload $fpSrc "www/wp-content/themes/extendable/front-page.php"

# 2. landing HTML → tema extendable (incluido por front-page.php)
Write-Host ""
Write-Host "→ Subiendo landing al tema..." -ForegroundColor White
$htmlSrc2 = Join-Path $DEPLOY_DIR "public\powerhelp-landing.html"
Ftp-Upload $htmlSrc2 "www/wp-content/themes/extendable/powerhelp-landing.html"

# 3. settings.html → www/settings.html (panel de admin)
Write-Host ""
Write-Host "→ Subiendo panel de admin..." -ForegroundColor White
$demoDst = Join-Path $DEPLOY_DIR "public\settingdemo.html" # El archivo fisico se puede llamar igual, pero el destino cambia
Ftp-Upload $demoDst "www/settings.html"

# 4. landing HTML → www/index.html  (landing page real)
Write-Host ""
Write-Host "→ Subiendo landing page principal..." -ForegroundColor White
$htmlSrc = Join-Path $DEPLOY_DIR "public\powerhelp-landing.html"
Ftp-Upload $htmlSrc "www/index.html"

# 2. Crear carpeta de assets en el servidor
Write-Host ""
Write-Host "→ Creando carpeta www/assets/ ..." -ForegroundColor White
Ftp-MkDir "www/assets"

# 3. Subir todos los archivos de assets/
Write-Host ""
Write-Host "→ Subiendo assets (logos, fotos)..." -ForegroundColor White
$assetsDir = Join-Path $DEPLOY_DIR "public\assets"
Get-ChildItem $assetsDir -File | ForEach-Object {
    Ftp-Upload $_.FullName "www/assets/$($_.Name)"
}

# ── Done ──────────────────────────────────────────────────────

Write-Host ""
Write-Host "=== DEPLOY COMPLETADO ===" -ForegroundColor Green
Write-Host ""
Write-Host "  Landing live en:" -ForegroundColor Yellow
Write-Host "  https://www.powerhelp.es" -ForegroundColor Cyan
Write-Host ""
Write-Host "  WordPress admin sigue funcionando en:" -ForegroundColor Yellow
Write-Host "  https://www.powerhelp.es/wp-admin" -ForegroundColor Cyan
Write-Host ""
Write-Host "  SIGUIENTE PASO: configura Formspree" -ForegroundColor Yellow
Write-Host "  1. Crea cuenta en formspree.io" -ForegroundColor White
Write-Host "  2. Crea un formulario y copia el ID" -ForegroundColor White
Write-Host "  3. Reemplaza YOUR_FORMSPREE_ID en el HTML" -ForegroundColor White
Write-Host "  4. Vuelve a ejecutar este script" -ForegroundColor White
Write-Host ""
