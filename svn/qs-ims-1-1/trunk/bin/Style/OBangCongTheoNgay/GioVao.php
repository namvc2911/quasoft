<?php
//
class Qss_Bin_Style_OBangCongTheoNgay_GioVao extends Qss_Lib_Style
{
    public function __doExecute()
    {
    	$bg = '';
        if($this->_data->GioVao == '' 
        	&& $this->_data->CaLamViec != ''
        	&& $this->_data->LoaiNgayNghi == '')
        {
            $bg = 'bgpink brred';
        }
        return $bg;
    }

}
?>