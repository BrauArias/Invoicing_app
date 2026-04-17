<?php
// PowerHelp WordPress Restore — borrar tras ejecutar
define('SETUP_TOKEN', 'ph2025restore');
if (!isset($_GET['token']) || $_GET['token'] !== SETUP_TOKEN) { die('403'); }

require_once __DIR__ . '/wp-load.php';
$user = get_user_by('login', 'admin');
wp_set_current_user($user->ID);

$results = [];
$uploads_url = 'https://powerhelp.es/wp-content/uploads/2025/08';

// ── Tema ─────────────────────────────────────────────────────
switch_theme('extendable');
$results[] = '✓ Tema Extendable activado';

// ── Logo y favicon ───────────────────────────────────────────
// Buscar attachment del logo si ya está en la biblioteca
global $wpdb;
$logo_id = $wpdb->get_var("SELECT ID FROM {$wpdb->posts} WHERE post_type='attachment' AND post_title LIKE 'PWRHelpLogoNEW-01%' LIMIT 1");
if (!$logo_id) {
    // Registrar el logo existente en la biblioteca de medios
    $logo_id = wp_insert_attachment([
        'post_mime_type' => 'image/png',
        'post_title'     => 'PowerHelp Logo',
        'post_status'    => 'inherit',
        'guid'           => $uploads_url . '/PWRHelpLogoNEW-01.png',
    ], WP_CONTENT_DIR . '/uploads/2025/08/PWRHelpLogoNEW-01.png');
    update_attached_file($logo_id, WP_CONTENT_DIR . '/uploads/2025/08/PWRHelpLogoNEW-01.png');
}
if ($logo_id) {
    set_theme_mod('custom_logo', $logo_id);
    update_option('site_icon', $logo_id);
    $results[] = "✓ Logo configurado (ID $logo_id)";
}

// ── Activar plugins ───────────────────────────────────────────
$plugins = [
    'all-in-one-seo-pack/all_in_one_seo_pack.php',
    'wpforms-lite/wpforms.php',
    'really-simple-ssl/really-simple-ssl.php',
    'iubenda-cookie-law-solution/iubenda_cookie_law_solution.php',
    'simplybook/simplybook.php',
    'akismet/akismet.php',
];
$activated = 0;
foreach ($plugins as $plugin) {
    $result = activate_plugin($plugin);
    if (!is_wp_error($result)) $activated++;
}
$results[] = "✓ $activated plugins activados";

// ── Eliminar páginas anteriores del setup ────────────────────
$old = get_page_by_path('inicio', OBJECT, 'page');
if ($old) wp_delete_post($old->ID, true);
$old = get_page_by_path('blog', OBJECT, 'page');
if ($old) wp_delete_post($old->ID, true);

// ── Landing Page ─────────────────────────────────────────────
$hero_img = $uploads_url . '/PWRHelpLogoNEW-01.png';
$agent_img = $uploads_url . '/pretty-smiling-lady-transperent-glasses-wide-smile-white-shirt-with-headset-isolated-white.jpg';

$home_content = '<!-- wp:cover {"url":"' . $uploads_url . '/PWRHelpLogoNEW-09.webp","dimRatio":60,"minHeight":500,"align":"full"} -->
<div class="wp-block-cover alignfull" style="min-height:500px">
<span aria-hidden="true" class="wp-block-cover__background has-background-dim-60 has-background-dim"></span>
<div class="wp-block-cover__inner-container">
<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"3rem"}},"textColor":"white"} -->
<h1 class="has-text-align-center has-white-color has-text-color" style="font-size:3rem">Tu negocio, potenciado</h1>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"1.3rem"}},"textColor":"white"} -->
<p class="has-text-align-center has-white-color has-text-color" style="font-size:1.3rem">PowerHelp impulsa tu empresa con soluciones digitales especializadas en e-commerce, producción audiovisual, consultoría de medios y staging virtual inmobiliario.</p>
<!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons">
<!-- wp:button {"backgroundColor":"vivid-cyan-blue","textColor":"white","style":{"border":{"radius":"6px"}}} -->
<div class="wp-block-button"><a class="wp-block-button__link has-white-color has-vivid-cyan-blue-background-color has-text-color has-background" href="#soluciones" style="border-radius:6px">Ver nuestras soluciones</a></div>
<!-- /wp:button -->
<!-- wp:button {"className":"is-style-outline","textColor":"white","style":{"border":{"radius":"6px"}}} -->
<div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-white-color has-text-color" href="#contacto" style="border-radius:6px">Contactar</a></div>
<!-- /wp:button -->
</div>
<!-- /wp:buttons -->
</div>
</div>
<!-- /wp:cover -->

