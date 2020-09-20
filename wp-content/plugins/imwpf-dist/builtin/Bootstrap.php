<?php
namespace imwpf\builtin; use imwpf\admin\Options; class Bootstrap { public static function start() { $options = Options::getInstance('imwpf_options')->get('system'); if (empty($options)) { $options = array(); } $Updater = new \imwpf\apis\Updater(); $Updater->start(); $Upload = new \imwpf\apis\Upload(); $Upload->start(); $Weixin = new \imwpf\apis\Weixin(); $Weixin->start(); $UI = new \imwpf\admin\UI(); $UI->start(); if (in_array('papi', $options)) { $Publish = new \imwpf\apis\Publish(); $Publish->start(); } if (in_array('optimize', $options)) { $Optimizer = new \imwpf\builtin\Optimizer(); $Optimizer->start(); } if (in_array('admin', $options)) { $AdminPower = new \imwpf\builtin\AdminPower(); $AdminPower->start(); } if (in_array('seo', $options)) { $SEO = new \imwpf\builtin\SEO(); $SEO->start(); } register_shutdown_function(function () { if (function_exists("fastcgi_finish_request")) { session_write_close(); set_time_limit(\imwpf\modules\Cron::MAX_EXECUTE_TIME); fastcgi_finish_request(); } \imwpf\modules\Cron::execute(); }); } } 