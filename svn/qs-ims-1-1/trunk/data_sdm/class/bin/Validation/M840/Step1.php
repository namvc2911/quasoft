<?php
class Qss_Bin_Validation_M840_Step1 extends Qss_Lib_WValidation
{
    public function onNext()
    {
        parent::init();
        $this->removeThoiGianKetThuc();

    }

    public function onBack()
    {
        parent::init();
        $this->removeThoiGianKetThuc();
    }

    public function removeThoiGianKetThuc() {
        $file = new Qss_Model_Import_Form('M840',false, false);

        $mEmp = new Qss_Model_Maintenance_Employee();
        $user = Qss_Register::get('userinfo');
        $emp  = $mEmp->getEmployeeByUserID($user->user_id); // Lấy nhân viên theo user đă

        $insert                         = array();
        $insert['OCongViecNhanVien']    = array();
        $insert['OCongViecNhanVien'][0] = array();

        $insert['OCongViecNhanVien'][0]['ThoiGianKetThuc'] = '';
        $insert['OCongViecNhanVien'][0]['ifid']            = $this->_params->IFID_M840;
        $insert['OCongViecNhanVien'][0]['ioid']            = $this->_params->IOID;

        $file->setData($insert);
        $file->generateSQL();
    }
}