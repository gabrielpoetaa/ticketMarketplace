<?php
// Redireciona vendor para painel ao inves de store-setup
add_action('init', function () {
    if (is_user_logged_in() && isset($_GET['store-setup']) && $_GET['store-setup'] === 'yes') {
        wp_safe_redirect(home_url('/store-manager/'));
        exit;
    }
});

