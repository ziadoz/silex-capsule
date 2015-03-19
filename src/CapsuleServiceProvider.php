<?php
namespace Ziadoz\Silex\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

class CapsuleServiceProvider implements ServiceProviderInterface
{
    /**
     * Register the Capsule service.
     *
     * @param Silex\Application $app
     * @return void
     **/
    public function register(Application $app)
    {
        $app['capsule.connection_defaults'] = [
            'driver'    => 'mysql',
            'host'      => 'localhost',
            'database'  => null,
            'username'  => 'root',
            'password'  => null,
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => null,
            'logging'   => false,
        ];

        $app['capsule.global']   = true;
        $app['capsule.eloquent'] = true;

        $app['capsule.container'] = $app->share(function() {
            return new Container;
        });

        $app['capsule.dispatcher'] = $app->share(function() use($app) {
            return new Dispatcher($app['capsule.container']);
        });

        $app['capsule'] = $app->share(function($app) {
            $capsule = new Capsule($app['capsule.container']);
            $capsule->setEventDispatcher($app['capsule.dispatcher']);

            if ($app['capsule.global']) {
                $capsule->setAsGlobal();
            }

            if ($app['capsule.eloquent']) {
                $capsule->bootEloquent();
            }

            if (! isset($app['capsule.connections'])) {
                $app['capsule.connections'] = [
                    'default' => (isset($app['capsule.connection']) ? $app['capsule.connection'] : []),
                ];
            }

            foreach ($app['capsule.connections'] as $connection => $options) {
                $options = array_replace($app['capsule.connection_defaults'], $options);
                $logging = $options['logging'];
                unset($options['logging']);

                $capsule->addConnection($options, $connection);

                if ($logging) {
                    $capsule->connection($connection)->enableQueryLog();
                } else {
                    $capsule->connection($connection)->disableQueryLog();
                }
            }

            return $capsule;
        });
    }

    /**
     * Boot the Capsule service.
     *
     * @param Silex\Application $app
     * @return void
     **/
    public function boot(Application $app)
    {
        if ($app['capsule.eloquent']) {
            $app->before(function() use($app) {
                $app['capsule'];
            }, Application::EARLY_EVENT);
        }
    }
}