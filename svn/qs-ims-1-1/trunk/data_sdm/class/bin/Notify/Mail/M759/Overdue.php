<?php
class Qss_Bin_Notify_Mail_M759_Overdue extends Qss_Lib_Notify_Mail
{
	const TYPE = 'SUBSCRIBE';
	
	const TITLE = 'Gửi email các phiếu bảo trì chưa hoàn thành';

	const INACTIVE = true;
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
		
		// Đoạn đầu thư
		$head_content  = '';
		$head_content .= '<table border="1" cellpadding="0" cellspacing="0">';
		$head_content .= '<tr>';
		$head_content .= '<th> Số phiếu </th>';
		$head_content .= '<th> Thiết bị </th>';
		$head_content .= '<th> Ngày yêu cầu </th>';
		$head_content .= '<th> Loại bảo trì </th>';
		$head_content .= '<tr>';

		// Đoạn cuối thư
		$footer_content  = '';
		$footer_content .= '</table>';
		$footer_content .= '<br/>';
		$footer_content .= 'QS-IMS Mailer<br/>';

		// Phiếu bảo trì còn tồn lại
		$not_complete_order_sql = sprintf('
		    /* Lấy các phiếu bảo trì chưa hoàn thành */
            SELECT phieubt.*, group_concat(concat(cv.MoTa,\' \',ifnull(cv.BoPhan,\'\')) SEPARATOR \'\<br>\') as CongViec
		    FROM OPhieuBaoTri AS phieubt
		    INNER JOIN qsiforms ON phieubt.IFID_M759 = qsiforms.IFID
		    LEFT JOIN OCongViecBTPBT as cv on cv.IFID_M759 = phieubt.IFID_M759
		    WHERE qsiforms.Status in (1, 2) /* (Soạn thảo, Đang thực hiện) */
		    GROUP BY phieubt.IOID
		    ORDER BY phieubt.NgayYeuCau DESC
	    ');
		$not_complete_order     = $this->_db->fetchAll($not_complete_order_sql);
		
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
		
		
		/*
		 * Soạn thư 
		 */
		foreach($not_complete_order as $order)
		{
		    if(!isset($content[$order->Ref_MaDVBT][$order->NgayYeuCau])) 
		    {
		        $content[$order->Ref_MaDVBT][$order->NgayYeuCau] = '';
		    }
		    
		    $content[$order->Ref_MaDVBT][$order->NgayYeuCau] .= '<tr>';
		    $content[$order->Ref_MaDVBT][$order->NgayYeuCau] .= '<td> ' . $order->SoPhieu . ' </td>';
		    $content[$order->Ref_MaDVBT][$order->NgayYeuCau] .= '<td> ' . $order->MaThietBi . ' - '. $order->TenThietBi. '<br>' . $order->CongViec . '</td>';
		    $content[$order->Ref_MaDVBT][$order->NgayYeuCau] .= '<td> ' . Qss_Lib_Date::mysqltodisplay($order->NgayYeuCau) . ' </td>';
		    $content[$order->Ref_MaDVBT][$order->NgayYeuCau] .= '<td> ' . $order->LoaiBaoTri . ' </td>';
		    $content[$order->Ref_MaDVBT][$order->NgayYeuCau] .= '<tr>';		    
		}
		/*
		 * Gửi thư
		 */
		foreach ($array_name as $key=>$value)
		{
		    $arrTos = array_merge($to_mails , $array_to_work_center[$key]);
		    $arrCcs = array_merge($cc_mails , $array_cc_work_center[$key]);
		    
		    if(isset($content[$key]) && $content[$key] && (count($arrTos) || count($arrCcs)))
		    {
		        $subject = 'Phiếu bảo trì chưa hoàn thành "'. $value . '" ' . date('d-m-Y');
		        $body = 'Phiếu bảo trì chưa hoàn thành "'. $value . '" ' . date('d-m-Y');
		        $body .= '<br/><br/>';		        
		        $body .= $head_content;
		        if(isset($content[$key][date('Y-m-d')]))
		        {
		          $body .= '<tr><th colspan="4" style="text-align:left">Phiếu bảo trì hôm nay cần xử lý</th></tr>';
		          $body .= $content[$key][date('Y-m-d')];
		          unset($content[$key][date('Y-m-d')]); // Loại ngày hôm nay
		        }
		        
		        if(count($content))
		        {
		            $body .= '<tr><th colspan="4" style="text-align:left">Phiếu bảo trì chưa xử lý</th></tr>';
		            foreach($content[$key] as $val)
		            {
		                $body .= $val;
		            }
		        }
		        //$body .= $content[$key].'<br>';
		        $body .= $footer_content;
		        $this->_sendMail($subject, $arrTos, $body,$arrCcs);
		    }
		}		
	}
}
?>