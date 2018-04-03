<?php
class Qss_Model_M339_Main extends Qss_Model_Abstract
{
	public function getLastPeriod()
    {
    	$sql = sprintf('
			SELECT
				*
			FROM OKyCong 
			inner join qsiforms on qsiforms.IFID = OKyCong.IFID_M339   
			WHERE ThoiGianKetThuc < date(now())
			and qsiforms.Status = 1
			order by ThoiGianKetThuc desc
			limit 1');
        return $this->_o_DB->fetchOne($sql);
    }
}