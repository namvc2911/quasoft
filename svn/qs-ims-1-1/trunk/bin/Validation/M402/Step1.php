<?php
class Qss_Bin_Validation_M402_Step1 extends Qss_Lib_WValidation
{
	/**
	 * Alert: Ngày yêu cầu đã đến hoặc quá hạn chưa?
	 */
	public function onAlert()
	{		
//		parent::init();
//		$extra        = new Qss_Model_Extra_Extra();
//		$compareDate  = Qss_Lib_Date::compareTwoDate($this->_params->NgayChuyenHang, date('Y-m-d') );
//
//		if($compareDate == 0)
//		{
//			$this->setError();
//			$this->setMessage($this->_translate(1));
//		}
//		elseif($compareDate == -1)
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
//        	$update = $this->services->Extra->Inventory->Revert($this->_form, 1);
//        	if($update->isError())
//			{
//				$this->setMessage($update->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//				$this->setError();
//			}
//        }
//    }
}