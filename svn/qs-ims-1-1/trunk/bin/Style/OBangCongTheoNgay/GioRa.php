<?php
//
class Qss_Bin_Style_OBangCongTheoNgay_GioRa extends Qss_Lib_Style
{
    public function __doExecute()
    {
    	$bg = '';
        if($this->_data->GioRa == '' 
        	&& $this->_data->CaLamViec != ''
        	&& $this->_data->LoaiNgayNghi == '')
        {
            $bg = 'bgpink brred';
        }
        return $bg;
    }

}
?>