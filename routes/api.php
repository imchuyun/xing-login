<?php
/**
 * API路由
 */

use Core\Router;

/** @var Router $router */
$router->group('/api', function ($r) {
    $r->group('/v1', function ($v1) {
        $v1->post('/app/create', 'Api\AppController@create');
        $v1->post('/app/update', 'Api\AppController@update');
        $v1->post('/app/delete', 'Api\AppController@delete');
        $v1->get('/app/list', 'Api\AppController@list');
        $v1->get('/app/info', 'Api\AppController@info');
        $v1->get('/app/logs', 'Api\AppController@logs');
        
    });
    $r->get('/status', function () {
        success([
            'version' => config('api.version'),
            'time' => date('Y-m-d H:i:s'),
        ], 'API is running');
    });
    
});
