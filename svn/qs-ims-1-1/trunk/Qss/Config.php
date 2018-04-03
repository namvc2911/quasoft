<?php
/**
 * Just for ini
 * 
 * @author HuyBD
 *
 */
class Qss_Config
{

	/**
	 * 
	 * @var array
	 */
	protected static $_params;

	/**
	 * Build config constructor
	 * 
	 * @param $folder
	 * @return void
	 */
	public function __construct ($folder = null)
	{
		if ( !is_array(self::$_params) )
		{
			self::$_params = array();
			if ( $handle = opendir($folder) )
			{
				while ( false !== ($file = readdir($handle)) )
				{
					$info = pathinfo($file);
					if ( $info['extension'] == 'ini' )
					{
						self::$_params[$info['filename']] = Qss_Util::arrayToObject(self::loadIniFile($folder . $file));
					}
				}
				closedir($handle);
			}
		
		}
	}

	/**
	 * Get config name
	 * 
	 * @param $name
	 * @param $default
	 * @return object
	 */
	static public function get ($name, $default = null)
	{
		if ( key_exists($name, self::$_params) )
		{
			return self::$_params[$name];
		}
		return $default;
	}

	/**
	 * Set object value
	 * 
	 * @param $name
	 * @param $value
	 * @return void
	 */
	static public function set ($name, $value)
	{
		self::$_params[$name] = $value;
	}

	/**
	 * 
	 * @param $sz_Ini
	 * @return array
	 */
	static public function loadIniFile ($file)
	{
		/* Load from ini string */
		$loaded = parse_ini_file($file, true);
		return self::convertIniLoaded($loaded);
	}

	/**
	 * 
	 * @param $sz_Ini
	 * @return array
	 */
	static public function loadIniString ($string)
	{
		/* Load from ini string */
		$loaded = parse_ini_string($string, true);
		return self::convertIniLoaded($loaded);
	}

	/**
	 * This allow we use ini to declare array in array
	 * e.g array arr1 has 3 level, we can use
	 * [arr1]
	 * ...
	 * [arr1 : arr2]
	 * ...
	 * [arr1 : arr2 : arr3]
	 * ...
	 * 
	 * @param $o_Loaded
	 * @return array
	 */
	static public function convertIniLoaded ($the_o_Loaded)
	{
		$a_RetVal = array();
		foreach ($the_o_Loaded as $sz_Key => $m_Data)
		{
			$a_Pieces = explode(':', $sz_Key);
			$a_Temp = array();
			$b_Start = false;
			while ( ($sz_Value = array_shift($a_Pieces)) !== null )
			{
				$sz_Value = trim($sz_Value);
				if ( !$b_Start && !isset($a_RetVal[$sz_Value]) )
				{
					$a_RetVal[$sz_Value] = $m_Data;
					$a_Temp = $a_RetVal[$sz_Value];
				}
				if ( sizeof($a_Pieces) )
				{
					self::b_fPushArrayElement($a_RetVal, $sz_Value, trim($a_Pieces[0]), $m_Data);
				}
				elseif ( !sizeof($a_Pieces) && $b_Start )
				{
					self::b_fPushArrayElement($a_RetVal, $sz_ParentKey, $sz_Value, $m_Data);
				}
				$b_Start = true;
				$sz_ParentKey = $sz_Value;
			}
		
		}
		
		return $a_RetVal;
	}

	/**
	 * 
	 * @param $a_Array
	 * @param $sz_FKey
	 * @param $sz_Key
	 * @param $m_Data
	 * @return boolean
	 */
	static public function b_fPushArrayElement (&$the_a_Array, $the_sz_FKey, $the_sz_Key, $the_m_Data)
	{
		foreach ($the_a_Array as $sz_Key => $m_Value)
		{
			if ( (string) $sz_Key == (string) $the_sz_FKey )
			{
				if ( !is_array($the_a_Array[$sz_Key]) )
				{
					$the_a_Array[$sz_Key] = array();
				}
				if ( is_array($the_a_Array[$sz_Key]) && !key_exists($the_sz_Key, $the_a_Array[$sz_Key]) )
				{
					$the_a_Array[$sz_Key][$the_sz_Key] = $the_m_Data;
				}
				return true;
			}
			if ( is_array($m_Value) )
			{
				self::b_fPushArrayElement($m_Value, $the_sz_FKey, $the_sz_Key, $the_m_Data);
				$the_a_Array[$sz_Key] = $m_Value;
			}
		}
		return false;
	}

}
?>