<?php

class Qss_Config_Ini extends Qss_Config_Abstract
{
	protected function _load()
	{
		$bySection = $this->_section !== NULL;
		$data = parse_ini_file($this->_filename, $bySection);
		
		if($data === FALSE)
		{
			throw new Exception('Could not read the ini file or the ' . $this->_filename .' is not valid ini file');
		}
		
		if($this->_section !== NULL)
		{
			$data = $data[$this->_section];
		}
		
		$this->_data = $data;
	}
}
?>