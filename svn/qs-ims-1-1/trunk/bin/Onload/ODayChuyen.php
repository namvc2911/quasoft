<?php
class Qss_Bin_Onload_ODayChuyen extends Qss_Lib_Onload
{
	
	/**
	 * onInsert
	 */
	public function __doExecute()
	{
		parent::__doExecute();
		$khuvuc = $this->_object->getFieldByCode('MaKhuVuc');
		if($khuvuc->intRefIOID)
		{
			$sql = sprintf('select * from OKhuVuc where IOID = %1$d',$khuvuc->intRefIOID);
			$dataSQL = $this->_db->fetchOne($sql);
			if($dataSQL)
			{
				if(!$this->_object->getFieldByCode('MaDayChuyen')->getValue())
				{
					$this->_object->getFieldByCode('MaDayChuyen')->setValue($dataSQL->MaKhuVuc);
				}
				if(!$this->_object->getFieldByCode('TenDayChuyen')->getValue())
				{
					$this->_object->getFieldByCode('TenDayChuyen')->setValue($dataSQL->Ten);
				}
			}
		}
	}
	
}