<?php
// Redireciona vendor para painel ao inves de store-setup
add_action('init', function () {
    if (is_user_logged_in() && isset($_GET['store-setup']) && $_GET['store-setup'] === 'yes') {
        wp_safe_redirect(home_url('/store-manager/'));
        exit;
    }
});

// Adicionando Tailwind
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'blocksy-child-tailwind',
        get_stylesheet_directory_uri() . '/src/output.css',
        [],
        filemtime(get_stylesheet_directory() . '/src/output.css')
    );
});

// Remove coluna "Admin Fee" da lista de pedidos vendor
add_filter('wcfmmp_product_commission', function ($commission, $product_id, $vendor_id) {
    return 0;
}, 50, 3);
