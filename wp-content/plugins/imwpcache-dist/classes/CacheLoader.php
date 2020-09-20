<?php
 namespace imwpcache\classes; class CacheLoader { protected static $Cache = null; protected static $config = null; public static function load() { if (self::$Cache != null) { return self::$Cache; } $config = self::config(); if (!$config) { return false; } $driver = "\\imwpcache\\drivers\\" . $config['type']; self::$Cache = new $driver; if (!self::$Cache->connect($config)) { return false; } return self::$Cache; } public static function config() { if (self::$config != null) { return self::$config; } $dir = dirname(__DIR__); if (!file_exists($dir . '/config/cache.php')) { return false; } self::$config = require $dir . '/config/cache.php'; return self::$config; } } 