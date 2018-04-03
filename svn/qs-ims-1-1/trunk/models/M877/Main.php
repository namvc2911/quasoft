<?php
class Qss_Model_M877_Main extends Qss_Model_Abstract {
    public function getData($year, $locIOID = 0, $eqGroupIOID = 0, $eqTypeIOID = 0, $equipIOID = 0, $workcenter = 0) {
        $mSDate    = $year.'-01-01';
        $mEDate    = $year.'-12-31';
        $arrChuKy  = array(); // Xác định chu kỳ đã tồn tại không add thêm vào mảng trả về + để lấy vật tư kể hoạch M724
        $arrChuKy[]= 0;
        $arrRetval = array(); // Mảng trả về
        $arrMonthForPeriod    = array(); // Xem xem chu ky xay ra luc nao
        $arrMonthForNonPeriod = array(); // Xem xem cac ke hoach khong co chu ky xay ra luc nao
        $arrItemForPeriod     = array();
        $arrItemForNonPeriod  = array();
        $arrLastForPeriod     = array(); // Lần bảo trì gần nhất cho các kế hoạch có chu kỳ
        $arrIFIDs             = array();
        $arrIFIDs[]           = 0;
        $arrRowSpanByEquip    = array(); // Tính rowspan theo Equip (Kết hợp đếm task và đếm vật tư)
        $arrVatTuM724         = array(); // Vat tu theo chu ky

        $sql = sprintf('
            SELECT ChiTiet.*, ThietBi.MaKhuVuc, KhuVuc.Ten AS TenKhuVuc
            FROM OKeHoachTongThe AS KeHoach
            INNER JOIN qsiforms AS IFormKeHoach ON KeHoach.IFID_M838 = IFormKeHoach.IFID
            INNER JOIN OKeHoachBaoTri AS ChiTiet ON KeHoach.IOID = ChiTiet.Ref_KeHoachTongThe
            INNER JOIN qsiforms AS IFormChiTiet ON ChiTiet.IFID_M837 = IFormChiTiet.IFID            
            LEFT JOIN ODanhSachThietBi AS ThietBi ON ChiTiet.Ref_MaThietBi = ThietBi.IOID
            LEFT JOIN OKhuVuc AS KhuVuc ON ThietBi.Ref_MaKhuVuc = KhuVuc.IOID
            WHERE IFormKeHoach.Status = 3 -- Ke hoach da duyet
                AND IFormChiTiet.Status = 2 -- Chi tiet ke hoach cung da duoc duyet
                AND (ChiTiet.NgayBatDau BETWEEN %1$s AND %2$s)
                -- AND ChiTiet.MaThietBi IN ("RPS.2CT101", "RPS.2P702")
            
            
        ', $this->_o_DB->quote($mSDate), $this->_o_DB->quote($mEDate));

        if($locIOID) {
            $locName = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):'';

            $sql .= sprintf('
                AND IFNULL(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)
            ', $locName->lft, $locName->rgt);
        }

        if($eqTypeIOID) {
            $eqTypes = $eqTypeIOID?$this->_o_DB->fetchOne(sprintf('select * from OLoaiThietBi where IOID = %1$d', $eqTypeIOID)):'';

            $sql  .= $eqTypes?sprintf('
                and IFNULL(ThietBi.Ref_LoaiThietBi, 0) IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d) '
            ,$eqTypes->lft, $eqTypes->rgt):'';
        }

        if($workcenter) {
            $sql  .= sprintf(' and IFNULL(ChiTiet.Ref_MaDVBT, 0) = %1$d ', $workcenter);
        }

        if($equipIOID) {
            $sql  .= sprintf(' and IFNULL(ThietBi.IOID, 0) = %1$d ', $equipIOID);
        }

        $sql .= sprintf(' ORDER BY KhuVuc.lft ');

        if(Qss_Lib_System::fieldActive('ODanhSachThietBi', 'STT')) {
            $sql .= sprintf(' , ThietBi.STT ');
        }

        $sql .= sprintf(' , ThietBi.MaThietBi, ChiTiet.MoTa ');

        $keHoachTongThe = $this->_o_DB->fetchAll($sql);

        // Dem so luong mot ke hoach & xem mot ke hoach xay ra o trong nhung thang nao
        // Chi dem voi loai bao tri dinh ky, cac loai khac chi can lay thang xay ra
        foreach ($keHoachTongThe as $item) {
            $arrIFIDs[] = $item->IFID_M837; // Lấy danh sách IFID để lấy vật tư

            if($item->Ref_ChuKy) { // Doi voi loai bao tri dinh ky
                if(!in_array($item->Ref_ChuKy, $arrChuKy)) {
                    $arrRetval[] = $item;
                    $arrChuKy[] = $item->Ref_ChuKy;
                }

                // Xem thang xay ra cho truong hop dinh ky
                if(!isset($arrMonthForPeriod[$item->Ref_ChuKy])) {
                    $arrMonthForPeriod[$item->Ref_ChuKy] = array();
                }

                if($item->NgayBatDau) {
                    $arrMonthForPeriod[$item->Ref_ChuKy][] = (int)date('m', strtotime($item->NgayBatDau));
                }
            }
            else { // Doi voi cac loai bao tri không định kỳ chỉ cần lấy tháng xảy ra đếm = 1
                $arrRetval[] = $item;

                // Xem thang xay ra cho truong hop dinh ky
                if(!isset($arrMonthForNonPeriod[$item->IFID_M837])) {
                    $arrMonthForNonPeriod[$item->IFID_M837] = array();
                }

                if($item->NgayBatDau) {
                    $arrMonthForNonPeriod[$item->IFID_M837][] = (int)date('m', strtotime($item->NgayBatDau));
                }
            }
        }

        // Lấy vật tư kế hoạch M724
        $sql = sprintf('
            SELECT VatTu.*, (IFNULL(VatTu.SoLuong, 0) * DonViTinh.HeSoQuyDoi) AS SoLuongDaQuyDoi, ChuKy.IOID AS Ref_ChuKy
            FROM OChuKyBaoTri AS ChuKy
            INNER JOIN OVatTu AS VatTu ON ChuKy.IFID_M724 = VatTu.IFID_M724
            INNER JOIN OSanPham AS SanPham ON VatTu.Ref_MaVatTu = SanPham.IOID
            INNER JOIN ODonViTinhSP AS DonViTinh ON SanPham.IFID_M113 = DonViTinh.IFID_M113
                AND VatTu.Ref_DonViTinh = DonViTinh.IOID
            WHERE ChuKy.IOID IN (%1$s)
            GROUP BY ChuKy.IOID
        ', implode(',', $arrChuKy));

        // echo '<pre>'; print_r($sql); die;
        $vatTuM724 = $this->_o_DB->fetchAll($sql);

        foreach($vatTuM724 as $item) {
            $arrVatTuM724[$item->Ref_ChuKy][$item->Ref_MaVatTu] = $item->SoLuongDaQuyDoi;
        }

        // Lấy vật tư theo kế hoạch (Luu y khong dung duoc group by va sum o day do co truong hop ke hoach khong dinh ky)
        // Không join vật tự M724 ở đây được vì đơn vị tính khác nhau có thể không join được hoặc nếu bỏ điều kiện
        // đơn vị tính trong join với vật tư M724 sẽ có trường hợp sinh ra nhiều dòng cho 1 dòng
        $sql = sprintf('
            SELECT 
                VatTu.*
                , (IFNULL(VatTu.SoLuongDuKien, 0) * DonViTinh.HeSoQuyDoi) AS SoLuongDaQuyDoi
                , ChiTiet.Ref_ChuKy
                , SanPham.DonViTinh AS DonViTinhCoSo
            FROM OKeHoachTongThe AS KeHoach
            INNER JOIN OKeHoachBaoTri AS ChiTiet ON KeHoach.IOID = ChiTiet.Ref_KeHoachTongThe
            INNER JOIN OVatTuKeHoach AS VatTu ON ChiTiet.IFID_M837 = VatTu.IFID_M837
            INNER JOIN OSanPham AS SanPham ON VatTu.Ref_MaVatTu = SanPham.IOID
            INNER JOIN ODonViTinhSP AS DonViTinh ON SanPham.IFID_M113 = DonViTinh.IFID_M113 
                AND VatTu.Ref_DonViTinh = DonViTinh.IOID
            WHERE  ChiTiet.IFID_M837 IN (%1$s)                    
        ', implode(',', $arrIFIDs));

        $vatTu = $this->_o_DB->fetchAll($sql);

        // Tinh tong so luong vat tu
        foreach($vatTu as $item) {
            if($item->Ref_ChuKy) {
                if(!isset($arrItemForPeriod[$item->Ref_ChuKy][$item->Ref_MaVatTu])) {
                    $arrItemForPeriod[$item->Ref_ChuKy][$item->Ref_MaVatTu] = $item;
                    $arrItemForPeriod[$item->Ref_ChuKy][$item->Ref_MaVatTu]->SoLuongDaCong = 0;
                    $arrItemForPeriod[$item->Ref_ChuKy][$item->Ref_MaVatTu]->SoLuongM724   = 0;
                }

                $arrItemForPeriod[$item->Ref_ChuKy][$item->Ref_MaVatTu]->SoLuongDaCong += $item->SoLuongDaQuyDoi;
                if(isset($arrVatTuM724[$item->Ref_ChuKy][$item->Ref_MaVatTu])) {
                    $arrItemForPeriod[$item->Ref_ChuKy][$item->Ref_MaVatTu]->SoLuongM724 =
                        $arrVatTuM724[$item->Ref_ChuKy][$item->Ref_MaVatTu];
                }

            }
            else {
                if(!isset($arrItemForNonPeriod[$item->IFID_M837][$item->Ref_MaVatTu])) {
                    $arrItemForNonPeriod[$item->IFID_M837][$item->Ref_MaVatTu] = $item;
                    $arrItemForNonPeriod[$item->IFID_M837][$item->Ref_MaVatTu]->SoLuongDaCong = 0;
                    $arrItemForNonPeriod[$item->IFID_M837][$item->Ref_MaVatTu]->SoLuongM724   = 0;
                }

                $arrItemForNonPeriod[$item->IFID_M837][$item->Ref_MaVatTu]->SoLuongDaCong += $item->SoLuongDaQuyDoi;
                $arrItemForNonPeriod[$item->IFID_M837][$item->Ref_MaVatTu]->SoLuongM724   += $item->SoLuongDaQuyDoi;
            }
        }

        // Tình lần bảo trì gần nhất (Chỉ cho các kế hoạch có chu kỳ)
        $sql   = sprintf('
            SELECT *
            FROM
            (
                SELECT  OKeHoachBaoTri.Ref_ChuKy, OPhieuBaoTri.Ngay
                FROM OKeHoachBaoTri 
                INNER JOIN OPhieuBaoTri ON OKeHoachBaoTri.Ref_ChuKy = OPhieuBaoTri.Ref_ChuKy
                INNER JOIN qsiforms ON OPhieuBaoTri.IFID_M759 = qsiforms.IFID            
                WHERE OKeHoachBaoTri.IFID_M837 IN(%1$s) AND qsiforms.Status IN (3, 4)
                ORDER BY OKeHoachBaoTri.Ref_ChuKy, OPhieuBaoTri.Ngay DESC
                LIMIT 18446744073709551615
            ) AS PhieuBaoTri
            GROUP BY Ref_ChuKy
        ', implode(',', $arrIFIDs), $this->_o_DB->quote($mEDate));

        $lanBaoTriGanNhat = $this->_o_DB->fetchAll($sql);

        foreach($lanBaoTriGanNhat as $item) {
            $arrLastForPeriod[$item->Ref_ChuKy] = $item->Ngay;
        }

        // Gắn các tháng xảy ra vào mảng trả về
        // Gắn vật tư vào mảng trả về
        foreach ($arrRetval as $key=>$item) {
            if(!isset($arrRetval[$key]->ArrThangXayRa )) {
                $arrRetval[$key]->ArrThangXayRa = array();
            }

            if(!isset($arrRetval[$key]->ArrVatTu )) {
                $arrRetval[$key]->ArrVatTu = array();
            }

            if(!isset($arrRetval[$key]->RowspanByTask )) {
                $arrRetval[$key]->RowspanByTask = 0;
            }

            if(!isset($arrRetval[$key]->NgayBaoTriLanCuoi )) {
                $arrRetval[$key]->NgayBaoTriLanCuoi = '';
            }

            if(!isset($arrRowSpanByEquip[$item->Ref_MaThietBi])) {
                $arrRowSpanByEquip[$item->Ref_MaThietBi] = 0;
            }

            for($iM = 1; $iM <= 12; $iM++) {
                $arrRetval[$key]->{"Thang".$iM} = ''; // init
            }

            // Gắn tháng xảy ra và vật tư vào mảng
            if($item->Ref_ChuKy) {
                if(isset($arrMonthForPeriod[$item->Ref_ChuKy])) {
                    for($iM = 1; $iM <= 12; $iM++) {
                        if(in_array($iM, $arrMonthForPeriod[$item->Ref_ChuKy])) {
                            $arrRetval[$key]->{"Thang".$iM} = 'v';
                        }
                    }
                }

                if(isset($arrItemForPeriod[$item->Ref_ChuKy])) {
                    $soLuongVatTu                   = count($arrItemForPeriod[$item->Ref_ChuKy]);
                    $soLuongVatTu                   = $soLuongVatTu > 1?$soLuongVatTu:1;
                    $arrRetval[$key]->ArrVatTu      = $arrItemForPeriod[$item->Ref_ChuKy];
                    $arrRetval[$key]->RowspanByTask = $soLuongVatTu;
                    $arrRowSpanByEquip[$item->Ref_MaThietBi] +=  $soLuongVatTu;
                }
                else {
                    $arrRowSpanByEquip[$item->Ref_MaThietBi] += 1; // 1 dòng cho trường hợp không có vật tư
                }

                if(isset($arrLastForPeriod[$item->Ref_ChuKy])) {
                    $arrRetval[$key]->NgayBaoTriLanCuoi  = $arrLastForPeriod[$item->Ref_ChuKy];
                }
            }
            else {
                if(isset($arrMonthForNonPeriod[$item->IFID_M837])) {
                    for($iM = 1; $iM <= 12; $iM++) {
                        if(in_array($iM, $arrMonthForNonPeriod[$item->IFID_M837])) {
                            $arrRetval[$key]->{"Thang".$iM} = 'v';
                        }
                    }
                }

                if(isset($arrItemForNonPeriod[$item->IFID_M837])) {
                    $soLuongVatTu                   = count($arrItemForNonPeriod[$item->IFID_M837]);
                    $soLuongVatTu                   = $soLuongVatTu > 1?$soLuongVatTu:1;
                    $arrRetval[$key]->ArrVatTu      = $arrItemForNonPeriod[$item->IFID_M837];
                    $arrRetval[$key]->RowspanByTask = $soLuongVatTu;
                    $arrRowSpanByEquip[$item->Ref_MaThietBi] +=  $soLuongVatTu;
                }
                else {
                    $arrRowSpanByEquip[$item->Ref_MaThietBi] += 1; // 1 dòng cho trường hợp không có vật tư
                }
            }
        }

        // Gắn rowspan của từng thiết bị
        foreach ($arrRetval as $key=>$item) {
            if(!isset($arrRetval[$key]->RowspanByEquip )) {
                $arrRetval[$key]->RowspanByEquip = 0;
            }

            if(isset($arrRowSpanByEquip[$item->Ref_MaThietBi])) {
                $arrRetval[$key]->RowspanByEquip = $arrRowSpanByEquip[$item->Ref_MaThietBi];
            }
        }


        // echo '<pre>'; print_r($arrMonthForNonPeriod); die;

        return $arrRetval;
    }
}