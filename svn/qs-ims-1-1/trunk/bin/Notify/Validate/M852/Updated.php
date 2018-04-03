<?php
class Qss_Bin_Notify_Validate_M852_Updated extends Qss_Lib_Notify_Mail
{
	const TYPE = 'TRIGGER';
	
	const TITLE = 'Gửi email khi tạo phiếu bảo trì';
    /**
     * Xử lý gửi mail của "Phiếu bảo trì"
     * 1. Gửi mail hàng ngày về phiếu bảo trì tồn chưa được xử lý.
     * 2. Gửi mail hàng ngày về kế hoạch bảo trì trong ngày.
     */
	public function __doExecute()
	{
		if($this->_form->i_Status == 1 || $this->_form->i_Status == 2)
		{
			if(Qss_Lib_System::formActive('M852'))
			{
				$sql = sprintf('update qsusers 
						left join (select ODanhSachNhanVien.Ref_TenTruyCap,
						count(*) as TongSo
						from  ODanhSachCongViec
						inner join qsiforms on qsiforms.IFID = ODanhSachCongViec.IFID_M852 
						inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID = ODanhSachCongViec.Ref_GiaoCho
						where qsiforms.Status in (1,2)
						group by ODanhSachNhanVien.Ref_TenTruyCap) as t
						on t.Ref_TenTruyCap = qsusers.UID
						left join (select ODanhSachNhanVien.Ref_TenTruyCap,
						count(*) as TongSo
						from  OPhieuBaoTri
						inner join qsiforms on qsiforms.IFID = OPhieuBaoTri.IFID_M759
						inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID = OPhieuBaoTri.Ref_NguoiThucHien
						where qsiforms.deleted <> 1 and qsiforms.Status in (1,2)
						group by ODanhSachNhanVien.Ref_TenTruyCap) as v
						on v.Ref_TenTruyCap = qsusers.UID
						set qsusers.Event = ifnull(t.TongSo,0) + ifnull(v.TongSo,0) 
						');
			}
			else
			{
				$sql = sprintf('update qsusers 
						left join (select ODanhSachNhanVien.Ref_TenTruyCap,
						count(*) as TongSo
						from  OPhieuBaoTri
						inner join qsiforms on qsiforms.IFID = OPhieuBaoTri.IFID_M759
						inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID = OPhieuBaoTri.Ref_NguoiThucHien
						where qsiforms.deleted <> 1 and qsiforms.Status in (1,2)
						group by ODanhSachNhanVien.Ref_TenTruyCap) as v
						on v.Ref_TenTruyCap = qsusers.UID
						set qsusers.Event = ifnull(v.TongSo,0) 
						');
			}
			$this->_db->execute($sql);
		}
	}
}
?>