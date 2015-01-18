<?php


namespace boukeversteegh\DecoratorModule;

error_reporting(E_ALL | E_STRICT);
define('PROJECT_BASE_PATH', __DIR__.'/../..');
define('TEST_BASE_PATH', __DIR__.'/..');

$autoloadFile = PROJECT_BASE_PATH.'/vendor/autoload.php';
if (!file_exists($autoloadFile)) {
    throw new \RuntimeException('Install dependencies to run test suite.');
}

/**
 * Test bootstrap, for setting up autoloading etc.
 *
 * @package boukeversteegh\DecoratorModule
 */
class Bootstrap
{
    /**
     * @var \Composer\Autoload\ClassLoader
     */
    protected $autoLoader;

    /**
     * @param $autoLoader
     */
    public function __construct($autoLoader)
    {
        $this->autoLoader = $autoLoader;
    }

    /**
     * Bootstrap the tests:
     */
    public function init()
    {
        $this->initAutoLoading();
    }

    /**
     * Add dependencies:
     */
    protected function initAutoLoading()
    {   
        // Why u no work?
        $this->autoLoader->addPsr4('boukeversteegh\\DecoratorModule\\', __DIR__.'/../../src/');
        $this->autoLoader->addPsr4('boukeversteegh\\DecoratorModule\\Fixtures\\', __DIR__.'/Fixtures/');
        
        require_once __DIR__.'/../../src/boukeversteegh/DecoratorModule/DecoratorManager.php';
        require_once __DIR__.'/../../src/boukeversteegh/DecoratorModule/Decorator.php';
        require_once __DIR__.'/Fixtures/Entity/Book.php';
        require_once __DIR__.'/Fixtures/Entity/User.php';
        require_once __DIR__.'/Fixtures/Traits/hasTitle.php';
        require_once __DIR__.'/Fixtures/Traits/hasAuthor.php';
        require_once __DIR__.'/Fixtures/Library/lendableTrait.php';
    }
}

$autoLoader = require $autoloadFile;
$bootstrap = new Bootstrap($autoLoader);
$bootstrap->init();
