<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Silex\Application;

$app['debug'] = true;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

$app->get('/', function () {
    return 'Dashboard';
});

$app->get('/inbox', function () {
    return 'Inbox';
});

$app->get('/inbox/message/{id}', function ($id) {
    return 'Inbox message ' . $id;
})->assert('id', '\d+');

$app->get('/sent', function () {
    return 'Sent';
});

$app->get('/sent/message{id}', function ($id) {
    return 'Sent message ' . $id;
})->assert('id', '\d+');

$app->get('/contacts', function () {
    return 'Contacts';
});

$app->run();
