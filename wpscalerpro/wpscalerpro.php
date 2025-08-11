<?php
/**
 * Plugin Name: WpscalerPro
 * Description: AI destekli WooCommerce içerik ve görsel optimizasyon eklentisi.
 * Version: 0.1.0
 * Author: WpscalerPro Team
 * Text Domain: wpscalerpro
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'WPSP_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPSP_URL', plugin_dir_url( __FILE__ ) );
define( 'WPSP_VERSION', '0.1.0' );

require_once WPSP_PATH . 'inc/loader.php';

// Eklenti yüklenirken dil dosyalarını yükle
add_action( 'plugins_loaded', function() {
  load_plugin_textdomain( 'wpscalerpro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
});
