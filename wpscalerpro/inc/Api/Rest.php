<?php
namespace WpscalerPro\Api;

use WpscalerPro\Admin\ApiKey;

require_once __DIR__ . '/../i18n.php';

class Rest {
  public static function register_routes() {
    register_rest_route('wpscalerpro/v1', '/apikey', [
      'methods' => ['GET', 'POST'],
      'callback' => [self::class, 'handle_apikey'],
      'permission_callback' => function () {
        return current_user_can('manage_options');
      },
    ]);
    register_rest_route('wpscalerpro/v1', '/ping', [
      'methods' => ['GET'],
      'callback' => [self::class, 'handle_ping'],
      'permission_callback' => '__return_true',
    ]);
    // Yeni örnek endpoint: /wpscalerpro/v1/test-error
    register_rest_route('wpscalerpro/v1', '/test-error', [
      'methods' => ['GET'],
      'callback' => [self::class, 'handle_test_error'],
      'permission_callback' => '__return_true',
    ]);
  }

  public static function handle_apikey($request) {
    $locale = $request->get_param('locale') ?: wpsp_get_locale();
    if ($request->get_method() === 'GET') {
      $apiKey = ApiKey::get();
      $masked = $apiKey ? ApiKey::mask($apiKey) : '';
      return [
        'apiKey' => $masked,
        'message' => '',
        'locale' => $locale,
      ];
    }

    // POST
    $params = $request->get_json_params();
    $newKey = isset($params['apiKey']) ? trim($params['apiKey']) : '';
    if ($newKey === '') {
      // Delete
      $ok = ApiKey::delete();
      return [
        'apiKey' => '',
        'message' => $ok ? wpsp_t('api_key_removed', $locale) : wpsp_t('api_key_remove_error', $locale),
        'locale' => $locale,
      ];
    } else {
      // Save
      $ok = ApiKey::save($newKey);
      return [
        'apiKey' => $ok ? ApiKey::mask($newKey) : '',
        'message' => $ok ? wpsp_t('api_key_saved', $locale) : wpsp_t('api_key_save_error', $locale),
        'locale' => $locale,
      ];
    }
  }

  public static function handle_ping($request) {
    $locale = $request->get_param('locale') ?: wpsp_get_locale();
    return [
      'status' => 'ok',
      'message' => wpsp_t('pong', $locale),
      'locale' => $locale,
    ];
  }

  // Yeni örnek endpoint: Hata ve başarı mesajı i18n ile
  public static function handle_test_error($request) {
    $locale = $request->get_param('locale') ?: wpsp_get_locale();
    $fail = $request->get_param('fail');
    if ($fail) {
      return [
        'status' => 'error',
        'message' => wpsp_t('sample_error', $locale, ['reason' => 'Test failure']),
        'locale' => $locale,
      ];
    }
    return [
      'status' => 'success',
      'message' => wpsp_t('sample_success', $locale),
      'locale' => $locale,
    ];
  }
}
