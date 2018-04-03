<?php
class Qss_Model_M765_Statistic extends Qss_Model_Abstract {
    public function thongKeThongSoThietBi($locationIOID = 0, $eqGroupIOID = 0, $eqTypeIOID = 0, $eqIOID = 0) {
        $retval     = array();
        $oldThietBi = '';
        $oldDiemDo  = '';
        $i          = -1;
        $whereTong  = '';
        $where1     = ''; // số 1 và 2 nối với sub query tương ứng, không phải đặt vô nghĩa
        $where2     = '';

        if($locationIOID) {
            $objLocation = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
            $whereTong .= sprintf('
                AND IFNULL(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft <= %1$d AND rgt >= %2$d)
            ', $objLocation->lft, $objLocation->rgt);
            $where1    .= sprintf('
                AND IFNULL(ThietBi1.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft <= %1$d AND rgt >= %2$d)
            ', $objLocation->lft, $objLocation->rgt);
            $where2    .= sprintf('
                AND IFNULL(ThietBi2.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft <= %1$d AND rgt >= %2$d)
            ', $objLocation->lft, $objLocation->rgt);
        }

        if($eqGroupIOID) {
            $whereTong .= sprintf('AND IFNULL(ThietBi.Ref_NhomThietBi, 0) = %1$d ', $eqGroupIOID);
            $where1    .= sprintf('AND IFNULL(ThietBi1.Ref_NhomThietBi, 0) = %1$d ', $eqGroupIOID);
            $where2    .= sprintf('AND IFNULL(ThietBi2.Ref_NhomThietBi, 0) = %1$d ', $eqGroupIOID);
        }

        if($eqTypeIOID) {
            $objEquipType = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $eqTypeIOID));
            $whereTong .= sprintf('
                AND IFNULL(ThietBi.Ref_LoaiThietBi, 0) IN (SELECT IOID FROM OLoaiThietBi WHERE lft <= %1$d AND rgt >= %2$d)
            ', $objEquipType->lft, $objEquipType->rgt);
            $where1    .= sprintf('
                AND IFNULL(ThietBi1.Ref_LoaiThietBi, 0) IN (SELECT IOID FROM OLoaiThietBi WHERE lft <= %1$d AND rgt >= %2$d)
            ', $objEquipType->lft, $objEquipType->rgt);
            $where2    .= sprintf('
                AND IFNULL(ThietBi2.Ref_LoaiThietBi, 0) IN (SELECT IOID FROM OLoaiThietBi WHERE lft <= %1$d AND rgt >= %2$d)
            ', $objEquipType->lft, $objEquipType->rgt);
        }

        if($eqIOID) {
            $whereTong .= sprintf('AND IFNULL(ThietBi.IOID, 0) = %1$d ', $eqIOID);
            $where1    .= sprintf('AND IFNULL(ThietBi1.IOID, 0) = %1$d ', $eqIOID);
            $where2    .= sprintf('AND IFNULL(ThietBi2.IOID, 0) = %1$d ', $eqIOID);
        }

        // Lấy được tổng số hoạt động
        // Lấy được sau từng loại bảo trì hoạt động
        $sql = sprintf('
            SELECT
                ThietBi.IOID
                , ThietBi.MaThietBi
                , ThietBi.TenThietBi
                , ThietBi.MaKhuVuc
                , KhuVuc.Ten AS TenKhuVuc
                , DiemDo.Ma AS DiemDo
                , DiemDo.ChiSo
                , DiemDo.IOID AS Ref_DiemDoTongSo
                , TongSo.TongSoHoatDong AS TongSoHoatDong
                , SauLoaiBaoTri.Ref_LoaiBaoTri
                , SauLoaiBaoTri.SoHoatDong AS SoHoatDongSauBaoTri                
            FROM ODanhSachThietBi AS ThietBi   
            INNER JOIN ODanhSachDiemDo AS DiemDo ON ThietBi.IFID_M705 = DiemDo.IFID_M705
            LEFT JOIN OKhuVuc AS KhuVuc ON ThietBi.Ref_MaKhuVuc = KhuVuc.IOID
            LEFT JOIN (
                SELECT NhatTrinh1.Ref_MaTB, NhatTrinh1.Ref_DiemDo, NhatTrinh1.DiemDo, NhatTrinh1.ChiSo, SUM(IFNULL(NhatTrinh1.SoHoatDong, 0)) AS TongSoHoatDong 
                FROM ONhatTrinhThietBi AS NhatTrinh1
                INNER JOIN ODanhSachThietBi AS ThietBi1 ON NhatTrinh1.Ref_MaTB = ThietBi1.IOID
                WHERE 1=1 %1$s
                GROUP BY NhatTrinh1.Ref_MaTB, NhatTrinh1.Ref_DiemDo
            ) AS TongSo ON ThietBi.IOID = TongSo.Ref_MaTB AND DiemDo.IOID = TongSo.Ref_DiemDo
            LEFT JOIN (
                SELECT NgayBaoTriCuoiTheoLoai2.*, NhatTrinh2.Ref_DiemDo, SUM(IFNULL(NhatTrinh2.SoHoatDong, 0)) AS SoHoatDong 
                FROM ONhatTrinhThietBi AS NhatTrinh2
                INNER JOIN ODanhSachThietBi AS ThietBi2 ON NhatTrinh2.Ref_MaTB = ThietBi2.IOID
                INNER JOIN
                (
                    SELECT Ref_MaThietBi, Ref_LoaiBaoTri, LoaiBaoTri, Ngay 
                    FROM (
                        SELECT *
                        FROM OPhieuBaoTri
                        INNER JOIN qsiforms ON OPhieuBaoTri.IFID_M759 = qsiforms.IFID
                        WHERE qsiforms.STATUS IN (3, 4) -- Hoàn thành hoặc đóng
                        ORDER BY Ref_MaThietBi, Ref_LoaiBaoTri, Ngay DESC 
                        LIMIT 999999999999999
                    ) AS TempTable1
                    GROUP BY Ref_MaThietBi, Ref_LoaiBaoTri
                ) AS NgayBaoTriCuoiTheoLoai2 ON NhatTrinh2.Ref_MaTB = NgayBaoTriCuoiTheoLoai2.Ref_MaThietBi
                    AND NhatTrinh2.Ngay > NgayBaoTriCuoiTheoLoai2.Ngay
                WHERE 1=1 %2$s
                GROUP BY NgayBaoTriCuoiTheoLoai2.Ref_MaThietBi, NgayBaoTriCuoiTheoLoai2.Ref_LoaiBaoTri, NhatTrinh2.Ref_DiemDo                                              
            ) AS SauLoaiBaoTri ON ThietBi.IOID = SauLoaiBaoTri.Ref_MaThietBi AND TongSo.Ref_DiemDo = SauLoaiBaoTri.Ref_DiemDo
            WHERE 1=1 %3$s
            ORDER BY ThietBi.MaThietBi, DiemDo.Ma, SauLoaiBaoTri.LoaiBaoTri
        ', $where1, $where2, $whereTong);
        // echo '<pre>'; print_r($sql); die;
        $data = $this->_o_DB->fetchAll($sql);

        // 1 thiet bi co nhieu diem do, 1 diem do co nhieu loai bao tri
        foreach ($data as $item) {
            if($oldThietBi != $item->IOID || $oldDiemDo != $item->Ref_DiemDoTongSo) {
                $i++;
                $retval[$i] = $item;
            }
            $retval[$i]->{'SauLoai'.$item->Ref_LoaiBaoTri} = $item->SoHoatDongSauBaoTri;

            $oldDiemDo  = $item->Ref_DiemDoTongSo;
            $oldThietBi = $item->IOID;
        }

        // echo '<pre>'; print_r($retval); die;
        return $retval;
    }

    public function deleteByIFIDs($IFIDs) {
        $IFIDs[] = 0;

        // Xóa trong bảng nhật trình thiết bị
        $sql = sprintf('
            DELETE FROM ONhatTrinhThietBi WHERE IFID_M765 IN (%1$s);
        ', implode(',', $IFIDs));

        $this->_o_DB->execute($sql);

        // Xóa trong bảng qsiforms
        $sql = sprintf('
            DELETE FROM qsiforms WHERE IFID IN (%1$s) AND FormCode = "M765";
        ', implode(',', $IFIDs));

        $this->_o_DB->execute($sql);
    }
}