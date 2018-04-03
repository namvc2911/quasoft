<?php

class Qss_Model_Maintenance_Employee extends Qss_Model_Abstract
{
	public function __construct()
	{
		parent::__construct();
	}
	

	/**
	 * Get all active tasks of employees are working
	 * @use-in: /report/maintenance/resource/time
	 * @return object 
	 */
	public function getTasksByEmployee()
	{
		$sql = sprintf('SELECT
							nv.IFID_M316 as IFID
							, nv.Ref_LichLamViec
							, kn.Ref_KyNang
						FROM ODanhSachNhanVien as nv
						INNER JOIN OKyNang as kn on nv.IFID_M316 = kn.IFID_M316
						INNER JOIN OCongViecBaoTri as cvbt on kn.Ref_KyNang = cvbt.IOID
						WHERE
							ifnull(ThoiViec, 0) = 0
							and ifnull(cvbt.HoatDong, 0) = 1
						ORDER BY nv.IFID_M316');
		return $this->_o_DB->fetchAll($sql);
	}

	public function getEmployeeByUserID($userID)
	{
        // @: Neu nhan vien o trong nhieu don vi thi chi mac dinh lay don vi dau tien duoc chon ra
		$sql = sprintf('
			SELECT NhanVien.*, DonVi.Ma AS MaDonViThucHien, DonVi.Ten AS TenDonViThucHien, DonVi.IOID AS RefDonViThucHien
			, DonVi.BaoTri,kv.Ref_Ma as KhuVuc,kv.Ma as MaKhuVuc
			FROM qsusers AS users
			INNER JOIN ODanhSachNhanVien AS NhanVien ON users.UID = NhanVien.Ref_TenTruyCap
			LEFT JOIN ONhanVien AS NhanVienDonVi ON IFNULL(NhanVienDonVi.IFID_M125, 0) != 0 
			    AND IFNULL(NhanVienDonVi.Ref_MaNV, 0) = NhanVien.IOID
            LEFT JOIN ODonViSanXuat AS DonVi ON IFNULL(NhanVienDonVi.IFID_M125, 0) = IFNULL(DonVi.IFID_M125, 0)
            LEFT JOIN OThietBi AS kv ON IFNULL(kv.IFID_M125, 0) = IFNULL(DonVi.IFID_M125, 0)
			WHERE users.UID = %1$d
		', $userID);
		return $this->_o_DB->fetchOne($sql);
	}

    public function getStockEmployeeByUserID($userID)
    {
        // @: Neu nhan vien o trong nhieu don vi thi chi mac dinh lay don vi dau tien duoc chon ra
        $sql = sprintf('
			SELECT NhanVien.*, DonVi.IOID AS Ref_Kho, DonVi.MaKho, DonVi.TenKho
			FROM qsusers AS users
			INNER JOIN ODanhSachNhanVien AS NhanVien ON users.UID = NhanVien.Ref_TenTruyCap
			LEFT JOIN ONhanVien AS NhanVienDonVi ON IFNULL(NhanVienDonVi.IFID_M601, 0) != 0 
			    AND IFNULL(NhanVienDonVi.Ref_MaNV, 0) = NhanVien.IOID
            LEFT JOIN ODanhSachKho AS DonVi ON IFNULL(NhanVienDonVi.IFID_M601, 0) = IFNULL(DonVi.IFID_M601, 0)
			WHERE users.UID = %1$d
		', $userID);
        return $this->_o_DB->fetchOne($sql);
    }

	public function getAllEmployee()
	{
		$sql = sprintf('SELECT * FROM ODanhSachNhanVien ORDER BY MaNhanVien');
		return $this->_o_DB->fetchAll($sql);
	}

    public function getFactories()
    {
        $sql = sprintf('
            SELECT *
            FROM ODanhSachNhanVien
            WHERE IFNULL(NhaMay, "") != ""
            GROUP BY NhaMay
            ORDER BY NhaMay
        ');
        return $this->_o_DB->fetchAll($sql);
    }

    public function getDepartments($nhaMay = false)
    {
        $where = ($nhaMay !== false)?sprintf(' AND NhaMay = "%1$s" ', $nhaMay):'';

        $sql = sprintf('
            SELECT *
            FROM ODanhSachNhanVien
            WHERE IFNULL(BoPhan, "") != "" %1$s
            GROUP BY BoPhan
            ORDER BY BoPhan
        ', $where);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getEmployees($arrEmployees)
    {
        $arrEmployees[] = 0;

        $sql = sprintf('
            SELECT *
            FROM ODanhSachNhanVien
            WHERE IOID IN (%1$s)
        ', implode(', ', $arrEmployees));
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy danh sách nhân viên theo phòng ban
     */
    public function getEmployeeByDepartment($department, $search = '')
    {
        $sql  = sprintf('SELECT * FROM ODanhSachNhanVien WHERE IFNULL(Ref_MaPhongBan, 0) = %1$d', $department);
        $sql .= $search?sprintf(' AND (MaNhanVien like "%%%1$s%%" OR TenNhanVien like "%%%1$s%%")', $search):'';
        $sql .= ' ORDER BY MaNhanVien ';
        return $this->_o_DB->fetchAll($sql);
    }
}
