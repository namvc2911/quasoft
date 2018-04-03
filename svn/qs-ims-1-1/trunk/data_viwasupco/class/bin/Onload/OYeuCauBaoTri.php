<?php
class Qss_Bin_Onload_OYeuCauBaoTri extends Qss_Bin_Onload_Base_OYeuCauBaoTri
{
	public function __doExecute() {
		parent::__doExecute();
        // Điền tên khu vực theo mã thiết bị
        $this->_DienMaKhuVuc();
        // Onload lại tên mặt hàng có thể sửa khi sử dụng mã tạm
        $this->_MaTam();

//        $mEmp = new Qss_Model_Maintenance_Employee();
//        $user = Qss_Register::get('userinfo');
//        $emp  = $mEmp->getEmployeeByUserID($user->user_id); // Lấy nhân viên theo user đă
//		if($emp)
//		{
//		    $khuvuc   = $emp->KhuVuc;
//		    $makhuvuc   = $emp->MaKhuVuc;
//		    if(Qss_Lib_System::fieldActive('OYeuCauBaoTri', 'MaKhuVuc'))
//			{
//				if ($khuvuc && !$this->_object->getFieldByCode('MaKhuVuc')->getRefIOID())
//				{
//					$this->_object->getFieldByCode('MaKhuVuc')->setValue($makhuvuc);
//					$this->_object->getFieldByCode('MaKhuVuc')->setRefIOID($khuvuc);
//
//				}
//			}
//		}
	}

	/**
     * Hàm custom riêng: Cho phép nơi nhận (Đơn vị bảo trì) lấy theo loại là bảo trì
     */
	public function NoiNhan() {
        $this->_object->getFieldByCode('NoiNhan')->arrFilters[] = sprintf('
            	v.IOID in (SELECT IOID FROM ODonViSanXuat WHERE IFNULL(BaoTri, 0) = 1)');
    }
}