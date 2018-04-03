<?php
//Bỏ vì chỉ cần tạo phiếu bảo trì
class Qss_Bin_Style_OPhieuBaoTri_MucDoUuTien extends Qss_Lib_Style
{
    public function __doExecute()
    {
        $bg = '';
        if($this->_data->MucDoUuTien == '0')
        {
            $bg = 'green bold left';
        }
        elseif($this->_data->MucDoUuTien == '1')
        {
            $bg = 'orange bold left';
        }
        elseif($this->_data->MucDoUuTien == '2')
        {
            $bg = 'deeppink bold left';
        }
        elseif($this->_data->MucDoUuTien == '3')
        {
            $bg = 'red bold left';
        }
        return $bg;
    }

}
?>