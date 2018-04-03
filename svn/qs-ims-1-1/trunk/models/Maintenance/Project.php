<?php
class Qss_Model_Maintenance_Project extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lấy danh sách thiết bị trong quản lý thiết bị theo dự án
     * @param $projectID
     */
    public function getEquipsByProject($projectID, $startDate = '', $endDate = '')
    {
        $filterByDate = '';
        if($startDate && $endDate)
        {
            $filterByDate = sprintf('
                AND ( thietbiduan.Ngay between %1$s and %2$s )'
                , $this->_o_DB->quote($startDate),$this->_o_DB->quote($endDate)
            );
        }

        $sql = sprintf('
            SELECT
                thietbi.*
                , sum(CASE WHEN thietbi.HinhThuc = 1 THEN ifnull(thietbi.SoLuong,0) END) AS SoLuongMua
                , sum(CASE WHEN thietbi.HinhThuc = 2 THEN ifnull(thietbi.SoLuong,0) END) AS SoLuongXuatKho
                , sum(CASE WHEN thietbi.HinhThuc = 10 THEN ifnull(thietbi.SoLuong,0) END) AS Mat
                , sum(CASE WHEN thietbi.HinhThuc = 11 THEN ifnull(thietbi.SoLuong,0) END) AS HuHong
                , sum(CASE WHEN thietbi.HinhThuc = 12 THEN ifnull(thietbi.SoLuong,0) END) AS TraLai
            FROM OQuanLyMuaThietBiDuAn AS thietbiduan
            INNER JOIN OChiTietMuaThietBiDuAn AS thietbi  ON thietbiduan.IFID_M809 = thietbi.IFID_M809
            WHERE thietbiduan.Ref_DuAn = %1$d %2$s
            GROUP BY thietbi.MaThietBi
        ', $projectID, $filterByDate);
        return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lấy danh sách vật tư  trong quản lý vật tư theo dự án
     * @param $projectID
     */
    public function getMaterialsByProject($projectID, $startDate = '', $endDate = '')
    {
        $filterByDate = '';
        if($startDate && $endDate)
        {
            $filterByDate = sprintf('
                AND ( vattuduan.Ngay between %1$s and %2$s )'
                , $this->_o_DB->quote($startDate),$this->_o_DB->quote($endDate)
            );
        }

        $sql = sprintf('
            SELECT
                vattu.*
                , sum(CASE WHEN vattu.HinhThuc = 1 THEN ifnull(vattu.SoLuong,0) END) AS SoLuongMua
                , sum(CASE WHEN vattu.HinhThuc = 2 THEN ifnull(vattu.SoLuong,0) END) AS SoLuongNhan
                , sum(CASE WHEN vattu.HinhThuc = 10 THEN ifnull(vattu.SoLuong,0) END) AS SuDung
                , sum(CASE WHEN vattu.HinhThuc = 11 THEN ifnull(vattu.SoLuong,0) END) AS Mat
                , sum(CASE WHEN vattu.HinhThuc = 12 THEN ifnull(vattu.SoLuong,0) END) AS HuHong
            FROM OQuanLyMuaVatTuDuAn AS vattuduan
            INNER JOIN OChiTietMuaVatTuDuAn AS vattu  ON vattuduan.IFID_M808 = vattu.IFID_M808
            WHERE vattuduan.Ref_DuAn = %1$d %2$s
            GROUP BY vattu.Ref_MaSP
        ', $projectID, $filterByDate);
        return $this->_o_DB->fetchAll($sql);
    }
	public function getProjectByUser($user)
	{
		if(Qss_Lib_System::formSecure('M803'))
		{
			$sql = sprintf('SELECT ODuAn.*
			FROM ODuAn
			inner join qsrecordrights on qsrecordrights.IFID = ODuAn.IFID_M803 
				and qsrecordrights.FormCode = "M803"
			where qsrecordrights.UID = %1$d and ODuAn.DeptID in (%2$s)',
			$user->user_id,
			$user->user_dept_list);
		}
		else
		{
			$sql = sprintf('SELECT * from ODuAn');
		}
			//echo $sql;die;
		return $this->_o_DB->fetchAll($sql);
	}
}
