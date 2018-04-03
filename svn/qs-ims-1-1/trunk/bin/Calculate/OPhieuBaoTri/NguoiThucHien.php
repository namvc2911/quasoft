<?php
class Qss_Bin_Calculate_OPhieuBaoTri_NguoiThucHien extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
        $oPhieuBaoTri = $this->_db->fetchOne(sprintf('
            select OPhanLoaiBaoTri.LoaiBaoTri, OPhieuBaoTri.Ref_MaThietBi, OPhieuBaoTri.Ref_MoTa 
            from OPhieuBaoTri
            INNER JOIN OPhanLoaiBaoTri ON OPhieuBaoTri.Ref_LoaiBaoTri = OPhanLoaiBaoTri.IOID
            where OPhieuBaoTri.IFID_M759 = %1$d'
        , $this->_object->i_IFID));
        $loaiBaoTri  = ($oPhieuBaoTri)?$oPhieuBaoTri->LoaiBaoTri:'';

		if(Qss_Lib_System::fieldActive('OBaoTriDinhKy', 'NguoiThucHien') && $loaiBaoTri == Qss_Lib_Extra_Const::MAINT_TYPE_PREVENTIVE)
		{
			$sql = sprintf('select Ref_NguoiThucHien AS QuanLy from OBaoTriDinhKy where IOID = %1$d', @(int)$oPhieuBaoTri->Ref_MoTa);
		}
		else
		{
			$sql = sprintf('select Ref_QuanLy AS QuanLy from ODanhSachThietBi where IOID = %1$d', @(int)$oPhieuBaoTri->Ref_MaThietBi);
		}
        $dataSQL = $this->_db->fetchOne($sql);
        return ($dataSQL)?(int)$dataSQL->QuanLy:0;
	}
}
?>