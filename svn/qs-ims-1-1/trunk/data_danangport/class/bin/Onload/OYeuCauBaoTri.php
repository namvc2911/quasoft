<?php
class Qss_Bin_Onload_OYeuCauBaoTri extends Qss_Bin_Onload_Base_OYeuCauBaoTri
{
	public function __doExecute()
	{
		parent::__doExecute();
        // Điền tên khu vực theo mã thiết bị
        $this->_DienMaKhuVuc();
        // Onload lại tên mặt hàng có thể sửa khi sử dụng mã tạm
        $this->_MaTam();
        // Điền người yêu cầu mặc định theo user
        $this->_NguoiYeuCau();
	}

    public function NguoiYeuCau() {

    }
}