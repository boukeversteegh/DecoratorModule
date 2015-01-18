<?php

namespace boukeversteegh\DecoratorModule\Tests;

use boukeversteegh\DecoratorModule\Module;

/**
 * Class ModuleTest
 *
 * @package boukeversteegh\DecoratorModule
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_be_initializable()
    {
        $module = new Module();
        $this->assertInstanceOf('boukeversteegh\DecoratorModule\Module', $module);
    }
}
