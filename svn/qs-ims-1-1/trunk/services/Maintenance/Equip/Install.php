<?php

class Qss_Service_Maintenance_Equip_Install extends Qss_Service_Abstract
{
    public function __doExecute ($params)
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

        if($this->isError())
        {
            return;
        }

        $installEquips = array();
        $countInstall  = array(); // Dem so luong cai dat theo thiet bi
        $mInstall      = new Qss_Model_Maintenance_Equip_Install();
        $insert        = array();
        $i             = 0;
        $j             = 0;

        if(!isset($params['fromvalue']) || !count($params['fromvalue']))
        {
            $params['fromvalue'] = array();
        }

        if(!isset($params['tovalue']) || !count($params['tovalue']))
        {
            $params['tovalue'] = array();
        }

        // Dem so dong cai dat theo thiet bi
        $installEquips = array_merge($params['fromvalue'], $params['tovalue']);
        $countInstall  = $mInstall->countInstallLine($installEquips);
        $before        = $mInstall->getLastInstallBeforeDate($params['date'],$installEquips);
        $lastBefore    = new stdClass();

        foreach($before as $item)
        {
            $lastBefore->{$item->ThietBiIOID} = $item;
        }

        // Dien dong cai dat dau tien cua thiet bi neu chua co dong nao
        foreach($countInstall as $item)
        {
            if($item->Total == 0)
            {
                $insert = array();
                $insert['OCaiDatDiChuyenThietBi'][0]['Ngay']            = $item->NgayDuaVaoSuDung?$item->NgayDuaVaoSuDung:date('Y-m-d');
                $insert['OCaiDatDiChuyenThietBi'][0]['MaThietBi']       = (int)$item->IOID;
                $insert['OCaiDatDiChuyenThietBi'][0]['KhuVuc']          = (int)$item->Ref_MaKhuVuc;
                $insert['OCaiDatDiChuyenThietBi'][0]['TrungTamChiPhi']  = (int)$item->Ref_TrungTamChiPhi;
                $insert['OCaiDatDiChuyenThietBi'][0]['DayChuyen']       = (int)$item->Ref_DayChuyen;
                $insert['OCaiDatDiChuyenThietBi'][0]['QuanLy']          = (int)$item->Ref_QuanLy;
                $insert['OCaiDatDiChuyenThietBi'][0]['TrucThuoc']       = (int)$item->Ref_TrucThuoc;

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
        }


        // Cai dat thiet bi phia ben tay trai <Tu phai chuyen sang trai
        foreach ($params['fromvalue'] as $item)
        {
            $last     = $mInstall->getLastInstall((int)$item);
            $lastTime = '';
            $newTime  = Qss_Lib_Date::displaytomysql($params['date']).' ';
            $newTime .= $params['time']?$params['time']:'00:00';

            if($last && $last->Ngay)
            {
                $lastTime = $last->Ngay.' ';
                $lastTime.= $last->Gio?$last->Gio:'00:00';
            }


            $true   = false;
            $insert = array();
            $insert['OCaiDatDiChuyenThietBi'][0]['Ngay']            = $params['date'];
            $insert['OCaiDatDiChuyenThietBi'][0]['Gio']             = $params['time'];
            $insert['OCaiDatDiChuyenThietBi'][0]['MaThietBi']       = (int)$item;

            if(isset($lastBefore->{(int)$item}))
            {
                $insert['OCaiDatDiChuyenThietBi'][0]['TrungTamChiPhi'] = $lastBefore->{(int)$item}->Ref_TrungTamChiPhi;
                $insert['OCaiDatDiChuyenThietBi'][0]['DayChuyen'] = $lastBefore->{(int)$item}->Ref_DayChuyen;
                $insert['OCaiDatDiChuyenThietBi'][0]['KhuVuc'] = $lastBefore->{(int)$item}->Ref_KhuVuc;
                $insert['OCaiDatDiChuyenThietBi'][0]['QuanLy'] = $lastBefore->{(int)$item}->Ref_NhanVien;
                $insert['OCaiDatDiChuyenThietBi'][0]['TrucThuoc'] = $lastBefore->{(int)$item}->Ref_TrucThuoc;
            }


            if($params['current'] == 'LOCATION')
            {
                $insert['OCaiDatDiChuyenThietBi'][0]['KhuVuc']          = (int)$params['fromid'];
                $true = true;

                if($params['parent_equip'])
                {
                    $insert['OCaiDatDiChuyenThietBi'][0]['TrucThuoc'] = (int)$params['parent_equip'];
                }
                else
                {
                    $insert['OCaiDatDiChuyenThietBi'][0]['TrucThuoc'] = (int)0;
                }

            }
            elseif($params['current'] == 'COSTCENTER')
            {
                $insert['OCaiDatDiChuyenThietBi'][0]['TrungTamChiPhi']  = (int)$params['fromid'];
                $true = true;
            }
            elseif($params['current'] == 'LINE')
            {
                $insert['OCaiDatDiChuyenThietBi'][0]['DayChuyen']       = (int)$params['fromid'];
                $true = true;
            }
            elseif($params['current'] == 'MANAGER')
            {
                $insert['OCaiDatDiChuyenThietBi'][0]['QuanLy']       = (int)$params['fromid'];
                $true = true;
            }

            if($true)
            {
                if(!$this->isError())
                {
                    $service = $this->services->Form->Manual('M173', 0, $insert, false);
                    if ($service->isError()) {
                        $this->setError();
                        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                    }
                }

                if(!$this->isError())
                {
                    if (!$lastTime || Qss_Lib_Date::compareTwoDate($newTime, $lastTime) == 1)
                    {
                        $update = array();
                        $update['ODanhSachThietBi'][0]['ioid']      = (int)$item;
                        $update['ODanhSachThietBi'][0]['ifid']      = (int)$params['fromifid'][$i];



                        if($params['current'] == 'LOCATION')
                        {
                            $update['ODanhSachThietBi'][0]['MaKhuVuc'] = (int)$params['fromid'];


                            $update['ODanhSachThietBi'][0]['TrucThuoc'] = (int)$params['parent_equip'];

                        }
                        elseif($params['current'] == 'COSTCENTER')
                        {
                            $update['ODanhSachThietBi'][0]['TrungTamChiPhi'] = (int)$params['fromid'];
                        }
                        elseif($params['current'] == 'LINE')
                        {
                            $update['ODanhSachThietBi'][0]['DayChuyen'] = (int)$params['fromid'];
                        }
                        elseif($params['current'] == 'MANAGER')
                        {
                            $update['ODanhSachThietBi'][0]['QuanLy'] = (int)$params['fromid'];
                        }


                        if(!$this->isError())
                        {
                            $service = $this->services->Form->Manual('M705',  $params['fromifid'][$i],  $update, false);
                            if ($service->isError())
                            {
                                $this->setError();
                                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                            }
                        }
                    }
                }
            }

            $i++;
        }


        // Cai dat thiet bi phia ben tay phai <Tu trai chuyen sang phai>
        foreach ($params['tovalue'] as $item)
        {
            $last     = $mInstall->getLastInstall((int)$item);
            $lastTime = '';
            $newTime  = Qss_Lib_Date::displaytomysql($params['date']).' ';
            $newTime .= $params['time']?$params['time']:'00:00';

            if($last && $last->Ngay)
            {
                $lastTime = $last->Ngay.' ';
                $lastTime.= $last->Gio?$last->Gio:'00:00';
            }


            $true   = false;
            $insert = array();
            $insert['OCaiDatDiChuyenThietBi'][0]['Ngay']            = $params['date'];
            $insert['OCaiDatDiChuyenThietBi'][0]['Gio']             = $params['time'];
            $insert['OCaiDatDiChuyenThietBi'][0]['MaThietBi']       = (int)$item;

            if(isset($lastBefore->{(int)$item}))
            {
                $insert['OCaiDatDiChuyenThietBi'][0]['TrungTamChiPhi'] = $lastBefore->{(int)$item}->Ref_TrungTamChiPhi;
                $insert['OCaiDatDiChuyenThietBi'][0]['DayChuyen'] = $lastBefore->{(int)$item}->Ref_DayChuyen;
                $insert['OCaiDatDiChuyenThietBi'][0]['KhuVuc'] = $lastBefore->{(int)$item}->Ref_KhuVuc;
                $insert['OCaiDatDiChuyenThietBi'][0]['QuanLy'] = $lastBefore->{(int)$item}->Ref_NhanVien;
                $insert['OCaiDatDiChuyenThietBi'][0]['TrucThuoc'] = $lastBefore->{(int)$item}->Ref_TrucThuoc;
            }



            if($params['current'] == 'LOCATION')
            {
                $insert['OCaiDatDiChuyenThietBi'][0]['KhuVuc']          = (int)$params['toid'];
                $true = true;

                if(Qss_Lib_System::fieldActive('TrucThuoc', 'ODanhSachThietBi')) {
                    if($params['parent_equip']) {
                        $insert['OCaiDatDiChuyenThietBi'][0]['TrucThuoc'] = (int)$params['parent_equip'];
                    }
                    else {
                        $insert['OCaiDatDiChuyenThietBi'][0]['TrucThuoc'] = (int)0;
                    }
                }
            }
            elseif($params['current'] == 'COSTCENTER')
            {
                $insert['OCaiDatDiChuyenThietBi'][0]['TrungTamChiPhi']  = (int)$params['toid'];
                $true = true;
            }
            elseif($params['current'] == 'LINE')
            {
                $insert['OCaiDatDiChuyenThietBi'][0]['DayChuyen']       = (int)$params['toid'];
                $true = true;
            }
            elseif($params['current'] == 'MANAGER')
            {
                $insert['OCaiDatDiChuyenThietBi'][0]['QuanLy']       = (int)$params['toid'];
                $true = true;
            }

            if($true)
            {
                if(!$this->isError())
                {
                    $service = $this->services->Form->Manual('M173', 0, $insert, false);
                    if ($service->isError()) {
                        $this->setError();
                        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                    }
                }


                if(!$this->isError())
                {
                    if (!$lastTime || Qss_Lib_Date::compareTwoDate($newTime, $lastTime) == 1)
                    {
                        $update = array();
                        $update['ODanhSachThietBi'][0]['ioid']      = (int)$item;
                        $update['ODanhSachThietBi'][0]['ifid']      = (int)$params['toifid'][$j];

                        if($params['current'] == 'LOCATION')
                        {
                            $update['ODanhSachThietBi'][0]['MaKhuVuc'] = (int)$params['toid'];

                            if(Qss_Lib_System::fieldActive('TrucThuoc', 'ODanhSachThietBi')) {
                                $update['ODanhSachThietBi'][0]['TrucThuoc'] = (int)$params['parent_equip'];
                            }
                        }
                        elseif($params['current'] == 'COSTCENTER')
                        {
                            $update['ODanhSachThietBi'][0]['TrungTamChiPhi'] = (int)$params['toid'];
                        }
                        elseif($params['current'] == 'LINE')
                        {
                            $update['ODanhSachThietBi'][0]['DayChuyen'] = (int)$params['toid'];
                        }
                        elseif($params['current'] == 'MANAGER')
                        {
                            $update['ODanhSachThietBi'][0]['QuanLy'] = (int)$params['toid'];
                        }


                        if(!$this->isError())
                        {
                            $service = $this->services->Form->Manual('M705',  $params['toifid'][$j],  $update, false);
                            if ($service->isError())
                            {
                                $this->setError();
                                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                            }
                        }
                    }
                }

            }
            $j++;
        }


        if(!$this->isError())
        {
            $this->setMessage('Cập nhật thành công!');
            //Qss_Service_Abstract::$_redirect = '/static/m173/index';
        }
    }
}
?>