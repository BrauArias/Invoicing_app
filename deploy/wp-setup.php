<?php
// PowerHelp WordPress Setup — borrar tras ejecutar
define('SETUP_TOKEN', 'ph2025setup');
if (!isset($_GET['token']) || $_GET['token'] !== SETUP_TOKEN) { die('403'); }

require_once __DIR__ . '/wp-load.php';

$user = get_user_by('login', 'admin');
wp_set_current_user($user->ID);

$results = [];

// Opciones generales
update_option('blogname',        'PowerHelp');
update_option('blogdescription', 'Soluciones digitales para tu negocio');
update_option('timezone_string', 'Europe/Madrid');
$results[] = '✓ Opciones generales configuradas';

// Landing Page
$home_content = '<!-- wp:heading {"textAlign":"center","level":1} -->
<h1 class="has-text-align-center">Soluciones digitales para tu negocio</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">En PowerHelp desarrollamos herramientas y plataformas digitales especializadas para diferentes sectores.</p>
<!-- /wp:paragraph -->
<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->
<!-- wp:heading {"textAlign":"center","level":2} -->
<h2 class="has-text-align-center">Nuestras Soluciones</h2>
<!-- /wp:heading -->
<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} --><h3>🏠 InmoVisualPro</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Software de gestión inmobiliaria con tour virtual y staging 3D profesional.</p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://inmovisualpro.es" target="_blank" rel="noreferrer noopener">Ver proyecto →</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div><!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} --><h3>🎬 MediaLab Studio</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Producción audiovisual y contenido digital para marcas y empresas.</p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://medialabstudio.es" target="_blank" rel="noreferrer noopener">Ver proyecto →</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div><!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} --><h3>📡 SSE MediaLab</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Consultoría y estrategia de medios digitales y comunicación.</p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://ssemedialab.com" target="_blank" rel="noreferrer noopener">Ver proyecto →</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div><!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:heading {"level":3} --><h3>🏡 Virtual Staging Valencia</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Staging virtual profesional para inmuebles en Valencia y toda España.</p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="https://virtualstagingvalencia.com" target="_blank" rel="noreferrer noopener">Ver proyecto →</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div><!-- /wp:column -->
</div><!-- /wp:columns -->
<!-- wp:separator --><hr class="wp-block-separator has-alpha-channel-opacity"/><!-- /wp:separator -->
<!-- wp:columns -->
<div class="wp-block-columns">
<!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3>🚀 Tecnología a medida</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Soluciones específicas para cada sector y necesidad empresarial.</p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3>🎯 Enfoque en resultados</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Cada proyecto diseñado para generar valor real a tu negocio.</p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3>🤝 Soporte continuo</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Acompañamos a nuestros clientes en cada etapa del crecimiento digital.</p><!-- /wp:paragraph --></div><!-- /wp:column -->
</div><!-- /wp:columns -->';

$home_id = wp_insert_post([
    'post_title'   => 'Inicio',
    'post_content' => $home_content,
    'post_status'  => 'publish',
    'post_type'    => 'page',
    'post_name'    => 'inicio',
]);
$results[] = (!is_wp_error($home_id) && $home_id) ? "✓ Landing page creada (ID $home_id)" : "✗ Error landing";

// Blog page
$blog_id = wp_insert_post([
    'post_title'   => 'Blog',
    'post_content' => '',
    'post_status'  => 'publish',
    'post_type'    => 'page',
    'post_name'    => 'blog',
]);
$results[] = (!is_wp_error($blog_id) && $blog_id) ? "✓ Página Blog creada (ID $blog_id)" : "✗ Error blog";

// Primer post
$post_id = wp_insert_post([
    'post_title'   => 'Bienvenidos a PowerHelp',
    'post_content' => '<!-- wp:paragraph --><p>Nos complace presentar <strong>PowerHelp</strong>, la plataforma de soluciones digitales para empresas. Coordinamos proyectos especializados: InmoVisualPro, MediaLab Studio, SSE MediaLab y Virtual Staging Valencia.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Mantente al tanto de nuestras novedades, lanzamientos y casos de éxito en este blog.</p><!-- /wp:paragraph -->',
    'post_status'  => 'publish',
    'post_type'    => 'post',
]);
$results[] = (!is_wp_error($post_id) && $post_id) ? "✓ Post de bienvenida creado" : "✗ Error post";

// Configurar Home y Blog estáticos
update_option('page_on_front',  $home_id);
update_option('page_for_posts', $blog_id);
update_option('show_on_front',  'page');
update_option('permalink_structure', '/%postname%/');
flush_rewrite_rules();
$results[] = '✓ Home y Blog configurados, permalinks actualizados';

// Menú principal
$menu_id = wp_create_nav_menu('Principal');
if (!is_wp_error($menu_id)) {
    $items = [
        ['Inicio',                   home_url('/')],
        ['Blog',                     home_url('/blog')],
        ['Facturación',              'https://factura.powerhelp.es'],
        ['InmoVisualPro',            'https://inmovisualpro.es'],
        ['MediaLab Studio',          'https://medialabstudio.es'],
        ['SSE MediaLab',             'https://ssemedialab.com'],
        ['Virtual Staging Valencia', 'https://virtualstagingvalencia.com'],
    ];
    foreach ($items as $item) {
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'  => $item[0],
            'menu-item-url'    => $item[1],
            'menu-item-status' => 'publish',
            'menu-item-type'   => 'custom',
        ]);
    }
    $locations = get_theme_mod('nav_menu_locations', []);
    foreach (['primary', 'menu-1', 'header-menu'] as $loc) {
        $locations[$loc] = $menu_id;
    }
    set_theme_mod('nav_menu_locations', $locations);
    $results[] = "✓ Menú de navegación creado con " . count($items) . " elementos";
}

// Limpiar contenido por defecto
wp_delete_post(1, true);
wp_delete_post(2, true);
$results[] = '✓ Contenido por defecto de WordPress eliminado';

// Output
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>PowerHelp Setup</title></head><body>';
echo '<pre style="font-family:monospace;padding:30px;background:#1e1e1e;color:#d4d4d4;font-size:14px;max-width:600px;margin:40px auto;border-radius:8px">';
echo "PowerHelp WordPress Setup\n";
echo str_repeat("─", 40) . "\n\n";
foreach ($results as $r) { echo $r . "\n"; }
echo "\n" . str_repeat("─", 40) . "\n";
echo "✓ Completado\n\n";
echo "Admin: https://powerhelp.es/wp-admin\n";
echo "Usuario: admin\n";
echo "Password: YOUR_WP_PASSWORD\n\n";
echo "BORRA este archivo ahora.\n";
echo '</pre></body></html>';
