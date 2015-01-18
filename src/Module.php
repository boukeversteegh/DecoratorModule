<?php
namespace boukeversteegh\DecoratorModule;

require_once __DIR__.'/boukeversteegh/DecoratorModule/DecoratorManager.php';
require_once __DIR__.'/boukeversteegh/DecoratorModule/Decorator.php';

class Module {
    public function __construct() {
        DecoratorManager::register();
    }
}