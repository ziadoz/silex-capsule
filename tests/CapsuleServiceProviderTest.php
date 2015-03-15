<?php
namespace Ziadoz\Silex\Provider\Tests;

use PHPUnit_Framework_TestCase;
use Silex\Application;
use Ziadoz\Silex\Provider\CapsuleServiceProvider;

class CapsuleServiceProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * Get a Silex application with the service registered against it.
     *
     * @return array
     **/
    public function getApplication()
    {
        $app = new Application();
        $app->register(new CapsuleServiceProvider(), [
            'capsule.connection' => [
                'driver'    => 'sqlite',
                'database'  => ':memory:',
                'prefix'    => '',
            ],
        ]);

        return $app;
    }

    /**
     * Test the Capsule service registers correctly.
     *
     * @var string
     **/
    public function testServiceRegisters()
    {
        $app = $this->getApplication();
        $this->assertInstanceOf('Illuminate\Database\Capsule\Manager', $app['capsule']);
    }

    /**
     * Test the Capsule connection is accessible.
     *
     * @var string
     **/
    public function testConnection()
    {
        $app = $this->getApplication();
        $this->assertInstanceOf('Illuminate\Database\Connection', $app['capsule']->connection());
    }

    /**
     * Test the Capsule schema is accessible.
     *
     * @var string
     **/
    public function testSchema()
    {
        $app = $this->getApplication();
        $this->assertInstanceOf('Illuminate\Database\Schema\Builder', $app['capsule']->schema());
    }    
}