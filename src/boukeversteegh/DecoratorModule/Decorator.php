<?php

namespace boukeversteegh\DecoratorModule;

class Decorator {
	public $classname;
	protected $traits = [];
	protected $interfaces = [];
	protected $properties = [];
	protected $DecoratorModule;

	public function __construct($classname, $DecoratorModule) {
		$this->classname = $classname;
		$this->DecoratorModule = $DecoratorModule;
	}

	public function __call($method, $args) {
		if( $method === 'use' ) {
			return $this->addTrait($args[0]);
		}
		if( $method === 'implements' ) {
			return $this->addInterface($args[0]);
		}
		if( $method === 'extends' )
			return $this->addExtends($args[0]);

		throw new \BadMethodCallException($method);
	}

	public function addTrait($trait) {
		$this->traits[] = $trait;
		return $this;
	}

	public function addInterface($interface) {
		$this->interfaces[] = $interface;
		return $this;
	}

	public function addExtends($class) {
		foreach(class_uses($class) as $trait) {
			$this->addTrait("\\$trait");
		}
		foreach(class_implements($class) as $interface) {
			$this->addInterface("\\$interface");
		}
		return;
	}

	public function autoload($alias) {
		// print "autoload: {$alias} --> {$this->classname}\n";
		$class_path = explode('\\', $this->classname);
		$class_basename  = array_pop($class_path);
		$class_namespace = implode('\\', $class_path);

		$alias_path = explode('\\', $alias);
		$alias_basename  = array_pop($alias_path);
		$alias_namespace = implode('\\', $alias_path);

		$traits = [];
		if( count($this->traits) ) {
			foreach($this->traits as $trait) {
				if( $class_namespace && $trait[0] !== '\\' ) {
					$trait = "\\$class_namespace\\$trait";
				}
				$traits[] = $trait;
			}
		}
		$traits = array_unique($traits);

		$interfaces = [];
		if( count($this->interfaces) ) {
			foreach($this->interfaces as $interface) {
				if( $class_namespace && $interface[0] !== '\\' ) {
					$interface = "\\$class_namespace\\$interface";
				}
				$interfaces[] = $interface;
			}
		}
		$interfaces = array_unique($interfaces);

		$def_properties = '';
		foreach($this->properties as $property) {
			$def_properties .= "\t\${$property->getName()};\n";
		}

		$def_use = count($traits) ? "\tuse\n\t\t" . implode(",\n\t\t", $traits) .";": "";
		$def_implements = (count($interfaces) ? "\n\timplements\n\t\t" . implode(",\n\t\t", $interfaces) : "");

		$def_namespace = ($alias_namespace ? "namespace ${alias_namespace};\n" : "");
		$def_class_basename = $alias_basename;

		$def_extends = "\n\textends \\{$this->classname}";

		$def = ("{$def_namespace}class {$def_class_basename} {$def_extends}{$def_implements}\n{\n{$def_use}{$def_properties}\n};\n");
		// print $def;
		eval($def);
	}
}