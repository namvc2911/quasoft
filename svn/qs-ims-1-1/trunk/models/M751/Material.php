<?php
class Qss_Model_M751_Material extends Qss_Model_Abstract {
    public function getMaterialsByIOIDs($materialIOIDs = array()) {
        $materialIOIDs[] = 0;

        $sql = sprintf('
            SELECT *
            FROM OYeuCauVatTu
            WHERE IOID IN (%1$s)
        ', implode(',', $materialIOIDs));

        return $this->_o_DB->fetchAll($sql);
    }

}