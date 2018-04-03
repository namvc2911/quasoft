<?php
//Bỏ vì chỉ cần tạo phiếu bảo trì
class Qss_Bin_Style_ODanhSachCongViec_UuTien extends Qss_Lib_Style
{
    public function __doExecute()
    {
        $bg = '';
        if($this->_data->UuTien == 'LOW')
        {
            $bg = 'green bold left';
        }
        elseif($this->_data->UuTien == 'MEDIUM')
        {
            $bg = 'orange bold left';
        }
        elseif($this->_data->UuTien == 'HIGHT')
        {
            $bg = 'red bold left';
        }
        return $bg;
    }

}
?>