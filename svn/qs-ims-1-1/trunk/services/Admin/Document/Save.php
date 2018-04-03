<?php

class Qss_Service_Admin_Document_Save extends Qss_Service_Abstract
{

	public function __doExecute ($params)
	{
		$filename = QSS_DATA_DIR . '/tmp/' . $params['szFile'];
		$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$params['szFile'] = $ext;
		$docmodel = new Qss_Model_Admin_Document();
		$id = $docmodel->save($params);
		if(is_file($filename))
		{
			if(!is_dir(QSS_DATA_DIR . "/documents/template/"))
			{
				mkdir(QSS_DATA_DIR . "/documents/template/");
			}
			$destfile = QSS_DATA_DIR . "/documents/template/" . $id . "." . $ext;
			$ret = copy($filename, $destfile);
			unlink($filename);
		}
	}

}
?>