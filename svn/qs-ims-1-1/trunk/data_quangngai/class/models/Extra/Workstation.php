<?php
/**
 * @author: Thinh Tuan
 */
class Qss_Model_Extra_Workstation extends Qss_Model_Abstract
{
	//-----------------------------------------------------------------------
	/**
	* Build constructor
	*'
	* @return void
	*/
	public function __construct ()
	{
		parent::__construct();
	}	
        
        public function getWorkstation()
	{
		$sql = sprintf('select * from  Tram');
		return $this->_o_DB->fetchAll($sql);
	}
        
        public function getWorkstationByID($id)
        {
            $where = '';
            if(is_array($id))
            {
                foreach($id as $iEle)
                {
                    $where .= $where?' or ':'';
                    $where .= $iEle?sprintf(' mc.IOID = %1$d ', $iEle):'';
                }
            }
            else
            {
                $where = $id?sprintf(' mc.IOID = %1$d ', $id):'';
            }
            $where = $where?sprintf(' where %1$s', $where):' where 1 = 0 ';
            
            $sql = sprintf (' select * from Tram as mc %1$s', $where);
            return $this->_o_DB->fetchAll($sql);
        }
}