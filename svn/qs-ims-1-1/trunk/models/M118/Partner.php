<?php
class Qss_Model_M118_Partner extends Qss_Model_Abstract
{
    public function getContactsByPartnerIOIDs($ioids = array()) {
        $ioids[] = 0;
        $sql = sprintf('
            SELECT OLienHeCaNhan.*, ODoiTac.IOID AS DoiTacIOID, ODoiTac.MaDoiTac
            FROM ODoiTac
            INNER JOIN OLienHeCaNhan ON ODoiTac.IFID_M118 = OLienHeCaNhan.IFID_M118
            WHERE ODoiTac.IOID IN (%1$s)', implode(',', $ioids));
        return $this->_o_DB->fetchAll($sql);
    }
}