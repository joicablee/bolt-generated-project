<?php
// Simple file-based i18n helper for backend

function wpsp_get_locale() {
  // 1. Try ?locale=xx param
  if (isset($_GET['locale']) && preg_match('/^[a-z]{2}$/', $_GET['locale'])) {
    return $_GET['locale'];
  }
  // 2. Try HTTP header
  if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $langs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    if (!empty($langs)) {
      $lang = substr($langs[0], 0, 2);
      if (preg_match('/^[a-z]{2}$/', $lang)) return $lang;
    }
  }
  // 3. Default
  return 'tr';
}

function wpsp_load_translations($locale = null) {
  if (!$locale) $locale = wpsp_get_locale();
  $file = __DIR__ . "/../languages/$locale.json";
  if (!file_exists($file)) $file = __DIR__ . "/../languages/tr.json";
  $json = file_get_contents($file);
  return json_decode($json, true) ?: [];
}

function wpsp_t($key, $locale = null, $replacements = []) {
  static $cache = [];
  $locale = $locale ?: wpsp_get_locale();
  if (!isset($cache[$locale])) {
    $cache[$locale] = wpsp_load_translations($locale);
  }
  $text = $cache[$locale][$key] ?? $key;
  // Basit değişken yerleştirme desteği
  if (!empty($replacements) && is_array($replacements)) {
    foreach ($replacements as $k => $v) {
      $text = str_replace("{{{$k}}}", $v, $text);
    }
  }
  return $text;
}
