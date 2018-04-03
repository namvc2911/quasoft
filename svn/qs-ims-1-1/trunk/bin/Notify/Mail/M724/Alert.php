<?php
class Qss_Bin_Notify_Mail_M724_Alert extends Qss_Lib_Notify_Mail
{
	const TYPE = 'SUBSCRIBE';
	
	const TITLE = 'Gửi email bảo trì định kỳ';
    /**
     * Xử lý gửi mail của "Phiếu bảo trì"
     * 1. Gửi mail hàng ngày về phiếu bảo trì tồn chưa được xử lý.
     * 2. Gửi mail hàng ngày về kế hoạch bảo trì trong ngày.
     */
	public function __doExecute()
	{
		// Variable
		$work_orders   = array(); // Danh sách phiếu bảo trì tồn hoặc kế hoạch
		$to_mails      = array(); // Người nhận thư
		$cc_mails      = array(); // Người nhận chuyển tiếp thư
		$users         = $this->_form->getRefUsers(); // Users được gắn vào hệ thống gửi mail
		$content       = array(); // Nội dung thư gửi

		$array_to_work_center = array(); // Danh sách người nhận thư trong tổ bảo trì
		$array_cc_work_center = array(); // Danh sách người nhận chuyển tiếp thư trong tổ bảo trì
		$array_name           = array(); // Danh sách tên tổ bảo trì
		
		$dateCreate = date('Y-m-d');
		$date       = date_create($dateCreate);
		$enddate    = date_create($dateCreate);
		$startfound = false;
		$i          = 0;

		while($i<14)
		{
			if($enddate->format('N') == 1)
			{
				$date       = date_create($enddate->format('Y-m-d'));
				$startfound = true;
			}
			elseif($enddate->format('N') == 7 && $startfound)
			{
				//$enddate = $sdate;
				break;
			}
			$enddate = Qss_Lib_Date::add_date($enddate, 1);
			$i++; 
		}
		$start = $date->format('d-m-Y');
		$end = $enddate->format('d-m-Y');
		//$start = '01-03-2018';
		//$end = '01-03-2018';
        $mStart                = Qss_Lib_Date::displaytomysql($start);
        $mEnd                  = Qss_Lib_Date::displaytomysql($end);
        $mWorkorder            = new Qss_Model_Maintenance_Workorder();
        $nextBack     = 1;
        $nextStart    =$start;
        $nextStart    = Qss_Lib_Date::compareTwoDate($nextStart, $end) == 1?$end:$nextStart;
        $nextStart    = Qss_Lib_Date::compareTwoDate($nextStart, $start) == -1?$start:$nextStart;
        
        $plans     = $this->_addBasicPlan($start, $end, $nextStart, $nextBack);
		
        $isSend = false;
		foreach($plans as $order)
		{
		    if(!isset($content[$order->Ref_DVBT][$order->Date])) 
		    {
		        $content[$order->Ref_DVBT][$order->Date] = '';
		    }
		    
		    $content[$order->Ref_DVBT][$order->Date] .= '<tr>';
		    $content[$order->Ref_DVBT][$order->Date] .= '<td> ' . $order->MaThietBi . ' - '. $order->TenThietBi . '</td>';
		    $content[$order->Ref_DVBT][$order->Date] .= '<td> ' . Qss_Lib_Date::mysqltodisplay($order->Date) . ' </td>';
		    $content[$order->Ref_DVBT][$order->Date] .= '<td> ' . $order->ChuKy . ' </td>';
		    $content[$order->Ref_DVBT][$order->Date] .= '<td> ' . $order->MoTa . ' </td>';
		    $content[$order->Ref_DVBT][$order->Date] .= '<tr>';		    
		}
		
		// Đoạn đầu thư
		$head_content  = '';
		$head_content .= '<table border="1" cellpadding="0" cellspacing="0">';
		$head_content .= '<tr>';
		$head_content .= '<th> Thiết bị </th>';
		$head_content .= '<th> Ngày yêu cầu </th>';
		$head_content .= '<th> Chu kỳ </th>';
		$head_content .= '<th> Mô tả </th>';
		$head_content .= '<tr>';

		// Đoạn cuối thư
		$footer_content  = '';
		$footer_content .= '</table>';
		$footer_content .= '<br/>';
		$footer_content .= 'QS-IMS Mailer<br/>';

		
		// Đơn vị bảo trì
		$work_centers_sql = sprintf('
            SELECT 
                ODonViSanXuat.*,
                ONhanVien.QuanLy, 
                qsusers.EMail,
                qsusers.UserName
            FROM ODonViSanXuat
            INNER JOIN ONhanVien ON ONhanVien.IFID_M125 = ODonViSanXuat.IFID_M125
            INNER JOIN ODanhSachNhanVien ON ODanhSachNhanVien.IOID = ONhanVien.Ref_MaNV
            INNER JOIN qsusers ON qsusers.UID =  ODanhSachNhanVien.Ref_TenTruyCap
            ORDER BY ODonViSanXuat.IFID_M125
	    ');
		$work_centers     = $this->_db->fetchAll($work_centers_sql);	

		// Lấy danh sách mail đơn vị bảo trì
		foreach ($work_centers as $item)
		{
		    if(!isset($arrToDonVi[$item->IOID]))
		    {
		        $array_to_work_center[$item->IOID] = array();
		    }
		    
		    if(!isset($arrCCDonVi[$item->IOID]))
		    {
		        $array_cc_work_center[$item->IOID] = array();
		    }
		    
		    if($item->QuanLy)
		    {
		        $array_cc_work_center[$item->IOID][$item->EMail] = $item->UserName;
		    }
		    else
		    {
		        $array_to_work_center[$item->IOID][$item->EMail] = $item->UserName;
		    }
		    
		    $array_name[$item->IOID] = $item->Ten;
		}	

		foreach ($this->_maillist as $item)
		{
			if($item->EMail)
		    {
		        $to_mails[$item->EMail] = $item->UserName;
		    }
		}	
				
		foreach ($array_name as $key=>$value)
		{
		    $arrTos = array_merge($to_mails , $array_to_work_center[$key]);
		    $arrCcs = array_merge($cc_mails , $array_cc_work_center[$key]);
		    if(isset($content[$key]) && $content[$key] && (count($arrTos) || count($arrCcs)))
		    {
		        $subject = 'Thiết bị cần bảo trì '. $value . ' ' . $start . ' ' . $end;
		        $body = 'Thiết bị cần bảo trì '. $value . ' ' . $start . ' ' . $end;
		        $body .= '<br/><br/>';		        
		        $body .= $head_content;
		        if(isset($content[$key][date('Y-m-d')]))
		        {
		          $body .= '<tr><th colspan="4" style="text-align:left">Thiết bị cần bảo trì hôm nay</th></tr>';
		          $body .= $content[$key][date('Y-m-d')];
		          unset($content[$key][date('Y-m-d')]); // Loại ngày hôm nay
		        }
		        
		        if(count($content))
		        {
		            $body .= '<tr><th colspan="4" style="text-align:left">Thiết bị đến hạn bảo trì</th></tr>';
		            foreach($content[$key] as $val)
		            {
		                $body .= $val;
		            }
		        }
		        unset($content[$key]); // Loại ngày hôm nay
		        //$body .= $content[$key].'<br>';
		        $body .= $footer_content;
		        
		        $this->_sendMail($subject, $arrTos, $body,$arrCcs);
		    }
		}
		if(count($content) && (count($to_mails) || count($cc_mails)))
	    {
	    	$subject = 'Thiết bị cần bảo trì ' . $start . ' ' . $end;
	        $body = 'Thiết bị cần bảo trì ' . $start . ' ' . $end;
	        $body .= '<br/><br/>';		        
	        $body .= $head_content;
	        foreach($content as $desc)
	        {
		        if(isset($desc[date('Y-m-d')]))
		        {
		          $body .= '<tr><th colspan="4" style="text-align:left">Thiết bị cần bảo trì hôm nay</th></tr>';
		          $body .= $desc[date('Y-m-d')];
		          unset($desc[date('Y-m-d')]); // Loại ngày hôm nay
		        }
		        
		        if(count($desc))
		        {
		            $body .= '<tr><th colspan="4" style="text-align:left">Thiết bị đến hạn bảo trì</th></tr>';
		            foreach($desc as $val)
		            {
		                $body .= $val;
		            }
		        }
	        }
			$body .= $footer_content;		        
	        $this->_sendMail($subject, $to_mails, $body,$cc_mails);
	    }		
	}
	private function _addBasicPlan($startDate, $endDate, $nextStart, $nextBack, $locIOID = 0, $group = 0, $equip = 0) 
	{
        $cNextStart = date_create($nextStart);
        $tNextStart = $cNextStart;

        $arrChiSo   = array();
        $retval     = array();
        $limit      = 100;
        $i          = -1;
        $count      = ($nextBack)?0:$limit;

        while (true)
        {
            $date      = $cNextStart->format('Y-m-d');

            if(!Qss_Lib_Date::checkInRangeTime($date, $startDate, $endDate)) // Ra khoi khoang thoi gian
            {
                break;
            }

            $basicplan = Qss_Model_Maintenance_Plan::createInstance();
            $plans 	   = $basicplan->getPlansByDate($date, $locIOID, 0, 0, $group, 0, $equip);

            foreach ($plans as $plan) {
                $plan->NgayKetThuc = ($plan->NgayKetThuc == '0000-00-00')?'':$plan->NgayKetThuc;
                $khuvuc            = (int) $plan->Ref_KhuVuc;
                $thietbi           = (int) $plan->Ref_MaThietBi;
                $bophan            = (int) $plan->Ref_BoPhan;
                $chuky             = (int) $plan->ChuKyIOID;

                if(($plan->CanCu == 1 || $plan->CanCu == 2) && isset($arrChiSo[$khuvuc][$thietbi][$bophan][$chuky])) {

                }
                else {

                    $retval[++$i]     = $plan;
                    $retval[$i]->Date = $date;
                    $count            = ($nextBack)?($count+1):($count-1);
                }

                if($plan->CanCu == 1 || $plan->CanCu == 2) {
                    $arrChiSo[$khuvuc][$thietbi][$bophan][$chuky] = 1;
                }
            }

            if($nextBack) {
                if(count($retval) && $count >= $limit) {
                    $temp = Qss_Lib_Date::add_date($cNextStart, 1);
                    $this->m759_plans_next_start = $temp->format('Y-m-d');
                    break;
                }
            }
            else
            {
                if(count($retval) && $count <= 0) {
                    $temp = Qss_Lib_Date::add_date($tNextStart, 1);
                    $this->m759_plans_next_start = $temp->format('Y-m-d');
                    break;
                }
            }

            if($nextBack) {
                $cNextStart = Qss_Lib_Date::add_date($cNextStart, 1);
            }
            else
            {
                $cNextStart = Qss_Lib_Date::diff_date($cNextStart, 1);
            }

        }

        return $retval;
    }
}
?>