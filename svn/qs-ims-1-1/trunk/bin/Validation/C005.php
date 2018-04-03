<?php
class Qss_Bin_Validation_C005 extends Qss_Lib_Validation
{
	
	public function onRead()
	{
		parent::init();
		$user = Qss_Register::get('userinfo');
		if($this->_form->read($user->user_id))
		{
			$sql = sprintf('
                UPDATE qsusers 
                INNER JOIN 
                (
                    SELECT 
                        ODanhSachNhanVien.Ref_TenTruyCap
                        , SUM(CASE WHEN qsfreader.IFID IS NULL THEN 1 ELSE 0 END) AS TongSo
                    FROM  ODanhSachNhanVien
                    INNER JOIN MNotifyUser ON ODanhSachNhanVien.IOID = MNotifyUser.Ref_MaNV
                    INNER JOIN MNotify ON MNotifyUser.IFID_C005 = MNotify.IFID_C005
                    INNER JOIN qsiforms ON MNotify.IFID_C005 = qsiforms.IFID
                    LEFT JOIN qsfreader ON qsfreader.IFID = MNotify.IFID_C005 AND ODanhSachNhanVien.Ref_TenTruyCap = qsfreader.UID
                    WHERE 
                        ODanhSachNhanVien.Ref_TenTruyCap = %1$d
                        AND qsiforms.UID <> %1$d
                ) AS t ON t.Ref_TenTruyCap = qsusers.UID
                SET qsusers.Notify = TongSo
            ', $user->user_id);
			$this->_db->execute($sql);	
		}
	}
}