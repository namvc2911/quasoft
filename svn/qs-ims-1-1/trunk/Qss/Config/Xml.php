<?php

class Qss_Config_Xml extends Qss_Config_Abstract
{
	protected function _load()
	{
		$xml = simplexml_load_file($this->_filename);
		
		if($xml === NULL)
			throw new Exception('Could not read the xml file or the ' . $this->_filename .' is not valid xml file');
		
		$section = $this->_section;
		if($section === NULL)
			$this->_data = (array)$xml;
		else
		{
			$this->_data = (array)$xml->$section;
		}
	}
}
?>