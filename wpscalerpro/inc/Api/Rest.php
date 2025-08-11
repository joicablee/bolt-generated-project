<?php
// WpscalerPro REST API endpointleri

require_once __DIR__ . '/../i18n.php';
require_once __DIR__ . '/../Admin/Settings.php';

add_action('rest_api_init', function () {
  register_rest_route('wpscalerpro/v1', '/settings', [
    'methods' => ['GET', 'POST'],
    'permission_callback' => function () {
      return current_user_can('manage_options');
    },
    'callback' => 'wpsp_rest_settings_handler',
  ]);

  register_rest_route('wpscalerpro/v1', '/locales', [
    'methods' => 'GET',
    'permission_callback' => function () {
      return current_user_can('manage_options');
    },
    'callback' => 'wpsp_rest_locales_handler',
  ]);
});

function wpsp_rest_settings_handler($request) {
  if ($request->get_method() === 'POST') {
    $params = $request->get_json_params();
    $api_key = isset($params['api_key']) ? sanitize_text_field($params['api_key']) : '';
    $locale = isset($params['locale']) ? sanitize_text_field($params['locale']) : 'en';

    wpsp_set_option('api_key', $api_key);
    wpsp_set_option('locale', $locale);

    return [
      'success' => true,
      'message' => wpsp_t('settings_saved', $locale),
      'api_key' => $api_key,
      'locale' => $locale,
    ];
  } else {
    $api_key = wpsp_get_option('api_key', '');
    $locale = wpsp_get_option('locale', 'en');
    return [
      'api_key' => $api_key,
      'locale' => $locale,
    ];
  }
}

function wpsp_rest_locales_handler($request) {
  $locales_dir = dirname(__DIR__, 2) . '/languages';
  $locales = [];
  foreach (glob($locales_dir . '/*.json') as $file) {
    $code = basename($file, '.json');
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    $locales[$code] = [
      'code' => $code,
      'name' => isset($data['language_name']) ? $data['language_name'] : strtoupper($code),
      'rtl' => !empty($data['rtl']),
    ];
  }
  return $locales;
}
