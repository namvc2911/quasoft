<?php
class Qss_Bin_Notify_Mail_M7911 extends Qss_Lib_Notify_Mail
{
	public function __doExecute()
	{
		//$mailmodel = new Qss_Model_Mail();
		$toMails = array();
		$ccMails = array();
		foreach ($this->_maillist as $item)
		{
			if($item->EMail)
			{
				$toMails[$item->EMail] = $item->UserName;
			}
		}
		$sql = sprintf('select ODonViSanXuat.*,ONhanVien.QuanLy, qsusers.EMail,qsusers.UserName
							from ODonViSanXuat 
							inner join ONhanVien on ONhanVien.IFID_M125 = ODonViSanXuat.IFID_M125
							inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID = ONhanVien.Ref_MaNV
							inner join qsusers on qsusers.UID =  ODanhSachNhanVien.Ref_TenTruyCap
							where qsusers.isActive = 1
							order by ODonViSanXuat.IFID_M125');
		$dataSQL = $this->_db->fetchAll($sql);
		$arrToDonVi = array();
		$arrCCDonVi = array();
		$arrName = array();
		foreach ($dataSQL as $item)
		{
			if(!isset($arrToDonVi[$item->IOID]))
			{
				$arrToDonVi[$item->IOID] = array();
			}
			if(!isset($arrCCDonVi[$item->IOID]))
			{
				$arrCCDonVi[$item->IOID] = array();
			}
			if($item->QuanLy)
			{
				$arrCCDonVi[$item->IOID][$item->EMail] = $item->UserName;
			}
			else
			{
				$arrToDonVi[$item->IOID][$item->EMail] = $item->UserName;
			}
			$arrName[$item->IOID] = $item->Ten;
		}
		foreach ($arrName as $key=>$value)
		{
			$arrTos = array_merge($toMails,$arrToDonVi[$key]);
			$arrCcs = array_merge($ccMails,$arrCCDonVi[$key]);
			$content = $this->sendReportByWC($key,$value,$arrTos);
			if($content)
			{
				$subject = 'Công việc chưa thực hiện '. $value . ' ' . date('d-m-Y');
				$body = 'Chào anh/chị' . '<br><br>';
				$body .= $content.'<br>';
				$body .= 'QS-IMS Mailer';
				$this->_sendMail($subject, $arrTos, $body,$arrCcs);	
			}		
		}
	}
	private function sendReportByWC($wc,$name,$to)
	{
		/*$sql = sprintf('select NgayYeuCau from OPhieuBaoTri
					inner join qsiforms as qsiforms.IFID = OPhieuBaoTri.IFID_M759
					inner join OCongViecBTPBT on OCongViecBTPBT.IFID_M759 = OPhieuBaoTri.IFID_M759
					where ifnull(OCongViecBTPBT.ThucHien,0) = 0 and STR_TO_DATE(NgayYeuCau,\'%%Y-%%m-%%d\') < %1$s
					and OPhieuBaoTri.Ref_MaDVBT = %2$d
					and Status < 4
					group by NgayYeuCau',
					$this->_db->quote(date('Y-m-d')),
					$wc);
		$dates = $this->_db->fetchAll($sql);
		$content = '';
		foreach ($dates as $item)
		{
			$ngay = Qss_Lib_Date::mysqltodisplay($item->NgayYeuCau);
			$params = array('workcenter'=>$wc,
							'date'=>$ngay);
			$content .= $this->_genHTML($this->_form->FormCode ,$params);
		}*/
		$sql = sprintf('select 1 from OPhieuBaoTri
					inner join qsiforms on qsiforms.IFID = OPhieuBaoTri.IFID_M759
					inner join OCongViecBTPBT on OCongViecBTPBT.IFID_M759 = OPhieuBaoTri.IFID_M759
					where ifnull(OCongViecBTPBT.ThucHien,0) = 0 and STR_TO_DATE(NgayYeuCau,\'%%Y-%%m-%%d\') < %1$s
					and OPhieuBaoTri.Ref_MaDVBT = %2$d
					and Status < 4
					limit 1',
					$this->_db->quote(date('Y-m-d')),
					$wc);

		$dates = $this->_db->fetchOne($sql);
		$content = '';
		if ($dates)
		{
			$params = array('workcenter'=>$wc);
			$content .= $this->_genHTML($this->_form->FormCode ,$params);
		}
		return $content;	
	}
}
?>