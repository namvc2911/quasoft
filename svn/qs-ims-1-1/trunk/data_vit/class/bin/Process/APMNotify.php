<?php

class Qss_Bin_Process_APMNotify extends Qss_Lib_Bin
{

    public function __doExecute()
    {
        $common     = new Qss_Model_Extra_Extra();
        $dateCreate = (isset($this->_params->Ngay) && $this->_params->Ngay && $this->_params->Ngay != '0000-00-00')?Qss_Lib_Date::displaytomysql($this->_params->Ngay):date('Y-m-d');

        // Trong hoa phat doi tuong vattu dung chung giua pbt m759 va ke hoach m724
//		'OVatTuPBT' = (QSS_DATA_FOLDER == 'data_hoaphat')?'OVatTu':'OVatTuPBT';
        $date = date_create($dateCreate);
        if(isset($this->_params->Ngay) && $this->_params->Ngay && $this->_params->Ngay != '0000-00-00')
        {
            $enddate = date_create($dateCreate);
        }
        else
        {
            //tính ngày bắt đầu là t2 tuần sau và kết thúc là cn tuần sau
            $enddate = date_create($dateCreate);
            $startfound = false;
            $i = 0;
            while($i<14)
            {
                if($enddate->format('N') == 1)
                {
                    $date = date_create($enddate->format('Y-m-d'));
                    $startfound = true;
                }
                elseif($enddate->format('N') == 7 && $startfound)
                {
                    //$enddate = $sdate;
                    break;
                }
                $enddate = Qss_Lib_Date::add_date($enddate, 1);
                //date_add($date,date_interval_create_from_date_string('1 day'));
                $i++;
            }
        }
        $arrChiSo = array();
        while($enddate >= $date)
        {
            $day = $date->format('d');
            $month = $date->format('m');
            $wday = $date->format('w');
            $year = $date->format('Y');
            $weekno = $date->format('W');

            $location = $this->_params->Ref_KhuVuc;
            $wc = $this->_params->Ref_DVBT;
            $basicplan = Qss_Model_Maintenance_Plan::createInstance();
            $dataSQL 	= $basicplan->getPlansByDate($date->format('Y-m-d'),$location,$wc);

            $params = array();
            $arrThoiGianTheoLich = array();
            $timeSql = sprintf('SELECT * FROM OCa');
            $shift = $this->_db->fetchAll($timeSql);
            $arrShift = array();
            foreach ($shift as $time)
            {
                $arrShift[$time->IOID]['startTime'] = $time ? $time->GioBatDau : '00:00:00';
                $arrShift[$time->IOID]['endTime'] = $time ? $time->GioKetThuc : '00:00:00';
            }

            foreach ($dataSQL as $item)
            {
                $thietbi = (int) $item->Ref_MaThietBi;
                $bophan = (int) $item->Ref_BoPhan;
                $chuky = (int) $item->ChuKyIOID;

                if(($item->CanCu == 1 || $item->CanCu == 2) && isset($arrChiSo[$thietbi][$bophan][$chuky]))
                {

                }
                else
                {
                    //manual to notification
                    $params = array();//reset

                    $ifid = $item->IFID_M724;


                    $materialsSql = sprintf('select * from OVatTu   WHERE IFID_M724 = %1$d', $ifid);
                    $materials = $this->_db->fetchAll($materialsSql);
                    $taskSql = sprintf('select * from OCongViecBT   WHERE IFID_M724 = %1$d', $ifid);
                    $task = $this->_db->fetchAll($taskSql);
                    //$techSQL = sprintf('select * from OKiemTraThongSo   WHERE IFID_M724 = %1$d', $ifid);
                    //$techParam   = $this->_db->fetchAll($techSQL);
                    $dichvuSQL = sprintf('select * from ODichVuBT   WHERE IFID_M724 = %1$d', $ifid);
                    $dichvuParam   = $this->_db->fetchAll($dichvuSQL);

                    $addays = (int)@$item->SoNgay;
                    $addays = ($addays > 1)?($addays - 1):0;
                    $params['OPhieuBaoTri'][0]['NgayYeuCau'] = $date->format('d-m-Y');
                    $params['OPhieuBaoTri'][0]['NgayBatDau'] = $date->format('d-m-Y');
                    $done = Qss_Lib_Date::add_date($date, $addays);
                    $params['OPhieuBaoTri'][0]['Ngay'] = $done->format('d-m-Y');
                    $params['OPhieuBaoTri'][0]['NgayDuKienHoanThanh'] = $done->format('d-m-Y');
                    $params['OPhieuBaoTri'][0]['Ca'] = $item->Ca;
                    $params['OPhieuBaoTri'][0]['LoaiBaoTri'] = $item->LoaiBaoTri;
                    $params['OPhieuBaoTri'][0]['MaDVBT'] = $item->DVBT;
                    $params['OPhieuBaoTri'][0]['MucDoUuTien'] = $item->MucDoUuTien;
                    $params['OPhieuBaoTri'][0]['MaThietBi'] = $item->MaThietBi;
                    $params['OPhieuBaoTri'][0]['BoPhan'] = @$item->BoPhan;
                    //$params['OPhieuBaoTri'][0]['BatDau'] = $startTime;
                    //$params['OPhieuBaoTri'][0]['KetThuc'] = $endTime;
                    $params['OPhieuBaoTri'][0]['ThoiGianDungMay'] = $item->DungMay;
                    $params['OPhieuBaoTri'][0]['ThoiGianXuLy'] = $item->SoPhut;
                    $params['OPhieuBaoTri'][0]['BenNgoai'] = $item->BenNgoai;
                    $params['OPhieuBaoTri'][0]['ChuKy'] = (int) $item->ChuKyIOID;
                    $params['OPhieuBaoTri'][0]['GhiVaoLyLich'] = 1;



                    $o = 0;
                    foreach ($task as $ta)
                    {
                        $params['OCongViecBTPBT'][$o]['ViTri'] = $ta->ViTri;
                        $params['OCongViecBTPBT'][$o]['Ten'] = $ta->Ten;
                        $params['OCongViecBTPBT'][$o]['ThoiGianDuKien'] = $ta->ThoiGian;
                        $params['OCongViecBTPBT'][$o]['NhanCong'] = $ta->NhanCong;
                        $params['OCongViecBTPBT'][$o]['ThoiGian'] = $ta->ThoiGian;
                        $params['OCongViecBTPBT'][$o]['MoTa'] = $ta->MoTa;
                        $params['OCongViecBTPBT'][$o]['ThucHien'] = 1;
                        $params['OCongViecBTPBT'][$o]['DanhGia'] = 1;

                        if(Qss_Lib_System::fieldActive('OCongViecBT', 'NguoiThucHien'))
                        {
                            $params['OCongViecBTPBT'][$o]['NguoiThucHien'] = $ta->NguoiThucHien;
                        }

                        /*if($ta->ThueNgoai)
                        {
                        $params['OCongViecBTPBT'][$o]['MaNCC'] = $ta->MaNCC;
                        $params['OCongViecBTPBT'][$o]['ChiPhi'] = 0;
                        $params['OCongViecBTPBT'][$o]['ThueNgoai'] = $ta->ThueNgoai;
                        }*/
                        $o++;
                    }

                    $n = 0;
                    foreach ($materials as $mat)
                    {
                        $params['OVatTuPBT'][$n]['ViTri'] = $mat->ViTri;
                        $params['OVatTuPBT'][$n]['MaVatTu'] = $mat->MaVatTu;
                        $params['OVatTuPBT'][$n]['ThuocTinh'] = @$mat->ThuocTinh;
                        $params['OVatTuPBT'][$n]['DonViTinh'] = $mat->DonViTinh;
                        $params['OVatTuPBT'][$n]['SoLuongDuKien'] = $mat->SoLuong;
                        $params['OVatTuPBT'][$n]['SoLuong'] = $mat->SoLuong;
                        $params['OVatTuPBT'][$n]['CongViec'] = $mat->CongViec;
                        //$params['OVatTu'][$n]['GiaTri']       = $mat->GiaTri/1000;
                        $n++;
                    }

                    $o = 0;
                    foreach ($dichvuParam as $ta)
                    {
                        $firstTime = $arrShift[$item->Ref_Ca]['startTime'];;//$beginShift;
                        for($interval = 0; $interval < $ta->SoLanKiemTra; $interval++)
                        {
                            //$params['OKiemTraThongSoPBT'][$o]['Ten']    = $ta->Ten;
                            $params['ODichVuPBT'][$o]['MaNCC']  = $ta->MaNCC;
                            $params['ODichVuPBT'][$o]['DichVu']    = $ta->DichVu;
                            $params['ODichVuPBT'][$o]['ChiPhiDuKien'] = $ta->ChiPhi;
                            $params['ODichVuPBT'][$o]['ChiPhi'] = $ta->ChiPhi;
                            $params['ODichVuPBT'][$o]['GhiChu']   = $ta->GhiChu;
                            $o++;
                        }
                    }

                    $service = $this->services->Form->Manual('M759', 0, $params, true);

                    /*if ($service->isError())
                    {
                        $this->setError();
                        $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                    }*/
                }
                if($item->CanCu == 1 || $item->CanCu == 2)
                {
                    $arrChiSo[$thietbi][$bophan][$chuky] = 1;
                }
            }
            $date = Qss_Lib_Date::add_date($date, 1);
        }
        if(isset($service) && $service->isError())
        {
            $this->setError();
            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
        }
    }
}
?>