<?php
class Qss_Bin_Trigger_ONhatTrinhThietBi extends Qss_Lib_Trigger
{
	/*public function onUpdate($object)
	{
		parent::init();
		$this->insertDailyRecordData($object);
	}
	
	public function onInserted($object)
	{
		parent::init();
		$this->updateDailyRecordDataToDB($object);
	}
	
	
	private function insertDailyRecordData($object)
	{
		$this->alertWhenEquipTypeChange($object);
		$this->updateDailyRecordDataToDB($object);
	}
	
	private function checkEquipTypeChange($object)
	{
		$dailyRecordLine = $this->_params->OBangNhatTrinhTB;
		$oldEqType       = @(int)$this->_params->Ref_TenTB;
		$newEqType       = @(int)$object->getFieldByCode('TenTB')->intRefIOID;
		$ret             = 1;// Luon duoc them dong phu
		
		if( ($oldEqType != $newEqType) && count((array)$dailyRecordLine))
		{
			$ret = 2; // Khong duoc thay doi loai thiet bi va them dong phu
		}
		
		if(($oldEqType == $newEqType) )
		{
			$ret = 3; // Khong can cap nhat dong phu 
		}
		return $ret;
	}	
	
	private function checkChangeSubPermission($object)
	{
		
	}
	
	private function alertWhenEquipTypeChange($object)
	{
		if($this->checkEquipTypeChange($object) == 2)
		{
			$this->setMessage('Bạn không thể thay đổi loại thiết bị trước khi xóa hết dòng phụ trong "Bảng Nhật Trình" của nhật trình hiện tại!');
			$this->setError();
		}
	}
	
	private function updateDailyRecordDataToDB($object)
	{
		if($this->checkEquipTypeChange($object) == 1)
		{
			// Model
			$equipModel   = new Qss_Model_Extra_Equip();

			// Init
			$refEquipType    = @(int)$this->_params->Ref_TenTB;
			$refPeriod       = @(int)$this->_params->Ref_Ky;
			$dailyRecordData = $equipModel->getDailyRecordPlanByEquipType($refEquipType, $refPeriod);
			$dailyRecordArr  = array();
			$dailyRecordNum  = 0;

			// Get daily record array
			if($dailyRecordData && count((array)$dailyRecordData))
			{
				foreach($dailyRecordData as $item)
				{
					$dailyRecordArr['OBangNhatTrinhTB'][$dailyRecordNum]['ChiSo']  = $item->ChiSo;
					$dailyRecordArr['OBangNhatTrinhTB'][$dailyRecordNum]['SoDau']  = 0;
					$dailyRecordArr['OBangNhatTrinhTB'][$dailyRecordNum]['SoCuoi'] = 0;
					$dailyRecordNum++;
				}

				$service = $this->services->Form->Manual('M765', $this->_params->IFID_M765, $dailyRecordArr, false);
				if ($service->isError())
				{
					$this->setError();
					$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
				}
			}
		}
	}*/
}
