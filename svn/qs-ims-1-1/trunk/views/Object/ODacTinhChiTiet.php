<?php
class Qss_View_Object_ODacTinhChiTiet extends Qss_View_Abstract
{
    /**
     * Khôi phục
     *
     * E:\Favorites\A-Quasoft\Versions\3.0\03\trunk\models\Maintenance\Equipment.php
     *
        public function getLoaiChiTiet($ifid)
        {
            $sql = sprintf('
                SELECT * from OLoaiThietBi as loai
                inner join ODanhMucChiTietThietBi as tb on tb.Ref_LoaiThietBi = loai.IOID
                where tb.IFID_M775=%1$d'
            ,$ifid);
            return $this->_o_DB->fetchOne($sql);
        }
     *
        public function getDacTinhChiTiet($ifid)
        {
            $sql = sprintf('
                SELECT dt.DonViTinh,dt.IOID as RefIOID, dt.Ten,dttb.IOID,dttb.GiaTri FROM ODacTinhKyThuat as dt
                inner join OLoaiThietBi as loai on loai.IFID_M770 = dt.IFID_M770
                inner join ODanhMucChiTietThietBi as tb on tb.Ref_LoaiThietBi = loai.IOID
                left join ODacTinhChiTiet as dttb on dttb.Ref_Ten = dt.IOID and dttb.IFID_M775 = tb.IFID_M775
                where tb.IFID_M775=%1$d'
            ,$ifid);
            return $this->_o_DB->fetchAll($sql);
        }
     */

	public function __doExecute($sql, $form, $object, $currentpage, $limit, $fieldorder ,$i_GroupBy)
    {
    	$ifid = $form->i_IFID;
		$model = new Qss_Model_Maintenance_Equipment();
		$this->html->loai = $model->getLoaiChiTiet($ifid);
		$this->html->data = $model->getDacTinhChiTiet($ifid);
	}
}
?>