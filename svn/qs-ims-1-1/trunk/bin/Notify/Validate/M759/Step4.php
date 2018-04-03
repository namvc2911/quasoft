<?php
class Qss_Bin_Notify_Validate_M759_Step4 extends Qss_Lib_Notify_Mail
{
	const TITLE ='Cập nhật lại lịch sự kiện';
	
	const TYPE ='TRIGGER';
	
	public function __doExecute($user,$status,$comment)
	{
		if($this->_form->i_Status == 1 || $this->_form->i_Status == 2)
		{
			if(Qss_Lib_System::formActive('C001'))
			{
				$sql = sprintf('update qsusers 
						left join (select ODanhSachNhanVien.Ref_TenTruyCap,
						count(*) as TongSo
						from  MTasks
						inner join qsiforms on qsiforms.IFID = MTasks.IFID_C001 
						inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID = MTasks.Ref_GiaoCho
						where qsiforms.Status in (1,2)
						group by ODanhSachNhanVien.Ref_TenTruyCap) as t
						on t.Ref_TenTruyCap = qsusers.UID
						left join (select ODanhSachNhanVien.Ref_TenTruyCap,
						count(*) as TongSo
						from  OPhieuBaoTri
						inner join qsiforms on qsiforms.IFID = OPhieuBaoTri.IFID_M759
						inner join ODanhSachNhanVien on ODanhSachNhanVien.IOID = OPhieuBaoTri.Ref_NguoiThucHien
						where qsiforms.Status in (1,2)
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
						where qsiforms.Status in (1,2)
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