<?php
class Qss_Bin_Validation_M612_Step2 extends Qss_Lib_Warehouse_WValidation
{	
	public function next()
	{
		parent::init();		
		$insert     = array();
		$stock      = array();
		$model      = new Qss_Model_Extra_Extra();
		$mWarehouse = new Qss_Model_Extra_Warehouse();
		//@todo: tạo phiếu xuất hoặc phiếu nhập ở đây
	}// on next
}