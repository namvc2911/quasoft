<?php
class Qss_Bin_Notify_Mail_M856_Step2 extends Qss_Lib_Notify_Mail
{
	const TITLE ='Gửi email xác nhận đang làm';
	
	const TYPE ='TRIGGER';

    const INACTIVE = true;
	public function __doExecute($user,$status,$comment)
	{
		$domain = $_SERVER['HTTP_HOST'];
		$sql = sprintf('select if(ifnull(ODanhSachNhanVien.EMail,"") != "",ODanhSachNhanVien.EMail,qsusers.EMail) as EMail,
						qsusers.UserName
						from qsusers 
						inner join ODanhSachNhanVien on ODanhSachNhanVien.Ref_TenTruyCap = qsusers.UID
						inner join ONhanVienNhanThongBao on ONhanVienNhanThongBao.Ref_MaNV = ODanhSachNhanVien.IOID
						where ONhanVienNhanThongBao.IFID_M856 = %1$d',
					$this->_params->IFID_M856);
		$wcs = $this->_db->fetchAll($sql);
		$arrToDonVi = array();
		$arrCCDonVi = array();
		foreach ($wcs as $item)
		{
			$arrToDonVi[$item->EMail] = $item->UserName;
		}
		foreach ($this->_maillist as $item)
		{
		    if($item->EMail)
		    {
		        $arrToDonVi[$item->EMail] = $item->UserName;
		    }
		}
		unset($arrToDonVi[$user->user_email]);
		if(count($arrToDonVi) || count($arrCCDonVi))
		{
			$subject = 'Thông báo :' . $this->_params->TieuDe;
			$body = sprintf('%1$s<br><br>',$this->_params->NoiDung);
			$body .= sprintf('Thông báo số : <a href="http://%2$s/user/form/edit?ifid=%3$d&deptid=%4$d">%1$s</a><br>',
					$this->_params->SoThongBao,
					$domain, 
					 $this->_params->IFID_M856,
					 1);
			$body .= 'Quasoft CMMS Mailer';
			$this->_sendMail($subject, $arrToDonVi, $body,$arrCCDonVi);	
		}
	}
}
?>