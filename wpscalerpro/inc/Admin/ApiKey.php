<?php
namespace WpscalerPro\Admin;

class ApiKey {
  const OPTION = 'wpscalerpro_api_key';

  public static function get() {
    return get_option(self::OPTION, '');
  }

  public static function save($key) {
    return update_option(self::OPTION, $key, false);
  }

  public static function delete() {
    return delete_option(self::OPTION);
  }

  public static function mask($key) {
    if (!$key) return '';
    $len = strlen($key);
    if ($len <= 8) return str_repeat('*', $len);
    return substr($key, 0, 4) . str_repeat('*', $len - 8) . substr($key, -4);
  }
}
