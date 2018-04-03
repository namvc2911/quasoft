<?php

class Qss_Service_Maintenance_Breakdown_Add extends Qss_Service_Abstract
{
    public function __doExecute ($params)
    {
        if(!isset($params['tovalue']) || !count($params['tovalue']))
        {
            $this->setError();
            $this->setMessage('Bạn phải chọn thiết bị dừng máy!');
        }

        if(!isset($params['startDate']) || !$params['startDate'])
        {
            $this->setError();
            $this->setMessage('Bạn phải chọn ngày bắt đầu dừng máy!');
        }

        if(!isset($params['endDate']) || !$params['endDate'])
        {
            $this->setError();
            $this->setMessage('Bạn phải chọn ngày kết thúc dừng máy!');
        }

        if(!isset($params['reason1']) || !$params['reason1'])
        {
            $this->setError();
            $this->setMessage('Bạn phải chọn nguyên nhân dừng máy!');
        }

        if($this->isError())
        {
            return;
        }

        $insert = array();
        $i      = 0;

        foreach($params['tovalue'] as $item)
        {
            $insert = array();
            $insert['OPhieuSuCo'][0]['MaThietBi']              = (int)$item;
            $insert['OPhieuSuCo'][0]['NgayDungMay']            = $params['startDate'];
            $insert['OPhieuSuCo'][0]['NgayKetThucDungMay']     = $params['endDate'];
            $insert['OPhieuSuCo'][0]['ThoiGianBatDauDungMay']  = $params['startTime'];
            $insert['OPhieuSuCo'][0]['ThoiGianKetThucDungMay'] = $params['endTime'];
            $insert['OPhieuSuCo'][0]['MaNguyenNhanSuCo']       = $params['reason1'];


            if(!$this->isError())
            {
                $service = $this->services->Form->Manual('M707',  0,  $insert, false);
                if ($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
            }

            $i++;
        }

//        if(!$this->isError())
//        {
//            $this->setMessage('Cập nhật thành công!');
//        }
    }
}
?>