<?php
class Qss_Service_Button_M759_Assign_Save extends Qss_Service_Abstract
{
    private function _saveTasks($insert, $ifid)
    {
        $error = 0;

        if(count($insert)) {
            $model = new Qss_Model_Import_Form('M759',false, false);
            $model->setData($insert);
            $model->generateSQL();
            $error = $model->countFormError() + $model->countObjectError();
        }
        else {
            $error = 1;
        }

        if($error > 0) {

            $this->setError();
            $this->setMessage($this->_translate(2));
        }
    }

    public function _validateSave($params)
    {
        $NguoiGiaoViec       = @(int)$params['NguoiGiao'];
        $NguoiChiuTrachNhiem = @(int)$params['NguoiChiuTrachNhiem'];
        $arrIOIDPhieuBaoTri  = @(array)$params['PhieuBaoTri'];
        $XoaNguoiGiaoViec    = @(int)$params['XoaNguoiGiaoViec'];
        $XoaNguoiChiuTrachNhiem = @(int)$params['XoaNguoiChiuTrachNhiem'];

        if(!count($arrIOIDPhieuBaoTri)) {
            $this->setMessage('Cần chọn ít nhất một phiếu bảo trì để giao việc.');
            $this->setError();
        }

//        if(!$XoaNguoiGiaoViec && !$XoaNguoiChiuTrachNhiem) {
//            if($NguoiGiaoViec == 0 && $NguoiChiuTrachNhiem == 0) {
//                $this->setMessage('Cần chọn người giao hoặc người chịu trách nhiệm để giao việc.');
//                $this->setError();
//            }
//        }
    }

    public function __doExecute($params)
    {
        $this->_validateSave($params);

        if(!$this->isError()) {
            $import              = new Qss_Model_Import_Form('M759',false, false);
            $NguoiGiaoViec       = @(int)$params['NguoiGiao'];
            $NguoiChiuTrachNhiem = @(int)$params['NguoiChiuTrachNhiem'];
            $arrIOIDPhieuBaoTri  = @(array)$params['PhieuBaoTri'];
            $NgayBatDau          = @$params['NgayBatDau'];
            $NgayHoanThanh       = @$params['NgayHoanThanh'];
            $XoaNguoiGiaoViec    = @(int)$params['XoaNguoiGiaoViec'];
            $XoaNguoiChiuTrachNhiem = @(int)$params['XoaNguoiChiuTrachNhiem'];
            $insert              = array();

            foreach ($arrIOIDPhieuBaoTri as $soPhieu) {
                $insert['OPhieuBaoTri'][0]['SoPhieu']       = $soPhieu;

                if(!$XoaNguoiGiaoViec){
                    if($NguoiGiaoViec) {
                        $insert['OPhieuBaoTri'][0]['NguoiGiaoViec'] = $NguoiGiaoViec;
                    }
                }
                else {
                    $insert['OPhieuBaoTri'][0]['NguoiGiaoViec'] = '';
                }


                if(!$XoaNguoiChiuTrachNhiem){
                    if($NguoiChiuTrachNhiem) {
                        $insert['OPhieuBaoTri'][0]['NguoiThucHien'] = $NguoiChiuTrachNhiem;
                    }
                }
                else {
                    $insert['OPhieuBaoTri'][0]['NguoiThucHien'] = '';
                }

                if($NgayBatDau) {
                    $insert['OPhieuBaoTri'][0]['NgayBatDau'] = Qss_Lib_Date::displaytomysql($NgayBatDau);
                }

                if($NgayHoanThanh) {
                    $insert['OPhieuBaoTri'][0]['Ngay'] = Qss_Lib_Date::displaytomysql($NgayHoanThanh);
                }

                $import->setData($insert);

            }

            // echo '<pre>'; print_r($NgayBatDau); die;

            $import->generateSQL();
            $error = $import->countFormError() + $import->countObjectError();

            if($error) {
                $this->setMessage('Cập nhật không thành công!');
                $this->setError();
            }
        }
    }
}