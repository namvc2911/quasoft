<?php
class Qss_Bin_Notify_Mail_M113_Safety extends Qss_Lib_Notify_Mail
{
	const TITLE ='Gửi email tổng hợp mặt hàng dưới mức tối thiểu';
	
	const TYPE ='SUBSCRIBE';
	
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
			if(!$item->QuanLy)
			{
				$toMails[$item->EMail] = $item->UserName;
			}
		}
		$sql = sprintf('select OSanPham.*,sum(SoLuongHC) as SoLuongHC, (SLToiThieu - sum(ifnull(SoLuongHC,0))) as Thieu
							from OSanPham 
							left join OKho on OKho.Ref_MaSP = OSanPham.IOID
							group by OSanPham.IOID
							having (SLToiThieu - sum(ifnull(SoLuongHC,0))) > 0');
		$dataSQL = $this->_db->fetchAll($sql);
		if(count((array)$dataSQL))
		{
			$subject = date('d-m-Y') . ': Các mặt hàng dưới mức tối thiểu ';
			$body = 'Chào anh/chị' . '<br><br>';
			$body .= '<table border = "1">';
			$body .= sprintf('<th>Mã mặt hàng</th><th>Tên mặt hàng</th><th>Đơn vị tính</th><td>Số lượng hiện có</td><td>Số lượng tối thiểu</td><td>Số lượng cần mua</td>');
			foreach ($dataSQL as $item)
			{
				$body .= '<tr>';	
				$body .= sprintf('<td>%1$s</td><td>%2$s</td><td>%3$s</td><td>%4$s</td><td>%5$s</td><td>%6$s</td>',
							$item->MaSanPham,
							$item->TenSanPham,
							$item->DonViTinh,
							$item->SoLuongHC,
							$item->SLToiThieu,
							$item->Thieu);
				$body .= '</tr>';
			}
			$body .= '</table>';
			$body .= 'QS-IMS Mailer';
			$this->_sendMail($subject, $toMails, $body,$ccMails);
		}
	}
}
?>