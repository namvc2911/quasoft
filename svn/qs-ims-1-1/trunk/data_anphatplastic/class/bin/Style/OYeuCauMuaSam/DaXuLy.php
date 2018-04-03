<?php
//Bỏ vì chỉ cần tạo phiếu bảo trì
class Qss_Bin_Style_OYeuCauMuaSam_DaXuLy extends Qss_Lib_Style
{
    public function __doExecute()
    {
        $bg = '';
        if((int)$this->_data->DaXuLy == 0 && (int)$this->_data->Status == 3)
        {
            $bg = 'bgpink bold';
        }
        return $bg;
    }

}
?>