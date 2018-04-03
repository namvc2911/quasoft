<?php
class Qss_Register
{

	protected static $_params;

	public function __construct ()
	{
		if ( !is_array(self::$_params) )
		{
			self::$_params = array();
		}
	}

	static public function get ($name, $default = null)
	{
		if ( key_exists($name, self::$_params) )
		{
			return self::$_params[$name];
		}
		return $default;
	}

	static public function set ($name, $value)
	{
		self::$_params[$name] = $value;
	
	}
}
?>