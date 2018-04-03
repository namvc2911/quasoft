<?php
class Qss_Bin_Trigger_OBin extends Qss_Lib_Trigger
{
	public function onInsert($object)
	{
		parent::init();
		$this->checkBinCapacityAvailable($object);
	}	
	public function onUpdate($object)
	{
		parent::init();
		$this->checkBinCapacityAvailable($object);
	}
	private function checkBinCapacityAvailable($object)
	{
		$newBinCapacity = $object->getFieldByCode('SucChua')->getValue();
		$zone           = $object->getFieldByCode('Zone')->getValue();
		$UOM            = $object->getFieldByCode('DonViTinh')->getValue();
		$changLineIOID  = $object->i_IOID;
		$zoneCapacity   = null;
		
                /*
                foreach ($this->_params->OZone as $val) {
			if($val->Ma == $zone)
			{
				$zoneCapacity = $val->SucChua;
				$zoneUOM      = $val->DonViTinh;
			}
		}
                 */
		

		if($newBinCapacity && !$UOM)
		{
			$this->setError();
			$this->setMessage("{$this->_translate(1)}");/*Bạn cần điền đơn vị tính.*/
			$this->setStatus('DonViTinh_1123');
		}
                /*
		elseif($zoneUOM && $UOM != $zoneUOM)
		{
			$this->setError();
			$this->setMessage($this->_translate(2).' "'.$zoneUOM.'"');//Đơn vị tính của zone và bin phải giống nhau.Đơn vị tính của zone hiện tại là
		}
                
		elseif($zoneCapacity)
		{
			$totalBinCapacityWithoutOne = 0;
			$totalBinCapacity           = 0;
			foreach ($this->_params->OBin as $val) {
				if($val->Zone == $zone && $val->IOID != $changLineIOID)
				{
					$totalBinCapacityWithoutOne += $val->SucChua;
				}
			}
			$totalBinCapacity = $newBinCapacity + $totalBinCapacityWithoutOne;
			
			if($totalBinCapacity > $zoneCapacity)
			{
				$this->setError();
				$this->setMessage($this->_translate(3).' '.$zone.' '.$this->_translate(4));
				//Sức chứa của  - đã vượt quá giới hạn được cấu hình trong zone.
			}
		}
                */

		
		

	}
}