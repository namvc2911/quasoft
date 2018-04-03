<?php
class Qss_Bin_Calculate_OPhieuBaoTri_VatTu extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
		$retval = '';
		$ifid =  $this->_object->i_IFID;
		if($ifid)
		{
			$sql = sprintf('select MaVatTu, TenVatTu, DonViTinh, SoLuong from OVatTuPBT where IFID_M759 = %1$d'
					,$ifid);
			$dataSQL = $this->_db->fetchAll($sql);
			foreach ($dataSQL as $item)
			{
				$retval .= sprintf('%1$s (%2$s %3$s) <br>'
						,$item->TenVatTu
						,$item->SoLuong
						,$item->DonViTinh);
			}
		}
		return $retval;
	}
}
?>