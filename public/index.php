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

// Twig templating engine
$app->register(new Silex\Provider\TwigServiceProvider, array(
    'twig.path' => __DIR__ . '/../views',
));

// Add global layout for templates
$app->before(function () use ($app) {
    $app['twig']->addGlobal('layout', $app['twig']->loadTemplate('layout.twig'));
});

// Textlocal
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
    return $app['twig']->render('dashboard.twig', array(
        'title' => 'Dashboard',
    ));
})->bind('dashboard');

$app->get('/compose', function () use ($app) {
    return $app['twig']->render('compose.twig', array(
        'title' => 'Compose',
    ));
})->bind('compose');

$app->get('/inbox', function () use ($app) {
    return $app['twig']->render('inbox.twig', array(
        'title' => 'Inbox',
    ));
})->bind('inbox');

$app->get('/sent', function () use ($app) {
    return $app['twig']->render('sent.twig', array(
        'title' => 'Sent',
    ));
})->bind('sent');

$app->get('/message/{id}', function ($id) use ($app) {
    return $app['twig']->render('message.twig', array(
        'title' => 'Message',
    ));
})->assert('id', '\d+')->bind('message');

$app->get('/contacts', function () use ($app) {
    return $app['twig']->render('contacts.twig', array(
        'title' => 'Contacts',
    ));
})->bind('contacts');

$app->run();
