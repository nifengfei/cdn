<?php
 namespace imwpcache\drivers; interface driver { public function connect($config); public function set($key, $value, $expires); public function get($key); public function delete($key); public function exists($key); public function flush(); public function getStats(); public function isExpire($key = null); }