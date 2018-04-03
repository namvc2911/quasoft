<?php
class Qss_Bin_Trigger_OKiemKeKho extends Qss_Lib_Trigger
{
	public function onUpdate($object)
	{
		parent::init();
		$this->remove($object);
	}
	public function onInsert($object)
	{
		parent::init();
		$this->remove($object);
	}
	private function remove(Qss_Model_Object $object)
	{
		$model        = new Qss_Model_Extra_Warehouse();
		$common       = new Qss_Model_Extra_Extra();
		$newWarehouse = isset($this->_params->Ref_MaKho)?$this->_params->Ref_MaKho:0;
		$ifid         = isset($this->_params->IFID_M612)?$this->_params->IFID_M612:0;
		$oldWarehouse = $object->getFieldByCode('MaKho')->intRefIOID;
		
		// Neu co su khac biet ve kho hoac ngay tien hanh kiem tra lai
		if($newWarehouse != $oldWarehouse )
		{
			$dataRemove = $common->getTable(array('*'), 'OChiTietKiemKe', array('IFID_M612'=>$ifid), array(), 'NO_LIMIT');
			$this->_removeData('IOID', $dataRemove, 'OChiTietKiemKe', 'M612', $ifid);
		}
	}
}