<!-- wp:html -->
<div id="soluciones"></div>
<!-- /wp:html -->

<!-- wp:heading {"textAlign":"center","level":2,"style":{"spacing":{"margin":{"top":"60px","bottom":"10px"}}}} -->
<h2 class="has-text-align-center" style="margin-top:60px;margin-bottom:10px">Nuestras Soluciones</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","style":{"color":{"text":"#666666"}}} -->
<p class="has-text-align-center has-text-color" style="color:#666666">Cada proyecto está diseñado para generar valor real en su sector</p>
<!-- /wp:paragraph -->

<!-- wp:columns {"style":{"spacing":{"padding":{"top":"40px","bottom":"40px"},"blockGap":"24px"}}} -->
<div class="wp-block-columns" style="padding-top:40px;padding-bottom:40px">

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"style":{"border":{"radius":"12px","width":"1px","color":"#e0e0e0"},"spacing":{"padding":{"all":"32px"}}},"backgroundColor":"white"} -->
<div class="wp-block-group has-white-background-color has-background" style="border-radius:12px;border-color:#e0e0e0;border-width:1px;border-style:solid;padding:32px">
<!-- wp:paragraph {"style":{"typography":{"fontSize":"2.5rem"}}} --><p style="font-size:2.5rem">🏠</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>InmoVisualPro</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Software de gestión inmobiliaria con tour virtual 360° y staging 3D profesional. Vende más y más rápido.</p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"style":{"border":{"radius":"4px"}}} --><div class="wp-block-button"><a class="wp-block-button__link" href="https://inmovisualpro.es" target="_blank" rel="noreferrer noopener" style="border-radius:4px">Saber más →</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div><!-- /wp:group -->
</div><!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"style":{"border":{"radius":"12px","width":"1px","color":"#e0e0e0"},"spacing":{"padding":{"all":"32px"}}},"backgroundColor":"white"} -->
<div class="wp-block-group has-white-background-color has-background" style="border-radius:12px;border-color:#e0e0e0;border-width:1px;border-style:solid;padding:32px">
<!-- wp:paragraph {"style":{"typography":{"fontSize":"2.5rem"}}} --><p style="font-size:2.5rem">🎬</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>MediaLab Studio</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Producción audiovisual y contenido digital de alta calidad para marcas y empresas que quieren destacar.</p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"style":{"border":{"radius":"4px"}}} --><div class="wp-block-button"><a class="wp-block-button__link" href="https://medialabstudio.es" target="_blank" rel="noreferrer noopener" style="border-radius:4px">Saber más →</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div><!-- /wp:group -->
</div><!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"style":{"border":{"radius":"12px","width":"1px","color":"#e0e0e0"},"spacing":{"padding":{"all":"32px"}}},"backgroundColor":"white"} -->
<div class="wp-block-group has-white-background-color has-background" style="border-radius:12px;border-color:#e0e0e0;border-width:1px;border-style:solid;padding:32px">
<!-- wp:paragraph {"style":{"typography":{"fontSize":"2.5rem"}}} --><p style="font-size:2.5rem">📡</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>SSE MediaLab</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Consultoría y estrategia de medios digitales, comunicación y posicionamiento de marca en el entorno digital.</p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"style":{"border":{"radius":"4px"}}} --><div class="wp-block-button"><a class="wp-block-button__link" href="https://ssemedialab.com" target="_blank" rel="noreferrer noopener" style="border-radius:4px">Saber más →</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div><!-- /wp:group -->
</div><!-- /wp:column -->

<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:group {"style":{"border":{"radius":"12px","width":"1px","color":"#e0e0e0"},"spacing":{"padding":{"all":"32px"}}},"backgroundColor":"white"} -->
<div class="wp-block-group has-white-background-color has-background" style="border-radius:12px;border-color:#e0e0e0;border-width:1px;border-style:solid;padding:32px">
<!-- wp:paragraph {"style":{"typography":{"fontSize":"2.5rem"}}} --><p style="font-size:2.5rem">🏡</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Virtual Staging Valencia</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Staging virtual profesional para inmuebles en Valencia y toda España. Transforma espacios vacíos en hogares soñados.</p><!-- /wp:paragraph -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button {"style":{"border":{"radius":"4px"}}} --><div class="wp-block-button"><a class="wp-block-button__link" href="https://virtualstagingvalencia.com" target="_blank" rel="noreferrer noopener" style="border-radius:4px">Saber más →</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div><!-- /wp:group -->
</div><!-- /wp:column -->

