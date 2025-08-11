<?php
class WPSP_Api_Rest {
  public function __construct() {
    add_action('rest_api_init', [$this, 'register_routes']);
  }

  public function register_routes() {
    register_rest_route('wpscalerpro/v1', '/ping', [
      'methods' => 'GET',
      'callback' => [$this, 'ping'],
      'permission_callback' => '__return_true'
    ]);
  }

  public function ping($request) {
    return ['pong' => true, 'version' => WPSP_VERSION];
  }
}

new WPSP_Api_Rest();
