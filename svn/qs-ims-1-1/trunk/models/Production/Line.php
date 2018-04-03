<?php

/**
 * @author: ThinhTuan
 * @component: Production
 */
class Qss_Model_Production_Line extends Qss_Model_Abstract {

    public function __construct() {
        parent::__construct();
    }
    
    public function getAll()
    {
        $sql = sprintf('select *
							from ODayChuyen
							order by IOID');
        return $this->_o_DB->fetchAll($sql);
    }
            
}

?>