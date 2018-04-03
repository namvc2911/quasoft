<?php

/**
 * Class Qss_Service_Static_M852_Checklist_Save
 * Lưu check list từ button chọn check list
 */
class Qss_Service_Static_M852_Checklist_Save extends Qss_Lib_Service
{
    function __doExecute($params)
    {
        if(!isset($params['list']) || !$params['list'])
        {
            $this->setError();
            $this->setMessage('Không tồn tại check list');
            return;
        }

        if(!isset($params['ioid']) || !count($params['ioid']))
        {
            $this->setError();
            $this->setMessage('Cần chọn ít nhất một dòng');
            return;
        }

        $mList = Qss_Model_Db::Table('OChiTietBangChonCongViec');
        $mList->select('OChiTietBangChonCongViec.*');
        $mList->where(sprintf('OChiTietBangChonCongViec.IOID IN  (%1$s)', implode(', ', $params['ioid'])));
        $mList->orderby('OChiTietBangChonCongViec.IOID');
        $oList  = $mList->fetchAll();
        $insert = array();
        $i      = 0;
        $model  = new Qss_Model_Import_Form('M852',false, false);


        foreach ($oList as $item)
        {
            $insert['OBangChonCongViecMau'][$i]['Ten']  = $item->Ten;
            $insert['OBangChonCongViecMau'][$i]['MoTa'] = $item->MoTa;
            $insert['OBangChonCongViecMau'][$i]['ifid'] = $params['ifid'];
            $i++;
        }

        if(count($insert))
        {
            $model->setData($insert);
            $model->generateSQL();
            $error = $model->countFormError() + $model->countObjectError();

            if($error)
            {
                echo '<pre>'; print_r($model->getErrorRows()); die;
                $this->setError();
                $this->setMessage('Có '.$error.' dòng lỗi!');
            }
        }
        else
        {
            $this->setError();
            $this->setMessage('Chi tiết bảng chọn cần ít nhất một dòng dữ liệu!');
        }
    }
}