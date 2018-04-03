<?php
class Qss_Bin_Onload_OCongViecNhanVien extends Qss_Lib_Onload
{

    public function __doExecute()
    {
        $mEmp  = new Qss_Model_Maintenance_Employee();
        $user  = Qss_Register::get('userinfo');
        $emp   = $mEmp->getEmployeeByUserID($user->user_id); // Lấy nhân viên theo user đăng nhập

        if($emp) {
            if (!$this->_object->i_IOID && $emp->IOID && !$this->_object->getFieldByCode('NguoiGiaoViec')->getRefIOID()) {
                $this->_object->getFieldByCode('NguoiGiaoViec')->setValue($emp->TenNhanVien);
                $this->_object->getFieldByCode('NguoiGiaoViec')->setRefIOID($emp->IOID);
            }
        }
    }
}