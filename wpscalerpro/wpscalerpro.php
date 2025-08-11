<?php
/*
Plugin Name: WPScaler Pro
Description: Gelişmiş ölçekleme ve SaaS entegrasyonu için WordPress eklentisi.
Version: 1.0.0
Author: Bolt AI
*/

if (!defined('ABSPATH')) exit;

define('WPSP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPSP_PLUGIN_URL', plugin_dir_url(__FILE__));

class WPScalerPro {
  public function __construct() {
    add_action('admin_menu', [$this, 'add_admin_menu']);
    add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
    add_action('rest_api_init', [$this, 'register_rest_routes']);
  }

  public function add_admin_menu() {
    add_menu_page(
      __('WPScaler Pro', 'wpscalerpro'),
      __('WPScaler Pro', 'wpscalerpro'),
      'manage_options',
      'wpscalerpro',
      [$this, 'render_admin_page'],
      'dashicons-admin-generic'
    );
  }

  public function render_admin_page() {
    echo '<div id="wpsp-admin-root"></div>';
  }

  public function enqueue_admin_assets($hook) {
    if ($hook !== 'toplevel_page_wpscalerpro') return;
    // Vite build edilmiş dosyayı yükle
    wp_enqueue_style('wpsp-admin-css', WPSP_PLUGIN_URL . 'assets/js/admin/admin.css', [], '1.0.0');
    wp_enqueue_script('wpsp-admin-js', WPSP_PLUGIN_URL . 'assets/js/admin/main.js', [], '1.0.0', true);
    // Çeviri dosyalarını frontend'e ilet
    wp_localize_script('wpsp-admin-js', 'wpspAdmin', [
      'ajax_url' => admin_url('admin-ajax.php'),
      'rest_url' => get_rest_url(),
      'nonce' => wp_create_nonce('wp_rest')
    ]);
  }

  public function register_rest_routes() {
    register_rest_route('wpscalerpro/v1', '/settings', [
      'methods' => ['GET', 'POST'],
      'callback' => [$this, 'handle_settings'],
      'permission_callback' => function () {
        return current_user_can('manage_options');
      }
    ]);
    register_rest_route('wpscalerpro/v1', '/locales', [
      'methods' => 'GET',
      'callback' => [$this, 'handle_locales'],
      'permission_callback' => function () {
        return current_user_can('manage_options');
      }
    ]);
  }

  public function handle_settings($request) {
    if ($request->get_method() === 'GET') {
      $api_key = get_option('wpsp_api_key', '');
      $locale = get_option('wpsp_locale', 'en');
      return [
        'api_key' => $api_key,
        'locale' => $locale,
      ];
    } else {
      $params = $request->get_json_params();
      if (isset($params['api_key'])) {
        update_option('wpsp_api_key', sanitize_text_field($params['api_key']));
      }
      if (isset($params['locale'])) {
        update_option('wpsp_locale', sanitize_text_field($params['locale']));
      }
      return [
        'success' => true,
        'message' => __('Ayarlar kaydedildi.', 'wpscalerpro')
      ];
    }
  }

  public function handle_locales() {
    // Dil dosyalarını oku
    $lang_dir = WPSP_PLUGIN_DIR . 'languages/';
    $locales = [];
    foreach (glob($lang_dir . '*.json') as $file) {
      $code = basename($file, '.json');
      $label = ucfirst($code);
      if ($code === 'en') $label = 'English';
      if ($code === 'tr') $label = 'Türkçe';
      $locales[$code] = $label;
    }
    return $locales;
  }
}

new WPScalerPro();
