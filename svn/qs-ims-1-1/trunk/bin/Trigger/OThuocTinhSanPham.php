<?php
class Qss_Bin_Trigger_OThuocTinhSanPham extends Qss_Lib_Trigger
{
	public function onUpdate($object)
	{
		parent::init();
		if($this->checkAttr($this->_params->IOID))
		{
			$new     = $object->getFieldByCode('ThuocTinh')->getValue();
			$model   = new Qss_Model_Extra_Products();
			$line  	 = $this->getLine($object->i_IOID);
			$old     = ($line)?	$line->ThuocTinh:'';
			$oldActive   = ($line)?$line->HoatDong:'';
			$newActive   = $object->getFieldByCode('HoatDong')->getValue();
			
			if($new != $old || ($oldActive == 1 && $oldActive != $newActive) )
			{
				$this->setError();
				$this->setMessage($this->_translate(1));/*Thuộc tính sản phẩm đã được sử dụng, không thể thay đổi!*/
			}

		}
	}
	
	public function onDelete($object)
	{
		parent::init();
		if($this->checkAttr($this->_params->IOID))
		{
			$this->setError();
			$this->setMessage($this->_translate(2));/*'Thuộc tính sản phẩm đã được sử dụng, không thể xóa !'*/
		}
	}
	
	private function checkAttr($refItem)
	{
		$model  = new Qss_Model_Extra_Products();
                $common = new Qss_Model_Extra_Extra();

                $ret   = $common->getTable(array('*'), 'OBangThuocTinh'
                            , array('Ref_MaSP'=>$refItem)
                            , array(), 1, 1);
		return  $ret?true:false;
	}
	
	private function getLine($ioid)
	{
		$arr = $this->_params->OThuocTinhSanPham;
		foreach ($arr as $val)
		{
			if($val->IOID == $ioid)
			{
				return $val;
			}
		}
		return false;
	}
}
?>