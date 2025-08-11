<?php
// Basit dosya tabanlı i18n desteği

function wpsp_t($key, $locale = null) {
  static $translations = [];
  if ($locale === null) {
    $locale = wpsp_get_option('locale', 'en');
  }
  if (!isset($translations[$locale])) {
    $translations[$locale] = wpsp_load_translations($locale);
  }
  $dict = $translations[$locale];
  return isset($dict[$key]) ? $dict[$key] : $key;
}

function wpsp_load_translations($locale) {
  $file = dirname(__DIR__) . "/languages/$locale.json";
  if (file_exists($file)) {
    $json = file_get_contents($file);
    $data = json_decode($json, true);
    if (is_array($data)) {
      return $data;
    }
  }
  return [];
}
