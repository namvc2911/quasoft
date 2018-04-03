<?php
/**
 * Qss_Loader class
 * - Configuration the namespaces and their paths
 * - Auto load a class by name
 * - Auto load a class by name and its possible namespaces
 * - Auto load and init an instance by name
 * - Auto load and init an instance by name and its possible namespaces
 * - Auto load, init an instance and execute a method
 * 
 * The Framework class name convention is leant from Zend Framework
 * 
 * @author datgs <giangsondat@gmail.com>
 */
class Qss_Loader
{
	/**
	 * - Keep the list of namespaces available for the project
	 * - The key of the array if namespace name and the value is the path to the namespace
	 * - There is only 1 namespace for a path
	 * - Don't accept empty namespace
	 * 
	 * @var array
	 */
	protected static $_nsPaths = array();
	
	/**
	 * - Keep the loaded class in an array
	 * - The key of the array is the class name, the value is the path to the class
	 * 
	 * @var array
	 */
	protected static $_clPaths = array();
	
	/**
	 * - Mark the classes as loaded
	 * - The key of the array is the class name
	 * - The value of the array is not important (because if the key is exist, the class is loaded)
	 * 
	 * @var array
	 */
	protected static $_clLoaded = array();
	
	/**
	 * - Add the new namespace to the $_namespaces
	 * - Use krsort to move the larger namespace first
	 * 
	 * @param string $namespace - The namespace name
	 * @param string $path - The path to the namespace
	 */
	public static function addPath($namespace, $path)
	{
		/* Init the namespace of The framework */
		if(!self::$_nsPaths)
			self::$_nsPaths['Qss'] = dirname(__FILE__);
		
		/* Add a new namespace and resort the namespaces (priority) */
		self::$_nsPaths[$namespace] = $path;
		krsort(self::$_nsPaths, SORT_STRING);
	}
	
	/**
	 * - Get the path of a namespace
	 * 
	 * @param string $namespace
	 * 
	 * @return string|NULL
	 */
	public static function getPath($namespace = 'Qss')
	{
		/* Init the namespace of The framework */
		if(!self::$_nsPaths)
			self::$_nsPaths['Qss'] = dirname(__FILE__);
			
		return isset(self::$_nsPaths[$namespace]) ? self::$_nsPaths[$namespace] : NULL;
	}
	
	/**
	 * - Get the path of a class by the name of the class
	 * - - Case 1: $class is the true class name
	 * - - Case 2: $class is the sub class name in the list of $namespace
	 * 
	 * @param string $class
	 * @param array|null $namespaces
	 * 
	 * @return string|NULL
	 */
	public static function getClassPath(&$class, $namespaces = NULL)
	{
		if(NULL === $namespaces)
		{
			/* If the class path is cached once, return the cached path */
			if(isset(self::$_clPaths[$class]))
				return self::$_clPaths[$class];
	
			/* Searching on all namespace with full class name*/
			foreach(self::$_nsPaths as $namespace => $path)
			{
				/* The $namespace is prefix of $class or not */
				/* Not found the namespace contain the class, continue searching */
				if(strpos($namespace, $class) !== 0)
					continue;
				
				$length = strlen($namespace);
				$className = $length ? substr($class, $length) : $class;
				
				/* Found the namespace contain the class, return the class path */
				$classPath = $path . '/' . str_replace('_' , '/', $className) . '.php';
				if(file_exists($classPath))
				{
					self::$_clPaths[$class] = $classPath;
					return $classPath;
				}
				/* Found the namespace but not found the class, break the loop */
				break;
			}
		}
		else
		{
			/* Find the class by namespace */
			foreach($namespaces as $namespace)
			{
				/* The namespace is invalid, continue with other namespace */
				if(!isset(self::$_nsPaths[$namespace]))
					continue;
				
				/* If the class path is cached once, return the cached path */
				$className = $namespace . '_' . $class;
				if(isset(self::$_clPaths[$classPath]))
					return self::$_clPaths[$classPath];
					
				/* Retrieve the namespace path */
				$path = self::$_nsPaths[$namespace];
	
				/* Found the namespace contains the class, return the class path*/
				$classPath = $path . '/' . str_replace('_' , '/', $class) . '.php';
				if(file_exists($classPath))
				{
					$class = $className;
					self::$_clPaths[$class] = $classPath;
					return $classPath;
				}
				/* Not found, continue searching on other namespace */
			}
		}
		/* Found nothing, return NULL */
		return NULL;
	}
	
	/**
	 * - Get the directory of a class with a namespace included
	 * 
	 * @param string $class
	 * @param array $namespaces
	 * 
	 * @return string|NULL
	 */
	public static function getClassDir(&$class, $namespaces = NULL)
	{
		$classPath = self::getClassPath($class, $namespaces);
		return NULL !== $classPath ? dirname($classPath) : NULL;
	}
	
	/**
	 * - Load a class from the library, with or without $namespaces included
	 * - 
	 * 
	 * @param string $class - The class name to be loaded
	 * @param array|string $namespaces 
	 * 
	 * @return string - Class name if the class is loaded
	 * 
	 * @throws Qss_Exception - If the class is not found
	 */
	public static function loadClass(&$class, $namespaces = NULl)
	{
		/* Has the class path, try to load */
		$classPath = self::getClassPath($class, $namespaces);
		
		/* If the class is loaded before, return the class name*/
		if(isset(self::$_clLoaded[$class]))
			return $class;
		
		if(NULL !== $classPath)
		{
			include $classPath;
			self::$_clLoaded[$class] = true;
			return $class;
		}
		/* Throw the exception about class not found */
		require_once dirname(__FILE__).'/Exception.php';
		if(NULL === $namespaces)
			throw new Qss_Exception($class . ' not found');
		else
			throw new Qss_Exception($class . ' not found in namespaces ' . var_export($namespaces, true));
	}
	
	/**
	 * - Load the instance object of a class, use reflection class
	 * 
	 * @param string $class - The class name
	 * @param array|null $args - The instance args
	 * @param array $namespaces
	 * 
	 * @return object
	 * 
	 * @throws Qss_Exception
	 */
	public static function loadInstance($class, $args = NULL, $namespaces = NULL)
	{
		$class = self::loadClass($class, $namespaces);
		try
		{
			$r = new ReflectionClass($class);
			if(NULL !== $args)
				return $r->newInstance($args);
			else
				return $r->newInstanceArgs();
		}
		catch (RefectionException $e)
		{
			require_once dirname(__FILE__).'/Exception.php';
			throw new Qss_Exception('Could not create instance for the ' . $class . ' : ' . $e->getMessage());
			unset($e);
		}
	}
	
	/**
	 * Load a class, init the instance and execute the method 
	 * 
	 * @param string $class
	 * @param string $method
	 * @param array $constructArgs
	 * @param array $methodArgs
	 * @param array $namespaces
	 * 
	 * @return object
	 * 
	 * @throws Qss_Exception
	 */
	public static function loadAndExecute($class, $method, $constructArgs = NULL, $methodArgs = NULL, $namespaces = NULL)
	{
		$instance = self::loadInstance($class, $constructArgs, $namespaces);
		if(is_callable($instance, true, $method))
		{
			if(NULL != $methodArgs)
				call_user_func_array(array($instance, $method), $methodArgs);
			else
				$instance->$method();
			return $instance;
		}
		else
			throw new Qss_Exception('Could not call the ' . $class . ':' . $method);
	}
}
?>