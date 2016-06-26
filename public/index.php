<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application;

$app['debug'] = filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN) ?: false;

/*
|--------------------------------------------------------------------------
| Service Providers
|--------------------------------------------------------------------------
*/

// Session
$app->register(new Silex\Provider\SessionServiceProvider);

// Twig templating engine
$app->register(new Silex\Provider\TwigServiceProvider, [
    'twig.path' => __DIR__ . '/../views',
]);

// Add global layout for templates
$app->before(function () use ($app) {
    $app['twig']->addGlobal('layout', $app['twig']->loadTemplate('layout.twig'));
});

// Textlocal
$app->register(new LucasDavies\Silex\TextlocalServiceProvider, [
    'textlocal.username' => $_ENV['TEXTLOCAL_USERNAME'],
    'textlocal.hash'     => $_ENV['TEXTLOCAL_HASH'],
    'textlocal.apiKey'   => $_ENV['TEXTLOCAL_APIKEY']
]);

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
*/

$app->get('/', function () use ($app) {
    return $app['twig']->render('dashboard.twig', [
        'title' => 'Dashboard',
    ]);
})->bind('dashboard');

$app->get('/compose', function () use ($app) {
    return $app['twig']->render('compose.twig', [
        'title' => 'Compose',
    ]);
})->bind('compose');

$app->post('/send', function (Request $request) use ($app) {
    $recipients = $request->get('recipients');
    $message = $request->get('message');
    
    // Error handling...
    
    // Only expect digits
    $recipients = explode(',', $recipients);
    $recipients = preg_replace('/\s+/', '', $recipients);
    $recipients = array_filter($recipients, 'ctype_digit');
    $recipients = array_unique($recipients);
    
    // Trim the message so we don't send any more characters that are necessary
    $message = trim($message);
    
    // Send SMS
    $app['textlocal']->sendSms(
        $recipients,
        $message,
        'Textlocal Demo App'
    );
    
    $app['session']->getFlashBag()->add('success', 'Message successfully sent.');
    return $app->redirect('/');
})->bind('send');

$app->get('/inbox', function () use ($app) {
    return $app['twig']->render('inbox.twig', [
        'title' => 'Inbox',
    ]);
})->bind('inbox');

$app->get('/sent', function () use ($app) {
    return $app['twig']->render('sent.twig', [
        'title' => 'Sent',
    ]);
})->bind('sent');

$app->get('/message/{id}', function ($id) use ($app) {
    return $app['twig']->render('message.twig', [
        'title' => 'Message',
    ]);
})->assert('id', '\d+')->bind('message');

$app->get('/contacts', function () use ($app) {
    return $app['twig']->render('contacts.twig', [
        'title' => 'Contacts',
    ]);
})->bind('contacts');

$app->run();
