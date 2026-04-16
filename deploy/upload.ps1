# InmoVisualPro — FTP Deployment Script
# Ejecutar desde el directorio raiz de la app:
#   powershell -ExecutionPolicy Bypass -File deploy\upload.ps1

$FTP_HOST = "poweyb.ftp.tb-hosting.com"
$FTP_USER = "powerhelpes@powerhelpes"
$FTP_PASS = "RS1kFpcTjQdAWYIz"
$FTP_BASE = "ftp://$FTP_HOST"

$APP_ROOT   = Split-Path -Parent $PSScriptRoot
$DEPLOY_DIR = $PSScriptRoot

$credentials = New-Object System.Net.NetworkCredential($FTP_USER, $FTP_PASS)

# ────────────────────────────────────────────────────────────
# Helpers
# ────────────────────────────────────────────────────────────

function Ftp-MkDir($remotePath) {
    try {
        $req = [System.Net.FtpWebRequest]::Create("$FTP_BASE/$remotePath")
        $req.Credentials = $credentials
        $req.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $req.UseBinary = $true
        $req.UsePassive = $true
        $req.KeepAlive = $false
        $resp = $req.GetResponse()
        $resp.Close()
        Write-Host "  [DIR] /$remotePath" -ForegroundColor Cyan
    } catch {
        # Ignore "directory already exists" errors (550)
        if ($_.Exception.Message -notmatch "550") {
            Write-Host "  [WARN] mkdir /$remotePath : $($_.Exception.Message)" -ForegroundColor Yellow
        }
    }
}

function Ftp-Upload($localFile, $remotePath) {
    try {
        $req = [System.Net.FtpWebRequest]::Create("$FTP_BASE/$remotePath")
        $req.Credentials = $credentials
        $req.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $req.UseBinary = $true
        $req.UsePassive = $true
        $req.KeepAlive = $false
        $req.ContentLength = (Get-Item $localFile).Length

        $buf = [System.IO.File]::ReadAllBytes($localFile)
        $stream = $req.GetRequestStream()
        $stream.Write($buf, 0, $buf.Length)
        $stream.Close()

        $resp = $req.GetResponse()
        $resp.Close()
        Write-Host "  [OK]  /$remotePath" -ForegroundColor Green
    } catch {
        Write-Host "  [ERR] /$remotePath : $($_.Exception.Message)" -ForegroundColor Red
    }
}

function Ftp-Delete($remotePath) {
    try {
        $req = [System.Net.FtpWebRequest]::Create("$FTP_BASE/$remotePath")
        $req.Credentials = $credentials
        $req.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
        $req.UsePassive = $true
        $resp = $req.GetResponse()
        $resp.Close()
        Write-Host "  [DEL] /$remotePath" -ForegroundColor DarkGray
    } catch { }
}

function Ftp-ListDir($remotePath) {
    try {
        $req = [System.Net.FtpWebRequest]::Create("$FTP_BASE/$remotePath")
        $req.Credentials = $credentials
        $req.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $req.UsePassive = $true
        $resp = $req.GetResponse()
        $reader = New-Object System.IO.StreamReader($resp.GetResponseStream())
        $content = $reader.ReadToEnd()
        $reader.Close()
        $resp.Close()
        return $content -split "`n" | Where-Object { $_ -ne "" } | ForEach-Object { $_.Trim() }
    } catch { return @() }
}

function Upload-Directory($localDir, $remoteDir, $excludes = @()) {
    Ftp-MkDir $remoteDir

    Get-ChildItem $localDir | ForEach-Object {
        $name = $_.Name
        $localPath = $_.FullName
        $remotePath = "$remoteDir/$name"

        # Check excludes
        foreach ($ex in $excludes) {
            if ($name -like $ex) { return }
        }

        if ($_.PSIsContainer) {
            Upload-Directory $localPath $remotePath $excludes
        } else {
            Ftp-Upload $localPath $remotePath
        }
    }
}

# ────────────────────────────────────────────────────────────
# STEP 1: Borrar app antigua www/Invoicing_app
# ────────────────────────────────────────────────────────────
Write-Host "`n=== Borrando app antigua www/Invoicing_app ===" -ForegroundColor Magenta

