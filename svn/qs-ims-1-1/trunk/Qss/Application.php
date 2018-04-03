<?php
/**
 *
 * @author HuyBD
 *
 */
class Qss_Application
{

	/**
	 * @var bootstrap
	 */
	public $options;

	protected static $_instance;

	/**
	 * Build application constructor
	 * @param array|string $config
	 * @return void
	 */
	public function __construct ($configFolder, $environment)
	{
		spl_autoload_register(array(__CLASS__, 'autoload'));
		if ( $configFolder )
		{
			$configs = new Qss_Config($configFolder);
			$this->options = $configs->get($environment);
			Qss_Register::set('configs', $this->options);
		}
	}

	/**
	 *
	 * @return Qss_Application
	 */
	public static function getInstance ()
	{
		if ( null === self::$_instance )
		{
			throw new Qss_Exception('Can not load instance');
		}
		return self::$_instance;
	}

	/**
	 * Auto load function
	 *
	 * @param $class
	 * @return unknown_type
	 */
	public static function autoload ($class)
	{
		$path = str_ireplace('_', '/', $class);
		if ( @include_once $path . '.php' )
		{
			return;
		}
		$options = (array) Qss_Register::get('configs')->loader;
		if ( is_array($options) )
		{
			foreach ($options as $loader)
			{
				if ( $loader->namespace == substr($class, 0, strlen($loader->namespace)) )
				{
					$path = str_ireplace('_', '/', substr($class, strlen($loader->namespace)));
					if(file_exists($loader->path . $path . '.php') && include_once $loader->path . $path . '.php')
					{
						break;
					}
				}
			}
		}
	}

	/**
	 *
	 *
	 * @return void
	 */
	public function run ()
	{
		/*Load php setting */
		if ( isset($this->options->php) )
		{
			foreach ($this->options->php as $key => $val)
			{
				ini_set($key, $val);
			}
		}
		$request = Qss_Request::getInstance();
		$arrPath = explode('/', trim($request->getPathInfo(), '/'));
		if ( ($module = array_shift($arrPath)) == null )
		{
			$module = @$this->options->module->default;
			if ( !$module )
			{
				throw new Qss_Exception('Could not find default module in config file');
			}
		}
		$module = ucfirst($module);
		$controller = @array_shift($arrPath);
		if ( !$controller )
		{
			$controller = 'Index';
		}

		$controller = ucfirst($controller);

		$action = '';
		$actionPath = '';
		foreach ($arrPath as $value)
		{
			$action .= ucfirst($value);
			$actionPath .= '/' . $value;
		}
		if ( !$action )
		{
			$action = 'Index';
			$actionPath = '/index';
		}
		if(isset($this->options->module->path_ext))
		{
			$include = $this->options->module->path_ext . $module . '/controllers/' . $controller . 'Controller.php';
			if ( !file_exists($include) || !include_once $include )
			{
				$include = $this->options->module->path . $module . '/controllers/' . $controller . 'Controller.php';
				if ( !file_exists($include) || !include_once $include )
				{
					//throw new Qss_Exception('File not found ' . $include);
					header( "Location: /" );
				}
			}
		}
		else
		{
			$include = $this->options->module->path . $module . '/controllers/' . $controller . 'Controller.php';
			if ( !file_exists($include) || !include_once $include )
			{
				//throw new Qss_Exception('File not found ' . $include);
				header( "Location: /" );
			}
		}
		if ( isset($this->options->database) )
		{
			Qss_Db::factory((array) $this->options->database);
		}
		$clasName = $module . '_' . $controller . 'Controller';
		if ( $controller == 'Index' )
		{
			if(isset($this->options->module->path_ext) && file_exists($this->options->module->path_ext . $module . '/html/' . $actionPath . '.phtml'))
			{
				$include = $this->options->module->path_ext . $module . '/html/' . $actionPath . '.phtml';
			}
			else 
			{
				$include = $this->options->module->path . $module . '/html/' . $actionPath . '.phtml';
			}
		}
		else
		{
			if(isset($this->options->module->path_ext) && file_exists($include = $this->options->module->path_ext . $module . '/html/' . strtolower($controller) . $actionPath . '.phtml'))
			{
				$include = $this->options->module->path_ext . $module . '/html/' . strtolower($controller) . $actionPath . '.phtml';
			}
			else 
			{
				$include = $this->options->module->path . $module . '/html/' . strtolower($controller) . $actionPath . '.phtml';
			}
		}
		$controller = new $clasName();
		$controller->setHtml($include);
		$controller->setAction($action);
		if ( @$this->options->layout )
		{
			$controller->layout = $this->options->layout->path . '/' . $this->options->layout->name . '.php';
		}
		$controller->init();
		$controller->run();
		if ( isset($this->options->database) )
		{
			Qss_Db::close((array) $this->options->database);
		}
	}
}
?>