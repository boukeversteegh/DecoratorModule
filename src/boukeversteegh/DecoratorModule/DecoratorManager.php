<?php

namespace boukeversteegh\DecoratorModule;

class DecoratorManager {
	protected static $decorators = array();
	protected static $registered = false;
	protected static $instance;

	protected function __construct() {
		self::register();
	}

	public static function instance() {
		if( !self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function register() {
		if( !self::$registered ) {
			spl_autoload_register(array(__CLASS__, 'autoload'), true);
			self::$registered = true;
		}		
	}

	public function decorate($alias, $baseclass=null) {
		if( !isset(self::$decorators[$alias]) ) {
			if( !$baseclass ) {
				throw new \Exception("Unknown alias: {$alias}. Must provide a baseclass");
			}
			self::$decorators[$alias] = new Decorator($baseclass, $this);
		} else
		if( self::$decorators[$alias]->classname !== $baseclass && $baseclass ) {
			$used_baseclass = self::$decorators[$alias]->classname;
			throw new \Exception("Cannot use '{$alias}' for {$baseclass}. Already used for {$used_baseclass}");
		}
		return self::$decorators[$alias];
	}


	public static function autoload($alias) {
		if( !isset(self::$decorators[$alias]) ) return;

		$decorator = self::instance()->decorate($alias);
		$decorator->autoload($alias);
	}
}
