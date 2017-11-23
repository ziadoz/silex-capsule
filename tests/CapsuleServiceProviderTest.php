<?php
namespace Ziadoz\Silex\Provider\Tests;

use Silex\Application;
use Ziadoz\Silex\Provider\CapsuleServiceProvider;
use Illuminate\Database\Capsule\Manager as Capsule;
use Book;

class CapsuleServiceProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * The Silex application.
     *
     * @var Application
     **/
    protected $app;

    /**
     * A PDO database connection.
     *
     * @var PDO
     **/
    protected $pdo;

    /**
     * Setup the application.
     *
     * @return array
     **/
    public function setUp()
    {
        $this->pdo = new \PDO('sqlite::memory:?cache=shared');
        $this->pdo->exec(file_get_contents(__DIR__ . '/fixtures/books.sql'));

        $this->app = new Application();
        $this->app->register(new CapsuleServiceProvider(), [
            'capsule.connection' => [
                'driver'    => 'sqlite',
                'database'  => ':memory:?cache=shared',
                'prefix'    => '',
            ],
        ]);

        $this->app['capsule'];
    }

    /**
     * Test the Capsule service registers correctly.
     **/
    public function testServiceRegisters()
    {
        $this->assertTrue(isset($this->app['capsule']));
        $this->assertTrue(isset($this->app['capsule.container']));
        $this->assertTrue(isset($this->app['capsule.dispatcher']));

        $this->assertInstanceOf('Illuminate\Database\Capsule\Manager', $this->app['capsule']);
        $this->assertInstanceOf('Illuminate\Container\Container', $this->app['capsule.container']);
        $this->assertInstanceOf('Illuminate\Events\Dispatcher', $this->app['capsule.dispatcher']);
        $this->assertInstanceOf('Illuminate\Database\Connection', $this->app['capsule']->connection());
        $this->assertInstanceOf('Illuminate\Database\Schema\Builder', $this->app['capsule']->schema());
    }

    /**
     * Test the Capsule global.
     */
    public function testCapsuleGlobal()
    {
        $books = Capsule::table('books')->get();
        $this->assertEquals(count($books), 5);
        $this->assertEquals($books[0]->author, 'Terry Pratchett');
    }

    /**
     * Test an Eloquent model.
     */
    public function testEloquentModel()
    {
        require_once __DIR__ . '/Model/Book.php';

        $books = Book::all();
        $this->assertEquals(count($books), 5);

        $book         = new Book();
        $book->title  = "The Hitchhiker's Guide to the Galaxy";
        $book->author = "Douglas Adams";
        $this->assertTrue($book->save());

        $result = Book::find($book->id);
        $this->assertEquals($book->id, $result->id);
    }

    /**
     * Destroy the application.
     *
     * @return array
     **/
    public function tearDown()
    {
        $this->app = null;
        $this->pdo->exec('DELETE * FROM books');
        $this->pdo->exec('DROP TABLE books');
        $this->pdo = null;
    }  
}