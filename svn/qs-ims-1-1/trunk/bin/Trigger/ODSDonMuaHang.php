<?php
class Qss_Bin_Trigger_ODSDonMuaHang extends Qss_Lib_Trigger
{
	
    
	/**
	 * onUpdated
	 * Cập nhật lại thuế 
	 * sau khi sửa lại bản ghi
	 */
	public function onUpdated($object)
	{
		parent::init();
		//$this->updateTax($object);
	
	}
	
	/**
	 * onInserted
	 * Thêm mới thuế 
	 * sau khi thêm mới bản ghi
	 */
	public function onInserted($object)
	{
		parent::init();
		//$this->updateTax($object);
	}
	
	/**
	 * onDeleted
	 * Cập nhật lại thuế 
	 * sau khi xóa bản ghi
	 */
	public function onDeleted($object)
	{
		parent::init();
		//$this->updateTax($object);
	}
	
	private function checkAttributes($object)
	{
		$model 		   		  = new Qss_Model_Extra_Products();
		$new   		   		  = $object->getFieldByCode('MaSP')->getValue();
		$attrfield     		  = $object->getFieldByCode('ThuocTinh');
		$attributes    		  = $attrfield->intRefIOID;
		if($attributes)
		{
			$next = $model->checkAttributesAvailable($new, $attributes);
			if(!$next)
			{
				$this->setError();
				$this->setMessage($this->_translate(1));/*'Bạn phải cập nhật lại thuộc tính sản phẩm.'*/
				$this->setStatus('ThuocTinh_1076');
			}
		}
		elseif(!$this->isError())
		{
			$requires  	   = $model->checkAttributeRequires($new);
			if($requires)
			{
				$this->setError();
				$this->setMessage($this->_translate(2));/*'Thuộc tính sản phẩm bắt buộc.'*/
				$this->setStatus('ThuocTinh_1076');
			}
		}
	}
	
	private function updateTax($object)
	{
		$model = new Qss_Model_Extra_Purchase();
		$taxs = $model->getTaxs($object->i_IFID);
		$aTaxs = array();
		
		foreach($taxs as $val)
		{
			$aTaxs[] = array(
							'MaThue'=>$val->MaThue,
						  	'TenThue'=>$val->TenThue , 
						  	'SoTienChiuThue'=>$val->ThanhTien/1000,
						  	'SoTienThue'=>($val->ThanhTien * $val->PhanTramChiuThue/100000)
			);
			
		}
		$params = array('OThueDonHang'=>$aTaxs);
		$service = $this->services->Form->Manual('M401',$object->i_IFID,$params,false);
		//var_dump($params); die;
		if($service->isError())
		{
			$this->setError();
			$this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
		}
	}
}
?>