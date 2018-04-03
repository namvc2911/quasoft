<?php
class Qss_Bin_Notify_Sms_M759_Inserted extends Qss_Lib_Notify_Sms
{
	const TYPE = 'TRIGGER';
	
	const TITLE = 'Gửi sms khi tạo phiếu sự cố';
    /**
     * Xử lý gửi mail của "Phiếu bảo trì"
     * 1. Gửi mail hàng ngày về phiếu bảo trì tồn chưa được xử lý.
     * 2. Gửi mail hàng ngày về kế hoạch bảo trì trong ngày.
     */
	public function __doExecute()
	{
        $loaiBaoTri = $this->_db->fetchOne(sprintf('select * from OPhanLoaiBaoTri where IOID = %1$d', $this->_params->Ref_LoaiBaoTri));

		if($loaiBaoTri && $loaiBaoTri->GuiSmsKhiTaoMoi)
		{
			$sql = sprintf('select  OKhuVuc.Ten AS TenKhuVuc from ODanhSachThietBi
					inner join OKhuVuc on ODanhSachThietBi.Ref_MaKhuVuc = OKhuVuc.IOID
					where ODanhSachThietBi.IOID = %1$d',
			$this->_params->Ref_MaThietBi);
			$khuvuc = $this->_db->fetchOne($sql);
			$domain = $_SERVER['HTTP_HOST'];
			$sql = sprintf('select ONhanVien.QuanLy, qsusers.Mobile,qsusers.UserName
							from ODonViSanXuat 
							inner join ONhanVien on ONhanVien.IFID_M125 = ODonViSanXuat.IFID_M125
							inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID = ONhanVien.Ref_MaNV
							inner join qsusers on qsusers.UID =  ODanhSachNhanVien.Ref_TenTruyCap
							where ODonViSanXuat.IOID = %1$d and qsusers.isActive = 1
							order by ODonViSanXuat.IFID_M125',
						$this->_params->Ref_MaDVBT);
			$wcs = $this->_db->fetchAll($sql);
			foreach ($wcs as $item)
			{
				if($item->Mobile)
				{
					$this->_maillist[] = $item->Mobile;
				}
			}
			foreach ($this->_maillist as $item)
			{
				$content = $this->_params->LoaiBaoTri . ' thiết bị '. $this->_params->MaThietBi . ' ngày ' . date('d-m-Y');
				$content .= ' Quasoft CMMS';
				$this->_sms->sendSMS($item, $content);
			}
		}
		
			
	}
}
?>