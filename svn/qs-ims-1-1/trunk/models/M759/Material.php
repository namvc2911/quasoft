<?php
class Qss_Model_M759_Material extends Qss_Model_Abstract {

    public function getTotalMaterialsOfM759($start, $end, $locIOID = 0, $equipGroupIOID = 0,  $equipTypeIOID = 0, $eqIOID = 0) {
        $retval = array();
        $loc    = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', (int)$equipTypeIOID));
        $where  = '';
        $where .= $loc?sprintf(' AND (ODanhSachThietBi.Ref_MaKhuVuc IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $loc->lft, $loc->rgt):'';
        $where .= ($eqIOID)?sprintf(' AND ODanhSachThietBi.IOID = %1$d  ', $eqIOID):'';
        $where .= $type?sprintf(' AND (ODanhSachThietBi.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $type->lft, $type->rgt):'';
        $where .= ($equipGroupIOID)?sprintf(' AND IFNULL(ODanhSachThietBi.Ref_NhomThietBi, 0) = %1$d  ', $equipGroupIOID):'';

        $sqlPhieuBaoTri = sprintf('
            SELECT 
                OVatTuPBT.*
                , OSanPham.DonViTinh
                , OSanPham.MaTam
                , SUM(IFNULL(OVatTuPBT.SoLuong, 0) * IFNULL(ODonViTinhSP.HeSoQuyDoi, 0)) AS SoLuongThucTe
            FROM OVatTuPBT
            INNER JOIN OSanPham ON OVatTuPBT.Ref_MaVatTu = OSanPham.IOID
            INNER JOIN OPhieuBaoTri ON OVatTuPBT.IFID_M759 = OPhieuBaoTri.IFID_M759
            INNER JOIN qsiforms ON OPhieuBaoTri.IFID_M759 = qsiforms.IFID                
            INNER JOIN ODonViTinhSP ON OVatTuPBT.Ref_DonViTinh = ODonViTinhSP.IOID
            LEFT JOIN ODanhSachThietBi ON OPhieuBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID
            WHERE (OPhieuBaoTri.NgayBatDau BETWEEN %1$s AND %2$s) AND qsiforms.Status IN (3, 4) %3$s
            GROUP BY IFNULL(OVatTuPBT.Ref_MaVatTu, 0), IF(IFNULL(OSanPham.MaTam, 0) = 1, OVatTuPBT.TenVatTu, 1)   
        ', $this->_o_DB->quote($start), $this->_o_DB->quote($end), $where);
        $objPhieuBaoTri = $this->_o_DB->fetchAll($sqlPhieuBaoTri);

        foreach($objPhieuBaoTri as $item) {

            if($item->MaTam) {
                $key = (int)$item->Ref_MaVatTu .'-' . $item->TenVatTu .'-' .(int)$item->Ref_DonViTinh;
            }
            else {
                $key = (int)$item->Ref_MaVatTu;
            }


            if(!isset($retval[$key])) {
                $temp = clone($item);
                $retval[$key] = $temp;
                $retval[$key]->SoLuong = 0;
            }

            $retval[$key]->SoLuong += $item->SoLuongThucTe;
        }

        return $retval;
    }

    public function trackMaterials($start, $end, $locIOID = 0, $equipGroupIOID = 0,  $equipTypeIOID = 0, $eqIOID = 0) {
        // Hàm sẽ trả về một mảng vật tư bao gồm số lượng thực tế từ phiếu bảo trì
        // Số lượng kế hoạch định kỳ lấy từ thiết lập chu kỳ bảo trì M724
        // Số lượng kế hoạch tổng thế lấy từ kế hoạch tổng thể M838
        $mM724Material = new Qss_Model_M724_Material();
        $mM838Material = new Qss_Model_M838_Material();

        $retval      = array();
        $dinhKy      = $mM724Material->getTotalMaterialsOfM724($start, $end, $locIOID, $equipGroupIOID,  $equipTypeIOID, $eqIOID);
        $tongThe     = $mM838Material->getTotalMaterialsOfM838($start, $end, $locIOID, $equipGroupIOID,  $equipTypeIOID, $eqIOID);
        $phieuBaoTri = $this->getTotalMaterialsOfM759($start, $end, $locIOID, $equipGroupIOID,  $equipTypeIOID, $eqIOID);

        foreach ($dinhKy as $key=>$item) {
            $retval[$key]['MaVatTu']       = $item->MaVatTu;
            $retval[$key]['TenVatTu']      = $item->TenVatTu;
            $retval[$key]['DonViTinh']     = $item->DonViTinh;
            $retval[$key]['SoLuongDinhKy'] = $item->SoLuong;
        }

        foreach ($tongThe as $key=>$item) {
            $retval[$key]['MaVatTu']        = $item->MaVatTu;
            $retval[$key]['TenVatTu']       = $item->TenVatTu;
            $retval[$key]['DonViTinh']      = $item->DonViTinh;
            $retval[$key]['SoLuongTongThe'] = $item->SoLuong;
        }

        foreach ($phieuBaoTri as $key=>$item) {
            $retval[$key]['MaVatTu']       = $item->MaVatTu;
            $retval[$key]['TenVatTu']      = $item->TenVatTu;
            $retval[$key]['DonViTinh']     = $item->DonViTinh;
            $retval[$key]['SoLuongBaoTri'] = $item->SoLuong;
        }

        return $retval;
    }

    public function compareWorkorderWithGeneralPlan($start, $end, $locIOID = 0, $equipGroupIOID = 0,  $equipTypeIOID = 0, $eqIOID = 0) {

        $loc    = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', (int)$equipTypeIOID));
        $where  = '';
        $where .= $loc?sprintf(' AND (ODanhSachThietBi.Ref_MaKhuVuc IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $loc->lft, $loc->rgt):'';
        $where .= ($eqIOID)?sprintf(' AND ODanhSachThietBi.IOID = %1$d  ', $eqIOID):'';
        $where .= $type?sprintf(' AND (ODanhSachThietBi.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $type->lft, $type->rgt):'';
        $where .= ($equipGroupIOID)?sprintf(' AND IFNULL(ODanhSachThietBi.Ref_NhomThietBi, 0) = %1$d  ', $equipGroupIOID):'';

        // Lưu ý không cho khu vực vào trong hàm này, nếu muốn sắp xếp theo khu vực thì viết riêng ra ngoài
        // tránh hàm càng ngày càng phức tạp. (Tương tự với các xắp xếp khác)
        $sql = sprintf('
            SELECT 
                MatHang.*
                , IFNULL(VatTuPhieuBaoTri.SoLuongThucTe, 0) AS SoLuongThucTe
                , IFNULL(VatTuKeHoachTongThe.SoLuongKeHoach, 0) AS SoLuongKeHoach
                , (IFNULL(VatTuPhieuBaoTri.SoLuongThucTe, 0) - IFNULL(VatTuKeHoachTongThe.SoLuongKeHoach, 0)) AS SoLuongChenhLech
                -- Nếu sử dụng mã tạm thì phải liệt kê tên theo tên vật tư được ghi
                , IF(IFNULL(MatHang.MaTam, 0) = 1, IFNULL(VatTuPhieuBaoTri.TenVatTu, VatTuKeHoachTongThe.TenVatTu), MatHang.TenSanPham) AS TenSanPham
            FROM OSanPham AS MatHang
            -- Cong tong vat tu theo thiet bi thuc te su dung, yêu cầu phiếu bảo trì phải hoàn thành hoặc đã đóng
            LEFT JOIN (
                SELECT 
                    OVatTuPBT.*
                    , SUM(IFNULL(OVatTuPBT.SoLuong, 0) * IFNULL(ODonViTinhSP.HeSoQuyDoi, 0)) AS SoLuongThucTe
                FROM OVatTuPBT
                INNER JOIN OSanPham ON OVatTuPBT.Ref_MaVatTu = OSanPham.IOID
                INNER JOIN OPhieuBaoTri ON OVatTuPBT.IFID_M759 = OPhieuBaoTri.IFID_M759
                INNER JOIN qsiforms ON OPhieuBaoTri.IFID_M759 = qsiforms.IFID                
                INNER JOIN ODonViTinhSP ON OVatTuPBT.Ref_DonViTinh = ODonViTinhSP.IOID
                LEFT JOIN ODanhSachThietBi ON OPhieuBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID
                WHERE (OPhieuBaoTri.NgayBatDau BETWEEN %1$s AND %2$s) AND qsiforms.Status IN (3, 4) %3$s
                GROUP BY IFNULL(OVatTuPBT.Ref_MaVatTu, 0)
                    , IF(IFNULL(OSanPham.MaTam, 0) = 1, OVatTuPBT.TenVatTu, 1)             
            ) AS VatTuPhieuBaoTri ON MatHang.IOID = VatTuPhieuBaoTri.Ref_MaVatTu
            -- Cong tong vat tu theo thiet bi ke hoach de ra, yêu cầu không có kế hoạch tổng thể hoặc đã được duyệt
            -- Đồng thời chi tiết cũng được duyệt
            -- Lưu ý có thể không có kể hoạch tổng thể mà vẫn có kế hoạch chi tiết vì vậy phải left join
            LEFT JOIN (
                SELECT 
                    OVatTuKeHoach.*
                    , SUM(IFNULL(OVatTuKeHoach.SoLuongDuKien, 0) * IFNULL(ODonViTinhSP.HeSoQuyDoi, 0)) AS SoLuongKeHoach
                FROM OVatTuKeHoach
                INNER JOIN OSanPham ON OVatTuKeHoach.Ref_MaVatTu = OSanPham.IOID
                INNER JOIN OKeHoachBaoTri ON OVatTuKeHoach.IFID_M837 = OKeHoachBaoTri.IFID_M837                
                INNER JOIN qsiforms  AS IformChiTiet ON OKeHoachBaoTri.IFID_M837 = IformChiTiet.IFID                                
                INNER JOIN ODonViTinhSP ON OVatTuKeHoach.Ref_DonViTinh = ODonViTinhSP.IOID
                LEFT JOIN OKeHoachTongThe ON  IFNULL(OKeHoachBaoTri.Ref_KeHoachTongThe, 0) = OKeHoachTongThe.IOID
                LEFT JOIN qsiforms AS IformTongThe ON OKeHoachTongThe.IFID_M838 = IformTongThe.IFID  
                LEFT JOIN ODanhSachThietBi ON OKeHoachBaoTri.Ref_MaThietBi = ODanhSachThietBi.IOID
                WHERE (OKeHoachBaoTri.NgayBatDau BETWEEN %1$s AND %2$s) 
                    AND (OKeHoachTongThe.IOID is null OR IformTongThe.Status = 3)
                    AND (IformChiTiet.Status = 2) 
                    %3$s
                GROUP BY IFNULL(OVatTuKeHoach.Ref_MaVatTu, 0)
                    , IF(IFNULL(OSanPham.MaTam, 0) = 1, OVatTuKeHoach.TenVatTu, 1)
            ) AS VatTuKeHoachTongThe ON MatHang.IOID = VatTuKeHoachTongThe.Ref_MaVatTu    
            -- Chỉ lấy ra những vật tư có trong kế hoạch hoặc trong phiếu bảo trì                
            WHERE (VatTuPhieuBaoTri.SoLuong > 0 OR VatTuKeHoachTongThe.SoLuong > 0)
            ORDER BY MaSanPham
        ', $this->_o_DB->quote($start), $this->_o_DB->quote($end), $where);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy toàn bộ Vật tư của phiếu bảo trì bao gồm cả kế hoạch lẫn thực tế (Những vật tư có ở cả hai bên và từng bên có riêng biệt)
     * @param array $orderIFIDArr
     * @return mixed
     */
    public function getMaterialsByIFIDs($orderIFIDArr = array()) {
        $orderIFIDArr[] = 0;

        // Công viêc >> Vị trí >> Vật tư
        $sql = sprintf('
            SELECT 
                VatTu.*
                , group_concat(
                    DISTINCT
                    IF(VatTu.SoLuongThucTe > 0 
                        ,  concat(VatTu.MaVatTu,\': \', VatTu.SoLuongThucTe, \' \', VatTu.DonViTinh) 
                        , null
                    )
                    SEPARATOR \'<br>\'
                ) as VatTuThucTeMau1
				, group_concat(
				    DISTINCT
				    IF(VatTu.SoLuongThucTe > 0 
				        ,  concat(VatTu.TenVatTu,\' (\', VatTu.MaVatTu ,\') : \', VatTu.SoLuongThucTe, \' \', VatTu.DonViTinh) 				        
				        , null
				    )
				    SEPARATOR \'<br>\'
                ) as VatTuThucTeMau2  
                , group_concat(
                    DISTINCT
                    IF(VatTu.SoLuongKeHoach > 0 
                        ,  concat(VatTu.MaVatTu,\': \', VatTu.SoLuongKeHoach, \' \', VatTu.DonViTinh) 
                        , null
                    )
                    SEPARATOR \'<br>\'

                ) as VatTuKeHoachMau1
				, group_concat(
				    DISTINCT
				    IF(VatTu.SoLuongKeHoach > 0 
				        ,  concat(VatTu.TenVatTu,\' (\', VatTu.MaVatTu ,\') : \', VatTu.SoLuongKeHoach, \' \', VatTu.DonViTinh) 				        
				        , null
				    )
				    SEPARATOR \'<br>\'
                ) as VatTuKeHoachMau2                        			            
            FROM 
            (
                -- Toàn bộ vật tư thực tế
                SELECT
                    VatTuThucTe.IFID_M759
                    , VatTuThucTe.Ref_ViTri                
                    , VatTuThucTe.ViTri
                    , VatTuThucTe.BoPhan             
                    , VatTuThucTe.Ref_MaVatTu
                    , VatTuThucTe.MaVatTu
                    , VatTuThucTe.TenVatTu
                    , VatTuThucTe.DonViTinh
                    , VatTuDuKien.SoLuong AS SoLuongKeHoach
                    , VatTuThucTe.SoLuong AS SoLuongThucTe
                    , VatTuThucTe.CongViec
                FROM OVatTuPBT AS VatTuThucTe
                LEFT JOIN OVatTuPBTDK AS VatTuDuKien ON VatTuThucTe.Ref_CongViec = VatTuDuKien.Ref_CongViec
                    AND VatTuThucTe.Ref_ViTri = VatTuDuKien.Ref_ViTri
                    AND VatTuThucTe.Ref_MaVatTu = VatTuDuKien.Ref_MaVatTu
                WHERE VatTuThucTe.IFID_M759 in (%1$s)
                
                UNION ALL
                
                -- Vật tư dự kiến không có trong thực tế
                SELECT
                    VatTuDuKien.IFID_M759
                    , VatTuDuKien.Ref_ViTri
                    , VatTuDuKien.ViTri
                    , VatTuDuKien.BoPhan
                    , VatTuDuKien.Ref_MaVatTu
                    , VatTuDuKien.MaVatTu
                    , VatTuDuKien.TenVatTu
                    , VatTuDuKien.DonViTinh
                    , VatTuDuKien.SoLuong AS SoLuongKeHoach
                    , 0 AS SoLuongThucTe
                    , VatTuDuKien.CongViec
                FROM OVatTuPBTDK AS VatTuDuKien
                LEFT JOIN OVatTuPBT AS VatTuThucTe ON VatTuDuKien.Ref_CongViec = VatTuThucTe.Ref_CongViec 
                    AND VatTuDuKien.Ref_ViTri = VatTuThucTe.Ref_ViTri 
                    AND VatTuDuKien.Ref_MaVatTu = VatTuThucTe.Ref_MaVatTu            
                WHERE VatTuDuKien.IFID_M759 in (%1$s) AND IFNULL(VatTuThucTe.IOID, 0) = 0
            ) AS VatTu
            GROUP BY VatTu.IFID_M759, VatTu.Ref_ViTri
            ORDER BY VatTu.IFID_M759, VatTu.Ref_ViTri
        ', implode(',', $orderIFIDArr));
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}