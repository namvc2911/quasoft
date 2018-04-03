<?php
class Qss_Model_M720_Location extends Qss_Model_Abstract
{
    /**
     * Lấy các khu vực không trực thuộc theo khu vực nào, sắp xếp theo hình cây
     */
    public function getLocationDontHasParent()
    {
        $sql = sprintf('
            SELECT * FROM OKhuVuc WHERE IFNULL(Ref_TrucThuoc, 0) = 0 ORDER BY lft
        ');

        return $this->_o_DB->fetchAll($sql);
    }
}