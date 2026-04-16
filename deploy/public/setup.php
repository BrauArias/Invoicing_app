<?php
/**
 * InmoVisualPro — Setup Script
 * https://powerhelp.es/factura/setup.php?token=inmo2025setup
 * BORRAR después de la instalación.
 */
define('SETUP_TOKEN', 'inmo2025setup');
if (!isset($_GET['token']) || $_GET['token'] !== SETUP_TOKEN) {
    http_response_code(403);
    die('<h2>403 Forbidden</h2><p>Accede con ?token=inmo2025setup</p>');
}

$laravelRoot = realpath(__DIR__ . '/../../laravel_factura');
$log = [];

function ok($msg)  { return "<li class='ok'>✓ $msg</li>"; }
function err($msg) { return "<li class='err'>✗ $msg</li>"; }
function inf($msg) { return "<li class='inf'>ℹ $msg</li>"; }
function mono($msg){ return "<li class='mono'>" . htmlspecialchars($msg) . "</li>"; }

// ── 1. Verificaciones básicas ────────────────────────────────────────────────
if (!$laravelRoot || !is_dir($laravelRoot)) {
    die('<p style="color:red">Laravel root no encontrado en: ' . htmlspecialchars(realpath(__DIR__.'/../..') ?: dirname(__DIR__,2)) . '</p>');
}
$log[] = ok("Laravel root: <code>$laravelRoot</code>");
$log[] = inf("PHP " . PHP_VERSION . " · SAPI: " . php_sapi_name());
$log[] = inf("PDO MySQL: " . (extension_loaded('pdo_mysql') ? '<b style="color:green">disponible</b>' : '<b style="color:red">NO disponible</b>'));

// ── 2. Test de conexión a MySQL ─────────────────────────────────────────────
$dbHosts = ['localhost', '127.0.0.1', 'poweyb-rflnmt91.db.tb-hosting.com'];
$dbName  = 'poweyb_rflnmt91';
$dbUser  = 'poweyb_rflnmt91';
$dbPass  = 'RS1kFpcTjQdAWYIz';
$workingHost = null;

if (extension_loaded('pdo_mysql')) {
    foreach ($dbHosts as $host) {
        try {
            $dsn = "mysql:host=$host;dbname=$dbName;charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [PDO::ATTR_TIMEOUT => 3]);
            $log[] = ok("MySQL conectado vía <code>$host</code>");
            $workingHost = $host;
            $pdo = null;
            break;
        } catch (PDOException $e) {
            $log[] = err("MySQL <code>$host</code>: " . htmlspecialchars($e->getMessage()));
        }
    }
    if (!$workingHost) {
        $log[] = err("No se pudo conectar a MySQL con ningún host. Revisa credenciales o contacta Nominalia.");
    }
}

// ── 3. Crear directorios de storage ─────────────────────────────────────────
$dirs = ['/storage/app/public/logos','/storage/app/public/invoices',
         '/storage/framework/cache/data','/storage/framework/sessions',
         '/storage/framework/views','/storage/logs','/bootstrap/cache'];
foreach ($dirs as $d) {
    $full = $laravelRoot . $d;
    if (!is_dir($full)) { @mkdir($full, 0775, true); }
}

// ── 4. Storage symlink ───────────────────────────────────────────────────────
$link = __DIR__ . '/storage';
$target = $laravelRoot . '/storage/app/public';
if (!is_link($link) && !is_dir($link)) {
    @symlink($target, $link) ? $log[] = ok("Symlink storage creado") : @mkdir($link, 0775, true);
}

// ── 5. Parchear .env con el host MySQL que funciona ─────────────────────────
if ($workingHost) {
    $envFile = $laravelRoot . '/.env';
    if (file_exists($envFile)) {
        $env = file_get_contents($envFile);
        $env = preg_replace('/^DB_HOST=.*/m', "DB_HOST=$workingHost", $env);
        file_put_contents($envFile, $env);
        $log[] = ok(".env actualizado con DB_HOST=<code>$workingHost</code>");
    }
}

