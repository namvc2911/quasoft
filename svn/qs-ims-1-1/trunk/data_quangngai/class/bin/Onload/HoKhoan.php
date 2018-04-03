<?php

class Qss_Bin_Onload_HoKhoan extends Qss_Lib_Onload
{

	public function __doExecute()
	{
		parent::__doExecute();
	}
	public function Huyen()
	{
		$tinh = $this->_object->getFieldByCode('Tinh')->getRefIOID();
		$where = sprintf(' v.Ref_Tinh = %1$d',$tinh);
		$this->_object->getFieldByCode('Huyen')->arrFilters[] = $where;
	}
	public function Xa()
	{
		$tinh = $this->_object->getFieldByCode('Huyen')->getRefIOID();
		$where = sprintf(' v.Ref_Huyen = %1$d',$tinh);
		$this->_object->getFieldByCode('Xa')->arrFilters[] = $where;
	}
}
