# Silex Capsule Service Provider

This is a service provider for the [Silex Micro Framework](http://silex.sensiolabs.org/) that integrates [Laravel's Eloquent ORM](http://laravel.com/docs/5.0/eloquent) via [Capsule](https://github.com/illuminate/database), its standalone wrapper implementation.

## Requirements

In order to use the service provider you'll need to be running **PHP 5.4+**

## Installation

The best way to install the service provider is using [Composer](https://getcomposer.org):

````shell
composer require ziadoz/silex-capsule:1.0
````

Alternatively, you can add it directly to your `composer.json` file: 

````json
{
    "require": {
        "ziadoz/silex-capsule": "1.0"
    }
}
````

## Basic Usage

To use it in your application just register the service provider with Silex: 

````php
<?php        
$app = new Silex\Application;

$app->register(new Ziadoz\Silex\Provider\CapsuleServiceProvider, [
    'capsule.connection' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'database',
        'username'  => 'username',
        'password'  => 'password',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
        'logging'   => true,
    ],
]);
````

For more information about the available options you should refer to the [Capsule documentation](https://github.com/illuminate/database).

Once you've registered the service provider a Capsule object will be created and Eloquent booted up before any of your route controllers are called. If you're interested in the technical aspects, Capsule is registered as `before` middleware with Silex using `Application::EARLY_EVENT`, so it'll only ever be made available once your application is run and before anything else happens.

If you need Capsule and Eloquent to be booted up before your application is run (`$app->run()`), for example in a Symfony Console command, you just need to access its array element within the dependency injection container: 

````php
$app['capsule']; 
````

Capsule will be made available globally by default, allowing you to write queries directly in your controllers should you wish:

````php
use Illuminate\Database\Capsule\Manager as Capsule;

$app->get('/book/{id}', function(Application $app, $id) {
    $book = Capsule::table('books')->where('id', $id)->get();
    return $app->json($book);
});
````

If you don't want Capsule to be booted then set the `capsule.global` setting to `false`. If you don't plan to use Eloquent to build models then you can also prevent it from being booted by setting `capsule.eloquent` to `false`.

Creating Eloquent models is identical to how you would create them in Laravel: 

````php
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = 'books';

    protected $fillable = [
        'title', 
        'author',
    ];

    protected $casts = [
        'title'  => 'string',
        'author' => 'string',
    ];

    // The rest of your model code...
}
````

You can then use it, and all of its features, in your controllers just like you would in Laravel: 

````php
$app->get('/books', function(Application $app) {
    $books = Book::with('tags')->all();
    return $app->json($books);
});

$app->post('/book', function(Application $app, Request $request) {
    $book         = new Book();
    $book->title  = $request->request->get('title');
    $book->author = $request->request->get('author');
    $book->save();
});
````

## Advanced Usage

You can setup multiple connections and even caching with the service provider; simply use the `capsule.connections` and `capsule.cache` options:

````php
<?php
$app = new Silex\Application;

$app->register(new Ziadoz\Silex\Provider\CapsuleServiceProvider, [
    // Connections
    'capsule.connections' => [
        'default' => [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'dname1',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'logging'   => false,
        ],

        'other' => [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'dbname2',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'logging'   => true,
        ],
    ],

    // Cache
    'capsule.cache' => [
        'driver' => 'apc',
        'prefix' => 'laravel',
    ],
]);
````

If you want to use caching you'll need to install the Laravel Events package, which you can do using Composer: 

````
composer require illuminte/events:5.*
````

Alternatively, you can add it directly to your `composer.json` file: 

````json
{
    "require": {
        "illuminate/events": "5.*"
    }
}
````
    
If you've enabled query logging on your connection, you can retrieve the log through Capsule: 

````php
Capsule::connection($name)->getQueryLog();
````

You can also use Eloquent's schema building tools, for example to build migrations: 

````php
$app['capsule']->schema()->create('books', function($table) {
    $table->increments('id');
    $table->string('title');
    $table->string('author');
    $table->timestamps();
});
````

## Options Example

The following is an example of all the available options that you can pass to the service provider: 

````php
<?php
$app = new Silex\Application;

$app->register(new Ziadoz\Silex\Provider\CapsuleServiceProvider, [
    // Multiple Connections
    'capsule.connections' => [
        'default' => [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'dname1',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'logging'   => false, // Toggle query logging on this connection.
        ],

        'other' => [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => 'dbname2',
            'username'  => 'root',
            'password'  => '',
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
            'logging'   => true,  // Toggle query logging on this connection.
        ],
    ],

    /*
    // Single Connection
    'capsule.connection' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'dbname',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => '',
        'logging'   => true, // Toggle query logging on this connection.
    ],
    */

    // Cache
    'capsule.cache' => [
        'driver' => 'apc',
        'prefix' => 'laravel',
    ],
    
    /*
    // Cache Options
    'capsule.cache' => [
        'driver'        => 'file',
        'path'          => '/path/to/cache',
        'connection'    => null,
        'table'         => 'cache',

        'memcached' => [
            [
                'host'      => '127.0.0.1',
                'port'      => 11211,
                'weight'    => 100
            ],
        ],

        'prefix' => 'laravel',
    ),
    */
    
    /*
    // Other Options
    'capsule.global'   => true, // Enable global access to Capsule query builder.
    'capsule.eloquent' => true, // Automatically boot Eloquent ORM.
    */
]);
````

## Testing

There are some basic tests to ensure that the Capsule object is correctly registered with Silex. You can run them using the [PHPUnit](https://phpunit.de/), but you'll also need SQLite as the tests use a simple in-memory database. 

If you make a pull request please ensure you add the accompanying tests.