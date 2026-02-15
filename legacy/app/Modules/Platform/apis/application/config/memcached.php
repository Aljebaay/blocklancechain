<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Memcached settings
| -------------------------------------------------------------------------
| Your Memcached servers can be specified below.
|
|	See: https://codeigniter.com/user_guide/libraries/caching.html#memcached
|
*/
$memcachedHost = getenv('MEMCACHED_HOST');
$memcachedPort = getenv('MEMCACHED_PORT');
$memcachedWeight = getenv('MEMCACHED_WEIGHT');

$config = array(
	'default' => array(
		'hostname' => $memcachedHost !== false && $memcachedHost !== '' ? (string) $memcachedHost : '127.0.0.1',
		'port'     => $memcachedPort !== false && $memcachedPort !== '' ? (string) $memcachedPort : '11211',
		'weight'   => $memcachedWeight !== false && $memcachedWeight !== '' ? (string) $memcachedWeight : '1',
	),
);
