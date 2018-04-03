<?php
class Qss_Bin_Trigger_ODanhSachCK extends Qss_Lib_Trigger
{
	/**
         * onInsert
	 * - Kiem tra xem neu chuyen cung kho thi hai kho co cai dat bin ko?
	 */
	public function onInsert($object)
	{
		parent::init();
		$this->checkWarehouse($object);
	}
	
	/**
         * onUpdate
	 * - Kiem tra xem neu chuyen cung kho thi hai kho co cai dat bin ko?
	 * - Xoa trang thai luu tru
	 */	
	public function onUpdate($object)
	{
		parent::init();
		$this->checkWarehouse($object);
		if(!$this->isError())
		{
			$this->deleteStockStatus($object);
		}
	}
		
	/**
	 * - Xoa trang thai luu tru
	 */
	public function onDelete($object)
	{
		parent::init();
		$this->deleteStockStatus($object);
	}
	
	private function checkWarehouse($object)
	{
		$model            = new Qss_Model_Extra_Products();
		$toWarehouse      = $object->getFieldByCode('KhoCD')->getValue();
		$fromWarehouse    = $object->getFieldByCode('KhoLH')->getValue();
		$refFromWarehouse = $object->getFieldByCode('KhoLH')->intRefIOID;
		
		if($toWarehouse == $fromWarehouse && !$model->checkHasBin($refFromWarehouse) )
		{
			$this->setError();
			$this->setMessage($this->_translate(3));
			/*'Bạn không thể chuyển cùng kho khi kho này không có zone nào được cài đăt.'*/
		}	
	}
		
	
	private function deleteStockStatus($object)
	{
		$model = new Qss_Model_Extra_Products();
		$itemCode = '';
		foreach ($this->_params->ODanhSachCK as $value) {
			if($value->IOID == $object->i_IOID)
			{
				$itemCode    = $value->Ref_MaSP;
				$fromWarehouse = $value->Ref_KhoLH;
				$toWarehouse = $value->Ref_KhoCD;
				$attributes  = (int)$value->Ref_ThuocTinh;
			}
		}
		
		$oldItem = $itemCode;
		$oldW    = $toWarehouse;
		$oldFW   = $fromWarehouse;
		$oldAttr = $attributes;
		
		$newItem = $object->getFieldByCode('MaSP')->intRefIOID;
		$newW    = $object->getFieldByCode('KhoCD')->intRefIOID;
		$newFW   = $object->getFieldByCode('KhoLH')->intRefIOID;
		$newAttr = (int)$object->getFieldByCode('ThuocTinh')->intRefIOID;
		
		if( ($oldItem != $newItem) || ($oldW != $newW) || ($oldAttr != $newAttr) || ($oldFW != $newFW) )
		{
			// IOIDArray sẽ giúp lưu những giá trị IOID cần xóa trong bảng lot & serial vả qsioidlink
			// param mảng tham số lưu các giá trị IOID này.
		   	$IOIDArray  = $model->getOldStockStatus($object->FormCode, $object->i_IOID, $object->i_IFID);
		   	$params     = array();
			foreach ($IOIDArray as $val)
			{
				$params[] = $val->IOID;
			}
			
			if(count($params))
			{
				/*
				// Tiến hành xóa IOIDLink
				foreach ($params as $val) {
					$this->_form->deleteIOIDLink($object->i_IOID, $val);
				}
				*/
				
				// Tiến hành xóa các dòng trong bảng lot & serial tương ứng với IOID của params truyền vào
				$params = array('OTrangThaiLuuTruCK'=>$params);
				$service = $this->services->Form->Remove('M603',$object->i_IFID,$params);
				
				if($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
			
		}
	}
}
?>