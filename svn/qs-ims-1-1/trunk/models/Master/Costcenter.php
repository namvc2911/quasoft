<?php
class Qss_Model_Master_Costcenter extends Qss_Model_Abstract
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getCostCenter()
    {
        $sql = sprintf('
			SELECT *
			FROM OTrungTamChiPhi AS cm
			WHERE  cm.DeptID in (%1$s)'
            , $this->_user->user_dept_list);
        return $this->_o_DB->fetchAll($sql);
    }
}