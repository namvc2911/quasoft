<?php

class Qss_Model_Maintenance_Equipment extends Qss_Model_Maintenance_Maintain
{
	public function __construct()
	{
		parent::__construct();
	}

    /**
     * Hàm lấy danh sách thiết bị sắp xếp theo cây loại thiết bị và tên thiết bị (Không lấy theo cha con của thiết bị)
     * Hàm được sử dụng trong dự án HFH
     * @param array $eqIOID
     * @param int $locIOID
     * @param int $costcenterIOID
     * @param int $eqGroupIOID
     * @param int $eqTypeIOID
     * @param array $status
     */
	public function getEquipmentsOrderByType(
        $eqIOID = array()
        , $locIOID = 0
        , $costcenterIOID = 0
        , $eqGroupIOID = 0
        , $eqTypeIOID = 0
        , $status = array()
        , $partnerIOID = 0
    )
    {
        $where  = ''; // Điều kiện lọc

        if(Qss_Lib_System::fieldActive('OKhuVuc', 'TrucThuoc'))
        {
            $loc    = ($locIOID != 0)?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):array();
            $where .= count((array)$loc)?sprintf('and IFNULL(ThietBi.Ref_MaKhuVuc, 0) in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)', $loc->lft, $loc->rgt):'';
        }
        else
        {
            $where .= ($locIOID != 0)?sprintf(' and IFNULL(ThietBi.Ref_MaKhuVuc, 0) = %1$d ', $locIOID):'';
        }

