<?php

class Qss_Filter_Trim implements Qss_Filter_Interface
{
	protected $_chars = null;
	
	public function __construct($chars = null)
	{
		$this->_chars = $chars;
	}
	
	public function filter(&$value)
	{
		$value = $this->_chars === null ? trim($value) : trim($value, $this->_chars);
	}
}
?>