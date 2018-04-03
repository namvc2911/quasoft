<?php

class Qss_Model_Maintenance_Asset extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getHandoverAssetsBaseOnClawBackLikeString($strAsset, $clawbackIOID = 0, $employee = 0)
    {
        $where = '';
        if($strAsset)
        {
            $where .=  sprintf(' AND (ODanhMucCongCuDungCu.Ma like "%%%1$s%%" OR ODanhMucCongCuDungCu.Ten like "%%%1$s%%")',$strAsset);
        }

        if($clawbackIOID != 0)
        {
            if($employee)
            {
                $where .= sprintf(' AND (IFNULL(OChiTietThuHoiTaiSan.Ref_MaNhanVienMoi, 0) = %1$d OR IFNULL(OChiTietThuHoiTaiSan.Ref_MaNhanVienMoi, 0) = 0)', $employee);
            }

            $sql = sprintf('
                SELECT
                    ODanhMucCongCuDungCu.*
                    , (IFNULL(OChiTietThuHoiTaiSan.SoLuong, 0) - IFNULL(PhieuBanGiao.SoLuong, 0)) AS SoLuongConLai
                    , PhieuBanGiao.SoPhieu AS PhieuBanGiao
                    , PhieuBanGiao.SoLuong AS SoLuongDaBanGiao
                FROM OPhieuThuHoiTaiSan
                INNER JOIN OChiTietThuHoiTaiSan ON OPhieuThuHoiTaiSan.IFID_M183 = OChiTietThuHoiTaiSan.IFID_M183
                INNER JOIN ODanhMucCongCuDungCu ON OChiTietThuHoiTaiSan.Ref_MaTaiSan = ODanhMucCongCuDungCu.IOID
                LEFT JOIN (
                    SELECT
                        IFNULL(OPhieuBanGiaoTaiSan.Ref_PhieuThuHoi, 0) AS Ref_PhieuThuHoi
                        , IFNULL(OChiTietBanGiaoTaiSan.Ref_MaTaiSan, 0) AS Ref_MaTaiSan
                        , IFNULL(OChiTietBanGiaoTaiSan.Ref_MaNhanVien, 0) AS Ref_MaNhanVien
                        , SUM( IFNULL(OChiTietBanGiaoTaiSan.SoLuong, 0) ) AS SoLuong
                        , GROUP_CONCAT(OPhieuBanGiaoTaiSan.SoPhieu separator ", " ) AS SoPhieu
                    FROM OPhieuBanGiaoTaiSan
                    INNER JOIN OChiTietBanGiaoTaiSan ON OPhieuBanGiaoTaiSan.IFID_M182 = OChiTietBanGiaoTaiSan.IFID_M182
                    WHERE IFNULL(OPhieuBanGiaoTaiSan.Ref_PhieuThuHoi, 0) = %1$d
                    GROUP BY OChiTietBanGiaoTaiSan.Ref_MaNhanVien, OChiTietBanGiaoTaiSan.Ref_MaTaiSan
                ) AS PhieuBanGiao ON OPhieuThuHoiTaiSan.IOID = PhieuBanGiao.Ref_PhieuThuHoi
                    AND ODanhMucCongCuDungCu.IOID = PhieuBanGiao.Ref_MaTaiSan
                    AND IFNULL(OChiTietThuHoiTaiSan.Ref_MaNhanVienMoi, 0) = PhieuBanGiao.Ref_MaNhanVien
                WHERE OPhieuThuHoiTaiSan.IOID = %1$d
                    %2$s
                LIMIT 100
            ', $clawbackIOID, $where);
        }
        else
        {
            $sql = sprintf('
                SELECT *
                FROM ODanhMucCongCuDungCu
                WHERE 1=1 %1$s
                ORDER BY Ma
                LIMIT 100
            ', $where);
        }
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }


    public function getHandoverEmployeesBaseOnClawBack($clawbackIOID, $asset = 0)
    {
        if($asset)
        {
            $where = sprintf(' AND IFNULL(OChiTietThuHoiTaiSan.Ref_MaTaiSan, 0) = %1$d', $asset);
        }

        $sql = sprintf('
        SELECT
            ODanhSachNhanVien.*
            , ODanhSachNhanVien.IOID AS Ref_NhanVien
            , ODanhSachNhanVien.IOID AS Ref_MaNhanVienMoi
            , (IFNULL(OChiTietThuHoiTaiSan.SoLuong, 0) - IFNULL(PhieuBanGiao.SoLuong, 0)) AS SoLuongConLai
            , PhieuBanGiao.SoPhieu AS PhieuBanGiao
            , PhieuBanGiao.SoLuong AS SoLuongDaBanGiao
            , OChiTietThuHoiTaiSan.PhanTramKhauHao AS PhanTramKhauHaoThuHoi
            , OChiTietThuHoiTaiSan.ThoiGianDaSuDung AS ThoiGianDaSuDung
        FROM OPhieuThuHoiTaiSan
        INNER JOIN OChiTietThuHoiTaiSan ON OPhieuThuHoiTaiSan.IFID_M183 = OChiTietThuHoiTaiSan.IFID_M183
        INNER JOIN ODanhSachNhanVien ON IFNULL(OChiTietThuHoiTaiSan.Ref_MaNhanVienMoi, 0) = ODanhSachNhanVien.IOID
        INNER JOIN ODanhMucCongCuDungCu ON OChiTietThuHoiTaiSan.Ref_MaTaiSan = ODanhMucCongCuDungCu.IOID
        LEFT JOIN (
            SELECT
                IFNULL(OPhieuBanGiaoTaiSan.Ref_PhieuThuHoi, 0) AS Ref_PhieuThuHoi
                , IFNULL(OChiTietBanGiaoTaiSan.Ref_MaTaiSan, 0) AS Ref_MaTaiSan
                , IFNULL(OChiTietBanGiaoTaiSan.Ref_MaNhanVien, 0) AS Ref_MaNhanVien
                , SUM( IFNULL(OChiTietBanGiaoTaiSan.SoLuong, 0) ) AS SoLuong
                , GROUP_CONCAT(OPhieuBanGiaoTaiSan.SoPhieu separator ", " ) AS SoPhieu
            FROM OPhieuBanGiaoTaiSan
            INNER JOIN OChiTietBanGiaoTaiSan ON OPhieuBanGiaoTaiSan.IFID_M182 = OChiTietBanGiaoTaiSan.IFID_M182
            WHERE IFNULL(OPhieuBanGiaoTaiSan.Ref_PhieuThuHoi, 0) = %1$d
            GROUP BY OChiTietBanGiaoTaiSan.Ref_MaNhanVien, OChiTietBanGiaoTaiSan.Ref_MaTaiSan
        ) AS PhieuBanGiao ON OPhieuThuHoiTaiSan.IOID = PhieuBanGiao.Ref_PhieuThuHoi
            AND ODanhMucCongCuDungCu.IOID = PhieuBanGiao.Ref_MaTaiSan
            AND IFNULL(OChiTietThuHoiTaiSan.Ref_MaNhanVienMoi, 0) = PhieuBanGiao.Ref_MaNhanVien
        WHERE OPhieuThuHoiTaiSan.IOID = %1$d
            %2$s
        LIMIT 100
        ', $clawbackIOID, $where);
        return $this->_o_DB->fetchAll($sql);
    }


    public function getHandoverEmployeesBaseOnClawBackLikeString($strEmployee = '', $clawbackIOID = 0, $asset = 0)
    {
        $where = '';
        if($strEmployee)
        {
            $where .=  sprintf(' AND (ODanhSachNhanVien.MaNhanVien like "%%%1$s%%" OR ODanhSachNhanVien.TenNhanVien like "%%%1$s%%")',$strEmployee);
        }

        if($clawbackIOID != 0) {
            if ($asset) {
                $where .= sprintf(' AND IFNULL(OChiTietThuHoiTaiSan.Ref_MaTaiSan, 0) = %1$d', $asset);
            }

            $sql = sprintf('
            SELECT
                ODanhSachNhanVien.*
                , ODanhSachNhanVien.IOID AS Ref_NhanVien
                , ODanhSachNhanVien.IOID AS Ref_MaNhanVienMoi
                , (IFNULL(OChiTietThuHoiTaiSan.SoLuong, 0) - IFNULL(PhieuBanGiao.SoLuong, 0)) AS SoLuongConLai
                , PhieuBanGiao.SoPhieu AS PhieuBanGiao
                , PhieuBanGiao.SoLuong AS SoLuongDaBanGiao
            FROM OPhieuThuHoiTaiSan
            INNER JOIN OChiTietThuHoiTaiSan ON OPhieuThuHoiTaiSan.IFID_M183 = OChiTietThuHoiTaiSan.IFID_M183
            INNER JOIN ODanhSachNhanVien ON IFNULL(OChiTietThuHoiTaiSan.Ref_MaNhanVienMoi, 0) = ODanhSachNhanVien.IOID
            INNER JOIN ODanhMucCongCuDungCu ON OChiTietThuHoiTaiSan.Ref_MaTaiSan = ODanhMucCongCuDungCu.IOID
            LEFT JOIN (
                SELECT
                    IFNULL(OPhieuBanGiaoTaiSan.Ref_PhieuThuHoi, 0) AS Ref_PhieuThuHoi
                    , IFNULL(OChiTietBanGiaoTaiSan.Ref_MaTaiSan, 0) AS Ref_MaTaiSan
                    , IFNULL(OChiTietBanGiaoTaiSan.Ref_MaNhanVien, 0) AS Ref_MaNhanVien
                    , SUM( IFNULL(OChiTietBanGiaoTaiSan.SoLuong, 0) ) AS SoLuong
                    , GROUP_CONCAT(OPhieuBanGiaoTaiSan.SoPhieu separator ", " ) AS SoPhieu
                FROM OPhieuBanGiaoTaiSan
                INNER JOIN OChiTietBanGiaoTaiSan ON OPhieuBanGiaoTaiSan.IFID_M182 = OChiTietBanGiaoTaiSan.IFID_M182
                WHERE IFNULL(OPhieuBanGiaoTaiSan.Ref_PhieuThuHoi, 0) = %1$d
                GROUP BY OChiTietBanGiaoTaiSan.Ref_MaNhanVien, OChiTietBanGiaoTaiSan.Ref_MaTaiSan
            ) AS PhieuBanGiao ON OPhieuThuHoiTaiSan.IOID = PhieuBanGiao.Ref_PhieuThuHoi
                AND ODanhMucCongCuDungCu.IOID = PhieuBanGiao.Ref_MaTaiSan
                AND IFNULL(OChiTietThuHoiTaiSan.Ref_MaNhanVienMoi, 0) = PhieuBanGiao.Ref_MaNhanVien
            WHERE OPhieuThuHoiTaiSan.IOID = %1$d
                %2$s
            LIMIT 100
            ', $clawbackIOID, $where);
        }
        else
        {
            $sql = sprintf('
                SELECT *
                FROM ODanhSachNhanVien
                WHERE 1=1 %1$s
                ORDER BY MaNhanVien
                LIMIT 100
            ', $where);
        }
        return $this->_o_DB->fetchAll($sql);
    }

    public function getHandoverAssetsBaseOnClawBack($clawbackIOID = 0, $employee = 0)
    {
        $where = '';

        if($clawbackIOID != 0)
        {
            if($employee)
            {
                $where .= sprintf(' AND IFNULL(OChiTietThuHoiTaiSan.Ref_MaNhanVienMoi, 0) = %1$d ', $employee);
            }

            $sql = sprintf('
                SELECT
                    ODanhMucCongCuDungCu.*
                    , ODanhMucCongCuDungCu.IOID AS Ref_TaiSan
                    , (IFNULL(OChiTietThuHoiTaiSan.SoLuong, 0) - IFNULL(PhieuBanGiao.SoLuong, 0)) AS SoLuongConLai
                    , PhieuBanGiao.SoPhieu AS PhieuBanGiao
                    , PhieuBanGiao.SoLuong AS SoLuongDaBanGiao
                    , OChiTietThuHoiTaiSan.PhanTramKhauHao AS PhanTramKhauHaoThuHoi
                    , OChiTietThuHoiTaiSan.ThoiGianDaSuDung AS ThoiGianDaSuDung
                FROM OPhieuThuHoiTaiSan
                INNER JOIN OChiTietThuHoiTaiSan ON OPhieuThuHoiTaiSan.IFID_M183 = OChiTietThuHoiTaiSan.IFID_M183
                INNER JOIN ODanhMucCongCuDungCu ON OChiTietThuHoiTaiSan.Ref_MaTaiSan = ODanhMucCongCuDungCu.IOID
                LEFT JOIN (
                    SELECT
                        IFNULL(OPhieuBanGiaoTaiSan.Ref_PhieuThuHoi, 0) AS Ref_PhieuThuHoi
                        , IFNULL(OChiTietBanGiaoTaiSan.Ref_MaTaiSan, 0) AS Ref_MaTaiSan
                        , IFNULL(OChiTietBanGiaoTaiSan.Ref_MaNhanVien, 0) AS Ref_MaNhanVien
                        , SUM( IFNULL(OChiTietBanGiaoTaiSan.SoLuong, 0) ) AS SoLuong
                        , GROUP_CONCAT(OPhieuBanGiaoTaiSan.SoPhieu separator ", " ) AS SoPhieu
                    FROM OPhieuBanGiaoTaiSan
                    INNER JOIN OChiTietBanGiaoTaiSan ON OPhieuBanGiaoTaiSan.IFID_M182 = OChiTietBanGiaoTaiSan.IFID_M182
                    WHERE IFNULL(OPhieuBanGiaoTaiSan.Ref_PhieuThuHoi, 0) = %1$d
                    GROUP BY OChiTietBanGiaoTaiSan.Ref_MaNhanVien, OChiTietBanGiaoTaiSan.Ref_MaTaiSan
                ) AS PhieuBanGiao ON OPhieuThuHoiTaiSan.IOID = PhieuBanGiao.Ref_PhieuThuHoi
                    AND ODanhMucCongCuDungCu.IOID = PhieuBanGiao.Ref_MaTaiSan
                    AND IFNULL(OChiTietThuHoiTaiSan.Ref_MaNhanVienMoi, 0) = PhieuBanGiao.Ref_MaNhanVien
                WHERE OPhieuThuHoiTaiSan.IOID = %1$d
                    %2$s
            ', $clawbackIOID, $where);
        }
        else
        {
            $sql = sprintf('SELECT ODanhMucCongCuDungCu.*, ODanhMucCongCuDungCu.IOID AS Ref_TaiSan FROM ODanhMucCongCuDungCu ORDER BY Ma', $where);
        }
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy tài sản đã bàn giao để thu hồi lại
     * @param array $employees
     * @param $clawbackIfid
     * @return mixed
     */
    public function getClawBackAssetsByEmployees($employees = array(), $clawbackIfid)
    {

        $sql         = sprintf('SELECT * FROM OPhieuThuHoiTaiSan WHERE IFID_M183 = %1$d ', $clawbackIfid);
        $datThuHoi   = $this->_o_DB->fetchOne($sql);
        $ngayThuHoi  = $datThuHoi?$datThuHoi->Ngay:date('Y-m-d');
        $nhaMayThu   = $datThuHoi?$datThuHoi->Ref_NhaMay:0;
        $where       = '';
        $where      .= sprintf(' AND OPhieuBanGiaoTaiSan.Ngay <= "%1$s" ', $ngayThuHoi);
        $where      .= $nhaMayThu?sprintf(' AND IFNULL(OChiTietBanGiaoTaiSan.Ref_NhaMay, 0) = %1$d ', $nhaMayThu):'';
        $orderByEmps = '';
        $i           = 1;

        if(count($employees))
        {
            $orderByEmps .= 'ORDER BY CASE OChiTietBanGiaoTaiSan.Ref_MaNhanVien ';

            foreach ($employees as $item)
            {
                $orderByEmps .= ' WHEN '.$item.' THEN '.$i++;
            }

            $orderByEmps .= ' END ';
        }

        $orderByEmps .= $orderByEmps?',  OChiTietBanGiaoTaiSan.MaTaiSan, OPhieuBanGiaoTaiSan.SoPhieu':'ORDER BY OChiTietBanGiaoTaiSan.MaTaiSan, OPhieuBanGiaoTaiSan.SoPhieu';

        $employees[] = 0;
        $sql = sprintf('
            SELECT
                ODanhMucCongCuDungCu.*
                , OChiTietBanGiaoTaiSan.*
                , OPhieuBanGiaoTaiSan.SoPhieu AS SoPhieuBanGiao
                , ODanhMucCongCuDungCu.IOID AS Ref_TaiSan
                , IF( IFNULL(iFormThuHoi.Status, 0) = 2
                    , (IFNULL(OChiTietBanGiaoTaiSan.SoLuong, 0) - SUM(IFNULL(OChiTietThuHoiTaiSan.SoLuong, 0)) - SUM(IFNULL(ChiTietThuHoiHienTai.SoLuong, 0)))
                    , (IFNULL(OChiTietBanGiaoTaiSan.SoLuong, 0) - SUM(IFNULL(ChiTietThuHoiHienTai.SoLuong, 0))) ) AS SoLuongConLai
                , CEIL((((CEIL((TIMESTAMPDIFF(DAY, OPhieuBanGiaoTaiSan.Ngay, %2$s)/30)*100)/100)*100) /IFNULL(ODanhMucCongCuDungCu.GiaTri,1)) * 100)/100 AS PhanTramKhauHaoConLai
                , CEIL((TIMESTAMPDIFF(DAY, OPhieuBanGiaoTaiSan.Ngay, %2$s)/30)*100)/100 + IFNULL(OChiTietBanGiaoTaiSan.ThoiGianDaSuDung, 0) AS ThoiGianDaSuDungKhiThuHoi
                , OPhieuBanGiaoTaiSan.IOID AS Ref_PhieuBanGiao
                , OChiTietBanGiaoTaiSan.NhaMay
                , OChiTietBanGiaoTaiSan.Ref_NhaMay
                , OChiTietBanGiaoTaiSan.BoPhan
                , OChiTietBanGiaoTaiSan.Ref_BoPhan
                , ODanhSachNhanVien.MaPhongBan AS NhaMayHienTai
                , ODanhSachNhanVien.MaBoPhan AS BoPhanHienTai
                , ODanhSachNhanVien.Ref_MaPhongBan AS Ref_NhaMayHienTai
                , ODanhSachNhanVien.Ref_MaBoPhan AS Ref_BoPhanHienTai                
            FROM OPhieuBanGiaoTaiSan
            INNER JOIN qsiforms ON OPhieuBanGiaoTaiSan.IFID_M182 = qsiforms.IFID
            INNER JOIN OChiTietBanGiaoTaiSan ON OPhieuBanGiaoTaiSan.IFID_M182 = OChiTietBanGiaoTaiSan.IFID_M182
            INNER JOIN ODanhSachNhanVien ON OChiTietBanGiaoTaiSan.Ref_MaNhanVien = ODanhSachNhanVien.IOID
            INNER JOIN ODanhMucCongCuDungCu ON OChiTietBanGiaoTaiSan.Ref_MaTaiSan = ODanhMucCongCuDungCu.IOID
            
            # chi tiet thu hoi khac
            LEFT JOIN OChiTietThuHoiTaiSan ON OPhieuBanGiaoTaiSan.IOID = OChiTietThuHoiTaiSan.Ref_PhieuBanGiao 
                AND OChiTietBanGiaoTaiSan.Ref_MaTaiSan = OChiTietThuHoiTaiSan.Ref_MaTaiSan
                AND OChiTietBanGiaoTaiSan.Ref_MaNhanVien = OChiTietThuHoiTaiSan.Ref_MaNhanVien
                AND OChiTietThuHoiTaiSan.IFID_M183 != %5$d
            LEFT JOIN OPhieuThuHoiTaiSan ON IFNULL(OChiTietThuHoiTaiSan.IFID_M183, 0) = OPhieuThuHoiTaiSan.IFID_M183 
            LEFT JOIN qsiforms AS iFormThuHoi ON OPhieuThuHoiTaiSan.IFID_M183 = iFormThuHoi.IFID
            
            # chi tiet thu hoi hien tai
            LEFT JOIN OChiTietThuHoiTaiSan AS ChiTietThuHoiHienTai ON OPhieuBanGiaoTaiSan.IOID = ChiTietThuHoiHienTai.Ref_PhieuBanGiao 
                AND OChiTietBanGiaoTaiSan.Ref_MaTaiSan = ChiTietThuHoiHienTai.Ref_MaTaiSan
                AND OChiTietBanGiaoTaiSan.Ref_MaNhanVien = ChiTietThuHoiHienTai.Ref_MaNhanVien
                AND ChiTietThuHoiHienTai.IFID_M183 = %5$d
            WHERE 
                IFNULL(qsiforms.Status, 0) = 2
                AND OChiTietBanGiaoTaiSan.Ref_MaNhanVien IN (%1$s)
                %4$s             
            GROUP BY OPhieuBanGiaoTaiSan.IOID, OChiTietBanGiaoTaiSan.MaNhanVien, OChiTietBanGiaoTaiSan.MaTaiSan
            HAVING SoLuongConLai > 0
            %3$s
            
        ', implode(', ', $employees), $this->_o_DB->quote($ngayThuHoi), $orderByEmps, $where, $clawbackIfid);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getClawBackDetailByIFID($ifid)
    {
        $sql = sprintf('SELECT * FROM OChiTietThuHoiTaiSan WHERE IFID_M183 = %1$d', $ifid);

        return $this->_o_DB->fetchAll($sql);
    }

    public function getTransaction($employee = 0, $asset = 0, $nhaMay = '', $boPhan = '')
    {
        $whereBanGiao  = $employee?sprintf(' AND IFNULL(ChiTiet.Ref_MaNhanVien, 0) = %1$d ', $employee):'';
        $whereBanGiao .= $asset?sprintf(' AND IFNULL(ChiTiet.Ref_MaTaiSan, 0) = %1$d ', $asset):'';
        $whereBanGiao .= $nhaMay?sprintf(' AND IFNULL(ChiTiet.NhaMay, 0) = %1$s ', $this->_o_DB->quote($nhaMay)):'';
        $whereBanGiao .= $boPhan?sprintf(' AND IFNULL(ChiTiet.BoPhan, 0) = %1$s ', $this->_o_DB->quote($boPhan)):'';

        $whereThuHoi  = $employee?sprintf(' AND (IFNULL(ChiTiet.Ref_MaNhanVien, 0) = %1$d OR IFNULL(ChiTiet.Ref_MaNhanVienMoi, 0) = %1$d) ', $employee):'';
        $whereThuHoi .= $asset?sprintf(' AND IFNULL(ChiTiet.Ref_MaTaiSan, 0) = %1$d ', $asset):'';
        $whereThuHoi .= $nhaMay?sprintf(' AND IFNULL(ChiTiet.NhaMay, 0) = %1$s ', $this->_o_DB->quote($nhaMay)):'';
        $whereThuHoi .= $boPhan?sprintf(' AND IFNULL(ChiTiet.BoPhan, 0) = %1$s ', $this->_o_DB->quote($boPhan)):'';

        $sql = sprintf('
            SELECT
                Phieu.SoPhieu, Phieu.Ngay
                , Phieu.PhieuThuHoi AS PhieuLienQuan
                , Phieu.DienGiai
                , ChiTiet.NhaMay, ChiTiet.BoPhan
                , ChiTiet.MaNhanVien, ChiTiet.TenNhanVien
                , ChiTiet.MaTaiSan, ChiTiet.TenTaiSan, ChiTiet.DonViTinh, ChiTiet.SoLuong, NULL AS Hong
                , ChiTiet.DonGia, ChiTiet.ThanhTien, ChiTiet.PhanTramKhauHao
                , NULL AS MaNhanVienMoi, NULL AS TenNhanVienMoi
                , "RECEIVE" AS `Type`
            FROM OPhieuBanGiaoTaiSan AS Phieu
            INNER JOIN OChiTietBanGiaoTaiSan AS ChiTiet ON Phieu.IFID_M182 = ChiTiet.IFID_M182
            WHERE 1=1 %1$s

            UNION ALL

            SELECT
                Phieu.SoPhieu, Phieu.Ngay
                , ChiTiet.PhieuBanGiao AS PhieuLienQuan
                , Phieu.DienGiai
                , ChiTiet.NhaMay, ChiTiet.BoPhan
                , ChiTiet.MaNhanVien, ChiTiet.TenNhanVien
                , ChiTiet.MaTaiSan, ChiTiet.TenTaiSan, ChiTiet.DonViTinh, ChiTiet.SoLuong, ChiTiet.Hong
                , ChiTiet.DonGia, ChiTiet.ThanhTien, ChiTiet.PhanTramKhauHao
                , ChiTiet.MaNhanVienMoi, ChiTiet.TenNhanVienMoi
                , "RETURN" AS `Type`
            FROM OPhieuThuHoiTaiSan AS Phieu
            INNER JOIN OChiTietThuHoiTaiSan AS ChiTiet ON Phieu.IFID_M183 = ChiTiet.IFID_M183
            WHERE 1=1 %2$s

            ORDER BY Ngay, SoPhieu, PhieuLienQuan, MaNhanVien, MaTaiSan
        ', $whereBanGiao, $whereThuHoi);

        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy bồi thường
     */
    public function getCompensation($start, $end, $employees = array(), $nhaMay = '', $boPhan = '')
    {
        $where  = count((array)$employees)?sprintf(' AND ChiTiet.Ref_MaNhanVien IN (%1$s) ', implode(', ', $employees)):'';
        $where .= $nhaMay?sprintf(' AND ChiTiet.Ref_NhaMay = %1$d ', $nhaMay):'';
        $where .= $boPhan?sprintf(' AND ChiTiet.Ref_BoPhan = %1$d ', $boPhan):'';

        $sql = sprintf('
            SELECT ChiTiet.*, Phieu.Ngay AS NgayThuHoi, PhieuBanGiao.Ngay AS NgayBanGiao, NhanVien.SoCMND
            FROM OPhieuThuHoiTaiSan AS Phieu            
            INNER JOIN OChiTietThuHoiTaiSan AS ChiTiet ON Phieu.IFID_M183 = ChiTiet.IFID_M183
            INNER JOIN ODanhSachNhanVien AS NhanVien ON ChiTiet.Ref_MaNhanVien = NhanVien.IOID
            INNER JOIN OPhieuBanGiaoTaiSan AS PhieuBanGiao ON ChiTiet.Ref_PhieuBanGiao = PhieuBanGiao.IOID
            WHERE IFNULL(ChiTiet.Hong, 0) = 1 AND (Phieu.Ngay BETWEEN %1$s AND %2$s) %3$s
            ORDER BY ChiTiet.MaNhanVien
        ', $this->_o_DB->quote($start), $this->_o_DB->quote($end), $where);
        return $this->_o_DB->fetchAll($sql);
    }

    public function countLinesByAssetsOfEmployees(
        $nhaMay = ''
        , $boPhan = ''
        , $employee = 0
        , $assetType = 0
        , $asset = 0
        , $start = ''
        , $end = ''
        , $all = false
        , $rest = false)
    {
        $where  = $nhaMay?sprintf(' AND IFNULL(ChiTietBanGiao.Ref_NhaMay, 0) = %1$d ', $nhaMay):'';
        $where .= $boPhan?sprintf(' AND IFNULL(ChiTietBanGiao.Ref_BoPhan, 0) = %1$d ', $boPhan):'';
        $where .= $employee?sprintf(' AND IFNULL(ChiTietBanGiao.Ref_MaNhanVien, 0) = %1$d ', $employee):'';
        $where .= $asset?sprintf(' AND IFNULL(ChiTietBanGiao.Ref_MaTaiSan, 0) = %1$d ', $asset):'';
        $having = $all?'':sprintf(' AND SoLuongConLai > 0  ');
        $having2 = $all?'':sprintf(' AND SoLuongConLaiCuoi > 0  ');

//        if(Qss_Lib_System::fieldActive('ODanhMucCongCuDungCu', 'Loai'))
//        {
//            $where .= $assetType?sprintf(' AND IFNULL(DungCu.Ref_Loai, 0) = %1$d ', $assetType):'';
//        }

        if($start && $end && $all)
        {
            // Khong noi chuoi o day vi day la dieu kien khi co ca start end va all
            $having = sprintf(' AND ((Ngay Between %1$s AND %2$s) OR SoLuongConLai > 0) ', $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        }

        if($start && $end)
        {
            // Khong noi chuoi o day vi day la dieu kien khi co ca start end va all
            $having = sprintf(' AND ((Ngay Between %1$s AND %2$s) ) ', $this->_o_DB->quote($start), $this->_o_DB->quote($end));

            if(!$all) { $having .= sprintf(' AND SoLuongConLai > 0'); }

            $joinThuHoi = sprintf(' AND ThuHoi.Ngay Between %1$s AND %2$s ', $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        }
        elseif ($start)
        {
            // Khong noi chuoi o day vi day la dieu kien khi co ca start end va all
            $having = sprintf(' AND Ngay >= %1$s  ', $this->_o_DB->quote($start));

            if(!$all) { $having .= sprintf(' AND SoLuongConLai > 0'); }

            $joinThuHoi = sprintf(' AND ThuHoi.Ngay >= %1$s ', $this->_o_DB->quote($start));
        }
        elseif ($end)
        {
            // Khong noi chuoi o day vi day la dieu kien khi co ca start end va all
            $having = sprintf(' AND Ngay <= %1$s ', $this->_o_DB->quote($end));

            if(!$all) { $having .= sprintf(' AND SoLuongConLai > 0'); }

            $joinThuHoi = sprintf(' AND ThuHoi.Ngay <= %1$s ', $this->_o_DB->quote($end));
        }

        if($rest)
        {
            $temSql = sprintf('
            SELECT DISTINCT Ref_BanGiao
            FROM(
                SELECT *, SUM(IFNULL(SoLuongConLai, 0)) AS SoLuongConLaiCuoi
                FROM
                (
                    SELECT
                        ChiTietBanGiao.IOID AS Ref_BanGiao
                        , ChiTietBanGiao.Ref_MaTaiSan
                        , ChiTietBanGiao.Ref_MaNhanVien
                        , BanGiao.Ngay
                        , BanGiao.IOID AS RefBanGiao                        
                        , ((IFNULL(ChiTietBanGiao.SoLuong, 0)) - SUM(IF(IFormThuHoi.IFID, IFNULL(ChiTietThuHoi.SoLuong, 0), 0))) SoLuongConLai
                    FROM OPhieuBanGiaoTaiSan AS BanGiao
                    INNER JOIN qsiforms AS IFormBanGiao ON BanGiao.IFID_M182 = IFormBanGiao.IFID
                    INNER JOIN OChiTietBanGiaoTaiSan AS ChiTietBanGiao ON BanGiao.IFID_M182 = ChiTietBanGiao.IFID_M182
                    INNER JOIN ODanhSachNhanVien AS NhanVien ON ChiTietBanGiao.Ref_MaNhanVien = NhanVien.IOID
                    INNER JOIN ODanhMucCongCuDungCu AS DungCu ON ChiTietBanGiao.Ref_MaTaiSan = DungCu.IOID
                    LEFT JOIN OChiTietThuHoiTaiSan AS ChiTietThuHoi ON BanGiao.IOID = ChiTietThuHoi.Ref_PhieuBanGiao
                        AND ChiTietBanGiao.Ref_MaNhanVien = ChiTietThuHoi.Ref_MaNhanVien
                        AND ChiTietBanGiao.Ref_MaTaiSan = ChiTietThuHoi.Ref_MaTaiSan            	   	
                    LEFT JOIN OPhieuThuHoiTaiSan AS ThuHoi ON ThuHoi.IFID_M183 = ChiTietThuHoi.IFID_M183 %4$s
                    LEFT JOIN qsiforms AS IFormThuHoi ON ThuHoi.IFID_M183 = IFormThuHoi.IFID
                        AND IFNULL(IFormThuHoi.Status, 0) = 2
                    WHERE IFNULL(IFormBanGiao.Status, 0) = 2 AND ChiTietBanGiao.NhaMay != NhanVien.MaPhongBan %1$s
                    GROUP BY BanGiao.IOID, ChiTietBanGiao.Ref_MaNhanVien, ChiTietBanGiao.Ref_MaTaiSan  
                    HAVING 1=1 %2$s
                ) AS T1
                GROUP BY Ref_MaNhanVien, Ref_MaTaiSan
                HAVING 1=1 %3$s
            ) AS T2
            
            ', $where, $having, $having2, $joinThuHoi);

            $where .= sprintf(' AND ChiTietBanGiao.IOID IN (%1$s) ', $temSql);
        }


        $sql = sprintf('
            SELECT COUNT(1) AS Total
            FROM
            (
                SELECT *, SUM(IFNULL(SoLuongConLai, 0)) AS SoLuongConLaiCuoi
                FROM
                (
                    SELECT 
                        ChiTietBanGiao.MaNhanVien
                        , ChiTietBanGiao.MaTaiSan
                        , ChiTietBanGiao.Ref_MaTaiSan
                        , ChiTietBanGiao.Ref_MaNhanVien
                        , ChiTietBanGiao.NhaMay
                        , ChiTietBanGiao.BoPhan
                        , NhanVien.MaPhongBan AS NhaMayHienTai
                        , NhanVien.MaBoPhan AS BoPhanHienTai
                        , BanGiao.Ngay
                        , BanGiao.IOID AS RefBanGiao
                        , ((IFNULL(ChiTietBanGiao.SoLuong, 0)) - SUM(IF(IFormThuHoi.IFID, IFNULL(ChiTietThuHoi.SoLuong, 0), 0))) SoLuongConLai
                    FROM OPhieuBanGiaoTaiSan AS BanGiao
                    INNER JOIN qsiforms AS IFormBanGiao ON BanGiao.IFID_M182 = IFormBanGiao.IFID
                    INNER JOIN OChiTietBanGiaoTaiSan AS ChiTietBanGiao ON BanGiao.IFID_M182 = ChiTietBanGiao.IFID_M182
                    INNER JOIN ODanhSachNhanVien AS NhanVien ON ChiTietBanGiao.Ref_MaNhanVien = NhanVien.IOID
                    INNER JOIN ODanhMucCongCuDungCu AS DungCu ON ChiTietBanGiao.Ref_MaTaiSan = DungCu.IOID
                    LEFT JOIN OChiTietThuHoiTaiSan AS ChiTietThuHoi ON BanGiao.IOID = ChiTietThuHoi.Ref_PhieuBanGiao
                        AND ChiTietBanGiao.Ref_MaNhanVien = ChiTietThuHoi.Ref_MaNhanVien
                        AND ChiTietBanGiao.Ref_MaTaiSan = ChiTietThuHoi.Ref_MaTaiSan            	   	
                    LEFT JOIN OPhieuThuHoiTaiSan AS ThuHoi ON ThuHoi.IFID_M183 = ChiTietThuHoi.IFID_M183 %4$s
                    LEFT JOIN qsiforms AS IFormThuHoi ON ThuHoi.IFID_M183 = IFormThuHoi.IFID
                        AND IFNULL(IFormThuHoi.Status, 0) = 2
                    WHERE IFNULL(IFormBanGiao.Status, 0) = 2 %1$s
                    GROUP BY BanGiao.IOID, ChiTietBanGiao.Ref_MaNhanVien, ChiTietBanGiao.Ref_MaTaiSan  
                    HAVING 1=1 %2$s
                ) AS T1
                GROUP BY NhaMay, Ref_MaNhanVien, Ref_MaTaiSan
                HAVING 1=1 %3$s
                ORDER BY  NhaMay, BoPhan, MaNhanVien, MaTaiSan, Ngay      
            ) AS T2
        ', $where, $having, $having2, $joinThuHoi);

        // echo '<pre>'; print_r($sql); die;
        $dataSql = $this->_o_DB->fetchOne($sql);

        return $dataSql?$dataSql->Total:0;
    }

    public function getAssetsOfEmployees(
        $nhaMay = ''
        , $boPhan = ''
        , $employee = 0
        , $assetType = 0
        , $asset = 0
        , $start = ''
        , $end = ''
        , $all = false
        , $rest = false
        , $page = 0
        , $perPage = 0
        )
    {
        $limit  = '';
        $where  = $nhaMay?sprintf(' AND IFNULL(ChiTietBanGiao.Ref_NhaMay, 0) = %1$d ', $nhaMay):'';
        $where .= $boPhan?sprintf(' AND IF( IFNULL(ChiTietBanGiao.Ref_NhaMay, 0) <> IFNULL(NhanVien.Ref_MaPhongBan, 0) 
                    , ChiTietBanGiao.Ref_BoPhan
                    , NhanVien.Ref_MaBoPhan
                )  = %1$d ', $boPhan):'';
        $where .= $employee?sprintf(' AND IFNULL(ChiTietBanGiao.Ref_MaNhanVien, 0) = %1$d ', $employee):'';
        $where .= $asset?sprintf(' AND IFNULL(ChiTietBanGiao.Ref_MaTaiSan, 0) = %1$d ', $asset):'';

        $having     = $all?'':sprintf(' AND SoLuongConLai > 0  ');
        $having2    = $all?'':sprintf(' AND SoLuongConLaiCuoi > 0  ');
        $joinThuHoi = '';

        if($page && $perPage)
        {
            $startRow = ($page - 1) * $perPage;
            $limit    = sprintf(' LIMIT %1$d, %2$d ', $startRow, $perPage);
        }

        if($start && $end)
        {
            // Khong noi chuoi o day vi day la dieu kien khi co ca start end va all
            $having = sprintf(' AND ((Ngay Between %1$s AND %2$s)) ', $this->_o_DB->quote($start), $this->_o_DB->quote($end));

            if(!$all) { $having .= sprintf(' AND SoLuongConLai > 0'); }

            $joinThuHoi = sprintf(' AND ThuHoi.Ngay Between %1$s AND %2$s ', $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        }
        elseif ($start)
        {
            // Khong noi chuoi o day vi day la dieu kien khi co ca start end va all
            $having = sprintf(' AND Ngay >= %1$s  ', $this->_o_DB->quote($start));

            if(!$all) { $having .= sprintf(' AND SoLuongConLai > 0'); }

            $joinThuHoi = sprintf(' AND ThuHoi.Ngay >= %1$s ', $this->_o_DB->quote($start));
        }
        elseif ($end)
        {
            // Khong noi chuoi o day vi day la dieu kien khi co ca start end va all
            $having = sprintf(' AND Ngay <= %1$s  ', $this->_o_DB->quote($end));

            if(!$all) { $having .= sprintf(' AND SoLuongConLai > 0'); }

            $joinThuHoi = sprintf(' AND ThuHoi.Ngay <= %1$s ', $this->_o_DB->quote($end));
        }

        if($rest)
        {
            $temSql = sprintf('

            SELECT DISTINCT Ref_ChiTietBanGiao
            FROM(
                SELECT *, SUM(IFNULL(SoLuongConLai, 0)) AS SoLuongConLaiCuoi
                FROM
                (
                    SELECT
                        ChiTietBanGiao.IOID AS Ref_ChiTietBanGiao
                        , ChiTietBanGiao.Ref_MaTaiSan
                        , ChiTietBanGiao.Ref_MaNhanVien  
                        , BanGiao.Ngay
                        , ((IFNULL(ChiTietBanGiao.SoLuong, 0)) - SUM(IF(IFormThuHoi.IFID, IFNULL(ChiTietThuHoi.SoLuong, 0), 0))) SoLuongConLai
                        , NhanVien.SoCMND
                    FROM OPhieuBanGiaoTaiSan AS BanGiao
                    INNER JOIN qsiforms AS IFormBanGiao ON BanGiao.IFID_M182 = IFormBanGiao.IFID
                    INNER JOIN OChiTietBanGiaoTaiSan AS ChiTietBanGiao ON BanGiao.IFID_M182 = ChiTietBanGiao.IFID_M182
                    INNER JOIN ODanhSachNhanVien AS NhanVien ON ChiTietBanGiao.Ref_MaNhanVien = NhanVien.IOID
                    INNER JOIN ODanhMucCongCuDungCu AS DungCu ON ChiTietBanGiao.Ref_MaTaiSan = DungCu.IOID
                    LEFT JOIN OChiTietThuHoiTaiSan AS ChiTietThuHoi ON BanGiao.IOID = ChiTietThuHoi.Ref_PhieuBanGiao
                        AND ChiTietBanGiao.Ref_MaNhanVien = ChiTietThuHoi.Ref_MaNhanVien
                        AND ChiTietBanGiao.Ref_MaTaiSan = ChiTietThuHoi.Ref_MaTaiSan            	   	
                    LEFT JOIN OPhieuThuHoiTaiSan AS ThuHoi ON ThuHoi.IFID_M183 = ChiTietThuHoi.IFID_M183 %4$s
                    LEFT JOIN qsiforms AS IFormThuHoi ON ThuHoi.IFID_M183 = IFormThuHoi.IFID
                        AND IFNULL(IFormThuHoi.Status, 0) = 2
                    WHERE IFNULL(IFormBanGiao.Status, 0) = 2 AND ChiTietBanGiao.NhaMay != NhanVien.MaPhongBan %1$s
                    GROUP BY BanGiao.IOID, ChiTietBanGiao.Ref_MaNhanVien, ChiTietBanGiao.Ref_MaTaiSan  
                    HAVING 1=1 %2$s
                ) AS T1
                GROUP BY Ref_MaNhanVien, Ref_MaTaiSan
                HAVING 1=1 %3$s
            ) AS T2
                     
            ', $where, $having, $having2, $joinThuHoi);

            $where .= sprintf(' AND ChiTietBanGiao.IOID IN (%1$s) ', $temSql);
        }


        $sql = sprintf('
            SELECT 
                *              
                , SUM(IFNULL(SoLuongDaBanGiao, 0)) AS SoLuongDaBanGiao
                , SUM(IFNULL(SoLuongConLai, 0)) AS SoLuongConLaiCuoi   
                , GROUP_CONCAT(DISTINCT NhaMay SEPARATOR ",") AS CacNhaMayDaChuyenDen
                , Ref_BoPhan1 AS Ref_BoPhan
                , BoPhan1 AS BoPhan
            FROM
            (
                SELECT 
                    ChiTietBanGiao.* 
                    , IF( IFNULL(ChiTietBanGiao.Ref_NhaMay, 0) <> IFNULL(NhanVien.Ref_MaPhongBan, 0) 
                        , ChiTietBanGiao.Ref_BoPhan
                        , NhanVien.Ref_MaBoPhan
                    ) AS Ref_BoPhan1                
                    , IF( IFNULL(ChiTietBanGiao.Ref_NhaMay, 0) <> IFNULL(NhanVien.Ref_MaPhongBan, 0) 
                        , ChiTietBanGiao.BoPhan
                        , NhanVien.BoPhan
                    ) AS BoPhan1                                           
                    , BanGiao.Ngay
                    , BanGiao.IOID AS RefBanGiao
                    , BanGiao.SoPhieu              		
                    , IFNULL(ChiTietBanGiao.SoLuong, 0) AS SoLuongDaBanGiao
                    , SUM(IF(IFormThuHoi.IFID, IFNULL(ChiTietThuHoi.SoLuong, 0), 0)) AS SoLuongDaThuHoi
                    , (IFNULL(ChiTietBanGiao.SoLuong, 0) - SUM(IF(IFormThuHoi.IFID, IFNULL(ChiTietThuHoi.SoLuong, 0), 0))) 
                    SoLuongConLai                     
                    , NhanVien.MaPhongBan AS NhaMayHienTai
                    , NhanVien.MaBoPhan AS BoPhanHienTai  
                    , NhanVien.SoCMND
                FROM OPhieuBanGiaoTaiSan AS BanGiao
                INNER JOIN qsiforms AS IFormBanGiao ON BanGiao.IFID_M182 = IFormBanGiao.IFID
                INNER JOIN OChiTietBanGiaoTaiSan AS ChiTietBanGiao ON BanGiao.IFID_M182 = ChiTietBanGiao.IFID_M182
                INNER JOIN ODanhSachNhanVien AS NhanVien ON ChiTietBanGiao.Ref_MaNhanVien = NhanVien.IOID
                INNER JOIN ODanhMucCongCuDungCu AS DungCu ON ChiTietBanGiao.Ref_MaTaiSan = DungCu.IOID
                LEFT JOIN OChiTietThuHoiTaiSan AS ChiTietThuHoi ON BanGiao.IOID = ChiTietThuHoi.Ref_PhieuBanGiao
                    AND ChiTietBanGiao.Ref_MaNhanVien = ChiTietThuHoi.Ref_MaNhanVien
                    AND ChiTietBanGiao.Ref_MaTaiSan = ChiTietThuHoi.Ref_MaTaiSan            	   	
                LEFT JOIN OPhieuThuHoiTaiSan AS ThuHoi ON ThuHoi.IFID_M183 = ChiTietThuHoi.IFID_M183 %5$s
                LEFT JOIN qsiforms AS IFormThuHoi ON ThuHoi.IFID_M183 = IFormThuHoi.IFID
                    AND IFNULL(IFormThuHoi.Status, 0) = 2
                WHERE IFNULL(IFormBanGiao.Status, 0) = 2 %1$s
                GROUP BY BanGiao.IOID, ChiTietBanGiao.Ref_MaNhanVien, ChiTietBanGiao.Ref_MaTaiSan  
                HAVING 1=1 %2$s
            ) AS T1
            GROUP BY NhaMay, Ref_MaNhanVien, Ref_MaTaiSan
            HAVING 1=1 %4$s
            ORDER BY  NhaMay, T1.BoPhan1, MaNhanVien, MaTaiSan, Ngay  
            %3$s
        ', $where, $having, $limit, $having2, $joinThuHoi);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }


    public function getDetailAssetOfEmployee($employee, $asset, $start, $end, $all = false, $factory = false)
    {
        $where  = '';
        $where .= ($factory !== false)?sprintf(' AND IFNULL(ChiTietBanGiao.NhaMay, "") = "%1$s" ', $factory):'';
        $where .= sprintf(' AND IFNULL(ChiTietBanGiao.Ref_MaNhanVien, 0) = %1$d ', $employee);
        $where .= sprintf(' AND IFNULL(ChiTietBanGiao.Ref_MaTaiSan, 0) = %1$d ', $asset);
        $having = $all?'':sprintf(' AND SoLuongConLai > 0  ');

        $joinThuHoi = '';

        if($start && $end)
        {
            // Khong noi chuoi o day vi day la dieu kien khi co ca start end va all
            $having = sprintf(' AND ((Ngay Between %1$s AND %2$s) ) ', $this->_o_DB->quote($start), $this->_o_DB->quote($end));

            if(!$all) { $having .= sprintf(' AND SoLuongConLai > 0'); }

            $joinThuHoi = sprintf(' AND ThuHoi.Ngay Between %1$s AND %2$s ', $this->_o_DB->quote($start), $this->_o_DB->quote($end));
        }
        elseif ($start)
        {
            // Khong noi chuoi o day vi day la dieu kien khi co ca start end va all
            $having = sprintf(' AND Ngay >= %1$s  ', $this->_o_DB->quote($start));

            if(!$all) { $having .= sprintf(' AND SoLuongConLai > 0'); }

            $joinThuHoi = sprintf(' AND ThuHoi.Ngay >= %1$s ', $this->_o_DB->quote($start));
        }
        elseif ($end)
        {
            // Khong noi chuoi o day vi day la dieu kien khi co ca start end va all
            $having = sprintf(' AND Ngay <= %1$s ', $this->_o_DB->quote($end));

            if(!$all) { $having .= sprintf(' AND SoLuongConLai > 0'); }

            $joinThuHoi = sprintf(' AND ThuHoi.Ngay <= %1$s ', $this->_o_DB->quote($end));
        }

        $sql = sprintf('
            SELECT 
                ChiTietBanGiao.*
                , BanGiao.Ngay
                , BanGiao.IOID AS RefBanGiao
                , BanGiao.SoPhieu              		
                , IFNULL(ChiTietBanGiao.SoLuong, 0) AS SoLuongDaBanGiao
                , SUM(IFNULL(ChiTietThuHoi.SoLuong, 0)) AS SoLuongDaThuHoi
                , (IFNULL(ChiTietBanGiao.SoLuong, 0) - SUM(IF(IFormThuHoi.IFID, IFNULL(ChiTietThuHoi.SoLuong, 0), 0))) SoLuongConLai
                , ((CEIL((TIMESTAMPDIFF(DAY, BanGiao.Ngay, %3$s)/30)*100)/100) + IFNULL(ChiTietBanGiao.ThoiGianDaSuDung, 0)) AS ThoiGianDaSuDung
                , (1 - IF(IFNULL(DungCu.GiaTri, 0) != 0, (CEIL((TIMESTAMPDIFF(DAY, BanGiao.Ngay, %3$s)/30)*100)/100 + IFNULL(ChiTietBanGiao.ThoiGianDaSuDung, 0))/DungCu.GiaTri, 0)) AS PhanTramConLai
                , NhanVien.MaPhongBan AS NhaMayHienTai
            FROM OPhieuBanGiaoTaiSan AS BanGiao
            INNER JOIN qsiforms AS IFormBanGiao ON BanGiao.IFID_M182 = IFormBanGiao.IFID
            INNER JOIN OChiTietBanGiaoTaiSan AS ChiTietBanGiao ON BanGiao.IFID_M182 = ChiTietBanGiao.IFID_M182
            INNER JOIN ODanhSachNhanVien AS NhanVien ON ChiTietBanGiao.Ref_MaNhanVien = NhanVien.IOID
            INNER JOIN ODanhMucCongCuDungCu AS DungCu ON ChiTietBanGiao.Ref_MaTaiSan = DungCu.IOID
            LEFT JOIN OChiTietThuHoiTaiSan AS ChiTietThuHoi ON BanGiao.IOID = ChiTietThuHoi.Ref_PhieuBanGiao
                AND ChiTietBanGiao.Ref_MaNhanVien = ChiTietThuHoi.Ref_MaNhanVien
                AND ChiTietBanGiao.Ref_MaTaiSan = ChiTietThuHoi.Ref_MaTaiSan            	   	
            LEFT JOIN OPhieuThuHoiTaiSan AS ThuHoi ON ThuHoi.IFID_M183 = ChiTietThuHoi.IFID_M183 %4$s
            LEFT JOIN qsiforms AS IFormThuHoi ON ThuHoi.IFID_M183 = IFormThuHoi.IFID
                AND IFNULL(IFormThuHoi.Status, 0) = 2
            
            WHERE IFNULL(IFormBanGiao.Status, 0) = 2 %1$s
            GROUP BY BanGiao.IOID, ChiTietBanGiao.Ref_MaNhanVien, ChiTietBanGiao.Ref_MaTaiSan  
            HAVING 1=1 %2$s
            ORDER BY  BanGiao.Ngay              
        ', $where, $having, $this->_o_DB->quote(date('Y-m-d')), $joinThuHoi);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }


    public function getRestAssetsOfEmployees(
        $nhaMay = ''
        , $boPhan = ''
        , $employee = 0
        , $asset = 0)
    {
        $where  = $nhaMay?sprintf(' AND IFNULL(ChiTietBanGiao.Ref_NhaMay, 0) = %1$d ', $nhaMay):'';
        $where .= $boPhan?sprintf(' AND IF( IFNULL(ChiTietBanGiao.Ref_NhaMay, 0) <> IFNULL(NhanVien.Ref_MaPhongBan, 0) 
                    , ChiTietBanGiao.Ref_BoPhan
                    , NhanVien.Ref_MaBoPhan
                )  = %1$d ', $boPhan):'';
        $where .= $employee?sprintf(' AND NhanVien.IOID = %1$d ', $employee):'';
        $where .= $asset?sprintf(' AND DungCu.IOID = %1$d ', $asset):'';

        $sql = sprintf('
            SELECT 
                ChiTietBanGiao.*
                , IF( IFNULL(ChiTietBanGiao.Ref_NhaMay, 0) <> IFNULL(NhanVien.Ref_MaPhongBan, 0) 
                    , ChiTietBanGiao.Ref_BoPhan
                    , NhanVien.Ref_MaBoPhan
                ) AS Ref_BoPhan                
                , IF( IFNULL(ChiTietBanGiao.Ref_NhaMay, 0) <> IFNULL(NhanVien.Ref_MaPhongBan, 0) 
                    , ChiTietBanGiao.BoPhan
                    , NhanVien.TenBoPhan
                ) AS BoPhan
                , BanGiao.Ngay
                , BanGiao.IOID AS RefBanGiao
                , BanGiao.SoPhieu              		
                , SUM(IFNULL(ChiTietBanGiao.SoLuong, 0)) AS SoLuongDaBanGiao
                , SUM(IFNULL(ChiTietThuHoi.SoLuong, 0)) AS SoLuongDaThuHoi
                , ((IFNULL(ChiTietBanGiao.SoLuong, 0)) - SUM(IF(IFNULL(IFormThuHoi.IFID, 0) != 0, IFNULL(ChiTietThuHoi.SoLuong, 0), 0))) SoLuongConLai
                , NhanVien.SoCMND
                , NhanVien.MaPhongBan AS NhaMayHienTai
                , NhanVien.MaBoPhan AS BoPhanHienTai
            FROM OPhieuBanGiaoTaiSan AS BanGiao
            INNER JOIN qsiforms AS IFormBanGiao ON BanGiao.IFID_M182 = IFormBanGiao.IFID
            INNER JOIN OChiTietBanGiaoTaiSan AS ChiTietBanGiao ON BanGiao.IFID_M182 = ChiTietBanGiao.IFID_M182
            INNER JOIN ODanhSachNhanVien AS NhanVien ON ChiTietBanGiao.Ref_MaNhanVien = NhanVien.IOID
            INNER JOIN ODanhMucCongCuDungCu AS DungCu ON ChiTietBanGiao.Ref_MaTaiSan = DungCu.IOID
            LEFT JOIN OChiTietThuHoiTaiSan AS ChiTietThuHoi ON BanGiao.IOID = ChiTietThuHoi.Ref_PhieuBanGiao
                AND ChiTietBanGiao.Ref_MaNhanVien = ChiTietThuHoi.Ref_MaNhanVien
                AND ChiTietBanGiao.Ref_MaTaiSan = ChiTietThuHoi.Ref_MaTaiSan            	   	
            LEFT JOIN OPhieuThuHoiTaiSan AS ThuHoi ON ThuHoi.IFID_M183 = ChiTietThuHoi.IFID_M183
            LEFT JOIN qsiforms AS IFormThuHoi ON ThuHoi.IFID_M183 = IFormThuHoi.IFID
                AND IFNULL(IFormThuHoi.Status, 0) = 2
            WHERE IFNULL(IFormBanGiao.Status, 0) = 2 %1$s
            GROUP BY ChiTietBanGiao.Ref_MaNhanVien, ChiTietBanGiao.Ref_MaTaiSan  
            HAVING SoLuongConLai > 0
            ORDER BY  ChiTietBanGiao.NhaMay, BoPhan, NhanVien.MaNhanVien, DungCu.IOID  
        ', $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }


    public function getAssetsHistory($handoverLine, $employee, $asset)
    {
        $retval           = array();
        $tempPhieuThuHoi  = -1;
        $tempPhieuBanGiao = $handoverLine;
        $tempNhanVien     = -1;
        $tempNhanVienMoi  = -1;




        while($tempPhieuThuHoi != 0)
        {
            if($tempNhanVien == -1)
            {
                $tempNhanVien = $employee;
            }

            $sqlBanGiao = sprintf('
                SELECT * , BanGiao.Ngay AS NgayBanGiaoChinh, "RECEIVE" AS TranType
                FROM OPhieuBanGiaoTaiSan AS BanGiao
                INNER JOIN qsiforms AS IFormBanGiao ON BanGiao.IFID_M182 = IFormBanGiao.IFID
                INNER JOIN OChiTietBanGiaoTaiSan AS ChiTietBanGiao ON BanGiao.IFID_M182 = ChiTietBanGiao.IFID_M182
                WHERE BanGiao.IOID = %1$d 
                    AND IFNULL(IFormBanGiao.Status, 0) = 2
                    AND ChiTietBanGiao.Ref_MaNhanVien = %2$d AND ChiTietBanGiao.Ref_MaTaiSan = %3$d
            ', $tempPhieuBanGiao, $tempNhanVien, $asset);
            $datBanGiao = $this->_o_DB->fetchOne($sqlBanGiao);

            // echo '<pre>'; print_r($sqlBanGiao);

            if($datBanGiao)
            {
                if($tempPhieuBanGiao == $handoverLine) // Phieu ban giao dau tien chen vao dau
                {
                    $retval[] = $datBanGiao;
                }
                else // phieu ban giao cua phieu thu hoi sau do
                {
                    array_unshift ($retval, $datBanGiao);
                }

                if($datBanGiao->Ref_PhieuThuHoi)
                {
                    $tempPhieuThuHoi = $datBanGiao->Ref_PhieuThuHoi;

                    // @todo: Can check ca ngay ban giao = ngay ban giao trong phieu thu hoi
                    $sqlThuHoi = sprintf('
                        SELECT *, ThuHoi.Ngay AS NgayThuHoiChinh, "RETURN" AS TranType
                        FROM OPhieuThuHoiTaiSan AS ThuHoi
                        INNER JOIN qsiforms AS IFormThuHoi ON ThuHoi.IFID_M183 = IFormThuHoi.IFID
                        INNER JOIN OChiTietThuHoiTaiSan AS ChiTietThuHoi ON ThuHoi.IFID_M183 = ChiTietThuHoi.IFID_M183
                        WHERE ThuHoi.IOID = %1$d 
                            AND IFNULL(IFormThuHoi.Status, 0) = 2
                            AND ChiTietThuHoi.Ref_MaNhanVienMoi = %2$d AND ChiTietThuHoi.Ref_MaTaiSan = %3$d
                    ', $tempPhieuThuHoi, $tempNhanVien, $asset);
                    $datThuHoi = $this->_o_DB->fetchOne($sqlThuHoi);
                    // echo '<pre>'; print_r($sqlThuHoi);


                    if($datThuHoi)
                    {
                        array_unshift ($retval, $datThuHoi);

                        if($datThuHoi->Ref_PhieuBanGiao)
                        {
                            $tempPhieuBanGiao = $datThuHoi->Ref_PhieuBanGiao;
                            $tempNhanVien     = $datThuHoi->Ref_MaNhanVien;
                        }
                    }
                    else
                    {
                        $tempPhieuThuHoi = 0;
                    }
                }
                else
                {
                    $tempPhieuThuHoi = 0;
                }
            }
            else
            {
                $tempPhieuThuHoi = 0;
            }
        }

        $sqlThuHoiTuBanGiaoNay = sprintf('
            SELECT *, ThuHoi.Ngay AS NgayThuHoiChinh, "RETURN" AS TranType
            FROM OPhieuThuHoiTaiSan AS ThuHoi
            INNER JOIN qsiforms AS IFormThuHoi ON ThuHoi.IFID_M183 = IFormThuHoi.IFID
            INNER JOIN OChiTietThuHoiTaiSan AS ChiTietThuHoi ON ThuHoi.IFID_M183 = ChiTietThuHoi.IFID_M183                    WHERE ChiTietThuHoi.Ref_PhieuBanGiao = %1$d AND IFNULL(IFormThuHoi.Status, 0) = 2
            
            AND ChiTietThuHoi.Ref_MaNhanVien = %2$d AND ChiTietThuHoi.Ref_MaTaiSan = %3$d', $handoverLine, $employee, $asset);
        // echo '<Pre>'; print_r($sqlThuHoiTuBanGiaoNay); die;

        $datThuHoiTuBanGiaoNay = $this->_o_DB->fetchAll($sqlThuHoiTuBanGiaoNay);

        if($datThuHoiTuBanGiaoNay)
        {
            foreach ($datThuHoiTuBanGiaoNay as $item)
            {
                $retval[] = $item;
            }
        }

// echo '<pre>'; print_r($retval); die;
        return $retval;
    }

    public function getHandoverDocNo($nhaMay = '')
    {
        $object     = new Qss_Model_Object(); $object->v_fInit('OPhieuBanGiaoTaiSan', 'M182');
        $document = new Qss_Model_Extra_Document($object);
        $document->setLenth(5);
        $document->setDocField('SoPhieu');
        $document->setPrefix($nhaMay.'BG.');
        return $document->getDocumentNo();
    }

    public function getClawbackDocNo($nhaMay = '')
    {
        $object     = new Qss_Model_Object(); $object->v_fInit('OPhieuThuHoiTaiSan', 'M183');
        $document = new Qss_Model_Extra_Document($object);
        $document->setLenth(5);
        $document->setDocField('SoPhieu');
        $document->setPrefix($nhaMay.'TH.');
        return $document->getDocumentNo();
    }

}