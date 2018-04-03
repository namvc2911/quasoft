<?php
class Print_M759_estimationController extends Qss_Lib_PrintController
{
    public function init()
    {
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }

    // --------------------------------- MẪU IN DANANGPORT ---------------------------------
    public function indexAction()
    {
        $mTable = Qss_Model_Db::Table('OVatTuPBTDK');
        $mTable->select('OVatTuPBTDK.*');
        $mTable->select('CASE WHEN IFNULL(OVatTuPBTDK.NguonVatTu, 0) = 1 THEN 1 ELSE 0 END AS VatTuCangCap');
        $mTable->select('CASE WHEN IFNULL(OVatTuPBTDK.NguonVatTu, 0) = 2 THEN 1 ELSE 0 END AS VatTuXuongMua');
        $mTable->select('CASE WHEN IFNULL(OSanPham.NhienLieu, 0) = 1 THEN 1 ELSE 0 END AS NhienLieuCangCap');
        $mTable->select(
            sprintf('
                CASE WHEN IFNULL(OSanPham.NhienLieu, 0) = 1 THEN 3 
                WHEN IFNULL(OVatTuPBTDK.NguonVatTu, 0) = 1  THEN 2 
                WHEN IFNULL(OVatTuPBTDK.NguonVatTu, 0) = 2  THEN 1
                END AS KieuVatTu
            ')
        );

        $mTable->join('LEFT JOIN OSanPham ON OVatTuPBTDK.MaVatTu = OSanPham.MaSanPham');
        // $mTable->join('LEFT JOIN OCauTrucThietBi ON OVatTuPBTDK.Ref_ViTri = OCauTrucThietBi.IOID');
        $mTable->where(sprintf('IFID_M759 = %1$d', $this->_params->IFID_M759));
        $mTable->orderby(
            sprintf('
                CASE WHEN IFNULL(OSanPham.NhienLieu, 0) = 1 THEN 3 
                WHEN IFNULL(OVatTuPBTDK.NguonVatTu, 0) = 1  THEN 2 
                WHEN IFNULL(OVatTuPBTDK.NguonVatTu, 0) = 2  THEN 1 END
            ')
        );
        // $mTable->orderby('OCauTrucThietBi.lft');
        $mTable->orderby('OVatTuPBTDK.TenVatTu');
        $data = $mTable->fetchAll();

        // echo '<pre>'; print_r($data); die;

        $this->html->PhieuBaoTri  = $this->_params;
        $this->html->report       = $data;
        $this->html->TongSoBanGhi = count((array)$data);

        // VatTuXuongMua
    }
}

?>