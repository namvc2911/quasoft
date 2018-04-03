<?php
//
class Qss_Bin_Style_OBangCongTheoNgay_LoaiNgayNghi extends Qss_Lib_Style
{
    public function __doExecute()
    {
    	$bg = '';
        if($this->_data->LoaiNgayNghi != '')
        {
            $bg = 'bold red';
        }
        return $bg;
    }

}
?>