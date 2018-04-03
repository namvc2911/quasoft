<?php
class Print_M759_checklistController extends Qss_Lib_PrintController
{
    public function init()
    {
        parent::init();
        $this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path . '/print.php';
    }

    // --------------------------------- MẪU IN DANANGPORT ---------------------------------
    public function indexAction()
    {
        $mTable = Qss_Model_Db::Table('OCongViecBTPBT');
        $mTable->where(sprintf('IFID_M759 = %1$d', $this->_params->IFID_M759));
        $data   = $mTable->fetchAll();
        $arrCongViec = array();

        foreach ($data as $item) {
            if(!isset($arrCongViec[(int)$item->Ref_ViTri])) {
                $arrCongViec[(int)$item->Ref_ViTri] = '';
            }

            $arrCongViec[(int)$item->Ref_ViTri] .= $item->MoTa.'<br/>';
        }


        $mTable = Qss_Model_Db::Table('OVatTuPBT');
        $mTable->select('OVatTuPBT.*');
        $mTable->join('LEFT JOIN OSanPham ON OVatTuPBT.Ref_MaVatTu = OSanPham.IOID');
        $mTable->where(sprintf('IFID_M759 = %1$d', $this->_params->IFID_M759));
        $mTable->orderby('OVatTuPBT.TenVatTu');
        $data = $mTable->fetchAll();

        $this->html->PhieuBaoTri  = $this->_params;
        $this->html->report       = $data;
        $this->html->TongSoBanGhi = count((array)$data);
        $this->html->CongViec     = $arrCongViec;

        // VatTuXuongMua
    }
}

?>