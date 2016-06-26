# Textlocal Demo App
A simple Silex app that demonstrates the integration of the Textlocal API.

Developed locally on OS X running [Laravel Valet](https://laravel.com/docs/5.2/valet) and PHP 7, this super simple demo app makes use of the [Silex microframework (v2)](https://github.com/silexphp/Silex) and a two-table SQLite database.

Utilises the following packages and libraries:

* [PHP Dotenv](https://github.com/vlucas/phpdotenv) - For environment variable configuration, specifically the Textlocal configuration
* [Doctrine DBAL](https://github.com/doctrine/dbal) - For a very basic SQLite database
* [Twig](https://github.com/twigphp/Twig) - Front end templating engine
* [Textlocal Service Provider](https://github.com/lucasdavies/silex-textlocal-service-provider) - Built for this demo, a Silex-specific service provider to expose the Textlocal API
* [Phinx](https://github.com/robmorgan/phinx/) - For database migrations
* [Symfony VarDumper Component](https://github.com/symfony/var-dumper) - For basic output debugging

## Installation
```
composer create-project lucasdavies/textlocal-demo-app -s dev
```

Update .env with your Textlocal credentials.

```
TEXTLOCAL_USERNAME="xxxxxx"
TEXTLOCAL_HASH="xxxxxx"
TEXTLOCAL_APIKEY="xxxxxx"
```
