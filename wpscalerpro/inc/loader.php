<?php
// Plugin loader: loads all modules

require_once __DIR__ . '/i18n.php';
require_once __DIR__ . '/Admin/ApiKey.php';
require_once __DIR__ . '/Api/Rest.php';
require_once __DIR__ . '/Admin/Admin.php';

add_action('rest_api_init', ['WpscalerPro\Api\Rest', 'register_routes']);
