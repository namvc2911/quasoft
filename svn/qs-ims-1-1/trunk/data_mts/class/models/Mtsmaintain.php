<?php
class Qss_Model_Mtsmaintain extends Qss_Model_Abstract
{
    /**
     * Lấy kế hoạch bao gồm cả phần thực hiện (Phiếu bảo trì đã đóng hoặc hoàn thành)
     * @note: Lưu ý query lấy trong và ngoài kế hoạch phải có số trường giống hệt nhau
     * @note: Customize riêng cho mts
     * @param $start
     * @param $end
     * @return mixed
     */
    public function getGeneralPlans($year, $location = 0, $type= 0) {

        $whereSql1 = '';
        $whereSql2 = '';

        if($location) {
            $objLoc = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $location));

            if($objLoc) {
                $whereSql1 .= sprintf('
                    AND (
                        IFNULL(KeHoach.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)
                        OR
                        IFNULL(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)
                    )                    
                ', $objLoc->lft, $objLoc->rgt);
            }
        }

        if($type) {
            $whereSql1 .= sprintf(' AND IFNULL(KeHoach.LoaiKeHoach, 0) = %1$d ', $type);
        }

        $sql = sprintf('
            -- Lay tat ca ke hoach trong khoang left join voi phieu bao tri           
            -- Lay tat ca phieu bao tri trong khoang khong co ke hoach
            -- Noi hai dieu kien tren lai ta duoc bao cao theo doi thuc hien
            -- @Note: Lưu ý ở đây sử dụng inner join với thiết bị, nhóm loại và khu vực do các
            -- trường này bắt buộc trong phiếu bảo trì và danh sách thiết bị nếu có thay đổi cần phải sửa lại phần này
            
            SELECT
                KeHoach.TenThietBi
                , KeHoach.LoaiKeHoach
                , TongThe.Ma AS SoKeHoach
                , KeHoach.Ref_LoaiBaoTri
                , KeHoach.LoaiBaoTri                
                , KeHoach.MoTa
                , NhomThietBi.IOID AS NhomThietBiIOID
                , LoaiThietBi.IOID AS LoaiThietBiIOID
                , KhuVuc.IOID AS KhuVucIOID
                , KhuVuc.Ten AS TenKhuVuc
                , ThietBi.NhomThietBi
                , ThietBi.LoaiThietBi                
                , IF(IFNULL(IformPhieuBaoTri.IFID,0) != 0, IFNULL(PhieuBaoTri.NgayBatDau, ""), "") AS NgayBatDau
                , IF(IFNULL(IformPhieuBaoTri.IFID,0) != 0, IFNULL(PhieuBaoTri.TuLam, 0), 0) AS TuLam
                , IF(IFNULL(IformPhieuBaoTri.IFID,0) != 0, IFNULL(PhieuBaoTri.TrongCongTy, 0), 0) AS TrongCongTy
                , IF(IFNULL(IformPhieuBaoTri.IFID,0) != 0, IFNULL(PhieuBaoTri.TrongTKV, 0), 0) AS TrongTKV
                , IF(IFNULL(IformPhieuBaoTri.IFID,0) != 0, IFNULL(PhieuBaoTri.NgoaiTKV, 0), 0) AS NgoaiTKV
                , IF(IFNULL(IformPhieuBaoTri.IFID,0) != 0, (
                    IFNULL(PhieuBaoTri.TuLam, 0) + IFNULL(PhieuBaoTri.TrongCongTy, 0) 
                    + IFNULL(PhieuBaoTri.TrongTKV, 0) + IFNULL(PhieuBaoTri.NgoaiTKV, 0)
                    ), 0
                ) AS TongSo                
                , (
                    IFNULL(KeHoach.TuLam, 0) + IFNULL(KeHoach.TrongCongTy, 0) 
                    + IFNULL(KeHoach.TrongTKV, 0) + IFNULL(KeHoach.NgoaiTKV, 0)
                ) AS TongSoKeHoach                
                , NhomThietBi.lft AS NhomThietBiLft
                , LoaiThietBi.lft AS LoaiThietBiLft     
                , KhuVuc.lft AS KhuVucLft
                , LoaiBaoTri.lft AS LoaiBaoTriLft
            FROM OKeHoachBaoTri AS KeHoach
            INNER JOIN qsiforms AS IFormChiTiet ON KeHoach.IFID_M837 = IFormChiTiet.IFID
            INNER JOIN OKeHoachTongThe AS TongThe ON IFNULL(KeHoach.Ref_KeHoachTongThe, 0) = TongThe.IOID
            INNER JOIN qsiforms AS IFormChinh ON TongThe.IFID_M838 = IFormChinh.IFID
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(KeHoach.Ref_MaThietBi, 0) = ThietBi.IOID
            INNER JOIN OKhuVuc AS KhuVuc ON ThietBi.Ref_MaKhuVuc = KhuVuc.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN ONhomThietBi AS NhomThietBi ON IFNULL(ThietBi.Ref_NhomThietBi, 0) = NhomThietBi.IOID
            INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON IFNULL(KeHoach.Ref_LoaiBaoTri, 0) = LoaiBaoTri.IOID
            LEFT JOIN OPhieuBaoTri AS PhieuBaoTri ON KeHoach.IOID = PhieuBaoTri.Ref_NgayKeHoach
            LEFT JOIN qsiforms AS IformPhieuBaoTri ON PhieuBaoTri.IFID_M759 = IformPhieuBaoTri.IFID AND IformPhieuBaoTri.Status IN (3, 4)
            WHERE KeHoach.DeptID in (%1$s) AND TongThe.Nam = %2$d AND IFormChinh.Status = 3 %3$s
                                                                      
            -- ORDER BY KhuVuc.lft, KeHoach.LoaiBaoTri, NhomThietBi.lft, LoaiThietBi.lft, KeHoach.LoaiKeHoach, KeHoach.TenThietBi
        ', $this->_user->user_dept_list, $year, $whereSql1, $whereSql2);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy tất cả phiếu bảo trì đột xuất, không có kế hoạch
     * @note: Lưu ý query lấy trong và ngoài kế hoạch phải có số trường giống hệt nhau
     * @note: Customize riêng cho mts
     */
    public function getDoneWorkOrdersNotPlan($year, $location = 0, $type= 0) {

        $whereSql1 = '';
        $start     = '01-01-'.$year;
        $end       = '31-12-'.$year;

        if($location) {
            $objLoc = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $location));

            if($objLoc) {
                $whereSql1 .= sprintf('
                    AND (
                        IFNULL(PhieuBaoTri.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)
                        OR
                        IFNULL(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d)
                    )                    
                ', $objLoc->lft, $objLoc->rgt);
            }
        }

        switch ($type) {
            case 1: // Quy 1
                $start = '01-01-'+year;
                $end   = '31-03-'+year;
            break;
            case 2: // Quy 2
                $start = '01-04-'.$year;
                $end   = '30-06-'.$year;
            break;
            case 3: // Quy 3
                $start = '01-07-'.$year;
                $end   = '30-09-'.$year;
            break;
            case 4: // Quy 4
                $start = '01-10-'.$year;
                $end   = '31-12-'.$year;
            break;
            case 5: // 6 thang dau nam
                $start = '01-01-'.$year;
                $end   = '30-06-'.$year;
            break;
            case 6: // 6 thang cuoi nam
                $start = '01-07-'.$year;
                $end   = '31-12-'.$year;
            break;
            case 7: // Ca nam
                $start = '01-01-'.$year;
                $end   = '31-12-'.$year;
            break;

            default:
                $start = '01-01-'.$year;
                $end   = '31-12-'.$year;
            break;
        }

        $whereSql1 .= sprintf(' AND ( PhieuBaoTri.NgayBatDau between %1$s AND %2$s )'
            , $this->_o_DB->quote($start)
            , $this->_o_DB->quote($end));

        $sql = sprintf('
            SELECT 
                PhieuBaoTri.TenThietBi
                , NULL AS LoaiKeHoach
                , NULL AS SoKeHoach
                , PhieuBaoTri.Ref_LoaiBaoTri
                , PhieuBaoTri.LoaiBaoTri                
                , PhieuBaoTri.MoTa
                , NhomThietBi.IOID AS NhomThietBiIOID
                , LoaiThietBi.IOID AS LoaiThietBiIOID
                , KhuVuc.IOID AS KhuVucIOID
                , KhuVuc.Ten AS TenKhuVuc
                , ThietBi.NhomThietBi
                , ThietBi.LoaiThietBi                
                , IFNULL(PhieuBaoTri.NgayBatDau, "") AS NgayBatDau
                , IFNULL(PhieuBaoTri.TuLam, 0) AS TuLam
                , IFNULL(PhieuBaoTri.TrongCongTy, 0) AS TrongCongTy
                , IFNULL(PhieuBaoTri.TrongTKV, 0) AS TrongTKV
                , IFNULL(PhieuBaoTri.NgoaiTKV, 0) AS NgoaiTKV
                , (
                    IFNULL(PhieuBaoTri.TuLam, 0) + IFNULL(PhieuBaoTri.TrongCongTy, 0) 
                    + IFNULL(PhieuBaoTri.TrongTKV, 0) + IFNULL(PhieuBaoTri.NgoaiTKV, 0)
                ) AS TongSo                
                , NULL AS TongSoKeHoach                
                , NhomThietBi.lft AS NhomThietBiLft
                , LoaiThietBi.lft AS LoaiThietBiLft     
                , KhuVuc.lft AS KhuVucLft
                , LoaiBaoTri.lft AS LoaiBaoTriLft
            FROM OPhieuBaoTri AS PhieuBaoTri
            INNER JOIN ODanhSachThietBi AS ThietBi ON IFNULL(PhieuBaoTri.Ref_MaThietBi, 0) = ThietBi.IOID
            INNER JOIN OKhuVuc AS KhuVuc ON ThietBi.Ref_MaKhuVuc = KhuVuc.IOID
            INNER JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            INNER JOIN ONhomThietBi AS NhomThietBi ON IFNULL(ThietBi.Ref_NhomThietBi, 0) = NhomThietBi.IOID
            INNER JOIN OPhanLoaiBaoTri AS LoaiBaoTri ON IFNULL(PhieuBaoTri.Ref_LoaiBaoTri, 0) = LoaiBaoTri.IOID            
            INNER JOIN qsiforms AS IformPhieuBaoTri ON PhieuBaoTri.IFID_M759 = IformPhieuBaoTri.IFID 
            WHERE PhieuBaoTri.DeptID in (%1$s)  %2$s
                -- AND IformPhieuBaoTri.Status IN (3, 4) 
                AND IFNULL(Ref_NgayKeHoach, 0) = 0
        ', $this->_user->user_dept_list, $whereSql1);

        return $this->_o_DB->fetchAll($sql);
    }

    public function getTheoDoiThucHienBaoTri($year, $location = 0, $type= 0) {
        // @note: Lưu ý query lấy trong và ngoài kế hoạch phải có số trường giống hệt nhau
        $trongKeHoach = $this->getGeneralPlans($year, $location, $type); // Kế hoạch bao gồm cả phiếu bảo trì của kế hoạch tổng thể
        $ngoaiKeHoach = $this->getDoneWorkOrdersNotPlan($year, $location, $type); // Các phiếu bảo trì đã hoàn thành hoặc đóng không có kế hoạch tổng thể
        $tempRetval   = array();
        $retval       = array();
        $index         = 0;

        foreach($trongKeHoach as $item) {
            $orderKey  = $item->KhuVucLft;
            $orderKey .= '-'.$item->LoaiBaoTriLft;
            $orderKey .= '-'.$item->NhomThietBiLft;
            $orderKey .= '-'.$item->LoaiThietBiLft;
            $orderKey .= '-'.$item->LoaiKeHoach;
            $orderKey .= '-'.$item->TenThietBi;

            // Gắn vào mảng
            $tempRetval[$orderKey] = $item;
        }

        foreach($ngoaiKeHoach as $item) {
            $orderKey  = $item->KhuVucLft;
            $orderKey .= '-'.$item->LoaiBaoTriLft;
            $orderKey .= '-'.$item->NhomThietBiLft;
            $orderKey .= '-'.$item->LoaiThietBiLft;
            $orderKey .= '-'.$item->LoaiKeHoach;
            $orderKey .= '-'.$item->TenThietBi;

            $tempRetval[$orderKey] = $item;
        }

        // Sắp xếp lại mảng trả về theo key order
        ksort($tempRetval);

        // Chuyển về mảng có key là index đơn giản
        foreach ($tempRetval as $item) {
            $retval[$index] = $item; // Chuyển về mảng đơn giản
            $index++;
        }

        return $retval;
    }
    function implementByLocation()
    {
  		$sql = sprintf('
            select OKhuVuc.Ten, sum(ifnull(TuLam,0)) +sum(ifnull(TrongCongTy,0))+sum(ifnull(TrongTKV,0))+sum(ifnull(NgoaiTKV,0)) as TongSo
            from OPhieuBaoTri
            inner join ODanhSachThietBi on ODanhSachThietBi.IOID = OPhieuBaoTri.Ref_MaThietBi
            inner join OKhuVuc on OKhuVuc.IOID =  ODanhSachThietBi.Ref_MaKhuVuc
            where year(Ngay) = YEAR(CURDATE()) 
            group by OKhuVuc.IOID
            order by OKhuVuc.lft');
        return $this->_o_DB->fetchAll($sql);  	
    }
}