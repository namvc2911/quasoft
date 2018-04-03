<?php
class Qss_Model_M708_Main extends Qss_Model_Abstract {
	public function checkSuCo($ifid_m759) {
		$sql = sprintf(
			'SELECT OPhanLoaiBaoTri.Loai,OPhieuBaoTri.IOID,OPhieuBaoTri.SoPhieu,OPhanLoaiBaoTri.LoaiBaoTri
		From OPhieuBaoTri
		INNER JOIN OPhanLoaiBaoTri
		on OPhanLoaiBaoTri.IOID =  OPhieuBaoTri.Ref_LoaiBaoTri
		WHERE OPhanLoaiBaoTri.LoaiBaoTri = "B" AND  OPhieuBaoTri.IFID_M759 = %1$d  '

			, $ifid_m759);
		return $this->_o_DB->fetchOne($sql);
	}

}

?>