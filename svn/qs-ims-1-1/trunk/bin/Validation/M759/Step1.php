<?php
class Qss_Bin_Validation_M759_Step1 extends Qss_Lib_WValidation
{

	public function onAlert()
	{
//		parent::init();
//		$deliveryDate = $this->_params->NgayYeuCau;
//		$now          = date('Y-m-d');
//		$now          = Qss_Lib_Date::i_fMysql2Time($now);
//		$deliveryDate = Qss_Lib_Date::i_fMysql2Time($deliveryDate);
//
//		if($now == $deliveryDate)
//		{
//			$this->setMessage($this->_translate(1).' '.$this->_params->SoPhieu .' '.$this->_translate(2));
//		}
//		elseif($now > $deliveryDate)
//		{
//			$this->setMessage($this->_translate(1).' '.$this->_params->SoPhieu .' '.$this->_translate(2));
//		}
//
//        $invModel  = new Qss_Model_Warehouse_Inventory();
//        $inventory = $invModel->getInventoryForItemsOfMaintainOrder($this->_params->IFID_M759);
//        $items     = array();
//
//        foreach ($inventory as $inv)
//        {
//            if($inv->Enough == 0)
//            {
//            $items[] = $inv->ItemCode;
//            }
//        }
//
//        if(count($items))
//        {
//            $this->setError();
//            $this->setMessage('Mã mặt hàng '. implode(', ', $items). ' không đủ số lượng tồn kho!');
//        }
	}
    
}