<?php
class Qss_Model_M052_Main extends Qss_Model_Abstract {

    /**
     * A. Các hàm liên quan đến xử lý cây phòng ban
     */

    /**
     * Tìm kiếm thông tin node con thông qua IOID của node cha
     * @param $parentIOID
     * @return array|void
     */
    public function getNodeByParentNode($parentIOID, $search) {
        $user       = Qss_Register::get('userinfo');

        $sql = sprintf('
                    SELECT PhongBan.*, IF((PhongBan.rgt - PhongBan.lft) > 1, 1, 0) AS HasChild
                    FROM OPhongBan AS PhongBan
                    WHERE 1=1                        ');
        // @note: $parentIOID == 0 tức là lấy root node

        // Chỉ khi không có search thì mới tìm theo node cha, nếu có search tìm theo mã và tên
        if($search) {
            $sql .= sprintf(' 
                AND PhongBan.MaPhongBan like "%%%1$s%%" OR PhongBan.TenPhongBan like "%%%1$s%%"            
            ', $search);
        }
        else {
            $sql .= sprintf(' AND IFNULL(PhongBan.Ref_TrucThuoc, 0) = %1$d ',$parentIOID);
        }


        // Kiểm tra phòng ban còn hoạt động không
        $sql .= sprintf('
                    AND (
                        IFNULL(PhongBan.HoatDong, 0) = 1 AND
                        (
                            ("%1$s" >= PhongBan.NgayBatDau AND IFNULL(PhongBan.NgayKetThuc, "") = "") 
                            OR
                            ("%1$s" BETWEEN PhongBan.NgayBatDau AND PhongBan.NgayKetThuc)                    
                        )
                    )
                ', date('Y-m-d'));

        // Ở trong cùng 1 công ty (Dep của hệ thông)
        if($user && $user->user_dept_list) {
            $sql .= sprintf(' AND PhongBan.DeptID in (%1$s)', $user->user_dept_list);
        }

        // Được phân quyền ở node root hoặc có 1 node con đươc phân quyền
        if(Qss_Lib_System::formSecure('M319')) {
            $sql .= sprintf('
                        AND (
                            (
                                SELECT count(qsrecordrights.IFID) AS Total
                                FROM OPhongBan AS PhongBanChild
                                INNER JOIN qsrecordrights ON qsrecordrights.FormCode = "M319" 
                                    AND PhongBanChild.IFID_M319 = qsrecordrights.IFID
                                WHERE PhongBanChild.lft >= PhongBan.lft AND PhongBanChild.rgt <= PhongBan.rgt 
                                    AND UID = %1$d
                            ) > 0
                        ) 
                        ',$user->user_id
            );
        }

        // Sắp xếp theo thứ tự trên cây
        $sql .= ' ORDER BY  ';
        $sql .= ' CASE WHEN IFNULL(PhongBan.Loai , 0) = 1 THEN 1 ';
        $sql .= ' WHEN IFNULL(PhongBan.Loai , 0) = 2 THEN 2 ';
        $sql .= ' WHEN IFNULL(PhongBan.Loai , 0) = 3 THEN 3   ';
        $sql .= ' ELSE 4 END  ';
        $sql .= ' , PhongBan.lft ';

        $sql .= sprintf(' LIMIT 100000');

        // echo '<pre>'; print_r($sql); die;

        return $this->_o_DB->fetchAll($sql);
    }

    public function countEmployees($departmentIOID, $searchEmp) {
        if($departmentIOID || $searchEmp) {
            $sql = sprintf('
                SELECT count(1) as Total
                FROM ODanhSachNhanVien 
                INNER JOIN OPhongBan ON ODanhSachNhanVien.Ref_MaPhongBan = OPhongBan.IOID
                WHERE 1=1                
            ');

            if($departmentIOID) {
                $sql .= sprintf('
                    AND (
                        OPhongBan.lft >= (SELECT lft FROM OPhongBan WHERE IOID = %1$d)
                        AND OPhongBan.rgt <= (SELECT rgt FROM OPhongBan WHERE IOID = %1$d)
                    )
                ', $departmentIOID);
            }

            if($searchEmp) {
                $sql .= sprintf('
                    AND (
                        ODanhSachNhanVien.MaNhanVien like "%%%1$s%%"
                        OR ODanhSachNhanVien.TenNhanVien like "%%%1$s%%"
                    )
                ', $searchEmp);
            }
            $data = $this->_o_DB->fetchOne($sql);
            return $data?$data->Total:0;
        }
        return 0;
    }

    public function getEmployees($departmentIOID, $searchEmp = '', $page, $display) {
        if($departmentIOID || $searchEmp) {
            $sql = sprintf('
                SELECT ODanhSachNhanVien.* 
                FROM ODanhSachNhanVien 
                INNER JOIN OPhongBan ON ODanhSachNhanVien.Ref_MaPhongBan = OPhongBan.IOID
                WHERE 1=1                
            ');

            if($departmentIOID) {
                $sql .= sprintf('
                    AND (
                        OPhongBan.lft >= (SELECT lft FROM OPhongBan WHERE IOID = %1$d)
                        AND OPhongBan.rgt <= (SELECT rgt FROM OPhongBan WHERE IOID = %1$d)
                    )
                ', $departmentIOID);
            }

            if($searchEmp) {
                $sql .= sprintf('
                    AND (
                        ODanhSachNhanVien.MaNhanVien like "%%%1$s%%"
                        OR ODanhSachNhanVien.TenNhanVien like "%%%1$s%%"
                    )
                ', $searchEmp);
            }

            $start = ceil(($page - 1) * $display);
            $sql .= sprintf(' LIMIT %1$d, %2$d', $start, $display);

            // echo '<pre>'; print_r($sql); die;
            return $this->_o_DB->fetchAll($sql);
        }
        return array();
    }

    public function getPhongBanByIOID($ioid) {
        $sql = sprintf('
                    SELECT PhongBan.* 
                    FROM OPhongBan AS PhongBan
                    WHERE IOID = %1$d                        ', $ioid);
        return $this->_o_DB->fetchOne($sql);
    }

    public function getNhanVienByIOID($ioid) {
        $sql = sprintf('
                    SELECT ODanhSachNhanVien.* 
                    FROM ODanhSachNhanVien 
                    WHERE IOID = %1$d                        ', $ioid);
        return $this->_o_DB->fetchOne($sql);
    }
}