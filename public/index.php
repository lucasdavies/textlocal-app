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

// Doctrine DBAL
$app->register(new Silex\Provider\DoctrineServiceProvider, [
    'db.options' => [
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__ . '/../demo.db',
    ],
]);

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
    
    // Only expect digits
    $recipients = explode(',', $recipients);
    $recipients = preg_replace('/\s+/', '', $recipients);
    $recipients = array_filter($recipients, 'ctype_digit');
    $recipients = array_unique($recipients);
    
    // Trim the message so we don't send any more characters that are necessary
    $message = trim($message);
    
    // Send SMS
    try {
        $app['textlocal']->sendSms(
            $recipients,
            $message,
            'Textlocal Demo App',
            null,
            true
        );
    } catch (Exception $e) {
        $app['session']->getFlashBag()->add('error', 'There was a problem sending your message. Please try again.');
        return $app->redirect('/');
    }
    
    // Save message
    $app['db']->insert('messages', ['message' => $message]);
    $message_id = $app['db']->lastInsertId();
    
    // Save recipients
    foreach ($recipients as $recipient) {
        $app['db']->insert('message_recipients', [
            'message_id' => $message_id,
            'number'     => $recipient,
        ]);
    }
    
    $app['session']->getFlashBag()->add('success', 'Message successfully sent.');
    return $app->redirect('/');
})->bind('send');

$app->get('/sent', function () use ($app) {
    // Get sent messages
    $query = 'SELECT
                  messages.id,
                  GROUP_CONCAT(message_recipients.number, ", ") recipients,
                  messages.sent
              FROM messages
              INNER JOIN message_recipients ON message_recipients.message_id = messages.id
              GROUP BY messages.id
              ORDER BY sent DESC';
              
    $messages = $app['db']->fetchAll($query);
    
    return $app['twig']->render('sent.twig', [
        'title'    => 'Sent',
        'messages' => $messages
    ]);
})->bind('sent');

$app->get('/message/{id}', function ($id) use ($app) {
    $query = 'SELECT
                  messages.id,
                  GROUP_CONCAT(message_recipients.number, ", ") recipients,
                  messages.message,
                  messages.sent
              FROM messages
              INNER JOIN message_recipients ON message_recipients.message_id = messages.id
              WHERE messages.id = ?
              GROUP BY messages.id';
              
    $message = $app['db']->fetchAssoc($query, [$id]);
    
    if (! $message) {
        return $app->redirect('/');
    }
    
    return $app['twig']->render('message.twig', [
        'title'   => 'Message',
        'message' => $message
    ]);
})->assert('id', '\d+')->bind('message');

$app->run();
