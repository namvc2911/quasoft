<?php
class Qss_Model_M005_User extends Qss_Model_Abstract
{
    public function getInfoOfActiveUser() {
        $sql = sprintf('
            SELECT * 
            FROM qsusers
            LEFT JOIN ODanhSachNhanVien ON ODanhSachNhanVien.Ref_TenTruyCap = qsusers.UID
            WHERE IFNULL(qsusers.isActive, 0) = 1
        ');
        return $this->_o_DB->fetchAll($sql);
    }

    public function getUserByUIDs($uids = array()) {
        $uids[] = 0;
        $sql = sprintf('
            SELECT * 
            FROM qsusers 
            LEFT JOIN ODanhSachNhanVien ON ODanhSachNhanVien.Ref_TenTruyCap = qsusers.UID
            WHERE UID IN (%1$s)
            ', implode(',', $uids));
        return $this->_o_DB->fetchAll($sql);
    }

    public function getUserByEmployees($employees = array()) {
        $employees[] = 0;
        $sql = sprintf('
            SELECT * 
            FROM qsusers 
            LEFT JOIN ODanhSachNhanVien ON ODanhSachNhanVien.Ref_TenTruyCap = qsusers.UID
            WHERE ODanhSachNhanVien.IOID IN (%1$s)
            ', implode(',', $employees));
        return $this->_o_DB->fetchAll($sql);
    }
}