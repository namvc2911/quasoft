<?php
class Qss_Model_Extra_News extends Qss_Model_Abstract
{

	public function __construct()
	{
		parent::__construct();
	}
    
    /**
     * Lay lich hoat dong cua cong ty (tin tuc khong phai la lich lam viec)
     */
    public function getWorkingCalendars($deptid)
    {
        $dep     = new Qss_Model_Admin_Department();
        $child   = $dep->getAllDepartmentIDsByParent($deptid);  
        
        $sql = sprintf('
            SELECT *
            FROM OThongTinChung AS thongtin
            WHERE thongtin.IFID_M148 is not null
            AND thongtin.DeptID in (%1$s)
        ', implode(', ', $child));
        return $this->_o_DB->fetchAll($sql);
    }
    
    /**
     * Lay tin tuc hoat dong cua cong ty (tin tuc khong phai la lich lam viec)
     */
    public function getActivate($deptid)
    {
        $dep     = new Qss_Model_Admin_Department();
        $child   = $dep->getAllDepartmentIDsByParent($deptid);  
        
        $sql = sprintf('
            SELECT *
            FROM OThongTinChung AS thongtin
            WHERE thongtin.IFID_M147 is not null
            AND thongtin.DeptID in (%1$s)
        ', implode(', ', $child));
        return $this->_o_DB->fetchAll($sql);
    }    
    
    /**
     * Lay bao cao san xuat cua cong ty (tin tuc khong phai la lich lam viec)
     */
    public function getManufacturingReport($deptid)
    {
        $dep     = new Qss_Model_Admin_Department();
        $child   = $dep->getAllDepartmentIDsByParent($deptid);  
        
        $sql = sprintf('
            SELECT *
            FROM OThongTinChung AS thongtin
            WHERE thongtin.IFID_M149 is not null
            AND thongtin.DeptID in (%1$s)
        ', implode(', ', $child));
        return $this->_o_DB->fetchAll($sql);
    }     
}