</div><!-- /wp:columns -->

<!-- wp:separator {"style":{"spacing":{"margin":{"top":"20px","bottom":"20px"}}}} -->
<hr class="wp-block-separator has-alpha-channel-opacity" style="margin-top:20px;margin-bottom:20px"/>
<!-- /wp:separator -->

<!-- wp:columns {"style":{"spacing":{"padding":{"top":"40px","bottom":"40px"}}},"backgroundColor":"light-gray"} -->
<div class="wp-block-columns has-light-gray-background-color has-background" style="padding-top:40px;padding-bottom:40px">
<!-- wp:column {"width":"40%"} -->
<div class="wp-block-column" style="flex-basis:40%">
<!-- wp:image {"url":"' . $agent_img . '","sizeSlug":"large"} -->
<figure class="wp-block-image size-large"><img src="' . $agent_img . '" alt="Soporte PowerHelp"/></figure>
<!-- /wp:image -->
</div><!-- /wp:column -->
<!-- wp:column {"width":"60%","style":{"spacing":{"padding":{"left":"32px"}}}} -->
<div class="wp-block-column" style="flex-basis:60%;padding-left:32px">
<!-- wp:heading {"level":2} --><h2>¿Por qué elegir PowerHelp?</h2><!-- /wp:heading -->
<!-- wp:list -->
<ul>
<li><strong>Tecnología a medida</strong> — Desarrollamos soluciones específicas para cada sector y necesidad empresarial.</li>
<li><strong>Experiencia demostrada</strong> — Proyectos activos en inmobiliaria, medios, e-commerce y producción audiovisual.</li>
<li><strong>Soporte continuo</strong> — Acompañamos a nuestros clientes en cada etapa de su crecimiento digital.</li>
<li><strong>Resultados medibles</strong> — Cada proyecto está orientado a generar impacto real en tu negocio.</li>
</ul>
<!-- /wp:list -->
<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link" href="#contacto">Habla con nosotros</a></div><!-- /wp:button --></div><!-- /wp:buttons -->
</div><!-- /wp:column -->
</div><!-- /wp:columns -->

<!-- wp:html -->
<div id="contacto"></div>
<!-- /wp:html -->

<!-- wp:heading {"textAlign":"center","level":2,"style":{"spacing":{"margin":{"top":"60px","bottom":"10px"}}}} -->
<h2 class="has-text-align-center" style="margin-top:60px;margin-bottom:10px">Contacta con nosotros</h2>
<!-- /wp:heading -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">¿Tienes un proyecto en mente? Escríbenos y te ayudamos a hacerlo realidad.</p>
<!-- /wp:paragraph -->
<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">📧 <a href="mailto:info@powerhelp.es">info@powerhelp.es</a> &nbsp;|&nbsp; 🌐 <a href="https://factura.powerhelp.es">Acceso a facturación</a></p>
<!-- /wp:paragraph -->';

$home_id = wp_insert_post([
    'post_title'   => 'Inicio',
    'post_content' => $home_content,
    'post_status'  => 'publish',
    'post_type'    => 'page',
    'post_name'    => 'inicio',
]);
$results[] = (!is_wp_error($home_id) && $home_id) ? "✓ Landing page creada con hero, soluciones y contacto" : "✗ Error landing";

// ── Blog page ─────────────────────────────────────────────────
$blog_id = wp_insert_post([
    'post_title'   => 'Blog',
    'post_content' => '',
    'post_status'  => 'publish',
    'post_type'    => 'page',
    'post_name'    => 'blog',
]);
$results[] = (!is_wp_error($blog_id) && $blog_id) ? "✓ Página Blog creada" : "✗ Error blog";

// ── Posts del blog ────────────────────────────────────────────
wp_insert_post([
    'post_title'   => 'Bienvenidos a la nueva PowerHelp',
    'post_content' => '<!-- wp:paragraph --><p>Tras años ayudando a empresas a crecer en el entorno digital, PowerHelp estrena nueva web con una presencia renovada y más proyectos que nunca.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Somos el grupo detrás de <strong>InmoVisualPro</strong>, <strong>MediaLab Studio</strong>, <strong>SSE MediaLab</strong> y <strong>Virtual Staging Valencia</strong>. Cada marca nació para resolver un problema específico de un sector concreto.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>En este blog encontrarás noticias, casos de éxito y recursos sobre tecnología, inmobiliaria, producción audiovisual y e-commerce.</p><!-- /wp:paragraph -->',
    'post_status'  => 'publish',
    'post_type'    => 'post',
    'post_name'    => 'bienvenidos-nueva-powerhelp',
]);

