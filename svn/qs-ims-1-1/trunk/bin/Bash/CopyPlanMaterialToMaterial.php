<?php
/**
* Copy từ công việc sang
 */
class Qss_Bin_Bash_CopyPlanMaterialToMaterial extends Qss_Lib_Bin
{
	public function __doExecute()
	{
        $ifid = $this->_form->i_IFID;

        if($ifid) 
        {
            $sql = sprintf('SELECT 1 FROM OVatTuPBT WHERE IFID_M759 = %1$d LIMIT 1', $ifid);
            $dat = $this->_db->fetchOne($sql);

            if(!$dat) {
                $sql = sprintf('
                insert ignore into OVatTuPBT(IFID_M759,HinhThuc,Ref_HinhThuc,CongViec,Ref_CongViec,ViTri,Ref_ViTri,BoPhan,Ref_BoPhan, Ngay, MaVatTu, TenVatTu, DonViTinh, SoLuong, DonGia, ChiPhi,NguonVatTu)
                select IFID_M759,HinhThuc,Ref_HinhThuc,CongViec,Ref_CongViec,ViTri,Ref_ViTri,BoPhan,Ref_BoPhan, Ngay, MaVatTu, TenVatTu, DonViTinh, SoLuong, DonGia, ChiPhi,NguonVatTu 
                from OVatTuPBTDK where IFID_M759 = %1$d
                ',$ifid);
                $this->_db->execute($sql);
            }

        }
	}
	
}