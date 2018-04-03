<?php
//Bỏ vì chỉ cần tạo phiếu bảo trì
class Qss_Bin_Style_ODanhSachCongViec_TinhTrang extends Qss_Lib_Style
{
    public function __doExecute()
    {
        $bg = '';
        if($this->_data->TinhTrang == 'OPEN')
        {
            $bg = 'bgpink bold center';
        }
        elseif($this->_data->TinhTrang == 'PENDING')
        {
            $bg = 'bgorange bold center';
        }
        elseif($this->_data->TinhTrang == 'CLOSED')
        {
            $bg = 'bggreen bold center white';
        }
        elseif($this->_data->TinhTrang == 'REJECT')
        {
            $bg = 'bgred bold center';
        }
        return $bg;
    }

}
?>