<?php
class WPSP_Admin {
  public function __construct() {
    add_action('admin_menu', [$this, 'add_menu']);
    add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
  }

  public function add_menu() {
    add_menu_page(
      __('WpscalerPro', 'wpscalerpro'),
      __('WpscalerPro', 'wpscalerpro'),
      'manage_options',
      'wpscalerpro',
      [$this, 'render_app'],
      'dashicons-admin-generic'
    );
  }

  public function enqueue_assets($hook) {
    if ($hook !== 'toplevel_page_wpscalerpro') return;
    wp_enqueue_script(
      'wpsp-admin-app',
      plugins_url('../../assets/js/admin/main.js', __FILE__),
      ['wp-element'],
      WPSP_VERSION,
      true
    );
    wp_enqueue_style(
      'wpsp-admin-style',
      plugins_url('../../assets/css/admin.css', __FILE__),
      [],
      WPSP_VERSION
    );
  }

  public function render_app() {
    echo '<div id="wpsp-admin-root"></div>';
  }
}

new WPSP_Admin();
