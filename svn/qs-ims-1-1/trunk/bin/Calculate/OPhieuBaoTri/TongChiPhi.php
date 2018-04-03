<?php
//cho đà nẵng
class Qss_Bin_Calculate_OPhieuBaoTri_TongChiPhi extends Qss_Lib_Calculate
{
    public function __doExecute()
    {
        $retval = '';
		$ifid =  $this->_object->i_IFID;
		$nhacong = (int)$this->_object->getFieldByCode('ChiPhiNhanCong')->getValue(false);
		if($ifid)
		{
			$sql = sprintf('select sum(ChiPhi) as chiphi from OVatTuPBT where IFID_M759 = %1$d'
					,$ifid);
			$dataSQL = $this->_db->fetchOne($sql);
			if($dataSQL)
			{
				$retval .= '<span class="bold">'.Qss_Lib_Util::formatMoney($dataSQL->chiphi + $nhacong*1000).'</span>';
			}
		}
		return $retval;
    }
}