$oldFiles = Ftp-ListDir "www/Invoicing_app"
foreach ($f in $oldFiles) {
    if ($f -ne "") { Ftp-Delete "www/Invoicing_app/$f" }
}
# Intentar borrar directorios (vendor)
$vendorFiles = Ftp-ListDir "www/Invoicing_app/vendor"
foreach ($f in $vendorFiles) {
    Ftp-Delete "www/Invoicing_app/vendor/$f"
}
try {
    $req = [System.Net.FtpWebRequest]::Create("$FTP_BASE/www/Invoicing_app/vendor")
    $req.Credentials = $credentials; $req.Method = [System.Net.WebRequestMethods+Ftp]::RemoveDirectory; $req.UsePassive = $true
    $req.GetResponse().Close()
} catch {}
try {
    $req = [System.Net.FtpWebRequest]::Create("$FTP_BASE/www/Invoicing_app")
    $req.Credentials = $credentials; $req.Method = [System.Net.WebRequestMethods+Ftp]::RemoveDirectory; $req.UsePassive = $true
    $req.GetResponse().Close()
    Write-Host "  [OK]  Carpeta antigua eliminada" -ForegroundColor Green
} catch { Write-Host "  [WARN] No se pudo eliminar www/Invoicing_app (puede requerir borrado manual)" -ForegroundColor Yellow }

# ────────────────────────────────────────────────────────────
# STEP 2: Subir laravel_factura (fuera de www/)
# ────────────────────────────────────────────────────────────
Write-Host "`n=== Subiendo laravel_factura/ ===" -ForegroundColor Magenta

$laravelExcludes = @(
    "node_modules", ".git", "deploy", "Invoicing_app", "Invoicingappspain",
    ".env", ".env.*",  # subiremos .env.production como .env
    "public",          # se sube por separado a www/factura/
    "tests", ".phpunit*", "phpunit.xml*",
    "*.md", "*.txt",
    "vite.config.*", "tsconfig*", "package*.json", "tailwind.config.*",
    ".editorconfig", ".gitignore", ".gitattributes"
)

$larravelDirs = @(
    "app", "bootstrap", "config", "database",
    "resources" , "routes", "storage", "vendor"
)

Ftp-MkDir "laravel_factura"

foreach ($dir in $larravelDirs) {
    $localPath = Join-Path $APP_ROOT $dir
    if (Test-Path $localPath) {
        Write-Host "  Subiendo $dir/ ..." -ForegroundColor White
        Upload-Directory $localPath "laravel_factura/$dir" @("*.log", "sessions/*", "cache/data/*")
    }
}

# Subir artisan (ejecutable PHP)
Ftp-Upload (Join-Path $APP_ROOT "artisan") "laravel_factura/artisan"

# Subir .env.production como .env
Write-Host "  Subiendo .env ..." -ForegroundColor White
Ftp-Upload (Join-Path $APP_ROOT ".env.production") "laravel_factura/.env"

# Crear storage dirs vacíos (estructura)
$storageDirs = @(
    "laravel_factura/storage/app/public/logos",
    "laravel_factura/storage/app/public/invoices",
    "laravel_factura/storage/framework/cache/data",
    "laravel_factura/storage/framework/sessions",
    "laravel_factura/storage/framework/views",
    "laravel_factura/storage/logs",
    "laravel_factura/bootstrap/cache"
)
foreach ($d in $storageDirs) { Ftp-MkDir $d }

# ────────────────────────────────────────────────────────────
# STEP 3: Subir public/ a www/factura/
# ────────────────────────────────────────────────────────────
Write-Host "`n=== Subiendo www/factura/ ===" -ForegroundColor Magenta

Ftp-MkDir "www/factura"

# build/ (assets compilados)
Write-Host "  Subiendo build/ ..." -ForegroundColor White
Upload-Directory (Join-Path $APP_ROOT "public\build") "www/factura/build" @()

# index.php modificado (apunta a laravel_factura)
Ftp-Upload (Join-Path $DEPLOY_DIR "public\index.php")   "www/factura/index.php"
Ftp-Upload (Join-Path $DEPLOY_DIR "public\.htaccess")   "www/factura/.htaccess"
Ftp-Upload (Join-Path $DEPLOY_DIR "public\setup.php")   "www/factura/setup.php"

# favicon si existe
$favicon = Join-Path $APP_ROOT "public\favicon.ico"
if (Test-Path $favicon) { Ftp-Upload $favicon "www/factura/favicon.ico" }

# ────────────────────────────────────────────────────────────
Write-Host "`n=== DESPLIEGUE COMPLETADO ===" -ForegroundColor Green
Write-Host ""
Write-Host "Siguiente paso:" -ForegroundColor Yellow
Write-Host "  1. Visita: https://powerhelp.es/factura/setup.php?token=inmo2025setup" -ForegroundColor Cyan
Write-Host "  2. Pulsa 'Ejecutar Migraciones'" -ForegroundColor Cyan
Write-Host "  3. Pulsa 'Cargar Datos Demo'" -ForegroundColor Cyan
Write-Host "  4. Borra setup.php" -ForegroundColor Cyan
Write-Host "  5. Login en: https://powerhelp.es/factura" -ForegroundColor Cyan
Write-Host "     Email:    admin@powerhelp.es" -ForegroundColor White
Write-Host "     Password: Secreto123!" -ForegroundColor White
