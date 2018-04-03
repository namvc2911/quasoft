<?php

class Qss_Service_Event_Type_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$filename = QSS_DATA_DIR . '/tmp/' . $params['szFile'];
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$params['szFile'] = $ext;
		$event = new Qss_Model_Event();
		$id = $event->saveType($params);
		if(is_file($filename))
		{
			if(!is_dir(QSS_DATA_DIR . "/documents/event/"))
			{
				mkdir(QSS_DATA_DIR . "/documents/event/");
			}
			$destfile = QSS_DATA_DIR . "/documents/event/" . $id . "." . $ext;
			$ret = copy($filename, $destfile);
			unlink($filename);
		}
	}

}
?>