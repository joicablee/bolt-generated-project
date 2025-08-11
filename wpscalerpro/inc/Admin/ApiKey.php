<?php
namespace WpscalerPro\Admin;

require_once __DIR__ . '/../i18n.php';

class ApiKey
{
  public static function save($key, $locale = null)
  {
    // Simülasyon: Gerçek kaydetme işlemi burada yapılacak
    if ($key === 'fail') {
      return [
        'success' => false,
        'message' => wpsp_t('api_key_save_error', $locale)
      ];
    }
    // Başarı
    return [
      'success' => true,
      'message' => wpsp_t('api_key_saved', $locale)
    ];
  }

  public static function remove($locale = null)
  {
    // Simülasyon: Gerçek silme işlemi burada yapılacak
    $success = true;
    if ($success) {
      return [
        'success' => true,
        'message' => wpsp_t('api_key_removed', $locale)
      ];
    } else {
      return [
        'success' => false,
        'message' => wpsp_t('api_key_remove_error', $locale)
      ];
    }
  }
}
