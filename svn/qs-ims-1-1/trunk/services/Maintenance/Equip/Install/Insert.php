<?php

class Qss_Service_Maintenance_Equip_Install_Insert extends Qss_Service_Abstract
{
    public function __doExecute($params)
    {
        if(!isset($params['date']) || !$params['date'])
        {
            $this->setError();
            $this->setMessage('Ngày yêu cầu bắt buộc!');
        }

        if(Qss_Lib_Date::compareTwoDate($params['date'], date('d-m-Y')) == 1)
        {
            $this->setError();
            $this->setMessage('Ngày phải nhỏ hơn hoặc bằng hiện tại!');
        }

        if(Qss_Lib_Date::compareTwoDate($params['date'], $params['start']) == -1)
        {
            $this->setError();
            $this->setMessage('Ngày phải lớn hơn hoặc bằng ngày đưa vào sử dụng của thiết bị! Ngày đưa vào sử dụng của thiết bị là '.Qss_Lib_Date::mysqltodisplay($params['start']).'.' );
        }

        if(!isset($params['location']) || !$params['location'])
        {
            $this->setError();
            $this->setMessage('Khu vực yêu cầu bắt buộc!');
        }

        if(
            (!isset($params['location']) || !$params['location'])
            && (!isset($params['costcenter']) || !$params['costcenter'])
            && (!isset($params['line']) || !$params['line'])
            && (!isset($params['manager']) || !$params['manager'])
        )
        {
            $this->setError();
            $this->setMessage('Thông tin cài đặt di chuyển yêu cầu bắt buộc!');
        }

        if($this->isError())
        {
            return;
        }

        $mInstall = new Qss_Model_Maintenance_Equip_Install();
        $last     = $mInstall->getLastInstall((int)$params['equip']);
        $lastTime = '';
        $newTime  = Qss_Lib_Date::displaytomysql($params['date']).' ';
        $newTime .= $params['time']?$params['time']:'00:00';

        if($last && $last->Ngay)
        {
            $lastTime = $last->Ngay.' ';
            $lastTime.= $last->Gio?$last->Gio:'00:00';
        }




        if(!$this->isError())
        {
            $ifid   = ($params['ifid'])?$params['ifid']:0;


            // neu khong co ban ghi nao trong truong hop them moi thi co the them mot ban ghi nua
            if($ifid == 0 && !$last)
            {
                $history = $mInstall->getInstallHistory((int)$params['equip']);

                $insert = array();
                $insert['OCaiDatDiChuyenThietBi'][0]['Ngay']            = $history[0]->NgayDuaVaoSuDung;
                $insert['OCaiDatDiChuyenThietBi'][0]['MaThietBi']       = (int)$params['equip'];
                $insert['OCaiDatDiChuyenThietBi'][0]['KhuVuc']          = (int)$history[0]->Ref_KhuVucTB;
                $insert['OCaiDatDiChuyenThietBi'][0]['TrungTamChiPhi']  = (int)$history[0]->Ref_TrungTamChiPhiTB;
                $insert['OCaiDatDiChuyenThietBi'][0]['DayChuyen']       = (int)$history[0]->Ref_DayChuyenTB;
                $insert['OCaiDatDiChuyenThietBi'][0]['QuanLy']          = (int)$history[0]->Ref_NhanVienTB;

                if(!$this->isError())
                {
                    $service = $this->services->Form->Manual('M173',  0,  $insert, false);
                    if ($service->isError())
                    {
                        $this->setError();
                        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                    }
                }

            }

            $insert = array();
            $insert['OCaiDatDiChuyenThietBi'][0]['Ngay']            = $params['date'];
            $insert['OCaiDatDiChuyenThietBi'][0]['Gio']             = $params['time'];
            $insert['OCaiDatDiChuyenThietBi'][0]['MaThietBi']       = (int)$params['equip'];
            $insert['OCaiDatDiChuyenThietBi'][0]['KhuVuc']          = (int)$params['location'];
            $insert['OCaiDatDiChuyenThietBi'][0]['TrungTamChiPhi']  = (int)$params['costcenter'];
            $insert['OCaiDatDiChuyenThietBi'][0]['DayChuyen']       = (int)$params['line'];
            $insert['OCaiDatDiChuyenThietBi'][0]['QuanLy']          = (int)$params['manager'];

            if(!$this->isError())
            {
                $service = $this->services->Form->Manual('M173',  $ifid,  $insert, false);
                if ($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }
            }

            if(!$this->isError())
            {
                if(!$lastTime || Qss_Lib_Date::compareTwoDate($newTime, $lastTime) == 1)
                {
                    $update = array();
                    $update['ODanhSachThietBi'][0]['MaKhuVuc']       = (int)$params['location'];
                    $update['ODanhSachThietBi'][0]['DayChuyen']      = (int)$params['line'];
                    $update['ODanhSachThietBi'][0]['TrungTamChiPhi'] = (int)$params['costcenter'];
                    $update['ODanhSachThietBi'][0]['QuanLy']         = (int)$params['manager'];

                    $service = $this->services->Form->Manual('M705',  (int)$params['equipifid'],  $update, false);
                    if ($service->isError())
                    {
                        $this->setError();
                        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                    }
                }
            }
        }

        if(!$this->isError())
        {
            $this->setMessage('Cập nhật thành công!');
            //Qss_Service_Abstract::$_redirect = '/static/m173/index';
        }
    }
}