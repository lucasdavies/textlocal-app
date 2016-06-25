<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

$app = new Silex\Application;

$app['debug'] = filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN) ?: false;

/*
|--------------------------------------------------------------------------
| Service Providers
|--------------------------------------------------------------------------
*/

$app->register(new LucasDavies\Silex\TextlocalServiceProvider, array(
    'textlocal.username' => $_ENV['TEXTLOCAL_USERNAME'],
    'textlocal.hash'     => $_ENV['TEXTLOCAL_HASH'],
    'textlocal.apiKey'   => $_ENV['TEXTLOCAL_APIKEY']
));

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

$app->get('/', function () use ($app) {
    return 'Dashboard';
});

$app->get('/compose', function () {
    return 'New message';
});

$app->get('/inbox', function () {
    return 'Inbox';
});

$app->get('/sent', function () {
    return 'Sent';
});

$app->get('/message/{id}', function ($id) {
    return 'Message ' . $id;
})->assert('id', '\d+');

$app->get('/contacts', function () {
    return 'Contacts';
});

$app->run();
