<?php

class Qss_Model_Maintenance_Request extends Qss_Model_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}

    /**
     * Lấy các yêu cầu chưa đóng, không bao gồm phiếu hủy
     */
    public function getRequestsDontClose($mStart = '', $mEnd = '', $locationIOID = 0, $equipIOID = 0, $equipGroup = 0)
    {
        $filter = ' AND qsiforms.Status in (0,1,2)';
        $filter.= ($mStart && $mEnd)?
            sprintf(' AND YeuCau.Ngay between %1$s and %2$s'
                ,$this->_o_DB->quote($mStart), $this->_o_DB->quote($mEnd)):'';
        $loc    = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locationIOID));
        $filter.= $loc?
            sprintf(' AND ifnull(ThietBi.Ref_MaKhuVuc, 0) IN (SELECT IOID FROM OKhuVuc WHERE lft >= %1$d AND rgt <= %2$d) '
                , $loc->lft, $loc->rgt):'';
        $filter.= $equipIOID?sprintf(' AND ThietBi.IOID = %1$d ', $equipIOID):'';
        $filter .= $equipGroup?sprintf(' AND IFNULL(ThietBi.Ref_NhomThietBi, 0) = %1$d ', $equipGroup):'';

        $sql = sprintf('
            SELECT YeuCau.*, TinhTrangXuLy.Name AS TinhTrangXuLy
            FROM OYeuCauBaoTri AS YeuCau
            INNER JOIN ODanhSachThietBi AS ThietBi ON YeuCau.Ref_MaThietBi = ThietBi.IOID
            INNER JOIN qsiforms ON YeuCau.IFID_M747 = qsiforms.IFID
            LEFT JOIN (
                select qsworkflowsteps.Name, YeuCau.IFID_M747
                FROM OYeuCauBaoTri AS YeuCau
                INNER JOIN ODanhSachThietBi AS ThietBi ON YeuCau.Ref_MaThietBi = ThietBi.IOID
                INNER JOIN OPhieuBaoTri ON YeuCau.IOID = OPhieuBaoTri.Ref_PhieuYeuCau
                inner join qsiforms on qsiforms.IFID = OPhieuBaoTri.IFID_M759
                inner join qsworkflows on qsworkflows.FormCode = qsiforms.FormCode
                inner join qsworkflowsteps on qsworkflowsteps.WFID = qsworkflows.WFID
                    and qsiforms.Status = qsworkflowsteps.StepNo
                WHERE 1=1 %1$s
            ) AS TinhTrangXuLy  ON YeuCau.IFID_M747 = TinhTrangXuLy.IFID_M747
            WHERE 1=1 %1$s
        ', $filter);
        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

	public function getRequestByIFID($ifid)
	{
		$sql = sprintf('
			SELECT
				YeuCau.*
				, SUM(CASE WHEN IFNULL(Phieu.IOID, 0) = 0 OR iform1.Status = 5 THEN 0 ELSE 1 END) AS SoLuongPhieuBaoTri
				, max( IFNULL(Phieu.IFID_M759, 0) ) AS BaoTriIFID
				, GROUP_CONCAT( IF(iform1.Status != 5, IFNULL(Phieu.IFID_M759, 0), null) SEPARATOR \', \') AS BaoTriIFIDs
				, GROUP_CONCAT( IF(iform1.Status != 5, IFNULL(Phieu.SoPhieu, \'\'), null) SEPARATOR \', \') AS BaoTriNos
			FROM OYeuCauBaoTri AS YeuCau
			LEFT JOIN OPhieuBaoTri AS Phieu ON YeuCau.IOID = Phieu.Ref_PhieuYeuCau
			LEFT JOIN qsiforms As iform1 ON Phieu.IFID_M759 = iform1.IFID
			WHERE YeuCau.IFID_M747 = %1$d
			GROUP BY YeuCau.IFID_M747
		', $ifid);
		// echo '<pre>'; print_r($sql); die;
		return $this->_o_DB->fetchOne($sql);
	}

	public function getRequests($start, $end, $locIOID = 0, $equipGroupIOID = 0,  $equipTypeIOID = 0, $equipIOID = 0)
    {
        $lang  = ($this->_user->user_lang == 'vn')?'':'_'.$this->_user->user_lang;
        $where  = '';
        $where .= ($start && $end)?sprintf(' AND (YeuCau.Ngay between %1$s and %2$s)', $this->_o_DB->quote($start), $this->_o_DB->quote($end)):'';
        $loc    = $locIOID?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):'';
        $where .= $loc?sprintf(' AND ((IFNULL(ThietBi.Ref_MaKhuVuc, 0) IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)) OR (IFNULL(YeuCau.Ref_MaKhuVuc, 0) IN (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)) )', $loc->lft, $loc->rgt):'';
        $type   = $this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $equipTypeIOID));
        $where .= $type?sprintf(' AND (IFNULL(ThietBi.Ref_LoaiThietBi, 0) IN (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d))', $type->lft, $type->rgt):'';
        $where .= ($equipGroupIOID)?sprintf(' AND IFNULL(ThietBi.Ref_NhomThietBi, 0) = %1$d  ', $equipGroupIOID):'';
        $where .= ($equipIOID)?sprintf(' AND IFNULL(YeuCau.Ref_MaThietBi, 0) = %1$d  ', $equipIOID):'';

        $sql = sprintf('
            SELECT 
                YeuCau.*
                , ThietBi.NhomThietBi
                , ThietBi.LoaiThietBi
                , WFlowStep.Name%1$s AS StepName
                , ThietBi.HangBaoHanh
            FROM OYeuCauBaoTri AS YeuCau
            INNER JOIN qsiforms AS IForm ON YeuCau.IFID_M747 = IForm.IFID
            INNER JOIN qsworkflows AS WFlow ON IForm.FormCode = WFlow.FormCode
            INNER JOIN qsworkflowsteps AS WFlowStep ON WFlow.WFID = WFlowStep.WFID AND IForm.Status =    WFlowStep.StepNo
            LEFT JOIN ODanhSachThietBi AS ThietBi ON ThietBi.IOID = YeuCau.Ref_MaThietBi
            WHERE 1=1 %2$s
            ORDER BY YeuCau.Ngay DESC -- Sap xep ngay theo yeu cau cua SDM
        ', $lang, $where);

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
	
	/**
    * @todo: Chuyển sang request của mua hàng
	 * All approved material requests not sent to purchase request yet
	 * @return object All approved material requests not sent yet
	 */
	public function getApprovedMaterialRequestNotSendYet()
	{
		$sql = sprintf('
			SELECT 
				ncvt.IFID_M709 AS IFID
				, ncvt.IOID
				, ncvt.SoPhieu
				, ncvt.Ngay
				, ncvt.NoiDung
			FROM ONhuCauVatTu AS ncvt
			INNER JOIN qsiforms AS qsi ON ncvt.IFID_M709 = qsi.IFID
			LEFT JOIN qsioidlink AS `link` ON link.FromIOID = ncvt.IOID
			    AND link.FromIFID = ncvt.IFID_M709
				AND link.FromIOID <> 0 AND link.ToIOID <> 0
			LEFT JOIN ONhuCauMuaHang AS ncmh ON ncmh.IOID = link.ToIOID
			    AND link.ToIFID = ncmh.IFID_M716
			WHERE ifnull(qsi.Status, 0) = 2 
			AND ifnull(ncmh.IOID, 0) = 0
			ORDER BY ncvt.Ngay
		');
		return $this->_o_DB->fetchAll($sql);
	}
	
	/**
     * @todo: Chuyển sang request của mua hàng
	 * Material request detail by its ifid
	 * @param int $ifid IFID_M709
	 * @return object Request item list by request ifid
	 */
	public function getMaterialRequestDetailByIFID($ifid)
	{
		$sql = sprintf('
			SELECT 
				ncvt.*,
				CASE WHEN ifnull(sp.SoLuongMua, 0) > ifnull(ncvt.SoLuong, 0)
				THEN ifnull(sp.SoLuongMua, 0)
				ELSE ifnull(ncvt.SoLuong, 0) END AS SoLuongCanMua
				, ifnull(sp.SLToiThieu, 0) AS DiemDo
				, (ifnull(k.SoLuong,0)/ifnull(dvtsp.HeSoQuyDoi,1)) AS TonKho
			FROM ODSNhuCauVatTu AS ncvt
			INNER JOIN OSanPham AS sp ON ncvt.Ref_MaSP = sp.IOID
			INNER JOIN ODonViTinhSP AS dvtsp ON sp.IFID_M113 = dvtsp.IFID_M113 
				AND ncvt.Ref_DonViTinh = dvtsp.IOID
			LEFT JOIN 
			(
			SELECT k1.*, 
				(ifnull(k1.SoLuongHC,0) * ifnull(dvtsp1.HeSoQuyDoi,0)) AS SoLuong
			FROM OKho AS k1
			INNER JOIN OSanPham AS sp1 ON k1.Ref_MaSP = sp1.IOID
			INNER JOIN ODonViTinhSP AS dvtsp1 ON sp1.IFID_M113 = dvtsp1.IFID_M113 
				AND k1.Ref_DonViTinh = dvtsp1.IOID
			GROUP BY k1.Ref_MaSP
			) AS k ON k.Ref_MaSP = sp.IOID
			WHERE IFID_M709 = %1$d
		', $ifid);
		return $this->_o_DB->fetchAll($sql);		
	}
	
	
	/**
     * @todo: Chuyển sang request của mua hàng
	 * List of material requests link to purchase request
	 * @param int $purchaseRequestIFID IFID_M716
	 * @return object List of material requests link to purchase request
	 */
	public function getMaterialRequestsByPurchaseRequest(
		$purchaseRequestIFID)
	{
		$sql = sprintf('
			SELECT 
				ncvt.*,
				ncvt.IFID_M709 AS MIFID
			FROM ONhuCauMuaHang AS ncmh
			INNER JOIN qsioidlink AS `link` ON link.ToIOID = ncmh.IOID AND link.ToIFID = ncmh.IFID_M716
			INNER JOIN ONhuCauVatTu AS ncvt ON link.FromIOID = ncvt.IOID AND link.FromIFID = ncvt.IFID_M709
			WHERE ncmh.IFID_M716 = %1$d
		', $purchaseRequestIFID);
		return $this->_o_DB->fetchAll($sql);		
	}
}
