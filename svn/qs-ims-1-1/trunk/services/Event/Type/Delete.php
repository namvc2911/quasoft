<?php

class Qss_Service_Event_Type_Delete extends Qss_Service_Abstract
{

	public function __doExecute ($id)
	{
		$event = new Qss_Model_Event();
		$dataSQL = $event->getById($id);
		if($dataSQL)
		{
			$event->delete($id);
			$file = QSS_DATA_DIR . '/documents/event/' . $dataSQL->TypeID . '.' . $dataSQL->File;
			if ( file_exists($file) )
			{
				unlink($file);
			}		
		}
	}

}
?>