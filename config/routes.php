<?php
use Cake\Routing\Router;

Router::plugin('Queue', ['path' => '/'], function ($routes) {
	$routes->prefix('admin', function ($routes) {
		$routes->connect('/:controller');
    $routes->fallbacks('InflectedRoute');	});
});
