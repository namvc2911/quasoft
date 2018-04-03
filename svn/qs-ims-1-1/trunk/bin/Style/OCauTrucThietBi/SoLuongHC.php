<?php
//Bỏ vì chỉ cần tạo phiếu bảo trì
class Qss_Bin_Style_OCauTrucThietBi_SoLuongHC extends Qss_Lib_Style
{
    public function __doExecute()
    {
    	$bg = '';
  	  	if($this->_data->SoLuongHC != $this->_data->SoLuongChuan)
  	  	{
  	  		$bg = 'bgpink right';
  	  	}
		return $bg;
    }

}
?>