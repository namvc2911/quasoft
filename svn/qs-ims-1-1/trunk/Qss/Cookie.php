<?php
class Qss_Cookie
{

	protected static $_params;

	public function __construct ()
	{
		if ( !is_array(self::$_params) )
		{
			global $HTTP_COOKIE_VARS;
			
			if ( isset($_COOKIE) )
			{
				self::$_params = $_COOKIE;
			}
			else
			{
				self::$_params = $HTTP_COOKIE_VARS;
			}
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
		$lifetime = time() + 365 * 24 * 60 * 60;
		setcookie($name, $value, $lifetime, '/');
	}
}
?>