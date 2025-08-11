<?php
namespace WpscalerPro\Admin;

require_once __DIR__ . '/../i18n.php';

class Admin {
  public static function init() {
    add_action('admin_menu', [self::class, 'add_menu']);
  }

  public static function add_menu() {
    $locale = wpsp_get_locale();
    add_menu_page(
      wpsp_t('api_key_management', $locale),
      'WpscalerPro',
      'manage_options',
      'wpscalerpro',
      [self::class, 'render_page'],
      'dashicons-shield-alt'
    );
  }

  public static function render_page() {
    $locale = wpsp_get_locale();
    ?>
    <div class="wrap">
      <h1><?php echo esc_html(wpsp_t('api_key_management', $locale)); ?></h1>
      <p><?php echo esc_html(wpsp_t('api_key_management_subtitle', $locale)); ?></p>
      <div id="wpscalerpro-admin-root"></div>
    </div>
    <?php
  }
}

add_action('admin_init', ['WpscalerPro\Admin\Admin', 'init']);
