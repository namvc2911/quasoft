<?php
class Qss_Service_Operation_MobileSave extends Qss_Service_Abstract
{
    /**/
    public function __doExecute($params, $nextStep = 0)
    {
        $model  = new Qss_Model_Import_Form('M840',false, false);
        $insert = array();
        $user   = Qss_Register::get('userinfo');

        $insert['OCongViecNhanVien'][0]['CongViec']              = @$params['OCongViecNhanVien_CongViec'];
        $insert['OCongViecNhanVien'][0]['NhanVien']              = @(int)$params['OCongViecNhanVien_NhanVien'];
        $insert['OCongViecNhanVien'][0]['MaThietBi']             = @$params['OCongViecNhanVien_MaThietBi'];
        $insert['OCongViecNhanVien'][0]['TenThietBi']            = '';

        $insert['OCongViecNhanVien'][0]['Ngay']                  = Qss_Lib_Date::displaytomysql(@$params['OCongViecNhanVien_Ngay']);
        $insert['OCongViecNhanVien'][0]['ThoiGianBatDau']        = Qss_Lib_Date::formatTime(@$params['OCongViecNhanVien_ThoiGianBatDau']);
        $insert['OCongViecNhanVien'][0]['ThoiGianKetThuc']       = Qss_Lib_Date::formatTime(@$params['OCongViecNhanVien_ThoiGianKetThuc']);
        $insert['OCongViecNhanVien'][0]['ThoiGianBatDauDuKien']  = Qss_Lib_Date::formatTime(@$params['OCongViecNhanVien_ThoiGianBatDauDuKien']);
        $insert['OCongViecNhanVien'][0]['ThoiGianKetThucDuKien'] = Qss_Lib_Date::formatTime(@$params['OCongViecNhanVien_ThoiGianKetThucDuKien']);

        $insert['OCongViecNhanVien'][0]['NguoiGiaoViec']         = @(int)$params['OCongViecNhanVien_NguoiGiaoViec'];
        $insert['OCongViecNhanVien'][0]['NgayGiao']              = Qss_Lib_Date::displaytomysql(@$params['OCongViecNhanVien_NgayGiao']);
        $insert['OCongViecNhanVien'][0]['ThoiGianGiaoViec']      = Qss_Lib_Date::formatTime(@$params['OCongViecNhanVien_ThoiGianGiaoViec']);
        $insert['OCongViecNhanVien'][0]['ifid']                  = $params['ifid']?$params['ifid']:0;

//
//        echo '<pre>'; print_r($insert); die;
//
//        $service = $this->services->Form->Manual('M840',  @(int)$params['ifid'],  $insert, false);
//        if ($service->isError())
//        {
//            $this->setError();
//            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//        }
//
//        if(!$this->isError()  && (!isset($params['back']) || !$params['back']))
//        {
//            $ifid = $service->getData();
//            Qss_Service_Abstract::$_redirect = '/mobile/m840/mytasks/edit?fid=M816&ifid='.$ifid.'&deptid='.$params['deptid']
//                .'&diemdo='.$params['diemdo'];
//        }
//
//
//        $array = array(
//            'OCongViecNhanVien' => array
//            (
//                'MaThietBi' => 0,
//                'Ngay' => '2017-01-09',
//                'ThoiGianBatDau' => '10:52',
//                'ThoiGianKetThuc' => '',
//                'CongViec' => 'Bật đèn',
//                'NhanVien' => 1
//            )
//        );


        $model->setData($insert);

        $model->generateSQL();

        $countInserted = $model->countFormImported();
        if($countInserted)
        {
            $importedRow = $model->getIFIDs();
        }

        $formError   = $model->countFormError();
        $objectError = $model->countObjectError();
        $error       = $formError + $objectError;

//        echo '<pre>'; print_r($model->getImportRows()); die;

        if($error > 0)
        {
            $this->setError();
            $this->setMessage('Có '.$error .' dòng lỗi!');
        }


        if(!$this->isError()) {

            $ifid = $params['ifid'];

            if(!$ifid)
            {
                foreach ($importedRow as $item) {
                    $ifid = $item->oldIFID;
                }
            }

            if($nextStep > 1)
            {
                $form = new Qss_Model_Form();
                $form->initData($ifid, $user->user_dept_id);
                $service = $this->services->Form->Request($form, $nextStep, $user, '');
            }


            if((!isset($params['back']) || !$params['back'])) {
                // echo '<pre>'; print_r($ioid); die;

                Qss_Service_Abstract::$_redirect = '/mobile/m840/mytasks/edit?fid=M840&ifid=' . $ifid
                    . '&deptid=' . $params['user']->user_dept_id
                    . '&diemdo='.  $params['diemdo']
                    . '&page='.  $params['page']
                    . '&perpage='.  $params['perpage']
                    . '&input_date='.  $params['input_date']
                    . '&m816_shift='.  $params['m816_shift']
                    . '&filter_location='.  $params['filter_location'];


            }


        }


    }
}