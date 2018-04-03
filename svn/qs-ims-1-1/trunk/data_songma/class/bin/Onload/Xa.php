<?php

class Qss_Bin_Onload_Xa extends Qss_Lib_Onload
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
}
