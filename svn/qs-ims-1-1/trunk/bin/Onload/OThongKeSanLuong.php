<?php

class Qss_Bin_Onload_OThongKeSanLuong extends Qss_Lib_Onload
{

	public function __doExecute()
	{
		parent::__doExecute();
        /*
		$common   = new Qss_Model_Extra_Extra();
		$MaLenhSX = @(string) $this->_object->getFieldByCode('MaLenhSX')->szValue;
		$ThaoDo   = @$common->getTable(array('*'), 'OSanXuat', array('MaLenhSX' => $MaLenhSX), array(), 1, 1)->ThaoDo;


		if ($ThaoDo == 1)
		{
			$this->_object->getFieldByCode('SoLuong')->bReadOnly = true;
			$this->_object->getFieldByCode('SoLuong')->dDouble = 0;

			$this->_object->getFieldByCode('SoLuongLoi')->bReadOnly = true;
			$this->_object->getFieldByCode('SoLuongLoi')->dDouble = 0;
		}
         * 
         */

	}
	public function MaSP()
	{
		$this->_object->getFieldByCode('MaSP')->arrFilters[] = sprintf(' v.SanXuat = 1 ');
	}

}