wp_insert_post([
    'post_title'   => 'Cómo el staging virtual transforma la venta de inmuebles',
    'post_content' => '<!-- wp:paragraph --><p>El staging virtual es una de las herramientas más eficaces en el sector inmobiliario moderno. Permite mostrar un inmueble vacío o desactualizado como si ya estuviera amueblado y decorado, todo mediante renders 3D de alta calidad.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>Desde <strong>Virtual Staging Valencia</strong> hemos comprobado que los inmuebles con staging virtual se venden hasta un 40% más rápido y a mejor precio. Descubre más en <a href="https://virtualstagingvalencia.com">virtualstagingvalencia.com</a>.</p><!-- /wp:paragraph -->',
    'post_status'  => 'publish',
    'post_type'    => 'post',
    'post_name'    => 'staging-virtual-venta-inmuebles',
]);

$results[] = '✓ 2 posts del blog creados';

// ── Página Amazon FBA (servicio propio) ───────────────────────
$amz_id = wp_insert_post([
    'post_title'   => 'Amazon FBA',
    'post_content' => '<!-- wp:heading --><h2>Vende más en Amazon con nuestra ayuda</h2><!-- /wp:heading --><!-- wp:paragraph --><p>En PowerHelp ayudamos a empresas a crecer en Amazon mediante estrategias de <strong>Fulfillment by Amazon (FBA)</strong>. Desde la optimización de listings hasta la gestión completa de tu cuenta.</p><!-- /wp:paragraph --><!-- wp:image {"url":"' . $uploads_url . '/amazon-fba-portada.jpg","sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="' . $uploads_url . '/amazon-fba-portada.jpg" alt="Amazon FBA"/></figure><!-- /wp:image --><!-- wp:heading {"level":3} --><h3>¿Qué incluye nuestro servicio?</h3><!-- /wp:heading --><!-- wp:list --><ul><li>Auditoría de tu cuenta Amazon actual</li><li>Optimización de listings y keywords</li><li>Gestión de campañas PPC</li><li>Estrategia de pricing y Buy Box</li><li>Soporte y formación continua</li></ul><!-- /wp:list --><!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link" href="mailto:info@powerhelp.es">Solicitar información</a></div><!-- /wp:button --></div><!-- /wp:buttons -->',
    'post_status'  => 'publish',
    'post_type'    => 'page',
    'post_name'    => 'amazon-fba',
]);
$results[] = (!is_wp_error($amz_id) && $amz_id) ? "✓ Página Amazon FBA creada" : "✗ Error Amazon FBA";

// ── Configurar Home y Blog ────────────────────────────────────
update_option('page_on_front',  $home_id);
update_option('page_for_posts', $blog_id);
update_option('show_on_front',  'page');
update_option('permalink_structure', '/%postname%/');
flush_rewrite_rules();
$results[] = '✓ Home y Blog configurados';

// ── Menú principal ────────────────────────────────────────────
wp_delete_nav_menu('Principal');
$menu_id = wp_create_nav_menu('Principal');
if (!is_wp_error($menu_id)) {
    $items = [
        ['Inicio',                   home_url('/')],
        ['Amazon FBA',               home_url('/amazon-fba')],
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
    foreach (['primary', 'menu-1', 'header-menu'] as $loc) {
        $locs = get_theme_mod('nav_menu_locations', []);
        $locs[$loc] = $menu_id;
        set_theme_mod('nav_menu_locations', $locs);
    }
    $results[] = "✓ Menú actualizado con " . count($items) . " elementos";
}

// Output
echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>PowerHelp Restore</title></head><body>';
echo '<pre style="font-family:monospace;padding:30px;background:#1e1e1e;color:#d4d4d4;font-size:14px;max-width:650px;margin:40px auto;border-radius:8px">';
echo "PowerHelp WordPress Restore\n";
echo str_repeat("─", 44) . "\n\n";
foreach ($results as $r) { echo $r . "\n"; }
echo "\n" . str_repeat("─", 44) . "\n";
echo "Visita: https://powerhelp.es\n";
echo "Admin:  https://powerhelp.es/wp-admin\n\n";
echo "BORRA este archivo ahora.\n";
echo '</pre></body></html>';
