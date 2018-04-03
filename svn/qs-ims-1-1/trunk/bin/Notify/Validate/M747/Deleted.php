<?php
class Qss_Bin_Notify_Validate_M747_Deleted extends Qss_Lib_Notify_Mail
{
	const TITLE ='Cập nhật khi xóa yêu cầu bảo trì';
	
	const TYPE ='TRIGGER';
	
	public function __doExecute()
	{
        $user = Qss_Register::get('userinfo');

        if(Qss_Lib_System::formActive('M856'))
		{
			$sql = sprintf('
	            UPDATE qsusers 
	            LEFT JOIN 
	            (
	                SELECT 
	                    ODanhSachNhanVien.Ref_TenTruyCap
	                    , SUM(CASE WHEN qsfreader.IFID IS NULL THEN 1 ELSE 0 END) AS TongSo
	                FROM  ODanhSachNhanVien
	                INNER JOIN ONhanVienNhanThongBao ON ODanhSachNhanVien.IOID = ONhanVienNhanThongBao.Ref_MaNV
	                INNER JOIN OThongBao     ON ONhanVienNhanThongBao.IFID_M856 = OThongBao.IFID_M856
	                INNER JOIN qsiforms    ON OThongBao.IFID_M856 = qsiforms.IFID
	                LEFT  JOIN qsfreader   ON qsfreader.IFID = OThongBao.IFID_M856 
	                    AND ODanhSachNhanVien.Ref_TenTruyCap = qsfreader.UID
	                WHERE qsiforms.deleted <> 1 and IFNULL(ODanhSachNhanVien.Ref_TenTruyCap, 0) <> %1$d
	                GROUP BY ODanhSachNhanVien.Ref_TenTruyCap
	            ) AS t ON t.Ref_TenTruyCap = qsusers.UID
	            LEFT JOIN 
	            (
	                SELECT 
	                    qsrecordrights.UID
	                    , count(*) AS TongSo
	                FROM OYeuCauBaoTri
	                INNER JOIN qsiforms ON qsiforms.IFID =  OYeuCauBaoTri.IFID_M747
	                inner join ODanhSachThietBi on ODanhSachThietBi.IOID = OYeuCauBaoTri.Ref_MaThietBi  
	                inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc 
	                inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID 
	                where qsiforms.deleted <> 1 and qsiforms.Status = 1 
	                group by qsrecordrights.UID
	            ) AS t1 ON t1.UID = qsusers.UID
	            SET qsusers.Notify = ifnull(t.TongSo) + ifnull(t1.TongSo,0)
	        ', $user->user_id);
			$this->_db->execute($sql);
		}
		else
		{
			$sql = sprintf('
	            UPDATE qsusers 
	            LEFT JOIN 
	            (
	                SELECT 
	                    qsrecordrights.UID
	                    , count(*) AS TongSo
	                FROM OYeuCauBaoTri
	                INNER JOIN qsiforms ON qsiforms.IFID =  OYeuCauBaoTri.IFID_M747
	                inner join ODanhSachThietBi on ODanhSachThietBi.IOID = OYeuCauBaoTri.Ref_MaThietBi  
	                inner join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc 
	                inner join qsrecordrights on OKhuVuc.IFID_M720 = qsrecordrights.IFID 
	                where qsiforms.deleted <> 1 and qsiforms.Status = 1 
	                group by qsrecordrights.UID
	            ) AS t1 ON t1.UID = qsusers.UID
	            SET qsusers.Notify = ifnull(t1.TongSo,0)
	        ', $user->user_id);
			$this->_db->execute($sql);
		}
		
	}
}
?>