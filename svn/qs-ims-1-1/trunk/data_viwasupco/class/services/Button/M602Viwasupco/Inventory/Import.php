<?php
require_once QSS_ROOT_DIR . '/lib/PHPExcel.php';
require_once QSS_ROOT_DIR . '/lib/PHPExcel/IOFactory.php';

class Qss_Service_Button_M602Viwasupco_Inventory_Import extends Qss_Service_Abstract
{
    public function __doExecute($params)
    {
		if(!is_dir($filename =  QSS_DATA_DIR . "/tmp/"))
		{
			mkdir($filename =  QSS_DATA_DIR . "/tmp/",0777);
		}
		$filename =  QSS_DATA_DIR . "/tmp/" . $params['excel_import'];
		if ( file_exists($filename) )
		{
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			if($ext == 'xls')
			{
				$objReader = PHPExcel_IOFactory::createReader('Excel5');
				$objPHPExcel = $objReader->load($filename);
				$ws = $objPHPExcel->getSheet(0);
				$A1 = $ws->getCell("A1")->getValue();
				if ( $A1 == 'Mã kho' )
				{
					$model = new Qss_Model_Inventory_ViwasupcoInventory();
					$model->syncInventory($ws);
				}
				else
				{
					$this->setError();
					$this->setMessage('<span class="error">'.$this->_translate(164).'</span>');
				}
			}
			else
			{
				$this->setError();
				$this->setMessage('<span class="error">'.$this->_translate(145).'</span>');
			}
			if(file_exists($filename))
			{
				unlink($filename);
			}
		}
    }
}