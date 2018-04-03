<?php
class Qss_Bin_Validation_M747_Step2 extends Qss_Lib_WValidation
{
	public function next()
	{
		parent::init();
		$commonModel = new Qss_Model_Extra_Extra();
		
		
		// Neu thiet bi bi mat thi cap nhat lai ngung hoat dong cua thiet bi
		if($this->_params->SuCo == Qss_Lib_Extra_Const::MAINTAIN_REQUEST_LOST)
		{
		    $eq = $commonModel->getTable(
                array('*')
		        , 'ODanhSachThietBi'
		        , array('IOID'=>$this->_params->Ref_MaThietBi)
		        , array()
		        , 'NO_LIMIT'
		        , 1
	        );
		    
		    if($eq)
		    {
    		    //$params['ODanhSachThietBi'][0]['NgungHoatDong'] = 1;
    			$params['ODanhSachThietBi'][0]['NgayNgung']     = $this->_params->Ngay;
    			$params['ODanhSachThietBi'][0]['TrangThai']     = Qss_Lib_Extra_Const::EQUIP_STOP_TYPE_LOST;
    			$params['ODanhSachThietBi'][0]['ioid']          = $eq->IOID;
    		    			
    		    			
                $service = $this->services->Form->Manual('M705', @(int)$eq->IFID_M705, $params, false);
                if($service->isError())
                {
                	$this->setError();
                	$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }	
		    }	    
		}
	}
}