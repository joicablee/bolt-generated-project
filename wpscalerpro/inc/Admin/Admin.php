<?php
// Admin panel loader - i18n helper dahil
require_once __DIR__ . '/../i18n.php';
require_once __DIR__ . '/ApiKey.php';

// Burada admin panel başlık ve açıklama metinleri i18n ile alınabilir
function wpsp_admin_panel_title($locale = null) {
  return wpsp_t('api_key_management', $locale);
}
function wpsp_admin_panel_subtitle($locale = null) {
  return wpsp_t('api_key_management_subtitle', $locale);
}

// Ek örnek: Admin panelinde başka bir statik metin gerekiyorsa i18n ile alınmalı
function wpsp_admin_panel_info($locale = null) {
  return wpsp_t('admin_panel_info', $locale);
}
