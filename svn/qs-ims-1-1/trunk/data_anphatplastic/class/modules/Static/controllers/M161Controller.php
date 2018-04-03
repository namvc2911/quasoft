<?php
class Static_M161Controller extends Qss_Lib_Controller
{
    public function init()
    {
        $this->layout     = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/report.php';
    	parent::init();
        $this->headScript($this->params->requests->getBasePath() . '/js/report-list.js');
        $this->headScript($this->params->requests->getBasePath() . '/js/form-edit.js');
    }

    public function indexAction()
    {

    }

    public function showAction()
    {
        $mOrderTasks  = new Qss_Model_Maintenance_Workorder_Tasks();
        $mCommon      = new Qss_Model_Extra_Extra();
        $retval       = array();
//        $ix           = 0;

        $start        = $this->params->requests->getParam('start', '');
        $end          = $this->params->requests->getParam('end', '');
        $employeeIOID = $this->params->requests->getParam('employee', 0);
        $equipIOID    = $this->params->requests->getParam('equipment', 0);
        $shiftIOID    = $this->params->requests->getParam('shift', 0);

        $oldIFID      = '';
        $kl           = 0;
        $tempData     = array();
        $minMaxByDate = array();
        $timeArr      = array();
        $oShift       = $mCommon->getTableFetchOne('OCa', array('IOID'=>$shiftIOID));
        $oEmployee    = $mCommon->getTableFetchOne('ODanhSachNhanVien', array('IOID'=>$employeeIOID));
        $data         = $mOrderTasks->getTasks(
            Qss_Lib_Date::displaytomysql($start)
            , Qss_Lib_Date::displaytomysql($end)
            , $employeeIOID
            , $equipIOID
            , $shiftIOID
        );


        foreach ($data as $key=>$item)
        {
            if($oldIFID != $item->IFID_M759) // Ghi lai mot phieu bao tri
            {
                Qss_Lib_Util::copyObject($item, $tempData[$kl]);
                $tempData[$kl]->RefCongViec = 0;
                $kl++;
            }

            // Ghi lai cong viec trong phieu bao tri
            if($item->RefCongViec)
            {
                $tempData[$kl++] = $item;
            }

            $oldIFID = $item->IFID_M759;
        }

        // echo '<pre>'; print_r($tempData); die;
        // echo '<pre>'; print_r($data); die;

        foreach($tempData as $item)
        {
            if($item->RefCongViec) // Doi voi cong viec con
            {
                // Phieu xay ra theo ngay 1, nhieu nhat la 2 ngay
                $item->ThoiGianBatDauDuKienCongViec = (!$item->ThoiGianBatDauDuKienCongViec || $item->ThoiGianBatDauDuKienCongViec  == '00:00:00')?'00:00':$item->ThoiGianBatDauDuKienCongViec;
                $item->ThoiGianKetThucDuKienCongViec = (!$item->ThoiGianKetThucDuKienCongViec || $item->ThoiGianKetThucDuKienCongViec  == '00:00:00')?'24:00':$item->ThoiGianKetThucDuKienCongViec;

                $endDateOfTask    = $item->NgayDuKien;
                $startTimeOfTask  = $item->NgayDuKien . ' ' . $item->ThoiGianBatDauDuKienCongViec;
                $endTimeOfTask    = $item->NgayDuKien . ' ' . $item->ThoiGianKetThucDuKienCongViec;

                // Neu gio ket thuc nho hon 5 gio sang thi tuc la no da chuyen sang mot ngay moi
                if((Qss_Lib_Date::compareTwoDate($startTimeOfTask, $endTimeOfTask) == 1
                        || (Qss_Lib_Date::compareTwoDate($startTimeOfTask, "{$item->NgayDuKien} 24:00:00") == -1
                            && Qss_Lib_Date::compareTwoDate($endTimeOfTask, "{$item->NgayDuKien} 05:00:00") == -1))
                    && $item->ThoiGianBatDauDuKienCongViec != $item->ThoiGianKetThucDuKienCongViec
                )
                {
                    $endDateOfTask =  date('Y-m-d', strtotime($item->NgayDuKien . " +1 days"));
                }

                $endTimeOfTask    = $endDateOfTask . ' ' . $item->ThoiGianKetThucDuKienCongViec;
                $microStart       = strtotime($startTimeOfTask);
                $microEnd         = strtotime($endTimeOfTask);

                // echo '<pre>'; print_r($startTimeOfTask. ' - ' . $endTimeOfTask); echo '<br/>';

                if(!isset($minMaxByDate[$item->NgayDuKien]['MIN'])
                    || ($minMaxByDate[$item->NgayDuKien]['MIN'] == 0 || $minMaxByDate[$item->NgayDuKien]['MIN'] > $microStart))
                {
                    $minMaxByDate[$item->NgayDuKien]['MIN'] = $microStart;
                }

                if(!isset($minMaxByDate[$item->NgayDuKien]['MAX'])
                    || ($minMaxByDate[$item->NgayDuKien]['MAX'] == 0 || $minMaxByDate[$item->NgayDuKien]['MAX'] < $microEnd))
                {
                    $minMaxByDate[$item->NgayDuKien]['MAX'] = $microEnd;
                }

            }
            else // Doi voi phieu
            {
                if($item->NgayBatDauDuKienPhieu && $item->NgayHoanThanhDuKienPhieu)
                {
                    $tempStart1 = date_create($item->NgayBatDauDuKienPhieu);
                    $tempEnd1   = date_create($item->NgayHoanThanhDuKienPhieu);

                    while ($tempStart1 <= $tempEnd1)
                    {
                        $tempStartTime = '00:00';
                        $tempEndTime   = '24:00';

                        if($tempStart1->format('Y-m-d') == $item->NgayBatDauDuKienPhieu
                            && $item->ThoiGianBatDauDuKienPhieu)
                        {
                            $tempStartTime = $item->ThoiGianBatDauDuKienPhieu;
                        }

                        if($tempStart1->format('Y-m-d') == $item->NgayHoanThanhDuKienPhieu
                            && $item->ThoiGianKetThucDuKienPhieu)
                        {
                            $item->ThoiGianKetThucDuKienPhieu = $item->ThoiGianKetThucDuKienPhieu == '00:00:00'?'24:00':$item->ThoiGianKetThucDuKienPhieu;
                            $tempEndTime = $item->ThoiGianKetThucDuKienPhieu;
                        }


                        $startTimeOfTask  = $tempStart1->format('Y-m-d') . ' ' . $tempStartTime;
                        $endTimeOfTask    = $tempStart1->format('Y-m-d') . ' ' . $tempEndTime;

                        $microStart       = strtotime($startTimeOfTask);
                        $microEnd         = strtotime($endTimeOfTask);

                        if(!isset($minMaxByDate[$tempStart1->format('Y-m-d')]['MIN'])
                            || ($minMaxByDate[$tempStart1->format('Y-m-d')]['MIN'] == 0 || $minMaxByDate[$tempStart1->format('Y-m-d')]['MIN'] > $microStart))
                        {
                            $minMaxByDate[$tempStart1->format('Y-m-d')]['MIN'] = $microStart;
                        }

                        if(!isset($minMaxByDate[$tempStart1->format('Y-m-d')]['MAX'])
                            || ($minMaxByDate[$tempStart1->format('Y-m-d')]['MAX'] == 0 || $minMaxByDate[$tempStart1->format('Y-m-d')]['MAX'] < $microEnd))
                        {
                            $minMaxByDate[$tempStart1->format('Y-m-d')]['MAX'] = $microEnd;
                        }

                        $tempStart1 = Qss_Lib_Date::add_date($tempStart1, 1);
                    }
                }

            }
        }

        // echo '<Pre>'; print_r($minMaxByDate); die;

        foreach ($minMaxByDate as $date=>$minMax)
        {
            // echo '<Pre>'; print_r(date('H:i', $minMax['MAX'])); die;
            $tempStart = $minMax['MIN'];

            while (strtotime("+2 hours", $tempStart) <= $minMax['MAX'])
            {
                if(isset($tempEnd))
                {
                    $tempStart = $tempEnd;
                }

                $tempEnd = strtotime("+2 hours", $tempStart);

                if($tempStart <  $minMax['MAX'])
                {
                    $timeArr[$date][] = array(
                        'Start'=>$tempStart
                    , 'End'=>$tempEnd
                    , 'Display'=> date('H:i', $tempStart) . ' - ' . date('H:i', $tempEnd)
                    , 'Data'=> array()
                    , 'Count'=> 0
                    );
                }
            }
        }

        // echo '<Pre>'; print_r($timeArr); die;

        // echo '<Pre>'; print_r($timeArr); die;
        foreach ($tempData as $item)
        {
            if($item->RefCongViec) // Cong viec con
            {
                $item->ThoiGianBatDauDuKienCongViec = (!$item->ThoiGianBatDauDuKienCongViec || $item->ThoiGianBatDauDuKienCongViec  == '00:00:00')?'00:00':$item->ThoiGianBatDauDuKienCongViec;
                $item->ThoiGianKetThucDuKienCongViec = (!$item->ThoiGianKetThucDuKienCongViec || $item->ThoiGianKetThucDuKienCongViec  == '00:00:00')?'24:00':$item->ThoiGianKetThucDuKienCongViec;

                $endDateOfTask    = $item->NgayDuKien;
                $startTimeOfTask  = $item->NgayDuKien . ' ' . $item->ThoiGianBatDauDuKienCongViec;
                $endTimeOfTask    = $item->NgayDuKien . ' ' . $item->ThoiGianKetThucDuKienCongViec;

                // Neu gio ket thuc nho hon 5 gio sang thi tuc la no da chuyen sang mot ngay moi
                if((Qss_Lib_Date::compareTwoDate($startTimeOfTask, $endTimeOfTask) == 1
                        || (Qss_Lib_Date::compareTwoDate($startTimeOfTask, "{$item->NgayDuKien} 24:00:00") == -1
                            && Qss_Lib_Date::compareTwoDate($endTimeOfTask, "{$item->NgayDuKien} 05:00:00") == -1))
                    && $item->ThoiGianBatDauDuKienCongViec != $item->ThoiGianKetThucDuKienCongViec
                )
                    $endTimeOfTask    = $endDateOfTask . ' ' . $item->ThoiGianKetThucDuKienCongViec;
                $microStart       = strtotime($startTimeOfTask);
                $microEnd         = strtotime($endTimeOfTask);

                foreach ($timeArr[$item->NgayDuKien] as $key=>$time)
                {
                    if($microStart <= $time['End'] && $microEnd >= $time['Start'])
                    {
                        $timeArr[$item->NgayDuKien][$key]['Data'][] = $item;
                        $timeArr[$item->NgayDuKien][$key]['Count']++;
                    }
                }
            }
            else // phieu bao tri
            {
                if($item->NgayBatDauDuKienPhieu && $item->NgayHoanThanhDuKienPhieu)
                {
                    $tempStart1 = date_create($item->NgayBatDauDuKienPhieu);
                    $tempEnd1   = date_create($item->NgayHoanThanhDuKienPhieu);

                    while ($tempStart1 <= $tempEnd1)
                    {
                        $tempStartTime = '00:00';
                        $tempEndTime   = '24:00';

                        if($tempStart1->format('Y-m-d') == $item->NgayBatDauDuKienPhieu
                            && $item->ThoiGianBatDauDuKienPhieu)
                        {
                            $tempStartTime = $item->ThoiGianBatDauDuKienPhieu;
                        }

                        if($tempStart1->format('Y-m-d') == $item->NgayHoanThanhDuKienPhieu
                            && $item->ThoiGianKetThucDuKienPhieu)
                        {
                            $item->ThoiGianKetThucDuKienPhieu = $item->ThoiGianKetThucDuKienPhieu == '00:00:00'?'24:00':$item->ThoiGianKetThucDuKienPhieu;
                            $tempEndTime = $item->ThoiGianKetThucDuKienPhieu;
                        }

                        $startTimeOfTask  = $tempStart1->format('Y-m-d') . ' ' . $tempStartTime;
                        $endTimeOfTask    = $tempStart1->format('Y-m-d') . ' ' . $tempEndTime;

                        $microStart       = strtotime($startTimeOfTask);
                        $microEnd         = strtotime($endTimeOfTask);

                        foreach ($timeArr[$tempStart1->format('Y-m-d')] as $key=>$time)
                        {

                            if(Qss_Lib_Date::checkInRangeTime($tempStart1->format('Y-m-d'), Qss_Lib_Date::displaytomysql($start)
                                , Qss_Lib_Date::displaytomysql($end)))
                            {
                                if($microStart <= $time['End'] && $microEnd >= $time['Start'])
                                {
                                    $timeArr[$tempStart1->format('Y-m-d')][$key]['Data'][] = $item;
                                    $timeArr[$tempStart1->format('Y-m-d')][$key]['Count']++;
                                }
                            }

                        }

                        $tempStart1 = Qss_Lib_Date::add_date($tempStart1, 1);
                    }

                }
            }

        }

//        echo '<Pre>'; print_r($timeArr); die;
//
//        foreach($data as $item)
//        {
//            $item->RefCongViec = (int)$item->RefCongViec;
//
//            if($item->ThoiGianBatDauDuKien && $item->ThoiGianKetThucDuKien)
//            {
//                $item->NgayKetThuc = $item->NgayBatDauDuKien;
//
//                $startTask     = $item->NgayBatDauDuKien  . ' ' . $item->ThoiGianBatDauDuKien;
//                $startTaskTemp = $item->NgayBatDauDuKien . ' 24:00:00';
//                $endTask       = $item->NgayKetThuc . ' ' . $item->ThoiGianKetThucDuKien;
//                $endTaskTemp   = $item->NgayKetThuc . ' 05:00:00';
//
//                // Neu gio ket thuc nho hon 5 gio sang thi tuc la no da chuyen sang mot ngay moi
//                if(Qss_Lib_Date::compareTwoDate($startTask, $endTask) == 1
//                    || (Qss_Lib_Date::compareTwoDate($startTask, $startTaskTemp) == -1 && Qss_Lib_Date::compareTwoDate($endTask, $endTaskTemp) == -1))
//                {
//                    $item->NgayKetThuc =  date('Y-m-d', strtotime($item->NgayBatDauDuKien . " +1 days"));
//                }
//
//                // Dem so dong cong them cho moi cong viec theo gio voi 2 tieng la 1 dong
//                $endTask    = $item->NgayKetThuc . ' ' . $item->ThoiGianKetThucDuKien;
//                $time       = Qss_Lib_Date::diffTime($startTask, $endTask, 'H');
//                $addRow     = ceil($time/2); // 2 gio tach ra thanh mot dong
//
//                for ($ik = 1; $ik <= $addRow; $ik++)
//                {
//                    $thoiGianLamViec  = ($start != $end)?Qss_Lib_Date::mysqltodisplay($item->NgayBatDauDuKien).'<br/>':"";
//
//                    if($ik == 1)
//                    {
//                        $ThoiGianBatDauDuKien  = date('H:i', strtotime($item->ThoiGianBatDauDuKien));
//                    }
//                    else
//                    {
//                        // $ThoiGianBatDauDuKien = date('H:i', strtotime("+2 hours", strtotime($item->ThoiGianBatDauDuKien)));
//                        if(isset($ThoiGianKetThucDuKien) && $ThoiGianKetThucDuKien)
//                        {
//                            $ThoiGianBatDauDuKien  = $ThoiGianKetThucDuKien;
//                            $ThoiGianKetThucDuKien =  '';
//                        }
//                    }
//
//                    if($ik == $addRow)
//                    {
//                        $ThoiGianKetThucDuKien = date('H:i', strtotime($item->ThoiGianKetThucDuKien));
//                    }
//                    else
//                    {
//                        $ThoiGianKetThucDuKien = date('H:i', strtotime("+2 hours", strtotime($ThoiGianBatDauDuKien)));
//                    }
//
//                    $thoiGianLamViec .= $ThoiGianBatDauDuKien . ' - '. $ThoiGianKetThucDuKien;
//
//                    // echo $thoiGianLamViec; echo '<br/>';
//
//
//                    $retval[++$ix]                  = (array)$item;
//                    $retval[$ix]['ThoiGianLamViec'] = $thoiGianLamViec;
//                    $retval[$ix]['KeyThoiGian']     = strtotime($item->NgayBatDauDuKien.' '.$ThoiGianBatDauDuKien);
//
////                    echo $ix. '/'. $item->NgayBatDauDuKien.' '.$ThoiGianBatDauDuKien.' '.$ThoiGianKetThucDuKien. ' --- '
////                        .$retval[$ix]->ThoiGianLamViec. '<br/>';
//                }
//            }
//            else
//            {
//                $retval[++$ix] = (array)$item;
//                $retval[$ix]['ThoiGianLamViec'] = '';
//                $retval[$ix]['KeyThoiGian']     = 0;
//            }
//        }
//
//        usort($retval, function($a, $b) {
//            return $a['KeyThoiGian'] - $b['KeyThoiGian'];
//        });

        $this->html->start        = $start;
        $this->html->end          = $end;
        $this->html->shift        = $oShift;
        $this->html->employee     = $oEmployee;
        $this->html->report       = $timeArr;
        $this->html->many         = ($start != $end)?true:false;

    }
}
