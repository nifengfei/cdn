<?php
/*
    Plugin Name: imwpf
    Plugin URI: http://www.imwpweb.com/tag/imwpf
    Description: wordpress功能增强扩展
    Version: 1.2.10
    Author: imwpweb
    Author URI: http://www.imwpweb.com
*/
define("IMWPF_URL", plugin_dir_url(__FILE__));

spl_autoload_register(function ($class) {
    $file = dirname(__DIR__) . '/' . str_replace('\\', '/', $class) . '.php';
    if (is_file($file)) {
        require_once $file;
        return ;
    }
    // 从dist文件夹中寻找
    $class = trim(str_replace('\\', '/', $class), '/');
    $components = explode('/', $class);
    $base = $components[0];
    $distBase = $base . '-dist';
    $class = str_replace($base, $distBase, $class);
    $file = dirname(__DIR__) . '/' . $class . '.php';
    if (is_file($file)) {
        require_once $file;
        return ;
    }
});

if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

\imwpf\builtin\Bootstrap::start();
