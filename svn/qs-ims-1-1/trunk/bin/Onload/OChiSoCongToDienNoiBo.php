<?php
class Qss_Bin_Onload_OChiSoCongToDienNoiBo   extends Qss_Lib_Onload
{
	public function __doExecute()
	{
		parent::__doExecute();
				
		if($this->_object->getFieldByCode('ThayCongTo')->getValue())
		{
		    $this->_object->getFieldByCode('ChiSoDau')->setValue(0);
		    $this->_object->getFieldByCode('HeSo')->bReadOnly = false;
		    $this->_object->getFieldByCode('HeSo')->bRequired = true;
		}
		else
		{
		    $this->_object->getFieldByCode('HeSo')->bReadOnly = true;
		    $this->_object->getFieldByCode('HeSo')->bRequired = false;		    
		}
	}
	
}