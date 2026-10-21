<?php

/**
 * TailorPro ERP - Front Controller Proxy for cPanel Root Deployment
 *
 * If the cPanel web root points directly to the project root instead of /public,
 * this file forwards execution to public/index.php.
 */

define('LARAVEL_START', microtime(true));

if (file_exists(__DIR__ . '/public/index.php')) {
    require __DIR__ . '/public/index.php';
}
