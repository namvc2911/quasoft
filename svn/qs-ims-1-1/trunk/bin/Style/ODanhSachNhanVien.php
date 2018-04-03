<?php
//Bỏ vì chỉ cần tạo phiếu bảo trì
class Qss_Bin_Style_ODanhSachNhanVien extends Qss_Lib_Style
{
    public function __doExecute()
    {
        if($this->_data->ThoiViec == 1)
    	{
    		return 'grey strickethrough';
    	}
        return '';
    }

}
?>