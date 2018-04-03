<?php
class Qss_Bin_Trigger_OSapXepKho extends Qss_Lib_Trigger
{
    
    
	/**
	 * onUpdate
	 * - Thong bao xoa dong phu neu doi kho
	 */	
	public function onUpdate($object)
	{
		parent::init();
        $this->checkDateRange($object);
		$this->alertWhenChangeWarehouse($object);
	}
    
	public function onInsert($object)
	{
		parent::init();
        $this->checkDateRange($object);
		//$this->alertWhenChangeWarehouse($object);
	}    
    
    public function checkDateRange($object)
    {
        $this->_checkDateRange($object->getFieldByCode('NgayBatDau')->getValue(), $object->getFieldByCode('NgayKetThuc')->getValue(), $this->_translate(1));
    }    
	
	private function alertWhenChangeWarehouse($object)
	{
		$newWarehouse = $object->getFieldByCode('Kho')->intRefIOID;
		$oldWarehouse = $this->_params->Ref_Kho;
		$lineCounter  = count((array)$this->_params->OChiTietSapXepKho);
		
		if(($newWarehouse != $oldWarehouse) && $lineCounter)
		{
			$this->setError();
			$this->setMessage('Để thay đổi kho sắp xếp, bạn phải xóa hết chi tiết sắp xếp cũ.');
		}
	}
}
?>