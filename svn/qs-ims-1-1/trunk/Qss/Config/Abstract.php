<?php

abstract class Qss_Config_Abstract
{
	protected $_filename = null;
	
	protected $_section = null;
	
	protected $_extends = false;
	
	protected $_data = array();
	
	public function __construct($filename, $section = null, $extends = false)
	{		
		if($section !== null)
			$this->_section = $section;

		$this->_extends = $extends;

		$this->_filename = $filename;

		$this->_load();
	}

	public function __set($key, $value)
	{
		$this->_data[$key] = $value;
	}
	
	public function __get($key)
	{
		return $this->_data[$key];
	}
	
	public function data()
	{
		return $this->_data;
	}
	
	abstract protected function _load();
}
?>