<?php
Class Qss_Service_Extra_Maintenance_Workorders_Save extends  Qss_Service_Abstract
{
    /*
     * @Des:
     * - Nếu có dòng yêu cầu cần cập nhật thì thực hiện cập nhật
     * - Tạo mảng cập nhật bao gồm đối tượng chính, vật tư, công việc bảo trì
     * , cài đặt.
     * - Cập nhật thành công chuyển trạng thái của yêu cầu bảo trì tương ứng
     * thành đang xử lý (Step 2)
     */
    public function __doExecute($params)
    {
        // Trong hoa phat doi tuong vattu dung chung giua pbt m759 va ke hoach m724
        if(isset($params['ifid']))
        {
            $common = new Qss_Model_Extra_Extra();
            $model  = new Qss_Model_Extra_Maintenance();
            $data   = array();
            $i      = 0;
            $shifts  =  $common->getTable(array('*'), 'OCa', array(), array(), 'NO_LIMIT');


            // init
            $materialsArr = array();
            $taskArr      = array();
            //$installArr   = array();
            $techParamArr = array();
            //$oldLocArr    = array();
            $shiftArr     = array();


            // Get data
            $where          = sprintf(' IFID_M724 in (%1$s)', implode(',', $params['ifid']));
            $whereOldLoc    = sprintf(' IOID in (%1$s)', implode(',', $params['refEq']));
            $materialsObj   = $common->getTable(array('*'), 'OVatTu', $where, array(), 'NO_LIMIT');
            $taskObj        = $common->getTable(array('*'), 'OCongViecBT', $where, array(), 'NO_LIMIT');
            //$installObj     = $common->getTable(array('*'), 'OCaiDatKHBT', $where, array('STT'), 'NO_LIMIT');
            $techParamObj   = $common->getTable(array('*'), 'ODichVuBT', $where, array(), 'NO_LIMIT');
            //$oldLocObj      = $common->getTable(array('IOID,MaKhuVuc'), 'ODanhSachThietBi', $whereOldLoc, array(), 'NO_LIMIT');


            foreach($shifts as $s)
            {
                $shiftArr[$s->MaCa] = $s;
            }



            // Attach array
            $materialsObjIndex = 0;
            foreach ($materialsObj as $item)
            {
                $materialsArr[$item->IFID_M724][$materialsObjIndex] = $item;
                $materialsObjIndex++;
            }

            $taskObjIndex = 0;
            foreach ($taskObj as $item)
            {
                $tasksArr[$item->IFID_M724][$taskObjIndex] = $item;
                $taskObjIndex++;
            }

            /* $installObjIndex = 0;
             foreach ($installObj as $item)
             {
                 $installArr[$item->IFID_M724][$installObjIndex] = $item;
                 $installObjIndex++;
             }*/

            $techParamObjIndex = 0;
            foreach ($techParamObj as $item)
            {
                $techParamArr[$item->IFID_M724][$techParamObjIndex] = $item;
                $techParamObjIndex++;
            }

            /*foreach ($oldLocObj as $item)
            {
                $oldLocArr[$item->IOID] = $item;
            }*/
            //check create from sheduling
            $viewtype = @$params['viewtype'];
            // Attach and sort insert data
            foreach ($params['ifid'] as $ifid)
            {
                $data = array();
                $beginShift = (isset($shiftArr[$params['shift'][$i]])
                    && $shiftArr[$params['shift'][$i]])
                    ?$shiftArr[$params['shift'][$i]]->GioBatDau:'';
                $data['OPhieuBaoTri'][0]['NgayYeuCau']  = $params['dateFilter'][$i];

                $ngaybatdau = $params['start'][$i];
                $ngaydukienhoanthanh = $params['end'][$i];
                if($viewtype)
                {
                    $start = date_create($ngaybatdau);
                    $end = date_create($ngaydukienhoanthanh);
                    $numofday = Qss_Lib_Date::divDate( $ngaybatdau,$ngaydukienhoanthanh);
                    $week = $params['week'];
                    $month = $params['month'];
                    $year = $params['year'];
                    switch ($viewtype)
                    {
                        case 'week':
                            $startdate = Qss_Lib_Date::getDateByWeek($week,$year);
                            if($week != $start->format('W'))//change start date
                            {
                                if(Qss_Lib_Date::compareTwoDate($ngaybatdau,$startdate->format('Y-m-d')) == 1)
                                {
                                    $numofweek = -Qss_Lib_Date::divDate($startdate->format('Y-m-d'),$ngaybatdau,'W');
                                }
                                else
                                {
                                    $numofweek = Qss_Lib_Date::divDate($ngaybatdau,$startdate->format('Y-m-d'),'W');
                                }
                                $start = Qss_Lib_Date::add_date($start,$numofweek,'WEEK');
                                $end = Qss_Lib_Date::add_date($start,$numofday,'DAY');
                                $ngaybatdau = $start->format('Y-m-d');
                                $ngaydukienhoanthanh = $end->format('Y-m-d');
                            }
                            break;
                        case 'month':
                            $startdate = date_create(sprintf('%3$s-%2$s-%1$s','01',str_pad($month, 2, '0', STR_PAD_LEFT),$year));
                            //echo $start->format('m');die;
                            if($month != $start->format('m') || $year != $start->format('Y'))//change start date
                            {
                                if(Qss_Lib_Date::compareTwoDate($ngaybatdau,$startdate->format('Y-m-d')) == 1)
                                {
                                    $numofmonth = - Qss_Lib_Date::divDate($startdate->format('Y-m-d'),$ngaybatdau,'m');
                                }
                                else
                                {
                                    $numofmonth = Qss_Lib_Date::divDate($ngaybatdau,$startdate->format('Y-m-d'),'m');
                                }
                                $start = Qss_Lib_Date::add_date($start,$numofmonth,'month');
                                $end = Qss_Lib_Date::add_date($start,$numofday,'DAY');
                                $ngaybatdau = $start->format('Y-m-d');
                                $ngaydukienhoanthanh = $end->format('Y-m-d');
                            }
                            break;
                    }
                }

                $data['OPhieuBaoTri'][0]['NgayBatDau']  = $ngaybatdau;

                if(Qss_Lib_System::fieldActive('OPhieuBaoTri','NgayDuKienHoanThanh'))
                {
                    $data['OPhieuBaoTri'][0]['NgayDuKienHoanThanh'] = $ngaydukienhoanthanh;
                }

                $data['OPhieuBaoTri'][0]['Ngay'] = $ngaydukienhoanthanh;

                //$data['OPhieuBaoTri'][0]['Ngay'] = $params['end'][$i];
                $data['OPhieuBaoTri'][0]['Ca']          = $params['shift'][$i];
                $data['OPhieuBaoTri'][0]['MaThietBi']   = $params['eqCode'][$i];
                $data['OPhieuBaoTri'][0]['MaThietBi']   = $params['eqCode'][$i];
                $data['OPhieuBaoTri'][0]['BoPhan']   = $params['component'][$i];
                $data['OPhieuBaoTri'][0]['MaDVBT']      = $params['workCenterFilter'][$i];
                $data['OPhieuBaoTri'][0]['MucDoUuTien'] = $params['priority'][$i];
                $data['OPhieuBaoTri'][0]['LoaiBaoTri']  = $params['type'][$i];
                $data['OPhieuBaoTri'][0]['ChuKy']  = (int) $params['chuky'][$i];
                $data['OPhieuBaoTri'][0]['GhiVaoLyLich']  = 1;
                //$data['OPhieuBaoTri'][0]['ioidlink']    = $params['ioid'][$i];

                $materials   = isset($materialsArr[$ifid])?$materialsArr[$ifid]:array();
                $task        = isset($tasksArr[$ifid])?$tasksArr[$ifid]:array();
                //$install     = isset($installArr[$ifid])?$installArr[$ifid]:array();
                $techParam   = isset($techParamArr[$ifid])?$techParamArr[$ifid]:array();
                //$oldLoc      = isset($oldLocArr[$ifid])?$oldLocArr[$ifid]:array();
                //$oldLoc      = $oldLoc?@(string)$oldLoc->MaKhuVuc:'';
                $i++;


                // OCongViecBT
                $o = 0;
                foreach ($task as $ta)
                {//Ten , BoPhan, ViTri, ThoiGian

                    $data['OCongViecBTPBT'][$o]['ViTri']  = $ta->ViTri;
                    $data['OCongViecBTPBT'][$o]['BoPhan'] = $ta->BoPhan;
                    $data['OCongViecBTPBT'][$o]['Ten']    = $ta->Ten;
                    $data['OCongViecBTPBT'][$o]['ThoiGian']  = $ta->ThoiGian;
                    $data['OCongViecBTPBT'][$o]['ThoiGianDuKien']  = $ta->ThoiGian;
                    $data['OCongViecBTPBT'][$o]['NhanCong']  = $ta->NhanCong;
                    $data['OCongViecBTPBT'][$o]['NhanCongDuKien']  = $ta->NhanCong;
                    $data['OCongViecBTPBT'][$o]['MoTa']   = $ta->MoTa;

                    $data['OCongViecBTPBT'][$o]['ThucHien']  = 1;
                    $data['OCongViecBTPBT'][$o]['DanhGia']   = 1;

                    if(Qss_Lib_System::fieldActive('OCongViecBT', 'NguoiThucHien'))
                    {
                        $data['OCongViecBTPBT'][$o]['NguoiThucHien'] = $ta->NguoiThucHien;
                    }

                    $o++;
                }

                // OVatTu
                $n = 0;
                foreach ($materials as $m)
                {
                    if(Qss_Lib_System::fieldActive('OVatTuPBT', 'BoPhan')
                        && Qss_Lib_System::fieldActive('OVatTu', 'BoPhan')
                    )
                    {
                        $data['OVatTuPBT'][$n]['BoPhan']   = $m->BoPhan;
                    }
                    if(Qss_Lib_System::fieldActive('OVatTuPBT', 'ViTri')
                        && Qss_Lib_System::fieldActive('OVatTu', 'ViTri')
                    )
                    {
                        $data['OVatTuPBT'][$n]['ViTri']   = $m->ViTri;
                    }
                    $data['OVatTuPBT'][$n]['MaVatTu']   = $m->MaVatTu;
                    $data['OVatTuPBT'][$n]['DonViTinh'] = $m->DonViTinh;
                    $data['OVatTuPBT'][$n]['ThuocTinh'] = @$m->ThuocTinh;
                    $data['OVatTuPBT'][$n]['SoLuong']   = $m->SoLuong;
                    $data['OVatTuPBT'][$n]['SoLuongDuKien']   = $m->SoLuong;
                    $data['OVatTuPBT'][$n]['CongViec']   = $m->CongViec;
                    //$data['OVatTuPBT'][$n]['GiaTri']    = $m->GiaTri/1000;
                    $n++;
                }

                /*$o = 0;
                foreach ($install as $ta)
                {
                    $data['OCaiDatDiChuyen'][$o]['Ngay']         = $params['dateFilter'];//@FIXING
                    $data['OCaiDatDiChuyen'][$o]['MaKVMoi']      = $ta->KhuVuc;
                    $data['OCaiDatDiChuyen'][$o]['LichLamViec']  = $ta->LichLamViec;
                    $data['OCaiDatDiChuyen'][$o]['GhiChu']       = $ta->GhiChu;

                    if($beginShift)
                    {
                        $data['OCaiDatDiChuyen'][$o]['ThoiGian']     = $beginShift;
                    }

//                    if($oldLoc || isset($olderLoc))
//                    {
//                        $data['OCaiDatDiChuyen'][$o]['MaKVHienTai']  = isset($olderLoc)?$olderLoc:$oldLoc;
//                    }
                    $olderLoc = $ta->KhuVuc;
                    $o++;
                }
                */

                $o = 0;
                foreach ($techParam as $ta)
                {
                    $firstTime = $beginShift;
                    for($interval = 0; $interval < $ta->SoLanKiemTra; $interval++)
                    {
                        $data['ODichVuPBT'][$o]['MaNCC']  = $ta->MaNCC;
                        $data['ODichVuPBT'][$o]['DichVu']    = $ta->DichVu;
                        $data['ODichVuPBT'][$o]['ChiPhiDuKien'] = $ta->ChiPhi;
                        $data['ODichVuPBT'][$o]['ChiPhi']   = $ta->ChiPhi;
                        $data['ODichVuPBT'][$o]['GhiChu']   = $ta->GhiChu;
                        $o++;
                    }
                }
                //echo '<pre>'; print_r($data); die;
                $service = $this->services->Form->Manual('M759',0,$data,false);


                if($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }



//                else
//                {
//                    $common->setStatus($params['rifid'], 2);
//                }
            }
            //die;

            if(!$this->isError())
            {
                //$this->setRedirect('/user/form?fid='.$params['fid']);
                //$this->setMessage('Cập nhật thành công!');
            }
        }
        else
        {
            $this->setError();
            $this->setMessage('No notification was selected!');
        }
    }
}