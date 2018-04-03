<?php
/**
 * 
 * @author HuyBD
 *
 */
class Qss_Lib_Trigger extends Qss_Lib_Bin
{
	public function onInsert($object){}
	public function onUpdate($object){}
	public function onDelete($object){}//for sub object
	public function onInserted($object){}
	public function onUpdated($object){}
	public function onDeleted($object){}//for sub object
	
    protected function _checkDateRange($startDate, $endDate, $message = 'Error')
    {
        if($startDate && $endDate && Qss_Lib_Date::compareTwoDate($startDate, $endDate) == 1)
        {
            $this->setError();
            $this->setMessage($message);
        }
    }
}
?>