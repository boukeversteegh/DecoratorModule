<?php

namespace boukeversteegh\DecoratorModule\Tests\Manager;

use boukeversteegh\DecoratorModule\DecoratorManager;

use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\HydratorPluginManager;

/**
 * Class DecoratorModuleTest
 *
 * @package boukeversteegh\DecoratorModule\Tests\Manager
 */
class DecoratorModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Setup the service manager
     */
    protected function setUp()
    {
    }

    /**
     * @test
     */
    public function it_should_be_initializable()
    {
        $dm = DecoratorManager::instance();
        $this->assertInstanceOf('boukeversteegh\DecoratorModule\DecoratorManager', $dm);
    }

    /**
     * @test
     */
    public function it_should_work()
    {
        $dm = DecoratorManager::instance();


        $dm->decorate('myBook','Fixtures\Entity\Book')
            ->use('\Fixtures\Traits\hasTitle');

        $dm->decorate('myBook')
            ->use('\Fixtures\Traits\hasAuthor')
            ->use('\Fixtures\Library\lendableTrait');

        $mybook = new \myBook;
        $mybook->setTitle('Book Title');
        $mybook->setAuthor('Author');
        $mybook->lendTo(new \Fixtures\Entity\User);

        $this->assertInstanceOf('Fixtures\Entity\Book', $mybook);
        $this->assertEquals('Book Title', $mybook->getTitle());
        $this->assertEquals('Author', $mybook->getAuthor());
    }

    /**
     * @test
     */
    public function it_should_allow_traits_of_different_namespaces()
    {
        $dm = DecoratorManager::instance();
        $dm->decorate('myBook', 'Fixtures\Entity\Book')
            ->use('\Fixtures\Traits\hasTitle');
        
        $this->assertTrue(class_exists('\\myBook'), 'Aliased class doesn\'t exist');

        $mybook = new \myBook;

        $this->assertClassHasAttribute('title', '\\myBook');
        $this->assertInstanceOf('Fixtures\Entity\Book', $mybook);
    }

    /**
     * @test
     */
    public function it_should_supper_adding_multiple_aliases()
    {
        $dm = DecoratorManager::instance();
        $dm->decorate('myBook1', 'Fixtures\Entity\Book');
        $dm->decorate('myBook2', 'Fixtures\Entity\Book');

        $this->assertTrue(class_exists('myBook1'), 'Aliased class doesn\'t exist');
        $this->assertTrue(class_exists('myBook2'), 'Aliased class doesn\'t exist');

        $this->assertInstanceOf('Fixtures\Entity\Book', new \myBook1);
        $this->assertInstanceOf('Fixtures\Entity\Book', new \myBook2);
    }

    /**
     * @test
     */
    public function it_should_keep_multiple_decorated_classes_separated()
    {
        $dm = DecoratorManager::instance();
        $dm->decorate('separate\b1', 'Fixtures\Entity\Book')
            ->use('\Fixtures\Traits\hasTitle');
        $dm->decorate('separate\b2', 'Fixtures\Entity\Book');

        new \separate\b1;
        new \separate\b2;
        $this->assertClassHasAttribute('title', '\separate\b1');
        $this->assertClassNotHasAttribute('title', 'separate\b2');
    }
}
