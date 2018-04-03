<?php
class Qss_Model_M838_Material extends Qss_Model_Abstract {

    /**
     * Hàm lấy vật tư của kê hoạch theo khoảng thời gian với số lượng cộng tổng lại theo từng vật tư.
     * @param $start
     * @param $end
     * @param $locIOID
     * @param $workcenterIOID
     * @param $equipGroupIOID
     * @param $equipTypeIOID
     * @param $eqIOID
     */
    public function getTotalMaterialsOfM838(
        $start
        , $end
        , $locIOID        = 0
        , $equipGroupIOID = 0
        , $equipTypeIOID  = 0
        , $eqIOID         = 0
    ) {
        $retval = array();
        $loc    = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', (int)$equipTypeIOID));
        $where  = '';
        $where .= $loc?sprintf(' AND (ODanhSachThietBi.Ref_MaKhuVuc IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))', $loc->lft, $loc->rgt):'';
        $where .= ($eqIOID)?sprintf(' AND ODanhSachThietBi.IOID = %1$d  ', $eqIOID):'';
        $where .= $type?sprintf(' AND (ODanhSachThietBi.Ref_LoaiThietBi IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $type->lft, $type->rgt):'';
        $where .= ($equipGroupIOID)?sprintf(' AND IFNULL(ODanhSachThietBi.Ref_NhomThietBi, 0) = %1$d  ', $equipGroupIOID):'';


        $sqTongThe = sprintf('
            SELECT 
                OVatTuKeHoach.*
                , OSanPham.DonViTinh
                , OSanPham.MaTam
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
        ', $this->_o_DB->quote($start), $this->_o_DB->quote($end), $where);
        $objTongThe = $this->_o_DB->fetchAll($sqTongThe);

        foreach($objTongThe as $item) {
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

            $retval[$key]->SoLuong += $item->SoLuongKeHoach;
        }

        return $retval;
    }
}