        if(Qss_Lib_System::fieldActive('OLoaiThietBi', 'TrucThuoc'))
        {
            $eqType = ($eqTypeIOID != 0)?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $eqTypeIOID)):array();
            $where .= count((array)$eqType)?sprintf('and (IFNULL(ThietBi.Ref_LoaiThietBi, 0) IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ',$eqType->lft, $eqType->rgt):'';
        }
        else
        {
            $where .= ($eqTypeIOID != 0)?sprintf(' and IFNULL(ThietBi.Ref_LoaiThietBi, 0) = %1$d ', $eqTypeIOID):'';
        }

        $where .= ($eqIOID != 0)?sprintf(' and IFNULL(ThietBi.IOID, 0) = %1$d ', $eqIOID):'';
        $where .= ($costcenterIOID != 0)?sprintf(' and IFNULL(ThietBi.Ref_TrungTamChiPhi, 0) = %1$d ', $costcenterIOID):'';
        $where .= ($eqGroupIOID != 0)?sprintf(' and IFNULL(ThietBi.Ref_NhomThietBi, 0) = %1$d ', $eqGroupIOID):'';
        $where .= (count($status))?sprintf(' and IFNULL(ThietBi.TrangThai, 0) in (%1$s) ', implode(', ', $status)):'';
        $where .= ($partnerIOID != 0)?sprintf(' and IFNULL(ThietBi.Ref_HangBaoHanh, 0) = %1$d ', $partnerIOID):'';

        $sql = sprintf('
            SELECT ThietBi.*, LoaiThietBi.MaLoai, LoaiThietBi.TenLoai, LoaiThietBi.MaLoai AS TitleCode, LoaiThietBi.TenLoai AS TitleName
                ,NhanVien.TenNhanVien
            FROM ODanhSachThietBi AS ThietBi
            LEFT JOIN OLoaiThietBi AS LoaiThietBi ON IFNULL(ThietBi.Ref_LoaiThietBi, 0) = LoaiThietBi.IOID
            LEFT JOIN ODanhSachNhanVien AS NhanVien ON IFNULL(ThietBi.Ref_QuanLy, 0) = NhanVien.IOID
            WHERE 1=1 %1$s
            ORDER BY LoaiThietBi.TenLoai, TRIM(ThietBi.TenThietBi), TRIM(ThietBi.MaThietBi)
        ', $where);

        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Hàm lấy danh sách thiết bị sắp xếp theo cây khu vực và tên thiết bị (Không lấy theo cha con của thiết bị)
     * Hàm được sử dụng trong dự án HFH
     * @param array $eqIOID
     * @param int $locIOID
     * @param int $costcenterIOID
     * @param int $eqGroupIOID
     * @param int $eqTypeIOID
     * @param array $status
     */
    public function getEquipmentsOrderByLocation(
        $eqIOID = array()
        , $locIOID = 0
        , $costcenterIOID = 0
        , $eqGroupIOID = 0
        , $eqTypeIOID = 0
        , $status = array()
        , $partnerIOID = 0
    )
    {
        $where  = ''; // Điều kiện lọc

        if(Qss_Lib_System::fieldActive('OKhuVuc', 'TrucThuoc'))
        {
            $loc    = ($locIOID != 0)?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID)):array();
            $where .= count((array)$loc)?sprintf('and IFNULL(ThietBi.Ref_MaKhuVuc, 0) in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)', $loc->lft, $loc->rgt):'';
        }
        else
        {
            $where .= ($locIOID != 0)?sprintf(' and IFNULL(ThietBi.Ref_MaKhuVuc, 0) = %1$d ', $locIOID):'';
        }

        if(Qss_Lib_System::fieldActive('OLoaiThietBi', 'TrucThuoc'))
        {
            $eqType = ($eqTypeIOID != 0)?$this->_o_DB->fetchOne(sprintf('SELECT * FROM OLoaiThietBi WHERE IOID = %1$d', $eqTypeIOID)):array();
            $where .= count((array)$eqType)?sprintf('and (IFNULL(ThietBi.Ref_LoaiThietBi, 0) IN  (select IOID from OLoaiThietBi where lft>= %1$d and  rgt <= %2$d)) ',$eqType->lft, $eqType->rgt):'';
        }
        else
        {
            $where .= ($eqTypeIOID != 0)?sprintf(' and IFNULL(ThietBi.Ref_LoaiThietBi, 0) = %1$d ', $eqTypeIOID):'';
        }

        $where .= ($eqIOID != 0)?sprintf(' and IFNULL(ThietBi.IOID, 0) = %1$d ', $eqIOID):'';
        $where .= ($costcenterIOID != 0)?sprintf(' and IFNULL(ThietBi.Ref_TrungTamChiPhi, 0) = %1$d ', $costcenterIOID):'';
        $where .= ($eqGroupIOID != 0)?sprintf(' and IFNULL(ThietBi.Ref_NhomThietBi, 0) = %1$d ', $eqGroupIOID):'';
        $where .= (count($status))?sprintf(' and IFNULL(ThietBi.TrangThai, 0) in (%1$s) ', implode(', ', $status)):'';
        $where .= ($partnerIOID != 0)?sprintf(' and IFNULL(ThietBi.Ref_HangBaoHanh, 0) = %1$d ', $partnerIOID):'';

        $sql = sprintf('
            SELECT ThietBi.*, KhuVuc.MaKhuVuc, KhuVuc.Ten AS TenKhuVuc, KhuVuc.MaKhuVuc AS TitleCode, KhuVuc.Ten AS TitleName
                , NhanVien.TenNhanVien
            FROM ODanhSachThietBi AS ThietBi
            LEFT JOIN OKhuVuc AS KhuVuc ON IFNULL(ThietBi.Ref_MaKhuVuc, 0) = KhuVuc.IOID
            LEFT JOIN ODanhSachNhanVien AS NhanVien ON IFNULL(ThietBi.Ref_QuanLy, 0) = NhanVien.IOID
            WHERE 1=1 %1$s
            ORDER BY KhuVuc.lft, TRIM(ThietBi.TenThietBi), TRIM(ThietBi.MaThietBi)
        ', $where);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getEquipTypes()
    {
    	$where = '';
    	$form = Qss_Lib_System::getFormByCode('M770');
    	if($form->Type == Qss_Lib_Const::FORM_TYPE_PRIVATE_LIST)
    	{
    		$where = sprintf(' and DeptID in (%1$s)',$this->_user->user_dept_list);
    	}
        $sql = sprintf('
            SELECT *, (
				SELECT
					count(*)
				FROM
					OLoaiThietBi AS u
				WHERE
					u.lft <= LoaiTB.lft
					AND u.rgt >= LoaiTB.rgt
					%1$s
			) AS `Level`
            FROM OLoaiThietBi AS LoaiTB
            where 1=1 %1$s
            ORDER BY LoaiTB.lft
        ',$where);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getEquipGroups()
    {
        $where = '';
        $form = Qss_Lib_System::getFormByCode('M704');
        if($form->Type == Qss_Lib_Const::FORM_TYPE_PRIVATE_LIST)
        {
            $where = sprintf(' and DeptID in (%1$s)',$this->_user->user_dept_list);
        }

        $sql = sprintf('
            SELECT *
            FROM ONhomThietBi
            where 1=1 %1$s
            ORDER BY ONhomThietBi.lft
        ',$where);
        return $this->_o_DB->fetchAll($sql);
    }

	/**
	 * Thong so thiet bi
	 * @param array $IOIDarray
	 * @return mix Equip Tech Param
	 */
	public function getTechnicalParameterValues($IOIDarray = array())
	{
        $IOIDarray[] = 0;
		$implode     = implode(',', $IOIDarray);
		$sql         = sprintf('
            SELECT TSTB.*, DSTB.IOID AS TBIOID 
            FROM ODanhSachThietBi as DSTB
            INNER JOIN OThongSoKyThuatTB as TSTB
            ON DSTB.IFID_M705 = TSTB.IFID_M705
            WHERE DSTB.IOID IN( %1$s )
            ORDER BY TSTB.IFID_M705', $implode
		);
		return $this->_o_DB->fetchAll($sql);

	}

	public function getTechnicalParameters($IOIDarray = array())
	{
        $IOIDarray[] = 0;
		$implode     = implode(',', $IOIDarray);
		$sql         = sprintf('
            SELECT TSTB.*, DSTB.IOID AS TBIOID 
            FROM ODanhSachThietBi as DSTB
            INNER JOIN ODacTinhThietBi as TSTB
            ON DSTB.IFID_M705 = TSTB.IFID_M705
            WHERE DSTB.IOID IN( %1$s )
            ORDER BY TSTB.IFID_M705', $implode
		);
		return $this->_o_DB->fetchAll($sql);

	}


	
	
	
	/**
	 * Lay danh sach vat tu tieu hao theo nhat trinh thiet bi
	 * @param unknown $startDate
	 * @param unknown $endDate
	 * @param unknown $equipIOID
	 * @param unknown $itemIOID
	 */
	public function getMaterialsFromDailyRecords(
	    $startDate
	    , $endDate
	    , $locIOID = 0
	    , $eqGroupIOID = 0
	    , $eqTypeIOID = 0
	    , $equipIOID = 0
	    , $itemIOID = 0)
	{
	    if(!$startDate || !$endDate) return array();
	    
	    // Điều kiện
	    $whereNhatTrinh = array();
	    $whereTHVT      = array();
	    
	    $whereNhatTrinh[] = sprintf( ' 
	        (nhattrinh.Ngay <= %1$s or nhattrinh.Ngay <= %2$s) '
	        , $this->_o_DB->quote($startDate), $this->_o_DB->quote($endDate)
	    );
	     
	    
	    $whereNhatTrinh[] = sprintf(' qsiforms.Status = 2 ');
	    
	    if($equipIOID)
	    {
	        $whereTHVT[] = sprintf(' thietbi.IOID = %1$d ', $equipIOID);
	        $whereNhatTrinh[] = sprintf(' nhattrinh.Ref_MaTB = %1$d ', $equipIOID);
	    }
	     
	    if($itemIOID)
	    {
	        $whereTHVT[] = sprintf(' vattuth.Ref_MaSanPham = %1$d ', $itemIOID);
	    }	

	    if($locIOID)
	    {
	        $findLocSql = sprintf('SELECT * FROM OKhuVuc WHERE IOID = %1$d', $locIOID);
	        $findLoc    = $this->_o_DB->fetchOne($findLocSql);
	    
	         
	        if($findLoc)
	        {
	            $whereTHVT[] = sprintf(' thietbi.Ref_MaKhuVuc in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d) ', $findLoc->lft, $findLoc->rgt);
	        }
	    }
	     
	    if($eqGroupIOID)
	    {
	        $whereTHVT[] = sprintf(' thietbi.Ref_NhomThietBi = %1$d ', $eqGroupIOID);
	    }
	     
	    if($eqTypeIOID)
	    {
	        $whereTHVT[] = sprintf(' thietbi.Ref_LoaiThietBi = %1$d ', $eqTypeIOID);
	    }	    

	    $whereNhatTrinhSql = count($whereNhatTrinh)?sprintf(' where %1$s ', implode(' and ', $whereNhatTrinh)):'';
	    $whereTHVTSql      = count($whereTHVT)?sprintf(' where %1$s ', implode(' and ', $whereTHVT)):'';
	    
	    $sql = sprintf('
	        SELECT 
	           ifnull(thietbi.IOID, 0) AS EqIOID,
	           thietbi.MaThietBi AS EqCode,
	           thietbi.TenThietBi AS EqName,
	           ifnull(vattuth.Ref_MaSanPham, 0) AS ItemIOID,
	           vattuth.MaSanPham AS ItemCode,
	           vattuth.TenSanPham AS ItemName, 
	           spham.DonViTinh AS ItemUOM,
	           (ifnull(vattuth.SoLuong, 0) * ifnull(dvtspham.HeSoQuyDoi, 0)) AS ItemBOMQty	        
	        FROM ODanhSachThietBi AS thietbi 
	        INNER JOIN
	        (
	           SELECT nhattrinh.*, MAX(Ngay) 
	           FROM ONhatTrinhThietBi AS nhattrinh
	           LEFT JOIN qsiforms ON qsiforms.IFID = nhattrinh.IFID_M765
	           %1$s
	           GROUP BY nhattrinh.Ref_MaTB
	        ) AS nhattrinhtb ON nhattrinhtb.Ref_MaTB = thietbi.IOID
	        INNER JOIN OVatTuTieuHao AS vattuth ON nhattrinhtb.IFID_M765 = vattuth.IFID_M765
	        LEFT JOIN OSanPham AS spham ON vattuth.Ref_MaSanPham = spham.IOID
	        LEFT JOIN ODonViTinhSP AS dvtspham ON spham.IFID_M113 = dvtspham.IFID_M113
	           AND ifnull(vattuth.Ref_DonViTinh, 0) = dvtspham.IOID	        
	        %2$s
	        ORDER BY thietbi.IOID, vattuth.Ref_MaSanPham     
        ', $whereNhatTrinhSql, $whereTHVTSql);
	    return $this->_o_DB->fetchAll($sql);	    
	}
	
	
	/**
	 * Lay lich dieu dong thiet bi trong mot ngay
	 * @todo: can chinh sua ham nay do co the lich dieu dong ko duoc su dung 
	 */
    public function getMoveCalByDateOfEquip($date, $equipIOID)
    {
        $sql = sprintf('
            SELECT 
                ltb.*
            FROM OLichThietBi AS ltb
            INNER JOIN qsiforms ON ltb.IFID_M706 = qsiforms.IFID
            WHERE 
                ltb.Ref_MaThietBi = %1$d 
                AND qsiforms.Status = 2
                AND (
                    (%2$s between ltb.NgayBatDau AND ltb.NgayKetThuc)
                    OR 
                    (%2$s >= ltb.NgayBatDau)
                )
            ORDER BY ltb.NgayBatDau DESC, ltb.IOID
            LIMIT 1
        ', $equipIOID, $this->_o_DB->quote($date));
        return $this->_o_DB->fetchOne($sql);
    }
    
    /**
     * Lay lich dieu dong cua nhieu thiet bi trong mot ngay
     * @todo: can chinh sua ham nay do co the lich dieu dong ko duoc su dung
     */
    public function getMoveCalByDateOfEquips($date, $equipIOIDArr)
    {
        $equipIOIDArr[] = 0;
        $sql = sprintf('
            SELECT *
            FROM
            (
                SELECT ltb.*
                FROM OLichThietBi AS ltb
                INNER JOIN qsiforms ON ltb.IFID_M706 = qsiforms.IFID
                WHERE ltb.Ref_MaThietBi in (%1$s) AND qsiforms.Status = 2
                    AND ((%2$s between ltb.NgayBatDau AND ltb.NgayKetThuc) OR (%2$s >= ltb.NgayBatDau))
                ORDER BY ltb.Ref_MaThietBi, ltb.NgayBatDau DESC, ltb.IOID 
            ) AS t
            GROUP BY Ref_MaThietBi
        ', implode(' , ', $equipIOIDArr), $this->_o_DB->quote($date));
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lay nhat trinh tu ngay dua vao sd cua mot thiet bi gop theo tung nam
     * @param int $eqIOID IOID cua thiet bi
     * @param int $paramIOID IOID cua chi so thiet bi
     * @param string $startDate ngay dua vao su dung cua thiet bi <yyyy-mm-dd>
     */
    public function getDailyRecordFromStartDateOfEquipGroupByYear($eqIOID, $paramIOID, $startDate)
    {
        $sql = sprintf('
            SELECT YEAR(nhattrinh.Ngay) AS `Year`, SUM(ifnull(chiso.SoHoatDong, 0)) AS `Total`
            FROM ONhatTrinhThietBi AS nhattrinh
            WHERE (nhattrinh.Ngay BETWEEN %1$s AND %2$s) AND nhattrinh.Ref_MaTB = %3$s AND Ref_ChiSo = %4$s 
            GROUP BY YEAR(nhattrinh.Ngay)
            ORDER BY YEAR(nhattrinh.Ngay)
        ' , $this->_o_DB->quote($startDate), $this->_o_DB->quote(date('Y-m-d')), $eqIOID, $paramIOID);
        return $this->_o_DB->fetchAll($sql);
    }
    
    public function getSparepartOfEquip($eqIOID)
    {
        $sql = sprintf('
            SELECT phutung.*, sanpham.DacTinhKyThuat
            FROM ODanhSachThietBi AS thietbi
            INNER JOIN OCauTrucThietBi AS phutung ON thietbi.IFID_M705 = phutung.IFID_M705
            INNER JOIN OSanPham AS sanpham ON phutung.Ref_MaSP = sanpham.IOID
            WHERE thietbi.IOID = %1$d
            ORDER BY phutung.lft
        ', $eqIOID);
        return $this->_o_DB->fetchAll($sql);        
    }

    public function getComponentOfEquip($eqIOID)
    {
        $sql = sprintf('
            SELECT cautruc.*
            , (SELECT count(*) FROM OCauTrucThietBi AS u  WHERE u.lft <= cautruc.lft AND u.rgt >= cautruc.rgt) AS LEVEL
            FROM OCauTrucThietBi AS cautruc
            INNER JOIN ODanhSachThietBi AS thietbi ON cautruc.IFID_M705 = thietbi.IFID_M705
            WHERE thietbi.IOID = %1$d
            ORDER BY cautruc.lft
        ', $eqIOID);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getCauTrucPhuTung($ifid)
    {
        $sql = sprintf('SELECT MaSP as ma,
					concat(ifnull(TenSP,\'\'),\' - \',ifnull(BoPhan,\'\'),\' - \',ifnull(ThongSoKyThuat,\'\')) as ten,
					SUM(SoLuong) as soluong,
					DonViTinh
    				from OCauTrucThietBi as ct 
    				where ct.IFID_M705=%1$d and PhuTung = 1
    				group by MaSP,BoPhan,DonViTinh,ThongSoKyThuat
    				order by case when ma is null or ma =\'\' then \'zzz\' end'
            ,$ifid);
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * @param int $materialIOID
     * @param int $locIOID
     * @param int $eTypeIOID
     * @param int $eGroupIOID
     * @return mixed
     */
    public function getMaterials(
        $materialIOID = 0
        , $locIOID = 0
        , $eTypeIOID = 0
        , $eGroupIOID = 0)
    {
        $filter = Qss_Model_Extra_Sql::createInstance();
        $filter->setEqualCond('dstb.Ref_LoaiThietBi', $eTypeIOID);
        $filter->setEqualCond('dstb.Ref_NhomThietBi', $eGroupIOID);
        $filter->setEqualCond('sp.IOID', $materialIOID);
        $locFilter = $this->getFilterByLocIOIDStr('dstb.IOID', $locIOID);
        if($locFilter)
        {
            $filter->filter[] = $locFilter;
        }

        $sql = sprintf('
			SELECT
				dstb.MaThietBi AS EquipCode
				, dstb.LoaiThietBi AS EquipName
				, vt.Ref_MaSP AS MaterialIOID
				, sp.MaSanPham AS MaterialCode
				, sp.TenSanPham AS MaterialName
				, vt.ViTri AS `Position`
				, vt.BoPhan AS `Component`
				, vt.DonViTinh AS `UOM`
				, ifnull(vt.SoLuongChuan, 0) AS `Qty`
				, ifnull(vt.SoLuongHC, 0) AS `CurQty`
			FROM OCauTrucThietBi AS vt
			INNER JOIN ODanhSachThietBi AS dstb ON dstb.IFID_M705 = vt.IFID_M705
			LEFT JOIN OSanPham AS sp ON vt.Ref_MaSP = sp.IOID
				%1$s
			ORDER BY vt.Ref_MaSP, dstb.IOID, vt.ViTri
		',$filter->getCondWithWhere());

        // echo '<pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
    
    public function getDocumentsOfEquip($eqIOID)
    {
        $sql = sprintf('
            SELECT qsdocumenttype.Type, count(1) AS Total
            FROM ODanhSachThietBi
            INNER JOIN qsfdocuments ON ODanhSachThietBi.IFID_M705 = qsfdocuments.IFID
            INNER JOIN qsdocumenttype ON qsfdocuments.DTID = qsdocumenttype.DTID
            WHERE ODanhSachThietBi.IOID = %1$d
            GROUP BY qsdocumenttype.DTID
        ', $eqIOID);
        return $this->_o_DB->fetchAll($sql);           
    }

    /**
     * Lấy danh sách thiết bị theo loại thiết bị của thiết bị trong một kế hoạch bảo tri
     * và thiết bị phải tuân thủ chưa có kế hoạch với loại bảo trì và bộ phận chính cùng
     * loại
     * @param type $planIFID
     * @return type
     */
    public function getEquipsByEquipTypeOfEquipOfPlan($planIFID)
    {
        $sql = sprintf('
            SELECT thietbi2.*
            FROM OBaoTriDinhKy AS kehoach
            INNER JOIN ODanhSachThietBi AS thietbi ON kehoach.Ref_MaThietBi = thietbi.IOID
            INNER JOIN OLoaiThietBi AS loaithietbi ON thietbi.Ref_LoaiThietBi = loaithietbi.IOID
            INNER JOIN OLoaiThietBi AS loaithietbi2 ON loaithietbi2.lft <= loaithietbi.lft
                AND loaithietbi2.rgt >= loaithietbi.rgt
            INNER JOIN ODanhSachThietBi AS thietbi2 ON loaithietbi2.IOID = thietbi2.Ref_LoaiThietBi
            LEFT JOIN OBaoTriDinhKy AS kehoach2 ON thietbi2.IOID = kehoach2.`Ref_MaThietBi`                 
                AND ifnull(kehoach.BoPhan, \'\') = ifnull(kehoach2.BoPhan, \'\')
            WHERE kehoach.IFID_M724 = %1$d and ifnull(kehoach2.IOID, 0) = 0 and thietbi2.IOID != thietbi.IOID
                and (
                    ifnull(kehoach.BoPhan, \'\') = \'\' 
                    or kehoach.BoPhan in (SELECT ViTri FROM OCauTrucThietBi WHERE IFID_M705 = thietbi2.IFID_M705)
                )
            ORDER BY thietbi2.MaThietBi
        ', $planIFID);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getDacTinhKyThuat($ifid)
    {
    	$sql = sprintf('SELECT OLoaiThietBi.* from OLoaiThietBi 
    					inner join ODanhSachThietBi on OLoaiThietBi.IOID =  ODanhSachThietBi.Ref_LoaiThietBi
    					where IFID_M705=%1$d'
    		,$ifid);	
    	$data = $this->_o_DB->fetchOne($sql);
    	$ioid = $data?$data->IOID:0;
    	$tructhuoc = $data?$data->Ref_TrucThuoc:0;
    	while($ioid)
    	{
	    	$sql = sprintf('SELECT dt.DonViTinh,dt.IOID as RefIOID, dt.Ten,dttb.IOID,dttb.GiaTri, loai.TrucThuoc
	    				FROM ODacTinhKyThuat as dt
						inner join OLoaiThietBi as loai on loai.IFID_M770 = dt.IFID_M770
						left join ODacTinhThietBi as dttb on dttb.Ref_Ten = dt.IOID and dttb.IFID_M705 = %2$d
						where loai.IOID=%1$d'
	    		,$ioid
	    		,$ifid);
	    	$dataSQL = $this->_o_DB->fetchAll($sql);
	    	if(count($dataSQL))
	    	{
	    		return $dataSQL;
	    	}
	    	if(Qss_Lib_System::fieldActive('OLoaiThietBi', 'TrucThuoc'))
	    	{
		    	$sql = sprintf('SELECT * from OLoaiThietBi where IOID=%1$d'
	    			,$tructhuoc);	
		    	$data = $this->_o_DB->fetchOne($sql);
		    	$ioid = $data?$data->IOID:0;
		    	$tructhuoc = $data?$data->Ref_TrucThuoc:0;
	    	}
	    	else 
	    	{
	    		$ioid = 0;
		    	$tructhuoc = 0;
	    	}
    	}
    	return array();
    }

 	public function getLoaiThietBi($ifid)
    {
    	$sql = sprintf('SELECT * from OLoaiThietBi as loai 
    				inner join ODanhSachThietBi as tb on tb.Ref_LoaiThietBi = loai.IOID
					where tb.IFID_M705=%1$d'
    		,$ifid);	
    	return $this->_o_DB->fetchOne($sql);
    }

	public function searchEquipmentByCodeAndName($search)
    {
    	$sql = sprintf('SELECT tb.*, (select 1 from OCauTrucThietBi as ct where ct.IFID_M705 = tb.IFID_M705 limit 1
                ) as HasComponent 
    				from ODanhSachThietBi as tb 
    				where tb.MaThietBi like %1$s or TenThietBi like %1$s
    				and tb.DeptID in (%2$s)
    				order by MaThietBi'
    		,$this->_o_DB->quote('%'.$search.'%')
    		,$this->_user->user_dept_list);
    	return $this->_o_DB->fetchAll($sql);
    }
    
    /**
	 * @param array $eqIOID 
	 * @param int $locIOID
	 * @param int $costcenterIOID
	 * @param int $eqGroupIOID
	 * @param int $eqTypeIOID
	 * @param int $sortBy order by theo loai thiet bi(1), nhom thiet bi(2)
	 * trung tam chi phi (3), khu vuc (4), mac dinh sap xep theo ma thiet bi
	 * @return fetchAll Danh sach thiet bi
	 */
	public function getEquipments(
		$eqIOID = array()
		, $locIOID = 0
		, $costcenterIOID = 0
		, $eqGroupIOID = 0
		, $eqTypeIOID = 0
		, $sortBy = 1
        , $status = array()
        , $kiemDinh = false
        , $orderByTenOrMaThietBi = true // Với giá trị true sắp xếp ưu tiên theo tên, false là theo mã.
    )
	{
		$filterSql    = ''; // Loc danh sach thiet bi cha
        $filterSqlAll = '';  // Loc danh sach thiet bi tong the
		$filterTemp = array();
		$sortSql    = ''; // Sap xep danh sach thiet bi

		// Loc theo thiet bi
		if (count((array)$eqIOID))
		{
			$filterSql .= sprintf(' and thietbicha1.IOID in (%1$s) ', implode(', ', $eqIOID));
		}
		else
        {
            $filterSql .= sprintf(' and ifnull(thietbicha1.Ref_TrucThuoc, 0) = 0 ');
        }

        if (count((array)$status))
        {
            $filterSql .= sprintf(' and thietbicha1.TrangThai in (%1$s) ', implode(', ', $status));
            $filterSqlAll .= sprintf(' and thietbicon.TrangThai in (%1$s) ', implode(', ', $status));
        }

		// Loc theo khu vuc
		if ($locIOID)
		{
			$sqlLocName = sprintf('select * from OKhuVuc where IOID = %1$d',$locIOID);
			$locName = $this->_o_DB->fetchOne($sqlLocName);			
			
			if ($locName)
			{
				$filterSql .= sprintf(' and ( thietbicha1.IOID in'
					. ' (select IOID from ODanhSachThietBi '
					. 'where Ref_MaKhuVuc in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d)))', $locName->lft, $locName->rgt);
			}
		}	

		// Loc theo trung tam chi phi
		if ($costcenterIOID)
		{
			$filterSql .= sprintf(' and Ref_TrungTamChiPhi = %1$d ', $costcenterIOID);
		}		
		
		// Loc theo nhom thiet bi
		if ($eqGroupIOID)
		{
			$filterSql .= sprintf(' and Ref_NhomThietBi = %1$d ', $eqGroupIOID);
		}

		// Loc theo loai thiet bi
		if ($eqTypeIOID)
		{
			$filterSql .= sprintf(' and Ref_LoaiThietBi = %1$d ', $eqTypeIOID);
		}

		if(Qss_Lib_System::fieldActive('ODanhSachThietBi', 'KiemDinh') && $kiemDinh)
        {
            $filterSql .= sprintf(' and IFNULL(thietbicha1.KiemDinh, 0) = 1 ');
            $filterSqlAll .= sprintf(' and IFNULL(thietbicon.KiemDinh, 0) = 1 ');
        }


        $allSort = 'ORDER BY thietbicon.lft';

		// Sap xep danh sach thiet bi
		switch ($sortBy)
		{
			case 1: // nhom theo loai thiet bi
				$sortSql = ' order by LoaiThietBiLft ';
				break;
			case 2: // nhom theo nhom thiet bi
				$sortSql = ' order by NhomThietBiLft ';
				break;
			case 3: // nhom theo trung tam chi phi
				$sortSql = ' order by TrungTamChiPhi ';
				break;
			case 4: // nhom theo khu vuc
				$sortSql = ' order by KhuVucLft ';
				break;
		}

		// Dòng code dở dơ này dùng cho viwasupco và pos
        // Một bên muốn sắp xếp theo mã, một bên thì tên @#$%^&
		if($orderByTenOrMaThietBi) {

            $sortSql .= ($sortSql != '')?', HPEquipName, HPEquipCode':'order by HPEquipName, HPEquipCode';
        }
        else {

            $sortSql .= ($sortSql != '')?', HPEquipCode, HPEquipName':'order by HPEquipCode, HPEquipName';
        }


		$sql = sprintf('
            SELECT *, (
					SELECT count(*)
					FROM ODanhSachThietBi AS u
					WHERE u.lft <= v.TBCLft AND u.rgt >= v.TBCRgt
				) AS CLevel
            FROM
            (
                SELECT
                    thietbichalonnhat.*
                    /* Thiet bi cha lon nhat */
                    , thietbichalonnhat.IOID as HPEQIOID /* Highest Parent */
                    , thietbichalonnhat.MaThietBi AS HPEquipCode
                    , thietbichalonnhat.TenThietBi AS HPEquipName
                    , thietbichalonnhat.Model AS HPModel
                    , thietbichalonnhat.NamSanXuat AS HPProductYear
                    , thietbichalonnhat.NgayDuaVaoSuDung AS HPBeginDate
                    , thietbichalonnhat.DacTinhKT AS HPTechNote
                    , IFNULL(thietbichalonnhat.TrangThai, 0) AS HPStatus
                    , thietbichalonnhat.Ref_LoaiThietBi AS HPRefEqType
                    , thietbichalonnhat.LoaiThietBi AS HPEqType
                    , thietbichalonnhat.Ref_NhomThietBi AS HPRefEqGroup
                    , thietbichalonnhat.NhomThietBi AS HPEqGroup
                    , thietbichalonnhat.RefLoc AS HPRefLoc
                    , thietbichalonnhat.LocCode AS HPLocCode
                    , thietbichalonnhat.LocName AS HPLocName
                    , thietbichalonnhat.LichLamViec AS HPCal
                    , thietbichalonnhat.HanBaoHanh AS HPWarranty
                    , thietbichalonnhat.HangBaoHanh AS HPPartner
                    , thietbichalonnhat.NgayMua AS HPWhen
                    , thietbichalonnhat.XuatXu AS HPWhere
                    , thietbichalonnhat.MaTaiSan AS AssetCode
                    , 1 AS HPLevel
                    
                    -- , thietbichalonnhat.LoaiThietBiLft
                    -- , thietbichalonnhat.NhomThietBiLft
                    -- , thietbichalonnhat.TrungTamChiPhi
                    -- , thietbichalonnhat.KhuVucLft
    
                    /* Thiet bi con truc thuoc */
                    , thietbicon.IOID as CEQIOID /* Children */
                    , thietbicon.MaThietBi AS CEquipCode
                    , thietbicon.TenThietBi AS CEquipName
                    , thietbicon.Model AS CModel
                    , thietbicon.NamSanXuat AS CProductYear
                    , thietbicon.NgayDuaVaoSuDung AS CBeginDate
                    , thietbicon.DacTinhKT AS CTechNote
                    , IFNULL(thietbicon.TrangThai, 0) AS CStatus
                    , thietbicon.Ref_LoaiThietBi AS CRefEqType
                    , thietbicon.LoaiThietBi AS CEqType
                    , thietbicon.Ref_NhomThietBi AS CRefEqGroup
                    , thietbicon.NhomThietBi AS CEqGroup
                    , khuvucthietbicon.IOID AS CRefLoc
                    , khuvucthietbicon.MaKhuVuc AS CLocCode
                    , khuvucthietbicon.Ten AS CLocName
                    , thietbicon.LichLamViec AS CCal
                    , thietbicon.HanBaoHanh AS CWarranty
                    , thietbicon.HangBaoHanh AS CPartner
                    , thietbicon.NgayMua AS CWhen
                    , thietbicon.XuatXu AS CWhere
                    , thietbicon.lft AS TBCLft
                    , thietbicon.rgt AS TBCRgt          
                    , thietbicon.MaTaiSan AS CAssetCode
                FROM
                (
                    select                      
                        thietbicha1.*
                        , khuvuc1.IOID AS RefLoc
                        , khuvuc1.MaKhuVuc AS LocCode
                        , khuvuc1.Ten AS LocName
                        , OLoaiThietBi.lft AS LoaiThietBiLft
                        , ONhomThietBi.lft AS NhomThietBiLft
       
                        , khuvuc1.lft AS KhuVucLft
                    from ODanhSachThietBi AS thietbicha1
                    left join OKhuVuc AS khuvuc1 ON ifnull(thietbicha1.Ref_MaKhuVuc, 0) = khuvuc1.IOID
                    left join OLoaiThietBi ON IFNULL(thietbicha1.Ref_LoaiThietBi, 0) = OLoaiThietBi.IOID
                    left join ONhomThietBi ON IFNULL(thietbicha1.Ref_NhomThietBi, 0) = ONhomThietBi.IOID
                    where 1=1
                        %1$s
                        
                    
                ) AS thietbichalonnhat                
                LEFT JOIN ODanhSachThietBi AS thietbicon ON thietbichalonnhat.lft <= thietbicon.lft 
                    AND thietbichalonnhat.rgt >= thietbicon.rgt
                LEFT JOIN OKhuVuc AS khuvucthietbicon ON ifnull(thietbicon.Ref_MaKhuVuc, 0) = khuvucthietbicon.IOID  
                WHERE 1=1 %3$s                
            ) AS v
            %2$s   
			'
			, $filterSql
			, $sortSql
            , $filterSqlAll
		);
		// echo '<pre>'; print_r($sql); die;
		return $this->_o_DB->fetchAll($sql);
	}
}