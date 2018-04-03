<?php

class Qss_Db
{

	/**
	 * Array of adapters
	 * @var array
	 */
	protected static $_adapters = array();

	/**
	 * Default adapter key
	 * @var string
	 */
	protected static $_defaultKey = '';

	/**
	 * Get the adapter by the key
	 * @param string $key
	 */
	public static function &getAdapter ($key)
	{
		return self::$_adapters[$key];
	}

	/**
	 * Set the adapter to the adapters list
	 * @param string $key
	 * @param Qss_Db_Adapter_Abstract $adapter
	 * @param bool $default
	 */
	public static function setAdapter ($key, $adapter, $default = false)
	{
		self::$_adapters[$key] = $adapter;
		if ( $default )
		self::setDefaultAdapter($key);
	}

	/**
	 * Set the adapter key as default
	 * @param string $key
	 */
	public static function setDefaultAdapter ($key)
	{
		self::$_defaultKey = $key;
	}

	/**
	 * Get the default adapter
	 */
	public static function &getDefaultAdapter ()
	{
		return self::getAdapter(self::$_defaultKey);
	}

	/**
	 * Factory method, used to build the adapters list from the configuration
	 * @param array $configs
	 */
	public static function factory ($configs)
	{

		foreach ($configs as $key => $config)
		{
			$classname = 'Qss_Db_' . $config->adapter;
			if ( class_exists($classname) )
			{
				$adapter = new $classname((array) $config);
				self::setAdapter($key, $adapter);
			}

		}
	}
	public static function close ($configs)
	{
		foreach ($configs as $key => $config)
		{
			$classname = 'Qss_Db_' . $config->adapter;
			if ( class_exists($classname) )
			{
				$adapter = self::getAdapter($key);
				$adapter->close();
			}

		}
	}
}
?>