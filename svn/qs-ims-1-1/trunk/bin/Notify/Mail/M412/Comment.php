<?php
class Qss_Bin_Notify_Mail_M412_Comment extends Qss_Lib_Notify_Mail
{
	const TYPE = 'TRIGGER';
	
	const TITLE = 'Gửi email khi có ý kiến phản hồi';
    /**
     * Xử lý gửi mail của "Phiếu bảo trì"
     * 1. Gửi mail hàng ngày về phiếu bảo trì tồn chưa được xử lý.
     * 2. Gửi mail hàng ngày về kế hoạch bảo trì trong ngày.
     */
	public function __doExecute($user,$comment)
	{
        $domain = $_SERVER['HTTP_HOST'];

        $arrToDonVi = array();
        $arrCCDonVi = array();

        foreach ($this->_maillist as $item)
        {
            if($item->EMail)
            {
                $arrToDonVi[$item->EMail] = $item->UserName;
            }
        }


//        $own = Qss_Lib_System::getUserByIFID(@(int)$this->_params->IFID_M412);
//
//        if($own && $own->EMail)
//        {
//            $arrToDonVi[$own->EMail] = $own->UserName;
//        }

        unset($arrToDonVi[$user->user_email]);


        if(count($arrToDonVi) || count($arrCCDonVi))
        {
            $subject = '['. $this->_params->DonViYeuCau .'] Phản hồi '. $this->_params->SoPhieu;
            $body = sprintf('%1$s:  <strong>"%2$s"</strong><br><br>',
                     $user->user_desc,
                     $comment);
            $body .= sprintf('Nội dung: %1$s<br><br>',$this->_params->NoiDung);
            $body .= sprintf('Số phiếu : <a href="http://%2$s/user/form/edit?ifid=%3$d&deptid=%4$d">%1$s</a><br>',
                    $this->_params->SoPhieu,
                    $domain,
                     $this->_params->IFID_M412,
                     1);
            $body .= 'Quasoft CMMS Mailer';
            $this->_sendMail($subject, $arrToDonVi, $body,$arrCCDonVi);
        }
			
	}
}
?>