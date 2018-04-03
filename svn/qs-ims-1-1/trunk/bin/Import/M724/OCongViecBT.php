<?php
class Qss_Bin_Import_M724_OCongViecBT extends Qss_Lib_Bin
{
	/**
	 * 
	 * Update tmp_OVatTuBT set Error = 1 nếu bộ phận không có bên trên/ giống như trong filter
	 */
    public function __doExecute()
    {
		$sql     = sprintf('
            update tmp_OCongViecBT 
            inner join tmp_OBaoTriDinhKy on tmp_OBaoTriDinhKy.IFID_M724 = tmp_OCongViecBT.IFID_M724 
            inner join OCauTrucThietBi as ct ON ifnull(tmp_OBaoTriDinhKy.Ref_BoPhan,0) = ct.IOID
            left join OCauTrucThietBi as ctphu ON ifnull(tmp_OCongViecBT.Ref_ViTri,0) = ctphu.IOID
            set tmp_OCongViecBT.Error = 4, tmp_OCongViecBT.ErrorMessage = "Vi trí nằm ngoài pham vi bảo trì"
            WHERE ifnull(tmp_OCongViecBT.Ref_ViTri,0) = 0
            or ctphu.lft < ct.lft or ctphu.rgt > ct.rgt');
        $this->_db->execute($sql);
    }
}
?>