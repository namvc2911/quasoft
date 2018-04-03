<?php
/**
 * Don mua hang
 *
 */
class Qss_Model_Purchase_Order extends Qss_Model_Abstract
{
    public function __construct ()
    {
        parent::__construct();
    }

    public function getDocNo()
    {
        $object = new Qss_Model_Object();
        $object->v_fInit('ODonMuaHang', 'M401');
        $document = new Qss_Model_Extra_Document($object);
        $document->setLenth(6);
        $document->setDocField('SoDonHang');
        $document->setPrefix('PO');
        return $document->getDocumentNo();
    }

    public function getOrderByIFID($orderIOID)
    {
        $sql = sprintf('
            SELECT donhang.*
            FROM ODonMuaHang AS donhang
            WHERE donhang.IFID_M401 = %1$d
        ', $orderIOID);
        return $this->_o_DB->fetchOne($sql);        
    }

    public function getOrderByIOID($orderIOID)
    {
        $sql = sprintf('
            SELECT donhang.*
            FROM ODonMuaHang AS donhang
            WHERE donhang.IOID = %1$d
        ', $orderIOID);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getOrderLineByIOID($orderIOID)
    {
        $sql = sprintf('
            SELECT danhsach.*
            FROM ODonMuaHang AS donhang
            INNER JOIN ODSDonMuaHang AS danhsach On donhang.IFID_M401 = danhsach.IFID_M401
            WHERE donhang.IOID = %1$d
        ', $orderIOID);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getOrderLineByIFID($orderIFID)
    {
        $sql = sprintf('
            SELECT danhsach.*
            FROM ODonMuaHang AS donhang
            INNER JOIN ODSDonMuaHang AS danhsach On donhang.IFID_M401 = danhsach.IFID_M401
            WHERE donhang.IFID_M401 = %1$d
        ', $orderIFID);
        return $this->_o_DB->fetchAll($sql);
    }

    public function countSessionsByUser($uid, $start = '', $end = '')
    {
        $where = ($start && $end)?sprintf(' AND (phien.Ngay BETWEEN %1$s AND %2$s) ', $this->_o_DB->quote($start), $this->_o_DB->quote($end)):'';
        $sql = sprintf('
            SELECT count(1) AS Total
            FROM OPhienXuLyMuaHang AS phien
            INNER JOIN qsiforms ON phien.IFID_M415 = qsiforms.IFID
            WHERE qsiforms.UID = %1$d %2$s
        ',$uid, $where);

        $dataSql = $this->_o_DB->fetchOne($sql);

        return $dataSql?$dataSql->Total:0;
    }

    /**
     * Lấy toàn bộ session theo user
     * @param unknown $uid
     */
    public function getAllSessionByUser($uid, $start = '', $end = '', $page = 0, $perpage = 0)
    {
        $where = ($start && $end)?sprintf(' AND (phien.Ngay BETWEEN %1$s AND %2$s) ', $this->_o_DB->quote($start), $this->_o_DB->quote($end)):'';
        $limit = '';

        if($page && $perpage)
        {
            $startSelect = ($page - 1) * $perpage;
            $limit       = sprintf(' LIMIT %1$d, %2$d', $startSelect, $perpage);
        }

        $sql = sprintf('
            SELECT
                phien.*
                , CacPhieuYeuCau.CacPhieuYeuCau
                , CacDonMuaHang.CacDonMuaHang
                , CacBaoGia.CacBaoGia
                , CONCAT( \'<a href=\"/user/form/edit?ifid=\',  KeHoach.IFID_M716 , \'&deptid=%2$d\" target=\"_blank\">\', phien.SoKeHoach, \'</a>\') AS SoKeHoach
            FROM OPhienXuLyMuaHang AS phien
            INNER JOIN qsiforms ON phien.IFID_M415 = qsiforms.IFID

            LEFT JOIN OKeHoachMuaSam AS KeHoach ON phien.Ref_SoKeHoach = KeHoach.IOID

            LEFT JOIN
            (
                SELECT  GROUP_CONCAT(CONCAT( \'<a href=\"/user/form/edit?ifid=\',  OYeuCauMuaSam.IFID_M412 , \'&deptid=%2$d\" target=\"_blank\">\', OYeuCauPhienXLMH.SoPhieu, \'</a>\') SEPARATOR \' , \') AS CacPhieuYeuCau, OPhienXuLyMuaHang.IFID_M415
                FROM OYeuCauMuaSam
                INNER JOIN OYeuCauPhienXLMH ON OYeuCauMuaSam.IOID = OYeuCauPhienXLMH.Ref_SoPhieu
                INNER JOIN OPhienXuLyMuaHang ON OYeuCauPhienXLMH.IFID_M415 = OPhienXuLyMuaHang.IFID_M415
                INNER JOIN qsiforms ON OPhienXuLyMuaHang.IFID_M415 = qsiforms.IFID
                WHERE qsiforms.UID = %1$d
                GROUP BY OPhienXuLyMuaHang.IFID_M415
            ) AS CacPhieuYeuCau ON CacPhieuYeuCau.IFID_M415 = phien.IFID_M415

            LEFT JOIN
            (
                SELECT  GROUP_CONCAT(DISTINCT CONCAT( \'<a href=\"/user/form/edit?ifid=\',  ODonMuaHang.IFID_M401 , \'&deptid=%2$d\" target=\"_blank\">\', ODonMuaHang.SoDonHang, \'</a>\') SEPARATOR \' , \') AS CacDonMuaHang, OPhienXuLyMuaHang.IFID_M415
                FROM OPhienXuLyMuaHang
                INNER JOIN OYeuCauPhienXLMH ON OPhienXuLyMuaHang.IFID_M415 = OYeuCauPhienXLMH.IFID_M415
                INNER JOIN qsiforms ON OPhienXuLyMuaHang.IFID_M415 = qsiforms.IFID
                INNER JOIN ODSDonMuaHang ON IFNULL(OYeuCauPhienXLMH.Ref_SoPhieu, 0) = IFNULL(ODSDonMuaHang.Ref_SoYeuCau, 0)
                INNER JOIN ODonMuaHang ON ODSDonMuaHang.IFID_M401 = ODonMuaHang.IFID_M401
                    AND IFNULL(ODonMuaHang.Ref_SoKeHoach, 0) = IFNULL(OPhienXuLyMuaHang.Ref_SoKeHoach, 0)
                WHERE qsiforms.UID = %1$d
                GROUP BY OPhienXuLyMuaHang.IFID_M415
            ) AS CacDonMuaHang  ON CacDonMuaHang.IFID_M415 = phien.IFID_M415

            LEFT JOIN
            (
                SELECT  GROUP_CONCAT( CONCAT( \'<a href=\"/user/form/edit?ifid=\',  OBaoGiaMuaHang.IFID_M406 , \'&deptid=%2$d\" target=\"_blank\">\',OBaoGiaMuaHang.SoChungTu, \'</a>\') SEPARATOR \' , \') AS CacBaoGia, OPhienXuLyMuaHang.IFID_M415
                FROM OPhienXuLyMuaHang
                INNER JOIN qsiforms ON OPhienXuLyMuaHang.IFID_M415 = qsiforms.IFID
                INNER JOIN OBaoGiaMuaHang ON IFNULL(OPhienXuLyMuaHang.Ref_SoKeHoach, 0) = IFNULL(OBaoGiaMuaHang.Ref_SoKeHoach, 0) AND IFNULL(OPhienXuLyMuaHang.Ref_SoKeHoach, 0) != 0
                WHERE qsiforms.UID = %1$d AND IFNULL(OBaoGiaMuaHang.KhongHopLe, 0) = 0
                GROUP BY OPhienXuLyMuaHang.IFID_M415
            ) AS CacBaoGia ON CacBaoGia.IFID_M415 = phien.IFID_M415

            WHERE qsiforms.UID = %1$d %3$s
            ORDER BY phien.Ngay DESC, qsiforms.SDate DESC
            %4$s
        ', $uid, $this->_user->user_dept_id, $where, $limit);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
    
    /**
     * Lấy session theo ifid
     * @param unknown $ifid
     */
    public function getSessionByIFID($ifid)
    {
        $sql = sprintf('
            SELECT
                phien.*, ifnull(phien.Buoc, 1) AS Buoc
            FROM OPhienXuLyMuaHang AS phien
            WHERE phien.IFID_M415 = %1$d
        ', $ifid);
        return $this->_o_DB->fetchOne($sql);
    }

    /**
     *
     */
    public function getSessionLineByIFID($ifid)
    {
        $sql = sprintf('
            SELECT
                phien.*
            FROM OYeuCauPhienXLMH AS phien
            WHERE phien.IFID_M415 = %1$d
        ', $ifid);
        return $this->_o_DB->fetchAll($sql);
    }
    
    /**
     * Lấy các đơn hàng rẻ nhất trong vài tháng gần đây theo yêu cầu của phiên xử lý mua hàng
     * Lưu 
     * @param unknown $sessionIFID
     * @param unknown $monthsAgo
     */
    public function getCheapestOrdersBySession($sessionIFID, $sessionDate, $fromDate)
    {
        $where = '';
        
        // Tính tư vài tháng trước đây
        if($fromDate)
        {
            $today   = $sessionDate?$sessionDate:date('Y-m-d');

            $where  .= sprintf(' and (donhang.NgayDatHang between %1$s and %2$s) '
                , $this->_o_DB->quote($fromDate), $this->_o_DB->quote($today));
        }
        
        $sql = sprintf('

            SELECT
                donmuahanggiarenhat.*
                , YeuCauPhienMuaHang.SoYeuCau
                , YeuCauPhienMuaHang.RequestIOID
            FROM

            /* Lấy ra đơn hàng rẻ nhất nhóm theo nhà cung cấp */
            (
                SELECT t1.*
                FROM
                (
                    SELECT
                        dsdonhang.Ref_MaSP AS ItemIOID
                        , dsdonhang.Ref_DonViTinh AS UomIOID
                        , dsdonhang.MaSP
                        , dsdonhang.TenSanPham
                        , dsdonhang.DonViTinh
                        , ifnull(yeucaumuahang.SoLuong, 0) AS SoLuongYeuCau
                        , ifnull(dsdonhang.DonGia, 0) AS DonGia
                        , donhang.Ref_MaNCC
                        , donhang.TenNCC
                        , ifnull(donhang.IFID_M401,0) AS OrderIFID
                        , donhang.LoaiTien
                        , donhang.SoDonHang

                    FROM ODonMuaHang AS donhang
                    INNER JOIN ODSDonMuaHang AS dsdonhang ON donhang.IFID_M401 = dsdonhang.IFID_M401
                    RIGHT JOIN
                    (
                        SELECT
                            dsyeucau.* , yeucau.SoPhieu, yeucau.IOID AS RequestIOID
                        FROM ODSYeuCauMuaSam AS dsyeucau
                        INNER JOIN OYeuCauMuaSam AS yeucau ON dsyeucau.IFID_M412 = yeucau.IFID_M412
                        INNER JOIN OYeuCauPhienXLMH AS yeucaucuaphien ON yeucau.IOID = yeucaucuaphien.Ref_SoPhieu
                        WHERE yeucaucuaphien.IFID_M415 = %1$d
                    ) AS yeucaumuahang ON ifnull(dsdonhang.Ref_MaSP, 0) = ifnull(yeucaumuahang.Ref_MaSP, 0)
                        AND ifnull(dsdonhang.Ref_DonViTinh, 0) = ifnull(yeucaumuahang.Ref_DonViTinh, 0)
                        AND IFNULL(dsdonhang.Ref_SoYeuCau, 0) != IFNULL(yeucaumuahang.RequestIOID, 0)
                    WHERE 1=1 %2$s /* and (donhang.NgayDatHang between 1 and 2) */
                    ORDER BY dsdonhang.Ref_MaSP, dsdonhang.Ref_DonViTinh, ifnull(dsdonhang.DonGia, 0) ASC
                ) AS t1
                GROUP BY t1.ItemIOID, t1.UomIOID

            ) AS donmuahanggiarenhat

            INNER JOIN (
                SELECT
                    ODSYeuCauMuaSam.Ref_DonViTinh
                    , ODSYeuCauMuaSam.Ref_MaSP
                    , OYeuCauMuaSam.IFID_M412 AS RequestIFID
                    , OYeuCauMuaSam.IOID AS RequestIOID
                    , OYeuCauMuaSam.SoPhieu AS SoYeuCau
                FROM OPhienXuLyMuaHang
                INNER JOIN OYeuCauPhienXLMH ON OPhienXuLyMuaHang.IFID_M415 = OYeuCauPhienXLMH.IFID_M415
                INNER JOIN OYeuCauMuaSam ON OYeuCauPhienXLMH.Ref_SoPhieu = OYeuCauMuaSam.IOID
                INNER JOIN ODSYeuCauMuaSam ON OYeuCauMuaSam.IFID_M412 = ODSYeuCauMuaSam.IFID_M412
                WHERE OPhienXuLyMuaHang.IFID_M415 = %1$d
            ) AS YeuCauPhienMuaHang ON donmuahanggiarenhat.ItemIOID = YeuCauPhienMuaHang.Ref_MaSP
                AND donmuahanggiarenhat.UomIOID = YeuCauPhienMuaHang.Ref_DonViTinh
            ORDER BY donmuahanggiarenhat.Ref_MaNCC, YeuCauPhienMuaHang.RequestIOID, donmuahanggiarenhat.MaSP, donmuahanggiarenhat.DonViTinh
            
        ', $sessionIFID, $where);
        
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);        
    }
    
    /**
     * Lấy các đơn hàng Gần nhất trong vài tháng gần đây theo yêu cầu của phiên xử lý mua hàng
     * <Neu co nhieu cung ngay thi lay ban ghi cuoi cung duoc tao trong ngay>
     * Lưu
     * @param unknown $sessionIFID
     * @param unknown $monthsAgo
     */
    public function getLastestOrdersBySession($sessionIFID, $sessionDate, $fromDate)
    {
        $where = '';
    
        // Tính tư vài tháng trước đây
        if($fromDate)
        {
            $today   = $sessionDate?$sessionDate:date('Y-m-d');

            $where  .= sprintf(' and (donhang.NgayDatHang between %1$s and %2$s) '
                , $this->_o_DB->quote($fromDate), $this->_o_DB->quote($today));
        }
    
        $sql = sprintf('

            SELECT
                donmuahanggiarenhat.*
                , YeuCauPhienMuaHang.SoYeuCau
                , YeuCauPhienMuaHang.RequestIOID
            FROM

            /* Lấy ra đơn hàng rẻ nhất nhóm theo nhà cung cấp */
            (
                SELECT t1.*
                FROM
                (
                    SELECT
                        dsdonhang.Ref_MaSP AS ItemIOID
                        , dsdonhang.Ref_DonViTinh AS UomIOID
                        , dsdonhang.MaSP
                        , dsdonhang.TenSanPham
                        , dsdonhang.DonViTinh
                        , ifnull(yeucaumuahang.SoLuong, 0) AS SoLuongYeuCau
                        , ifnull(dsdonhang.DonGia, 0) AS DonGia
                        , donhang.Ref_MaNCC
                        , donhang.TenNCC
                        , ifnull(donhang.IFID_M401,0) AS OrderIFID
                        , donhang.LoaiTien
                        , donhang.SoDonHang

                    FROM ODonMuaHang AS donhang
                    INNER JOIN ODSDonMuaHang AS dsdonhang ON donhang.IFID_M401 = dsdonhang.IFID_M401
                    RIGHT JOIN
                    (
                        SELECT
                            dsyeucau.* , yeucau.SoPhieu, yeucau.IOID AS RequestIOID
                        FROM ODSYeuCauMuaSam AS dsyeucau
                        INNER JOIN OYeuCauMuaSam AS yeucau ON dsyeucau.IFID_M412 = yeucau.IFID_M412
                        INNER JOIN OYeuCauPhienXLMH AS yeucaucuaphien ON yeucau.IOID = yeucaucuaphien.Ref_SoPhieu
                        WHERE yeucaucuaphien.IFID_M415 = %1$d
                    ) AS yeucaumuahang ON ifnull(dsdonhang.Ref_MaSP, 0) = ifnull(yeucaumuahang.Ref_MaSP, 0)
                        AND ifnull(dsdonhang.Ref_DonViTinh, 0) = ifnull(yeucaumuahang.Ref_DonViTinh, 0)
                        AND IFNULL(dsdonhang.Ref_SoYeuCau, 0) != IFNULL(yeucaumuahang.RequestIOID, 0)
                    WHERE 1=1 %2$s /* and (donhang.NgayDatHang between 1 and 2) */
                    ORDER BY dsdonhang.Ref_MaSP, dsdonhang.Ref_DonViTinh, donhang.NgayDatHang DESC, donhang.IOID DESC
                ) AS t1
                GROUP BY t1.ItemIOID, t1.UomIOID

            ) AS donmuahanggiarenhat

            INNER JOIN (
                SELECT
                    ODSYeuCauMuaSam.Ref_DonViTinh
                    , ODSYeuCauMuaSam.Ref_MaSP
                    , OYeuCauMuaSam.IFID_M412 AS RequestIFID
                    , OYeuCauMuaSam.IOID AS RequestIOID
                    , OYeuCauMuaSam.SoPhieu AS SoYeuCau
                FROM OPhienXuLyMuaHang
                INNER JOIN OYeuCauPhienXLMH ON OPhienXuLyMuaHang.IFID_M415 = OYeuCauPhienXLMH.IFID_M415
                INNER JOIN OYeuCauMuaSam ON OYeuCauPhienXLMH.Ref_SoPhieu = OYeuCauMuaSam.IOID
                INNER JOIN ODSYeuCauMuaSam ON OYeuCauMuaSam.IFID_M412 = ODSYeuCauMuaSam.IFID_M412
                WHERE OPhienXuLyMuaHang.IFID_M415 = %1$d
            ) AS YeuCauPhienMuaHang ON donmuahanggiarenhat.ItemIOID = YeuCauPhienMuaHang.Ref_MaSP
                AND donmuahanggiarenhat.UomIOID = YeuCauPhienMuaHang.Ref_DonViTinh
            ORDER BY donmuahanggiarenhat.Ref_MaNCC, YeuCauPhienMuaHang.RequestIOID, donmuahanggiarenhat.MaSP, donmuahanggiarenhat.DonViTinh
        ', $sessionIFID, $where);
    
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }    
    
    /**
     * Lấy các đơn hàng nhanh nhat trong vài tháng gần đây theo yêu cầu của phiên xử lý mua hàng
     * <Neu co nhieu bang nhau thi lay thang gan nhat>
     * Lưu
     * @param unknown $sessionIFID
     * @param unknown $monthsAgo
     */
    public function getFastestOrdersBySession($sessionIFID, $sessionDate, $fromDate)
    {
        $where = '';
    
        // Tính tư vài tháng trước đây
        if($fromDate)
        {
            $today   = $sessionDate?$sessionDate:date('Y-m-d');

            $where  .= sprintf(' and (donhang.NgayDatHang between %1$s and %2$s) '
                , $this->_o_DB->quote($fromDate), $this->_o_DB->quote($today));
        }

        $sql = sprintf('
                SELECT
                    donmuahanggiarenhat.*
                    , YeuCauPhienMuaHang.SoYeuCau
                    , YeuCauPhienMuaHang.RequestIOID
                FROM
    
                /* Lấy ra đơn hàng rẻ nhất nhóm theo nhà cung cấp */
                (
                    SELECT t1.*
                    FROM
                    (
                        SELECT
                            dsdonhang.Ref_MaSP AS ItemIOID
                            , dsdonhang.Ref_DonViTinh AS UomIOID
                            , dsdonhang.MaSP
                            , dsdonhang.TenSanPham
                            , dsdonhang.DonViTinh
                            , ifnull(yeucaumuahang.SoLuong, 0) AS SoLuongYeuCau
                            , ifnull(dsdonhang.DonGia, 0) AS DonGia
                            , donhang.Ref_MaNCC
                            , donhang.TenNCC
                            , ifnull(donhang.IFID_M401,0) AS OrderIFID
                            , donhang.LoaiTien
                            , donhang.SoDonHang

                        FROM ODonMuaHang AS donhang
                        INNER JOIN ODSDonMuaHang AS dsdonhang ON donhang.IFID_M401 = dsdonhang.IFID_M401
                        RIGHT JOIN
                        (
                            SELECT
                                dsyeucau.* , yeucau.SoPhieu, yeucau.IOID AS RequestIOID
                            FROM ODSYeuCauMuaSam AS dsyeucau
                            INNER JOIN OYeuCauMuaSam AS yeucau ON dsyeucau.IFID_M412 = yeucau.IFID_M412
                            INNER JOIN OYeuCauPhienXLMH AS yeucaucuaphien ON yeucau.IOID = yeucaucuaphien.Ref_SoPhieu
                            WHERE yeucaucuaphien.IFID_M415 = %1$d
                        ) AS yeucaumuahang ON ifnull(dsdonhang.Ref_MaSP, 0) = ifnull(yeucaumuahang.Ref_MaSP, 0)
                            AND ifnull(dsdonhang.Ref_DonViTinh, 0) = ifnull(yeucaumuahang.Ref_DonViTinh, 0)
                            AND IFNULL(dsdonhang.Ref_SoYeuCau, 0) != IFNULL(yeucaumuahang.RequestIOID, 0)
                        WHERE 1=1 %2$s /* and (donhang.NgayDatHang between 1 and 2) */
                        ORDER BY dsdonhang.Ref_MaSP, dsdonhang.Ref_DonViTinh,
            				CASE WHEN ifnull(donhang.NgayYCNH, \'\') = \'\' OR ifnull(donhang.NgayYCNH, \'\') = \'0000-00-00\'
							THEN 10000000000 ELSE DATEDIFF(donhang.NgayYCNH,donhang.NgayDatHang) END 
                            , donhang.NgayDatHang DESC, donhang.IOID DESC
                    ) AS t1
                    GROUP BY t1.ItemIOID, t1.UomIOID

                ) AS donmuahanggiarenhat
    
                INNER JOIN (
                    SELECT
                        ODSYeuCauMuaSam.Ref_DonViTinh
                        , ODSYeuCauMuaSam.Ref_MaSP
                        , OYeuCauMuaSam.IFID_M412 AS RequestIFID
                        , OYeuCauMuaSam.IOID AS RequestIOID
                        , OYeuCauMuaSam.SoPhieu AS SoYeuCau
                    FROM OPhienXuLyMuaHang
                    INNER JOIN OYeuCauPhienXLMH ON OPhienXuLyMuaHang.IFID_M415 = OYeuCauPhienXLMH.IFID_M415
                    INNER JOIN OYeuCauMuaSam ON OYeuCauPhienXLMH.Ref_SoPhieu = OYeuCauMuaSam.IOID
                    INNER JOIN ODSYeuCauMuaSam ON OYeuCauMuaSam.IFID_M412 = ODSYeuCauMuaSam.IFID_M412
                    WHERE OPhienXuLyMuaHang.IFID_M415 = %1$d
                ) AS YeuCauPhienMuaHang ON donmuahanggiarenhat.ItemIOID = YeuCauPhienMuaHang.Ref_MaSP
                    AND donmuahanggiarenhat.UomIOID = YeuCauPhienMuaHang.Ref_DonViTinh

                ORDER BY donmuahanggiarenhat.Ref_MaNCC, YeuCauPhienMuaHang.RequestIOID, donmuahanggiarenhat.MaSP, donmuahanggiarenhat.DonViTinh


    
        ', $sessionIFID, $where);
    
        return $this->_o_DB->fetchAll($sql);
    }
    
    public function getRequestInSession($requestIOID, $sessionIFID)
    {
        $sql = sprintf('
            SELECT yeucauphien.* 
            FROM OYeuCauPhienXLMH AS yeucauphien
            WHERE yeucauphien.Ref_SoPhieu = %1$d AND yeucauphien.IFID_M415 = %2$d
        ', $requestIOID, $sessionIFID);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getOrdersBySession($sessionIFID)
    {
        $sql = sprintf('
            SELECT
                donhang.*, qsiforms.Status
            FROM ODonMuaHang AS donhang
            INNER JOIN qsiforms ON donhang.IFID_M401 = qsiforms.IFID
            INNER JOIN ODSDonMuaHang AS danhsach ON donhang.IFID_M401 = danhsach.IFID_M401
            INNER JOIN OYeuCauPhienXLMH AS yeucaucuaphien ON danhsach.Ref_SoYeuCau = yeucaucuaphien.Ref_SoPhieu
            WHERE yeucaucuaphien.IFID_M415 = %1$d
            GROUP BY donhang.IFID_M401            
        ', $sessionIFID);
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);        
    }

    public function getOrderLinesBySession($sessionIFID)
    {
        $sql = sprintf('
            SELECT
                danhsach.*, donhang.Ref_MaNCC, donhang.MaNCC, donhang.TenNCC
            FROM ODonMuaHang AS donhang
            INNER JOIN qsiforms ON donhang.IFID_M401 = qsiforms.IFID
            INNER JOIN ODSDonMuaHang AS danhsach ON donhang.IFID_M401 = danhsach.IFID_M401
            INNER JOIN OYeuCauPhienXLMH AS yeucaucuaphien ON danhsach.Ref_SoYeuCau = yeucaucuaphien.Ref_SoPhieu
            WHERE yeucaucuaphien.IFID_M415 = %1$d
        ', $sessionIFID);

//        echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy thông tin đặt hàng
     * @param $planIOID
     * @return mixed
     */
    public function getOrderInfo($orderIOID, $planIOID = 0)
    {
        $where = $planIOID?sprintf(' AND IFNULL(DonMuaHang.Ref_SoKeHoach, 0) = %1$d ',$planIOID):'';

        $sql = sprintf('
            SELECT DonMuaHang.*
                , DonMuaHang.SoDonHang
                , BaoGia.SoChungTu AS SoBaoGia
                , BaoGia.NgayBaoGia
                , DoiTac.TenDoiTac
                , DoiTac.DiaChi
                , DoiTac.DienThoai
                , DoiTac.Fax
            FROM ODonMuaHang AS DonMuaHang
            LEFT JOIN OKeHoachMuaSam AS KeHoach ON IFNULL(DonMuaHang.Ref_SoKeHoach, 0) = KeHoach.IOID
            LEFT JOIN (
                SELECT  BaoGia1.*
                FROM OBaoGiaMuaHang AS BaoGia1
                INNER JOIN ODSBGMuaHang AS DanhSach1 ON BaoGia1.IFID_M406 = DanhSach1.IFID_M406
                WHERE IFNULL(DanhSach1.ChonBaoGia, 0) = 1 AND IFNULL(BaoGia1.Ref_SoKeHoach, 0) = %1$d
                GROUP By BaoGia1.IFID_M406
            ) AS BaoGia ON IFNULL(KeHoach.IOID, 0) = IFNULL(BaoGia.Ref_SoKeHoach, 0)
                AND DonMuaHang.Ref_MaNCC = BaoGia.Ref_MaNCC
            INNER JOIN ODoiTac AS DoiTac ON DonMuaHang.Ref_MaNCC = DoiTac.IOID
            WHERE IFNULL(DonMuaHang.IOID, 0) = %2$d %1$s
        ', $where, $orderIOID);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchOne($sql);
    }

    public function getOrderLineGroupByRequest($orderIOID)
    {
        $sql = sprintf('
            SELECT DanhSach.*
            FROM ODonMuaHang AS DonMuaHang
            INNER JOIN ODSDonMuaHang AS DanhSach ON DonMuaHang.IFID_M401 = DanhSach.IFID_M401
            LEFT JOIN OYeuCauMuaSam AS YeuCau ON DanhSach.Ref_SoYeuCau = YeuCau.IOID
            WHERE DonMuaHang.IOID = %1$d
            ORDER BY IFNULL(YeuCau.IOID, 0)
        ', $orderIOID);
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    public function getHireOrders($startDate, $endDate, $supplier = 0)
    {
        $lang          = $this->_user->user_lang;
        $stepNameField = ($lang == 'vn')?'Name':'Name_'.$lang;

        $filter        = '';
        $filter       .= $supplier?sprintf(' AND IFNULL(DonMua.Ref_MaNCC, 0) = %1$d ', $supplier):'';

        $sql = sprintf('
            SELECT
                DanhSachDonMua.*
                , DonMua.TenNCC
                , DonMua.SoDonHang
                , DonMua.NgayDatHang
                , DonMua.NgayYCNH
                , qsworkflowsteps.%3$s AS Name
                , qsworkflowsteps.Color
                , DonMua.Ref_MaNCC
            FROM ODSDonMuaHang AS DanhSachDonMua
            INNER JOIN ODonMuaHang AS DonMua ON DanhSachDonMua.IFID_M401 = DonMua.IFID_M401
            INNER JOIN qsiforms ON DonMua.IFID_M401 = qsiforms.IFID
            INNER JOIN qsworkflows ON qsiforms.FormCode = qsworkflows.FormCode
            INNER JOIN qsworkflowsteps ON qsworkflows.WFID = qsworkflowsteps.WFID
                AND qsiforms.Status = qsworkflowsteps.StepNo
            WHERE DonMua.NgayDatHang BETWEEN %1$s AND %2$s
                AND IFNULL(DanhSachDonMua.Thue, 0) = 1
                %4$s
            ORDER BY DanhSachDonMua.IFID_M401'
        , $this->_o_DB->quote($startDate)
        , $this->_o_DB->quote($endDate)
        , $stepNameField
        , $filter);
        //echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
}
