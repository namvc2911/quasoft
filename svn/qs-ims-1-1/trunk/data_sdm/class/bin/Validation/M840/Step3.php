<?php
class Qss_Bin_Validation_M840_Step3 extends Qss_Lib_WValidation
{
    public function onNext()
    {
        parent::init();

        $file = new Qss_Model_Import_Form('M840',false, false);

        $mEmp = new Qss_Model_Maintenance_Employee();
        $user = Qss_Register::get('userinfo');
        $emp  = $mEmp->getEmployeeByUserID($user->user_id); // Lấy nhân viên theo user đă
        $mTable = Qss_Model_Db::Table('OCongViecNhanVien');
        $mTable->where(sprintf('IFID_M840 = %1$d', $this->_form->i_IFID));
        $OCongViecNhanVien = $mTable->fetchOne();


        $insert                         = array();
        $insert['OCongViecNhanVien']    = array();
        $insert['OCongViecNhanVien'][0] = array();

        if($OCongViecNhanVien && $OCongViecNhanVien->ThoiGianKetThuc == '' ) {
            $insert['OCongViecNhanVien'][0]['ThoiGianKetThuc'] = date('H:i');
        }

        if($OCongViecNhanVien && $OCongViecNhanVien->NhanVien == '' ) {
            $insert['OCongViecNhanVien'][0]['NhanVien']        = @(int)$emp->IOID;
        }

        $insert['OCongViecNhanVien'][0]['ifid']            = $this->_params->IFID_M840;
        $insert['OCongViecNhanVien'][0]['ioid']            = $this->_params->IOID;

        $file->setData($insert);
        $file->generateSQL();

//        $error = $file->countFormError() + $file->countObjectError();
//
//
//        if($error)
//        {
//            $this->setError();
//            $this->setMessage('Có '.$error.' dòng lỗi!');
//        }
    }

}