// ── 6. Bootstrap Laravel + ejecutar comandos ────────────────────────────────
function laravel_artisan(string $root, string $cmd, array $params = []): array
{
    // Evitar redefinición de constante si se llama varias veces
    if (!defined('LARAVEL_START')) define('LARAVEL_START', microtime(true));

    static $app = null;
    static $kernel = null;

    $out = [];
    $code = 0;
    try {
        $prevDir = getcwd();
        chdir($root);

        if ($app === null) {
            require_once $root . '/vendor/autoload.php';
            $app    = require $root . '/bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        }

        ob_start();
        $code = $kernel->call($cmd, array_merge($params, ['--force' => true, '--no-interaction' => true]));
        $raw  = ob_get_clean();

        chdir($prevDir);
        $out = array_filter(array_map('trim', explode("\n", $raw)));
    } catch (Throwable $e) {
        @ob_end_clean();
        $out  = ["EXCEPCIÓN: " . $e->getMessage(), "  en " . $e->getFile() . ":" . $e->getLine()];
        $code = 1;
    }
    return ['out' => array_values($out), 'code' => $code];
}

$showMigrateBtn = true;
$showSeedBtn    = true;

if (isset($_POST['migrate']) && $workingHost) {
    $log[] = inf("Ejecutando migraciones…");
    $r = laravel_artisan($laravelRoot, 'migrate');
    foreach ($r['out'] as $l) $log[] = mono($l);
    if ($r['code'] === 0) {
        $log[] = ok("<b>Migraciones completadas.</b>");
        $showMigrateBtn = false;
    } else {
        $log[] = err("Error en migraciones (código {$r['code']})");
        // Suggest fresh if tables already exist
        if (isset($r['out'][0]) && str_contains(implode(' ', $r['out']), 'already exists')) {
            $log[] = inf("Hay tablas ya existentes. Usa <b>Migración Limpia</b> para borrarlas y empezar de cero.");
        }
    }
}

if (isset($_POST['migrate_fresh']) && $workingHost) {
    $log[] = inf("Ejecutando migrate:fresh (borra todas las tablas y re-migra)…");
    $r = laravel_artisan($laravelRoot, 'migrate:fresh');
    foreach ($r['out'] as $l) $log[] = mono($l);
    if ($r['code'] === 0) {
        $log[] = ok("<b>Migración limpia completada.</b>");
        $showMigrateBtn = false;
    } else {
        $log[] = err("Error en migrate:fresh (código {$r['code']})");
    }
}

if (isset($_POST['seed']) && $workingHost) {
    $log[] = inf("Ejecutando seeder…");
    $r = laravel_artisan($laravelRoot, 'db:seed');
    foreach ($r['out'] as $l) $log[] = mono($l);
    $log[] = $r['code'] === 0 ? ok("<b>Datos demo cargados.</b>") : err("Error en seeder (código {$r['code']})");
}

if (isset($_POST['migrate']) || isset($_POST['migrate_fresh']) || isset($_POST['seed'])) {
    laravel_artisan($laravelRoot, 'config:clear');
    laravel_artisan($laravelRoot, 'view:clear');
    laravel_artisan($laravelRoot, 'cache:clear');
    $log[] = ok("Caché limpiada");
}

?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8">
<title>InmoVisualPro — Setup</title>
<style>
*{box-sizing:border-box}
body{font-family:system-ui,sans-serif;max-width:740px;margin:40px auto;padding:20px;background:#f5f5f5;color:#333}
h1{color:#1e3a5f;margin-bottom:2px}
.card{background:#fff;border-radius:8px;padding:20px;margin:14px 0;box-shadow:0 1px 4px rgba(0,0,0,.1)}
h3{margin:0 0 12px;color:#1e3a5f}
ul{list-style:none;padding:0;margin:0}
li{padding:4px 2px;border-bottom:1px solid #f5f5f5;font-size:13.5px;line-height:1.5}
li.ok{color:#27ae60} li.err{color:#e74c3c} li.inf{color:#2980b9}
li.mono{font-family:monospace;font-size:12px;color:#555;padding-left:12px}
code{background:#f0f0f0;padding:1px 5px;border-radius:3px;font-size:12px}
button{background:#1e3a5f;color:#fff;border:none;padding:10px 22px;border-radius:6px;cursor:pointer;margin:4px;font-size:14px}
button:hover{background:#152843} button.g{background:#d4a017} button.g:hover{background:#b8891b}
.warn{background:#fff3cd;border-left:4px solid #ffc107;padding:10px 14px;border-radius:4px;font-size:13px;margin-top:14px}
.noconn{background:#fde8e8;border-left:4px solid #e74c3c;padding:10px 14px;border-radius:4px;font-size:13px;margin-top:14px}
</style></head><body>

<h1>InmoVisualPro — Instalación</h1>
<p style="color:#999;font-size:12px">PHP <?= PHP_VERSION ?> · <?= htmlspecialchars($laravelRoot) ?></p>

<div class="card">
    <h3>Diagnóstico</h3>
    <ul><?= implode('', $log) ?></ul>
</div>

<?php if (!$workingHost): ?>
<div class="noconn">
    ❌ <strong>No hay conexión a MySQL.</strong><br>
    Posibles causas:<br>
    · El usuario/contraseña de la BD es incorrecto<br>
    · La BD no está creada en Nominalia cPanel<br>
    · El usuario no tiene permisos sobre la BD<br><br>
    Verifica en <strong>cPanel → Bases de datos MySQL</strong> que existen:
    base de datos <code>poweyb_rflnmt91</code>, usuario <code>poweyb_rflnmt91</code>
    y que el usuario tiene <strong>TODOS LOS PRIVILEGIOS</strong>.
</div>
<?php else: ?>
<div class="card">
    <h3>Acciones</h3>
    <form method="post" action="?token=<?= SETUP_TOKEN ?>">
        <?php if ($showMigrateBtn): ?>
        <button type="submit" name="migrate" value="1">▶ Ejecutar Migraciones</button>
        <button type="submit" name="migrate_fresh" value="1" style="background:#c0392b" onclick="return confirm('¿Borrar TODAS las tablas y re-migrar desde cero?')">⚠ Migración Limpia (migrate:fresh)</button>
        <?php endif ?>
        <button type="submit" name="seed" value="1" class="g">🌱 Cargar Datos Demo</button>
    </form>
    <p style="font-size:12px;color:#999;margin-top:10px">
        1. Migraciones → 2. Datos Demo → 3. Borrar este archivo
    </p>
</div>
<?php endif ?>

<div class="warn">
    ⚠ <strong>Borrar este archivo tras la instalación.</strong><br>
    Login: <strong>admin@powerhelp.es</strong> / <strong>Secreto123!</strong>
    en <a href="https://powerhelp.es/factura">powerhelp.es/factura</a>
</div>
</body></html>
