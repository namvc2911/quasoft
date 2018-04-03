<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_Fobject extends Qss_Model_Abstract
{
	
	public $FormCode;//
	
	public $ObjectCode;//mainobject
	
	public $ifid;
	
	public $ioid;
	
	public $data;
	
	public function __construct ($ifid)
	{
		parent::__construct();
		$this->ifid = $ifid;
		$sql = sprintf('select * from qsiforms
						inner join qsfobjects on qsiforms.FormCode = qsfobjects.FormCode   
						where qsiforms.IFID = %1$d and Main = 1'
					,$ifid);
		$dataSQL = $this->_o_DB->fetchOne($sql);
		if($dataSQL)
		{
			$this->FormCode = $dataSQL->FormCode;
			$this->ObjectCode = $dataSQL->ObjectCode;
			$sql = sprintf('select * from %1$s where IFID_%2$s = %3$d'
					,$this->ObjectCode
					,$this->FormCode
					,$ifid);
			$dataSQL = $this->_o_DB->fetchOne($sql);
			if($dataSQL)
			{
				$this->ioid = $dataSQL->IOID;
				$this->data = $dataSQL;
			}
			return true;	
		}
		return false;
	}
	public function getRefField($ObjectCode)
	{
		$retval = array();
		$sql = sprintf('select * from qsfields
						where ObjectCode = %1$s and RefObjectCode = %2$s and Effect = 1'
					,$this->_o_DB->quote($ObjectCode)
					,$this->_o_DB->quote($this->ObjectCode));
		$dataSQL = $this->_o_DB->fetchAll($sql);
		foreach ($dataSQL as $item)
		{
			$retval[] = $item->FieldCode;
		}
		return $retval;
	}
}
?>