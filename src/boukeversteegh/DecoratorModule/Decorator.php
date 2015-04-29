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
		if( $method === 'trait' ) {
			return $this->addTrait($args);
		}
		if( $method === 'implements' ) {
			return $this->addInterface($args[0]);
		}
		if( $method === 'extends' )
			return $this->addExtends($args[0]);

		throw new \BadMethodCallException($method);
	}

	public function addTrait($traits) {
		if( func_num_args() > 1 )
			$traits = func_get_args();
		foreach($traits as $trait)
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
		$definition = $this->getDefinition($alias);
		eval($definition);
	}

	public function getDefinition($alias) {
		$class_path = explode('\\', $this->classname);
		$class_basename  = array_pop($class_path);
		$class_namespace = implode('\\', $class_path);
		$rf_class = new \ReflectionClass($this->classname);

		$alias_path = explode('\\', $alias);
		$alias_basename  = array_pop($alias_path);
		$alias_namespace = implode('\\', $alias_path);

		$traits = [];
		$index_methods = [];
		if( count($this->traits) ) {
			foreach($this->traits as $trait) {
				if( $class_namespace && $trait[0] !== '\\' ) {
					$trait = "\\$class_namespace\\$trait";
				}

				if( isset($traits[$trait]) ) continue;

				$rf_trait = new \ReflectionClass($trait); 
				if( !$rf_trait->isTrait() ) {
					throw new \Exception("$trait is not a trait");
				}

				$rf_methods = $rf_trait->getMethods();
				foreach($rf_methods as $method) {
					// print_r(get_class_methods($method));
					$index_methods[$method->name][] = $trait;
				}
				$traits[$trait] = [];
			}
		}

		$method_map = [];
		$methods = [];

		# Detect duplicate methods
		foreach($index_methods as $method => $method_traits) {

			if( count($method_traits) > 1 ) {
				$merged_method = [];
				foreach($method_traits as $method_trait) {
					$method_alias = $method . "_$method_trait";

					$traits[$method_trait][$method] = $method_alias;

					$merged_method[] = "call_user_func_array(array(\$this, '{$method_alias}'), func_get_args());";
				}
				
				if( $rf_class->hasMethod($method) ) {
					$merged_method[] = "call_user_func_array(array('parent', '{$method}'), func_get_args());";
				}
				$methods[$method] = $merged_method;
				# Use the last method instead of the others
				$method_map[] = end($method_traits) . "::{$method} insteadof " . implode(",",array_slice($method_traits,0, -1));
			}
		}

		# Interfaces
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

		# Properties
		$def_properties = '';
		foreach($this->properties as $property) {
			$def_properties .= "\t\${$property->getName()};\n";
		}
		$def_traits = [];
		foreach($traits as $trait => $trait_methods) {
			$def_trait = $trait;
			if( count( $trait_methods ) ) {
				foreach($trait_methods as $from => $to) {
					$method_map[] = "$trait::$from as $to";
				}
			}
			$def_traits[] = $def_trait;
		}

		$def_method_map = (count($method_map) ? " {\n\t\t" . implode(";\n\t\t", $method_map) . ";\n\t} " : "");

		$def_use = (count($traits) ? "\tuse " . implode(", ", $def_traits) : "");

		if( !empty($def_use) ) {
			if( !empty($def_method_map) ) {
				$def_use .= "{$def_method_map}\n";
			} else {
				$def_use .= ";\n";
			}
		}

		$def_implements = (count($interfaces) ? "\n\timplements\n\t\t" . implode(",\n\t\t", $interfaces) : "");

		$def_namespace = ($alias_namespace ? "namespace ${alias_namespace};\n" : "");

		$def_class_docblock = $rf_class->getDocComment();

		if (!empty($def_class_docblock) ) {
			$def_class_docblock = trim($def_class_docblock) . "\n";
		}

		$def_class_basename = $alias_basename;

		$def_extends = "\n\textends \\{$this->classname}";

		$def_methods = '';
		foreach($methods as $method => $lines) {
			$method_params = [];
			if( $rf_class->hasMethod($method) ) {
				$rf_method = $rf_class->getMethod($method);
				foreach($rf_method->getParameters() as $rf_param) {
					$hint = '';
					if( $rf_param->isArray() ) $hint = 'array ';
					if( $rf_param->getClass() ) $hint = $rf_param->getClass()->name . ' ';
					$method_params[] = $hint.'$'.$rf_param->name;
				}
			}

			$def_method_signature = implode(", ", $method_params);
			$def_method = "\tfunction $method ($def_method_signature) {\n\t\t";
			$def_method .= implode("\n\t\t", $lines);
			$def_method .= "\n\t}\n";

			$def_methods .= $def_method;
		}

		$def = ("{$def_namespace}{$def_class_docblock}class {$def_class_basename} {$def_extends}{$def_implements}\n{\n{$def_use}{$def_properties}{$def_methods}};\n");
		return $def;
	}
}