<?php
//Bỏ vì chỉ cần tạo phiếu bảo trì
class Qss_Bin_Style_ODanhSachCongViec_PhanTram extends Qss_Lib_Style
{
    public function __doExecute()
    {
        $bg = '';
        if($this->_data->PhanTram >= 70)
        {
            $bg = 'green bold right';
        }
        elseif($this->_data->UuTien >= 50)
        {
            $bg = 'orange bold right';
        }
        else
        {
            $bg = 'red bold right';
        }
        return $bg;
    }

}
?>