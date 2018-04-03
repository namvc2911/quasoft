<?php
class Qss_Service_Monitor_MobileSave extends Qss_Service_Abstract
{
    /**/
    public function __doExecute($params, $nextStep = 0)
    {
        if($params['ONhatTrinhThietBi_DinhLuong']) {
            if(!isset($params['ONhatTrinhThietBi_GiaTri']) || !$params['ONhatTrinhThietBi_GiaTri'])
            {
                $this->setError();
                $this->setMessage('Giá trị yêu cầu bắt buộc');
                return;
            }
        }
        else {
            if(!isset($params['ONhatTrinhThietBi_TinhTrang']) || !$params['ONhatTrinhThietBi_TinhTrang'])
            {
                $this->setError();
                $this->setMessage('Tình trạng yêu cầu bắt buộc');
                return;
            }
        }

        $model  = new Qss_Model_Import_Form('M765',false, false);
        $insert = array();
        $user   = Qss_Register::get('userinfo');

        $insert['ONhatTrinhThietBi'][0]['MaTB']             = $params['ONhatTrinhThietBi_MaTB'];
        $insert['ONhatTrinhThietBi'][0]['Ngay']             = Qss_Lib_Date::displaytomysql($params['ONhatTrinhThietBi_Ngay']);
        $insert['ONhatTrinhThietBi'][0]['DiemDo']           = $params['diemdo'];

        if((int)$params['ONhatTrinhThietBi_BoPhan'])
        {
            $insert['ONhatTrinhThietBi'][0]['BoPhan']           = (int)$params['ONhatTrinhThietBi_BoPhan'];
        }


        if($params['ONhatTrinhThietBi_Ca'])
        {
            $insert['ONhatTrinhThietBi'][0]['Ca']           = (string)$params['ONhatTrinhThietBi_Ca'];
        }
        $insert['ONhatTrinhThietBi'][0]['ThoiGian']         = $params['ONhatTrinhThietBi_ThoiGian'];

        if($params['ONhatTrinhThietBi_DinhLuong']) {
            $insert['ONhatTrinhThietBi'][0]['SoHoatDong']   = $params['ONhatTrinhThietBi_GiaTri'];
        }
        else {
            $insert['ONhatTrinhThietBi'][0]['Dat']    = $params['ONhatTrinhThietBi_TinhTrang'];
            $insert['ONhatTrinhThietBi'][0]['SoHoatDong']   = $params['ONhatTrinhThietBi_TinhTrang'];
        }

        $insert['ONhatTrinhThietBi'][0]['NguyenNhan']       = $params['ONhatTrinhThietBi_NguyenNhan'];
        $insert['ONhatTrinhThietBi'][0]['BienPhapKhacPhuc'] = $params['ONhatTrinhThietBi_BienPhapKhacPhuc'];
        $insert['ONhatTrinhThietBi'][0]['GhiChu']           = $params['ONhatTrinhThietBi_GhiChu'];

        // echo '<pre>'; print_r($insert); die;

//        $service = $this->services->Form->Manual('M765',  @(int)$params['ifid'],  $insert, false);
//        if ($service->isError())
//        {
//            $this->setError();
//            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//        }
//
//        if(!$this->isError()  && (!isset($params['back']) || !$params['back']))
//        {
//            $ifid = $service->getData();
//            Qss_Service_Abstract::$_redirect = '/mobile/m816/mytasks/edit?fid=M816&ifid='.$ifid.'&deptid='.$params['deptid']
//                .'&diemdo='.$params['diemdo'];
//        }


        $model->setData($insert);

        $model->generateSQL();
        $importedRow = $model->getIFIDs();
        $formError   = $model->countFormError();
        $objectError = $model->countObjectError();
        $error       = $formError + $objectError;

        // echo '<pre>'; print_r($model->getImportRows()); die;

        if($error > 0)
        {
            $this->setError();
            $this->setMessage('Có '.$error .' dòng lỗi!');
        }

        if(!$this->isError())
        {

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

            if(!$this->isError() && (!isset($params['back']) || !$params['back'])) {
                // echo '<pre>'; print_r($ioid); die;

                Qss_Service_Abstract::$_redirect = '/mobile/m816/mytasks/edit?fid=M816'
                    . '&ifid=' . $ifid
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