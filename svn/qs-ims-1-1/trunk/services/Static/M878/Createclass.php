<?php

class Qss_Service_Static_M878_Createclass extends Qss_Lib_Service
{
    function __doExecute($params)
    {
        $this->_validate($params);

        if(!$this->isError()) {
            $model  = new Qss_Model_Import_Form('M328',false, false);
            $insert = array();
            $i      = 0;

            $insert['OLopHoc'][0]['ifid'] = $params['ifid'];

            foreach($params['Employee'] as $empl) {
                if($empl) {
                    $insert['ODanhSachHocVien'][$i]['MaNhanVien']  = (int)$empl;
                    $insert['ODanhSachHocVien'][$i]['TenNhanVien'] = (int)$empl;
                    $i++;
                }
            }

            if(isset($insert['ODanhSachHocVien']) && count($insert['ODanhSachHocVien'])) {
                $model->setData($insert);
                $model->generateSQL();

                $error = $model->countFormError() + $model->countObjectError();

                if($error)
                {
                    // echo '<pre>'; print_r($model->getImportRows()); die;
                    $this->setError();
                    $this->setMessage('Có '.$error.' dòng lỗi!');
                }
            }
        }
    }

    private function _validate($params)
    {
        if(!isset($params['Employee']) || !count($params['Employee'])) {
            $this->setError();
            $this->setMessage('Chưa chọn học viên!');
        }

        if(!isset($params['ifid']) || !$params['ifid']) {
            $this->setError();
            $this->setMessage('Tạo không thành công!');
        }
    }

}