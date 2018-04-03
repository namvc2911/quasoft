<?php

class Qss_Validation
{

	protected $config;

	public function __construct ($config = array())
	{
		$this->config = $config;
	}

	public function isValid ($value, &$message)
	{
		if ( is_array($this->config) )
		{
			if ( $value == '' )
			{
				if ( !@$this->config['required'] )
				{
					return true;
				}
				else
				{
					$message = @$this->config['requiredmessage'] ? @$this->config['requiredmessage'] : @$this->config['message'];
					return false;
				}
			}
			if ( isset($this->config['pattern']) )
			{
				$message= @$this->config['patternmessage'] ? @$this->config['patternmessage'] : @$this->config['message'];
				return self::isPatternValid($this->config['pattern'], $value);
			}
			else
			{
				$retval = true;
				switch ( $this->config['datatype'] )
				{
					case 'int':
						$retval = self::isInt($value);
						break;
					case 'email':
						$retval = self::isEMail($value);
						break;
					case 'float':
						$retval = self::isFloat($value);
						break;
					case 'array':
						$retval = self::isArray($value);
						break;
				}
				if ( $retval == '' )
				{
					$message = @$this->config['message'];
				}
				else
				{
					if ( isset($this->config['maxlength']) )
					{
						$retval = strlen($value) > (int) $this->config['maxlength'];
						if ( !$retval )
						{
							$message = @$this->config['maxlengthmessage'] ? @$this->config['maxlengthmessage'] : @$this->config['message'];
						}
					}
				}
				return $retval;
			}
		}
	}

	/**
	 * Validate the value with the validation
	 *
	 * @param string $value
	 */
	public static function isInt ($value)
	{
		return is_numeric($value);
	}

	/**
	 * Validate the value with the validation
	 *
	 * @param string $value
	 */
	public static function isFloat ($value)
	{
		return is_numeric($value);
	}

	/**
	 * Validate the value with the validation
	 *
	 * @param string $value
	 */
	public static function isEmail ($value)
	{
		return preg_match("/^([_a-z0-9\-]+)(\.[_a-z0-9\-]+)*@([a-z0-9\-]{2,}\.)*([a-z]{2,4})(\s*,\s*([_a-z0-9\-]+)(\.[_a-z0-9\-]+)*@([a-z0-9\-]{2,}\.)*([a-z]{2,4}))*$/", $value);
	}

	/**
	 * Validate the value with the validation
	 *
	 * @param string $value
	 */
	public static function isPatternValid ($pattern, $value)
	{
		return preg_match($pattern, $value);
	}
	public static function isArray ($value)
	{
		return is_array($value);
	}
}
?>