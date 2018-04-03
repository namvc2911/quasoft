<?php
class Qss_Bin_Notify_Mail_M506_Step5 extends Qss_Lib_Notify_Mail
{
	const TITLE ='Gửi email khi gửi đề nghị xuất kho được duyệt';
	
	const TYPE ='TRIGGER';

    const INACTIVE = true;
	
	public function __doExecute($user,$status,$comment)
	{

        $sql = sprintf('
				   SELECT
				    ncmh.*
				   FROM OXuatKho AS ncmh
				   WHERE ncmh.IFID_M506 = %1$d', $this->_form->i_IFID);
        $dataSQL = $this->_db->fetchOne($sql);

		//khi nhận hàng gửi cho người yêu cầu
		$toMails = array();
		$ccMails = array();
		
		$domain   = $_SERVER['HTTP_HOST'];
		$phieuyc  = '';
		$phieuyc .= "<p>Phiếu xuất kho vật tư được phê duyệt<strong>";
        $phieuyc .= "<a href=\"http://{$domain}/user/form/edit?ifid={$dataSQL->IFID_M506}&deptid={$dataSQL->DeptID}\">Số phiếu: {$dataSQL->SoChungTu} </a></strong> </p>";
        //$phieuyc .= "<a href=\"http://{$domain}/mobile/m506/approval/list\">Điện thoại: {$dataSQL->SoChungTu} </a></strong> </p>";

		foreach ($this->_maillist as $item)
		{
		    if($item->EMail)
		    {
		        $toMails[$item->EMail] = $item->UserName;
		    }
		}
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
			$this->_sendMail($this->_form->sz_Name, $toMails, $body,$ccMails);
		}
	}
}
?>