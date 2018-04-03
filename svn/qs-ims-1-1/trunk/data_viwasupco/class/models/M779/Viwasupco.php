<?php

class Qss_Model_M779_Viwasupco extends Qss_Model_Abstract {
    public function __construct() {
        parent::__construct();
    }

    public function getDetailWorkorderForM779(
        $start = ''
        , $end = ''
        , $locIOID = 0
        , $eqIOID = 0
        , $workcenter = 0
        , $orderByLocation = false) {
        $arrNoiCongViecVoiVatTuDK = array();
        $arrNoiCongViecVoiVatTu   = array();
        $arrNoiCongViecVoiVatTuDK2= array();
        $arrNoiCongViecVoiVatTu2  = array();
        $arrIFIDs                 = array();
        $arrIFIDs[]               = 0; // Cho 1 phần tử để tránh lỗi trắng
        $oldSoPhieu               = '';

        $sqlCongViecPhieu = sprintf('
            SELECT 
                PhieuBaoTri.*
                , CongViec.ViTri
                , CongViec.BoPhan
                , CongViec.MoTa AS MoTaCongViec
                , IFNULL(CongViec.IOID, 0) AS CongViecIOID
                , IFNULL(CongViec.Ref_ViTri, 0) AS Ref_ViTri
                , IForm.Status
                , IF(IFNULL(KhuVucThietBi.IOID, 0) = 0, KhuVucPhieu.MaKhuVuc, KhuVucThietBi.MaKhuVuc) AS MaKhuVuc
                , IF(IFNULL(KhuVucThietBi.IOID, 0) = 0, KhuVucPhieu.Ten, KhuVucThietBi.Ten) AS TenKhuVuc                
        ');

        // Ext fields
        $sqlCongViecPhieu.= Qss_Lib_System::fieldActive('OCongViecBTPBT', 'Ngay')
            ?', CongViec.Ngay AS NgayCongViecThucTe':',null AS NgayCongViecThucTe';
        $sqlCongViecPhieu.= Qss_Lib_System::fieldActive('OCongViecBTPBT', 'NguoiThucHien')
            ?', CongViec.NguoiThucHien AS NguoiThucHienCongViec':',null AS AS NguoiThucHienCongViec';
        $sqlCongViecPhieu.= Qss_Lib_System::fieldActive('OCongViecBTPBT', 'NguoiThucHien')
            ?', CongViec.GhiChu AS GhiChuCongViec':',null AS AS GhiChuCongViec';

        if($orderByLocation) {
            $sqlCongViecPhieu .= sprintf(' , IF(IFNULL(KhuVucThietBi.IOID, 0) = 0, KhuVucPhieu.MaKhuVuc, KhuVucThietBi.MaKhuVuc) AS MaKhuVucSapXep');
            $sqlCongViecPhieu .= sprintf(' , IF(IFNULL(KhuVucThietBi.IOID, 0) = 0, KhuVucPhieu.Ten, KhuVucThietBi.Ten) AS TenKhuVucSapXep');
            $sqlCongViecPhieu .= sprintf(' , IF(IFNULL(KhuVucThietBi.IOID, 0) = 0, KhuVucPhieu.lft, KhuVucThietBi.lft) AS LftKhuVucSapXep');
        }
        else {
            $sqlCongViecPhieu .= sprintf(' , NULL AS MaKhuVucSapXep');
            $sqlCongViecPhieu .= sprintf(' , NULL AS TenKhuVucSapXep');
        }

        $sqlCongViecPhieu .= sprintf('
            FROM OPhieuBaoTri AS PhieuBaoTri
            INNER JOIN qsiforms AS IForm ON PhieuBaoTri.IFID_M759 = IForm.IFID
            LEFT JOIN ODanhSachThietBi AS ThietBi ON PhieuBaoTri.Ref_MaThietBi = ThietBi.IOID
        ');

        if($orderByLocation) {
            $sqlCongViecPhieu .= sprintf(' LEFT JOIN OKhuVuc AS KhuVucThietBi ON ThietBi.Ref_MaKhuVuc = KhuVucThietBi.IOID');
            $sqlCongViecPhieu .= sprintf(' LEFT JOIN OKhuVuc AS KhuVucPhieu ON PhieuBaoTri.Ref_MaKhuVuc = KhuVucPhieu.IOID');
        }

        $sqlCongViecPhieu .= sprintf('
            LEFT JOIN OCongViecBTPBT AS CongViec ON PhieuBaoTri.IFID_M759 = CongViec.IFID_M759
            LEFT JOIN OCauTrucThietBi AS CauTruc ON CongViec.Ref_ViTri = CauTruc.IOID
            WHERE 1=1 
        ');

        // Lọc
        $loc = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):'';
        $wc  = $workcenter?$this->_o_DB->fetchOne(sprintf('SELECT * FROM ODonViSanXuat WHERE IOID = %1$d', $workcenter)):'';

        $sqlCongViecPhieu .= ($start && $end)
            ?sprintf(' AND (PhieuBaoTri.NgayBatDauDuKien >= %1$s and PhieuBaoTri.NgayBatDauDuKien <= %2$s)', $this->_o_DB->quote($start), $this->_o_DB->quote($end)):'';
        $sqlCongViecPhieu .= $loc?sprintf(
            ' 
            AND (
                (IFNULL(ThietBi.Ref_MaKhuVuc, 0) IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))
                OR (IFNULL(PhieuBaoTri.Ref_MaKhuVuc, 0) IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))
            )'
            , $loc->lft, $loc->rgt):'';
        $sqlCongViecPhieu .= ($eqIOID)?sprintf(' AND ThietBi.IOID = %1$d  ', $eqIOID):'';
        $sqlCongViecPhieu .= $wc
            ?sprintf(' AND (PhieuBaoTri.Ref_MaDVBT IN (select IOID from ODonViSanXuat where lft>= %1$d and  rgt <= %2$d))', $wc->lft, $wc->rgt):'';

        $tmpOrderby = '';
        if($orderByLocation) {
            $tmpOrderby .= $tmpOrderby?', ':'';
            $tmpOrderby .= sprintf(' IF(IFNULL(KhuVucThietBi.IOID, 0) = 0, KhuVucPhieu.lft, KhuVucThietBi.lft) ');
        }

        if(Qss_Lib_System::fieldActive('ODanhSachThietBi', 'STT')) {
            $tmpOrderby .= $tmpOrderby?', ':'';
            $tmpOrderby .= ' ThietBi.STT ';
        }

        $tmpOrderby .= $tmpOrderby?', ':'';
        $tmpOrderby .= ' PhieuBaoTri.NgayBatDauDuKien, PhieuBaoTri.SoPhieu, CauTruc.lft, CongViec.MoTa';

        $sqlCongViecPhieu .= ' ORDER BY ' . $tmpOrderby;

        // echo '<pre>'; print_r($sqlCongViecPhieu); die;
        $objCongViecPhieu = $this->_o_DB->fetchAll($sqlCongViecPhieu);

        // Lấy IFIDs
        foreach($objCongViecPhieu as $item) {
            if($item->IFID_M759 && !in_array($item->IFID_M759, $arrIFIDs)) {
                $arrIFIDs[] = $item->IFID_M759;
            }
        }

        // Lấy vật tư dự kiến theo phiếu bảo trì
        if(Qss_Lib_System::objectInForm('M759', 'OVatTuPBTDK')) {

            $sqlVatTuDuKien = sprintf('
                SELECT *, CONCAT("- ", TenVatTu, " (", MaVatTu, "): ", SoLuong, " ", DonViTinh, "") AS VatTuGroup
                FROM OVatTuPBTDK AS VatTu
                WHERE IFID_M759 IN (%1$s)
                ORDER BY IFID_M759, MaVatTu
          ', implode(',', $arrIFIDs));
            $objVatTuDuKien = $this->_o_DB->fetchAll($sqlVatTuDuKien);

            // echo '<pre>'; print_r($sqlVatTuDuKien); die;

            foreach ($objVatTuDuKien as $item) {
                $keyMangVatTu = (int)$item->IFID_M759.'-'.(int)$item->Ref_CongViec;
                $arrNoiCongViecVoiVatTuDK[(int)$item->IFID_M759][]  = $item->VatTuGroup;
                $arrNoiCongViecVoiVatTuDK2[$keyMangVatTu][]         = $item->VatTuGroup;
            }
        }

        // Lấy vật tư thực tế theo phiếu bảo trì
        if(Qss_Lib_System::objectInForm('M759', 'OVatTuPBT')) {
            $sqlVatTu = sprintf('
                SELECT *, CONCAT("- ", TenVatTu, " (", MaVatTu, "): ", SoLuong, " ", DonViTinh, "") AS VatTuGroup
                FROM OVatTuPBT AS VatTu
                WHERE IFID_M759 IN (%1$s)
                ORDER BY IFID_M759, MaVatTu             
          ', implode(',', $arrIFIDs));

            $objVatTu = $this->_o_DB->fetchAll($sqlVatTu);

            foreach ($objVatTu as $item) {
                $keyMangVatTu = (int)$item->IFID_M759.'-'.(int)$item->Ref_CongViec;


                $arrNoiCongViecVoiVatTu2[$keyMangVatTu][]        = $item->VatTuGroup;
            }
        }

        // echo '<pre>'; print_r($arrNoiCongViecVoiVatTu2);

        // Gắn vật tư vào phiếu, công việc, vị trí tương ứng
        foreach($objCongViecPhieu as $key=>$item) {

            if($oldSoPhieu == $item->SoPhieu && in_array($item->Status, array(1, 2))) {
                unset($objCongViecPhieu[$key]);
                continue;
            }

            $keyMangVatTu = (int)$item->IFID_M759.'-'.(int)$item->CongViecIOID;
            $objCongViecPhieu[$key]->ToanBoVatTuDuKien  = array();
            $objCongViecPhieu[$key]->ToanBoVatTuThucTe  = array();
            $objCongViecPhieu[$key]->Rowspan            = 0;
            $rowspan = 0;

            if(in_array($item->Status, array(1, 2))) {
                // In toan bo vat tu du kien theo 1 cong viec chinh
                if(isset($arrNoiCongViecVoiVatTuDK[(int)$item->IFID_M759]) && count($arrNoiCongViecVoiVatTuDK[(int)$item->IFID_M759])) {
                    $objCongViecPhieu[$key]->ToanBoVatTuDuKien = $arrNoiCongViecVoiVatTuDK[(int)$item->IFID_M759];
                    $rowspan = count($arrNoiCongViecVoiVatTuDK[(int)$item->IFID_M759]);
                }
            }
            else {
                // In dung vat tu du kien theo cong viec con tu buoc  hoan thanh
                if(isset($arrNoiCongViecVoiVatTuDK2[$keyMangVatTu]) && count($arrNoiCongViecVoiVatTuDK2[$keyMangVatTu])) {
                    $objCongViecPhieu[$key]->ToanBoVatTuDuKien = $arrNoiCongViecVoiVatTuDK2[$keyMangVatTu];
                    $rowspan = count($arrNoiCongViecVoiVatTuDK2[$keyMangVatTu]);
                }
                // In vat tu thuc te theo cong viec con tu buoc hoan thanh
                if(isset($arrNoiCongViecVoiVatTu2[$keyMangVatTu])
                    && count($arrNoiCongViecVoiVatTu2[$keyMangVatTu])) {
                    $objCongViecPhieu[$key]->ToanBoVatTuThucTe = $arrNoiCongViecVoiVatTu2[$keyMangVatTu];
                    $tempCountVatTu = count($arrNoiCongViecVoiVatTu2[$keyMangVatTu]);
                    if($rowspan < $tempCountVatTu) {
                        $rowspan = $tempCountVatTu;
                    }
                }
            }


            $objCongViecPhieu[$key]->Rowspan = $rowspan;
            $oldSoPhieu = $item->SoPhieu;
        }

        // echo '<pre>'; print_r($objCongViecPhieu); die;
        return $objCongViecPhieu;
    }
}