<?php

class Qss_Service_Admin_Document_Delete extends Qss_Service_Abstract
{

	public function __doExecute ($id)
	{
		$docmodel = new Qss_Model_Admin_Document();
		$dataSQL = $docmodel->getById($id);
		if($dataSQL)
		{
			$docmodel->delete($id);
			$file = QSS_DATA_DIR . '/documents/template/' . $dataSQL->DTID . '.' . $dataSQL->File;
			if ( file_exists($file) )
			{
				unlink($file);
			}		
		}
		
	}

}
?>