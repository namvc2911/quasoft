<?php
Class Qss_Service_Extra_Maintenance_Equip_Param_Manual_Save extends  Qss_Service_Abstract
{
    public function __doExecute($params)
    {
        //echo '<Pre>'; print_r($params); die;
        $eqs    = (isset($params['diemdo']) && $params['diemdo'])?$params['diemdo']:array();
        $date   = (isset($params['input_date']) && $params['input_date'])?$params['input_date']:date('d-m-Y');
        $time   = (isset($params['input_time']) && $params['input_time'])?$params['input_time']:date('d-m-Y');
        $author = (isset($params['m816_author']) && $params['m816_author'])?$params['m816_author']:0;
        $insert = array();
        $i      = 0;

        foreach($eqs as $eq)
        {
            if($params['dinhluong'][$i] == 0) // OK/Not Ok
            {
                $insert = array();
                $insert['ONhatTrinhThietBi'][0]['DiemDo'] 		= (int)$eq;
                $insert['ONhatTrinhThietBi'][0]['Ngay']      	= $date;
                $insert['ONhatTrinhThietBi'][0]['Ca']      	    = (int)$params['m816_shift'];
                $insert['ONhatTrinhThietBi'][0]['ThoiGian']     = $time;
                $insert['ONhatTrinhThietBi'][0]['NguoiKiemTra'] = (int)$author;
                $insert['ONhatTrinhThietBi'][0]['Dat']          = (int)$params['val'][$i];
                $insert['ONhatTrinhThietBi'][0]['TinhTrang']    = (int)$params['status'][$i];
            }
            else
            {
                if(isset($params['val'][$i]) && is_numeric($params['val'][$i]))
                {
                    $insert = array();
                    $insert['ONhatTrinhThietBi'][0]['DiemDo'] 		= (int)$eq;
                    $insert['ONhatTrinhThietBi'][0]['Ngay']      	= $date;
                    $insert['ONhatTrinhThietBi'][0]['Ca']      	    = (int)$params['m816_shift'];
                    $insert['ONhatTrinhThietBi'][0]['ThoiGian']     = $time;
                    $insert['ONhatTrinhThietBi'][0]['NguoiKiemTra'] = (int)$author;
                    $insert['ONhatTrinhThietBi'][0]['SoDau']	    = 0;
                    $insert['ONhatTrinhThietBi'][0]['SoCuoi']		= is_numeric($params['val'][$i])?$params['val'][$i]:0;
                    $insert['ONhatTrinhThietBi'][0]['SoHoatDong']	= is_numeric($params['val'][$i])?$params['val'][$i]:0;
                    $insert['ONhatTrinhThietBi'][0]['TinhTrang']    = (int)$params['status'][$i];
                }
            }

            //echo '<pre>'; print_r($insert);
            if(count($insert))
            {
                $service = $this->services->Form->Manual('M765', 0 , $insert);
                if($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
            }

            $i++;

        }

        //die;


        if(count($eqs) && !$this->isError())
        {
            $this->setMessage('Cập nhật thành công!');
        }
    }
}