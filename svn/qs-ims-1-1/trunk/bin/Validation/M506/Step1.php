<?php
class Qss_Bin_Validation_M506_Step1 extends Qss_Lib_WValidation
{
	/**
	 * Alert: Ngày yêu cầu đã đến hoặc quá hạn chưa?
	 */
	public function onAlert()
	{		
//		parent::init();
//		$deliveryDate = $this->_params->NgayChuyenHang;
//		$now          = date('Y-m-d');
//		$compare      = Qss_Lib_Date::compareTwoDate($deliveryDate, $now);
//
//		if($compare == 0)
//		{
//			$this->setError();
//			$this->setMessage($this->_translate(1));
//		}
//		elseif($compare == -1)
//		{
//			$this->setError();
//			$this->setMessage($this->_translate(2));
//		}
	}
    
//    public function back()
//    {
//		parent::init();
//        if(!$this->isError())
//        {
//            $update = $this->services->Extra->Inventory->Revert($this->_form, 0);
//       	 	if($update->isError())
//			{
//				$this->setMessage($update->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//				$this->setError();
//			}
//        }
//    }
}