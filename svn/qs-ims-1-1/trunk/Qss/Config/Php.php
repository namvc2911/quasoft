<?php

class Qss_Config_Php extends Qss_Config_Abstract
{
	protected function _load()
	{
		$this->_data = include $this->_filename;
	}
}
?>