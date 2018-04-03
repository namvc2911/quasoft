<?php
/**
 * @author: Huy.Bui
 * @component: Cac mau in su co thiet bi
 */
class Print_M759Controller extends Qss_Lib_PrintController
{
	public function init()
	{
		parent::init();
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
	}

    /**
     * In bien ban su co
     */
    public function documentAction()
    {
        $this->html->data = $this->_params;
        $this->html->status = $this->_form->i_Status;
    }

    // --------------------------------- MẪU IN DANANGPORT ---------------------------------
    public function danangportEstimateAction()
    {
        $mTable = Qss_Model_Db::Table('OVatTuPBTDK');
        $mTable->select('OVatTuPBTDK.*');
        $mTable->select('CASE WHEN IFNULL(OSanPham.NhienLieu, 0) = 0 THEN 1 ELSE 0 END AS VatTuXuongMua');
        $mTable->select('CASE WHEN IFNULL(OSanPham.NhienLieu, 0) = 0 THEN 0 ELSE 1 END AS NhienLieuCangCap');
        $mTable->join('INNER JOIN OSanPham ON OVatTuPBTDK.Ref_MaVatTu = OSanPham.IOID');
        // $mTable->join('LEFT JOIN OCauTrucThietBi ON OVatTuPBTDK.Ref_ViTri = OCauTrucThietBi.IOID');
        $mTable->where(sprintf('IFID_M759 = %1$d', $this->_params->IFID_M759));
        $mTable->orderby('CASE WHEN IFNULL(OSanPham.NhienLieu, 0) = 0 THEN 1 ELSE 2 END');
        // $mTable->orderby('OCauTrucThietBi.lft');
        $mTable->orderby('OVatTuPBTDK.TenVatTu');
        $data = $mTable->fetchAll();

        $this->html->PhieuBaoTri  = $this->_params;
        $this->html->report       = $data;
        $this->html->TongSoBanGhi = count((array)$data);

        // VatTuXuongMua
    }
}

?>