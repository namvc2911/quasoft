<?php
class Qss_Bin_Validation_M113 extends Qss_Lib_Validation
{
	
	public function onValidated()
	{
		// hhe;
		parent::init();
		$model = new Qss_Model_Extra_Warehouse();
		$quantity = $model->getItemQuantity($this->_params->IOID);
		$soluonghienco =  ($quantity) ? $quantity->SoLuongHC : 0;
		if($this->_params->SLToiThieu
			&& $soluonghienco < $this->_params->SLToiThieu)
		{
			$this->setError();
			$this->setMessage(sprintf($this->_translate(1).'  %1$d %2$s '.$this->_translate(2)
							,$this->_params->SLToiThieu - $soluonghienco , $this->_params->DonViTinh
							));	
		}
		
		if($this->_params->SLToiDa && $soluonghienco > $this->_params->SLToiDa)
		{
			$this->setError();
			$this->setMessage(sprintf($this->_translate(3).'  %1$d %2$s '.$this->_translate(4)
							,$soluonghienco - $this->_params->SLToiDa , $this->_params->DonViTinh
							));	
		}
		
	}
}