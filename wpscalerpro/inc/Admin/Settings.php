<?php
// WpscalerPro Admin Settings Page

require_once __DIR__ . '/../i18n.php';

function wpsp_get_option($key, $default = '') {
  $val = get_option('wpsp_' . $key, null);
  return $val !== null ? $val : $default;
}

function wpsp_set_option($key, $value) {
  return update_option('wpsp_' . $key, $value);
}

function wpsp_delete_option($key) {
  return delete_option('wpsp_' . $key);
}

// Admin menu ekle
add_action('admin_menu', function() {
  add_menu_page(
    wpsp_t('plugin_name'),
    wpsp_t('plugin_name'),
    'manage_options',
    'wpscalerpro',
    'wpsp_render_settings_page',
    'dashicons-admin-generic'
  );
});

// React admin panelini ve root div'i ekle
add_action('admin_enqueue_scripts', function($hook) {
  if ($hook !== 'toplevel_page_wpscalerpro') return;

  // React ve admin panel scriptlerini yükle
  $plugin_url = plugins_url('', dirname(__FILE__, 2));
  wp_enqueue_style('wpsp-admin-css', $plugin_url . '/assets/css/admin.css', [], '1.0.0');
  wp_enqueue_script(
    'wpsp-admin-js',
    $plugin_url . '/assets/js/admin/main.js',
    ['react', 'react-dom'],
    '1.0.0',
    true
  );
  // React ve ReactDOM'u WordPress'e ekle (CDN'den)
  wp_enqueue_script(
    'react',
    'https://unpkg.com/react@18/umd/react.development.js',
    [],
    '18.2.0',
    false
  );
  wp_enqueue_script(
    'react-dom',
    'https://unpkg.com/react-dom@18/umd/react-dom.development.js',
    ['react'],
    '18.2.0',
    false
  );
});

// Ayar sayfası render
function wpsp_render_settings_page() {
  if (!current_user_can('manage_options')) {
    return;
  }
  echo '<div class="wrap"><div id="wpsp-admin-root"></div></div>';
}
