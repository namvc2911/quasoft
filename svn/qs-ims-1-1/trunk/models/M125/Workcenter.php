<?php

/**
 * Class Qss_Model_M759_Workorder
 * 1. getClosedUnbrokenByEquip($eqID) Lấy các phiếu bảo trì không phải sự cố theo thiết bị.
 * 2. countWorkOrdersByStatus($start = '' , $end = '', $workcenter = 0, $employee = 0)
 * Đếm phiếu bảo trì theo tình trạng của phiếu bảo trì
 */
class Qss_Model_M125_Workcenter extends Qss_Model_Abstract
{
     public function getWorkCenters($startdate,$enddate)
    {
    	$sqlwhere = '1=1';
		if(Qss_Lib_System::formSecure('M125'))
		{
			$sqlwhere .= sprintf(' and ODonViSanXuat.IFID_M125 in (select IFID from qsrecordrights where FormCode = "M125" and UID=%1$d)'
					,$this->_user->user_id);
		}
        $sql = sprintf('
			SELECT
				*
			FROM ODonViSanXuat
			WHERE %1$s'
            /* ORDER BY */
            , $sqlwhere);
        return $this->_o_DB->fetchAll($sql);
    }
}