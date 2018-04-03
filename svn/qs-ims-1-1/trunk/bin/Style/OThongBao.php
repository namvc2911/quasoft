<?php
//Bỏ vì chỉ cần tạo phiếu bảo trì
class Qss_Bin_Style_OThongBao extends Qss_Lib_Style
{
    public function __doExecute()
    {
        $user = Qss_Register::get('userinfo');

    	if(isset($this->_data->unRead) && $this->_data->unRead && (int)$user->user_id != (int)$this->_data->UID)
    	{
    		return 'bold';
    	}
        return '';
    }

}
?>