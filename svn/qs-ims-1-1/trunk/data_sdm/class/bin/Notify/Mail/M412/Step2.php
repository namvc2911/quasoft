<?php
class Qss_Bin_Notify_Mail_M412_Step2 extends Qss_Lib_Notify_Mail
{
	const TITLE ='Gửi email khi gửi kỹ thuật duyệt';
	
	const TYPE ='TRIGGER';

    const INACTIVE = true;
	public function __doExecute($user,$status,$comment)
	{

        $sql = sprintf('
				   SELECT
				    ncmh.*
				   FROM OYeuCauMuaSam AS ncmh
				   WHERE ncmh.IFID_M412 = \'%1$d\'
				  ', $this->_form->i_IFID);
        $dataSQL = $this->_db->fetchOne($sql);

		//khi nhận hàng gửi cho người yêu cầu
		$toMails = array();
		$ccMails = array();
		
		$domain   = $_SERVER['HTTP_HOST'];
		$phieuyc  = '';
		$phieuyc .= "<p>Phiếu yêu cầu vật tư <strong>";
        $phieuyc .= "<a href=\"http://{$domain}/user/form/edit?ifid={$dataSQL->IFID_M412}&deptid={$dataSQL->DeptID}\">{$dataSQL->SoPhieu} </a></strong> </p>";

		foreach ($this->_maillist as $item)
		{
		    if($item->EMail)
		    {
		        $toMails[$item->EMail] = $item->UserName;
		    }
		}


//        $own = Qss_Lib_System::getUserByIFID(@(int)$this->_params->IFID_M412);
//
//        if($own && $own->EMail)
//        {
//            $toMails[$own->EMail] = $own->UserName;
//        }

        unset($toMails[$user->user_email]);


		if(count($toMails))
		{
			$body = sprintf('Chào bạn!
				 <p>%1$s vừa cập nhật tình trạng <strong>%2$s</strong> %3$s</p>
				 <strong>"%5$s"</strong>',
				 $user->user_desc,
				 $status,
				 $this->_form->sz_Name, 
				 $comment,
				 $phieuyc);

            $fieldNgayYeuCauActive = Qss_Lib_System::fieldActive('ODSYeuCauMuaSam', 'NgayYeuCau');
            $fieldNgayCanCoActive = Qss_Lib_System::fieldActive('ODSYeuCauMuaSam', 'NgayCanCo');
            $fieldMaKhoActive = Qss_Lib_System::fieldActive('ODSYeuCauMuaSam', 'MaKho');
            $fieldMucDichActive = Qss_Lib_System::fieldActive('ODSYeuCauMuaSam', 'MucDich');
            $fieldNCCActive = Qss_Lib_System::fieldActive('ODSYeuCauMuaSam', 'NCC');

            $body .= '<br/>';
            $body .= '<br/>';
            $body .= '<table cellpadding="0" cellspacing="0" border="1">';
            $body .= '<tr style="background:gray;">';
            if($fieldNgayYeuCauActive) { $body .= '<th>Ngày yêu cầu </th>'; }
            if($fieldNgayCanCoActive) { $body .= '<th>Ngày cần có</th>';}
            $body .= '<th>Mã MH </th>';
            $body .= '<th>Tên MH</th>';
            $body .= '<th>ĐVT</th>';
            $body .= '<th>Số lượng</th>';
            if($fieldMaKhoActive) { $body .= '<th>Địa điểm giao</th>';}
            if($fieldMucDichActive) { $body .= '<th>Mục đích</th>';}
            if($fieldNCCActive) { $body .= '<th>Nhà CC</th>';}
            $body .= '</tr>';

            foreach ($this->_params->ODSYeuCauMuaSam as $item)
            {
                $body .= '<tr>';
                if($fieldNgayYeuCauActive) { $body .= '<td style="text-align: center">'.Qss_Lib_Date::mysqltodisplay(@$item->NgayYeuCau).'</td>'; }
                if($fieldNgayCanCoActive) {$body .= '<td style="text-align: center">'.Qss_Lib_Date::mysqltodisplay(@$item->NgayCanCo).'</td>';}
                $body .= '<td style="text-align: left">'.$item->MaSP.'</td>';
                $body .= '<td style="text-align: left">'.$item->TenSP.'</td>';
                $body .= '<td style="text-align: left">'.$item->DonViTinh.'</td>';
                $body .= '<td style="text-align: right">'.Qss_Lib_Util::formatNumber($item->SoLuong).'</td>';
                if($fieldMaKhoActive) { $body .= '<td style="text-align: left">'.@$item->MaKho.'</td>';}
                if($fieldMucDichActive) { $body .= '<td style="text-align: left">'.@$item->MucDich.'</td>';}
                if($fieldNCCActive) { $body .= '<td style="text-align: left">'.@$item->NCC.'</td>';}
                $body .= '</tr>';
            }
            $body .= '</table>';
            $body .= '<br/>';
            $body .= '<br/>';

            $subject = '['.$this->_form->FormCode.'] '. $this->_step->szStepName . ' ' . $this->_params->SoPhieu;
			$this->_sendMail($subject, $toMails, $body,$ccMails);
		}
	}
}
?>