<?php
Class Qss_Service_Extra_Attributes_Saveattrvalue extends  Qss_Service_Abstract
{
	public function __doExecute($params)
	{

		if($this->validatex($params))
		{
			$model           = new Qss_Model_Extra_Products();
			$attrConfig      = $model->getItemAttributesConfig($params['product']);
			$item            = array();
			
			//echo '<pre>'; print_r($params); die;
			/* Lay mang cho manual */
			//$item['OThuocTinh'][0]['MaThuocTinh']  = $params['AttrCode'];
			//$item['OThuocTinh'][0]['ThuocTinh']    = $params['AttrName'];
			$item['OGiaTriThuocTinh'][0]['GiaTri'] = $params['attr'];
			$item['OGiaTriThuocTinh'][0]['ioid']   = $params['ttioid'];
			
			/* Manual */
			$service = $this->services->Form->Manual('M111', $params['ttifid'],$item,false);
			if($service->isError())
			{
				$this->setError();
				$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
			}
			else 
			{
				$this->setMessage('Cập nhật thành công!');
			}
		}
		else 
		{
			$this->setError();
		}
		
		
	}
	
	public function validatex($params)
	{
		$model           = new Qss_Model_Extra_Products();
		$attrConfig      = $model->getItemAttributesConfig($params['product']);
		$retval          = true;
		//echo '<pre>'; print_r($params); die;
		
		if ( $params['KieuSo'] == 1 && ( !$params['attr'] || !is_numeric($params['attr']) ))
		{
			$this->setMessage('Kiểu yêu cầu là kiểu số!');
			$retval = false;
		}
		return $retval;
	}
}