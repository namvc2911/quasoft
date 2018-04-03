<?php

class Qss_Service_Maintenance_Calibration_Insert extends Qss_Service_Abstract
{
    public function __doExecute($params)
    {
        if(!isset($params['date']) || !$params['date'])
        {
            $this->setError();
            $this->setMessage('Ngày kiểm định/hiệu chuẩn yêu cầu bắt buộc!');
        }

        if(!isset($params['type']) || !$params['type'])
        {
            $this->setError();
            $this->setMessage('Loại kiểm định/hiệu chuẩn yêu cầu bắt buộc!');
        }

        if(!isset($params['period']) || !$params['period'])
        {
            $this->setError();
            $this->setMessage('Chu kỳ kiểm định/hiệu chuẩn yêu cầu bắt buộc!');
        }

        if(!isset($params['equip']) || !count($params['equip']))
        {
            $this->setError();
            $this->setMessage('Thiết bị kiểm định/hiệu chuẩn yêu cầu bắt buộc!');
        }

        if($this->isError())
        {
            return;
        }


        // Them hieu chuan kiem diinh
        $i       = 0;
        foreach($params['equip'] as $equipIOID)
        {
            // Ngay kiem dinh tiep theo
            $addMonth = 0;

            switch((int)$params['period'])
            {
                case 1: $addMonth = 1; break;
                case 2: $addMonth = 3; break;
                case 3: $addMonth = 6; break;
                case 4: $addMonth = 12; break;
            }

            $nextCalibration = date('Y-m-d', strtotime($params['date']. ' +'.$addMonth.' months' ));
            // echo '<pre>'; print_r($nextCalibration); die;

            $insert                                                  = array();
            $insert['OHieuChuanKiemDinh'][0]['MaThietBi']            = (int)$equipIOID;
            $insert['OHieuChuanKiemDinh'][0]['BoPhan']               = (int)$params['component'][$i];
            $insert['OHieuChuanKiemDinh'][0]['Ngay']                 = $params['date'];
            $insert['OHieuChuanKiemDinh'][0]['Loai']                 = (int)$params['type'];
            $insert['OHieuChuanKiemDinh'][0]['ChuKy']                = (int)$params['period'];
            $insert['OHieuChuanKiemDinh'][0]['DonVi']                = (int)$params['workcenter'];
            $insert['OHieuChuanKiemDinh'][0]['NoiDung']              = $params['content'];
            $insert['OHieuChuanKiemDinh'][0]['CacThongSoKiemTra']    = $params['tech'];
            $insert['OHieuChuanKiemDinh'][0]['NgayKiemDinhTiepTheo'] = $nextCalibration;

            if(!$this->isError())
            {
                $service = $this->services->Form->Manual('M753',  0,  $insert, false);
                if ($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
            }

            $i++;
        }

        if(!$this->isError())
        {
            Qss_Service_Abstract::$_redirect = '/user/form?fid=M753';
        }

    }
}