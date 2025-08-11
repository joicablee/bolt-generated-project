<?php
// Otomatik class yükleyici ve temel bootstrap
spl_autoload_register(function($class) {
  $prefix = 'WPSP_';
  $base_dir = __DIR__ . '/';
  if (strpos($class, $prefix) === 0) {
    $file = $base_dir . str_replace('_', '/', substr($class, strlen($prefix))) . '.php';
    if (file_exists($file)) require $file;
  }
});

// Temel admin ve API endpointlerini yükle
if (is_admin()) {
  require_once __DIR__ . '/Admin/Admin.php';
}
require_once __DIR__ . '/Api/Rest.php';
