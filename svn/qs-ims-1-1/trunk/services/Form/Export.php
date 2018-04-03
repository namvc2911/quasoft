<?php
require_once QSS_ROOT_DIR . '/lib/PHPExcel.php';
require_once QSS_ROOT_DIR . '/lib/PHPExcel/IOFactory.php';

class Qss_Service_Form_Export extends Qss_Service_Abstract
{

	protected $szFN;

	protected $objWriter;

	protected $arrConfig;

	protected $arrIFIDNo;

	public function __doExecute ($params)
	{
		$this->arrConfig = array();
		$this->arrIFIDNo = array();

		$fid = $params['fid'];
		$ifid = $params['ifid'];
		$deptid = $params['deptid'];
		$objid = $params['objid'];
		$download = $params['type'];
		$user = Qss_Register::get('userinfo');
		$this->szFN = $params['excel_import'];
		$deptid = $deptid ? $deptid : $user->user_dept_id;
		$form = new Qss_Model_Form();
		$objects = array();
		$subobjects = array();
		if ( $ifid)
		{
			$form->initData($ifid, $deptid);
			
		}
		else
		{
			$form->init($fid, $deptid, Qss_Register::get('userinfo')->user_id);
			
		}
		if($objid)
		{
			$objects[] = $form->o_fGetObjectByCode($objid);
			$name = $form->o_fGetObjectByCode($objid)->sz_Name;
		}
		else 
		{
			$objects = $form->o_fGetMainObjects();
			$subobjects = $form->a_fGetSubObjects();
			$name = $form->sz_Name;
		}
		$this->szFN = QSS_DATA_DIR . "/tmp/" . uniqid() . ".xlsx";
		$objPHPExcel = new PHPExcel();
		$this->objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$this->objWriter->setPreCalculateFormulas(false);
		$ws = $objPHPExcel->setActiveSheetIndex(0);
		$ws->setTitle($form->sz_Name);
		$this->doExport($ws, $form, $objects, $download);
		if(count($subobjects))
		{
			foreach ($subobjects as $key=>$item)
			{
				if(!($item->bPublic & 1))
				{
					$subws = $objPHPExcel->createSheet();
					$subws->setTitle($item->sz_Name);
					$this->doExport($subws, $form, array($item), $download,true);
				}
			}
		}
		$objPHPExcel->setActiveSheetIndex(0);
		$this->objWriter->save($this->szFN);
		return array($this->szFN,$name);
	}

	private function getRow ($ws, $idx)
	{
		foreach ($ws->getRowIterator() as $row)
		{
			if ( $row->getRowIndex() == $idx )
			return $row;
		}
		return null;
	}

	function fetchHeader ($ws, Qss_Model_Object $object)
	{
		$arrConfig = array();
		$row = $this->getRow($ws, 1);
		$cellIterator = $row->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
		foreach ($cellIterator as $cell)
		{
			if ( !is_null($cell) )
			{
				$fields = $object->loadFields();
				foreach ($fields as $f)
				{
					if($f->bEffect && $f->intFieldType != 17)
					{
						if ( $f->FieldCode == $cell->getValue() )//$f->ObjectCode . '_' . $f->FieldCode == $cell->getValue()
						{
							$arrConfig[$f->ObjectCode][$f->FieldCode] = $cell->getColumn();
						}
					}
				}
			}
		}
		if ( !sizeof($arrConfig) )
		{
			return false;
		}
		$fields = $object->loadFields();
		foreach ($fields as $f)
		{
			if ( $f->bRequired && !isset($arrConfig[$f->ObjectCode][$f->FieldCode]))
			{
				return false;
			}
		}
		$this->arrConfig[$object->ObjectCode] = $arrConfig;
		return true;
	}

	function doExport ($ws, Qss_Model_Form $form, $allobjects, $download,$allsub = false)
	{
		$excelcolums = array_keys(Qss_Lib_Const::$EXCEL_COLUMN);
		$mainobject = $form->o_fGetMainObject();
		$this->genHeader($ws, $allobjects, $download);
		$db = Qss_Db::getAdapter('main');
		if ( $download == 2 || $download == 3 )
		{
			$filter = Qss_Cookie::get('form_' . $form->FormCode . '_filter');
			$filter = unserialize($filter);
			if(isset($allobjects[0]))//sub
			{
				$sql = $form->sz_fGetSubSQLByUser(Qss_Register::get('userinfo'), 1, 0,array(),$filter,array(),'',$allobjects[0]->ObjectCode);
				$objects = $db->fetchAll($sql[0]);//$mainobject->a_fGetIOIDBySQL($sql[0],1,0);
				$ws->setTitle($allobjects[0]->sz_Name);
			}
			else
			{
				if($form->i_IFID)
				{
					$filter['IFID'] = $form->i_IFID;
				}
				$sql = $form->sz_fGetSQLByUser(Qss_Register::get('userinfo'), 1, 0,array(),$filter);
				$objects = $db->fetchAll($sql[0]);//$mainobject->a_fGetIOIDBySQL($sql[0],1,0);
				$ws->setTitle($mainobject->sz_Name);
			}

			$j = 2;//dòng
			$k = 1;//cột
			$arr =array();
			foreach ($objects as $data)
			{
				$temp = array();
				if(!isset($this->arrIFIDNo[$data->IFID]))
				{
					$this->arrIFIDNo[$data->IFID] = $k;
				}
				//cột đầu tiên
				$temp[] = $this->arrIFIDNo[$data->IFID];
				$i = 1;
				foreach($allobjects as $object)
				{
					if($object->bPublic & 1)
					{
						continue;
					}
					$fields = $object->loadFields();
					$object->i_IOID = $data->IOID;
					$object->i_IFID = $data->IFID;
					foreach ($fields as $f)
					{
						if($f->bEffect && $f->intFieldType != 17)
						{
							$classname = 'Qss_Bin_Calculate_'.$f->ObjectCode.'_'.$f->FieldCode;
							if($f->intFieldType == 17 && class_exists($classname))
							{
								$autocal = new $classname($object);
								$val = $autocal->__doExecute();
								if($val && $f->intFieldType == 11)
								{
									$val = $val / 1000;
								}
								$temp[] = $val;
							}
							elseif($f->intFieldType == 9)
							{
								$temp[] = null;
								$val = $data->{$f->FieldCode};
								$fn = QSS_DATA_DIR.'/documents/'.$val;
								if($val && file_exists($fn))
								{
									$ok = true;
									$ext = pathinfo($fn, PATHINFO_EXTENSION);
									if( $ext == 'jpg'
										||$ext == 'jpeg'
										||$ext == 'pjpeg')
									{
										$gdImage = @imagecreatefromjpeg($fn);
									}
									elseif( $ext == 'png')
									{
										$gdImage = @imagecreatefrompng($fn);
									}
									elseif( $ext == 'gif')
									{
										$gdImage = @imagecreatefromgif($fn);
									} 
									else
									{
										$ok = false;
									}
									$ok = $gdImage?true:false;
									if($ok)
									{
										// Add a drawing to the worksheetecho date('H:i:s') . " Add a drawing to the worksheet\n";
										$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
										$objDrawing->setImageResource($gdImage);
										$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_JPEG);
										$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
										$objDrawing->setHeight(100);
										$objDrawing->setHeight(120);
										$objDrawing->setCoordinates($excelcolums[$i].$j);
										$objDrawing->setWorksheet($ws);
										$ws->getRowDimension($j)->setRowHeight(90);
										$ws->getColumnDimension($excelcolums[$i])->setWidth(23);
									}
								}
							}
							else
							{
								$val = $data->{$f->FieldCode};
								if($val && $f->intFieldType == 11)
								{
									$val = $val / 1000;
								}
								$temp[] = (string) $val;
							}
							$i ++;
						}
					}
				}
				$arr[] = $temp;
				$j ++;
				$k+=1;
			}
			$i = 1;
			$bind = array();
			foreach ($allobjects as $object)
			{
				if($object->bPublic & 1)
				{
					continue;
				}
					
				$fields = $object->loadFields();

				foreach ($fields as $f)
				{
					if($f->bEffect && $f->intFieldType != 17)
					{
						if($f->intFieldType == 1 || $f->intFieldType == 2 || $f->intFieldType == 3)
						{
							//$ws->getStyle($excelcolums[$i].'1:'.$excelcolums[$i].($j+1))
							//->getNumberFormat()
							//->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
							$bind[] = $excelcolums[$i];
							//$ws->getStyle($excelcolums[$i].'1:'.$excelcolums[$i].($j+1))
							//->setQuotePrefix(true);
						}
						$i++;
					}
				}
			}
			if(count($bind))
			{
				PHPExcel_Cell::setValueBinder(new PHPExcel_Cell_MyColumnValueBinder($bind));
			}
			$ws->fromArray($arr,NULL,'A2');
		}
		//$this->objWriter->save($this->szFN);
	}
	function genHeader ($ws, $objects, $download)
	{
		$i = 1;
		$cell = $ws->getCellByColumnAndRow(0, 1);
		$cell->setValue('*');
		$user = Qss_Register::get('userinfo');
		$arr = array_values($objects);
		if($arr[0]->b_Main)
		{
			$helpcontent = 'Cột * được đánh số thứ tự không trùng nhau nhằm liên kết dữ liệu với các sheet tiếp theo, ví dụ: Thiết bị A có cột * là 1, thì tất cả các dòng ở sheet cấu trúc phụ tùng có cột * là 1 là phụ tùng của thiết bị A';
		}
		else
		{
			$helpcontent = 'Cột * là giá trị tham chiếu với sheet đầu tiên, nếu sheet này là sheet đầu tiên thì có thể để trống';
		}
		$ws->getComment($cell->getCoordinate())
		->getText()->createTextRun($helpcontent);
		foreach($objects as $object)
		{
			$fields = $object->loadFields();
			foreach ($fields as $f)
			{
				if($f->bEffect && $f->intFieldType != 17)
				{
					$cell = $ws->getCellByColumnAndRow($i, 1);
					$cell->setValue($f->szFieldName);
					$help = QSS_PUBLIC_DIR . '/help/objects/'.$object->ObjectCode.'/'.$f->FieldCode.'_'.$user->user_lang.'.html';
					if(file_exists($help))
					{
						$helpcontent = file_get_contents($help);
						if($helpcontent)
						{
							$ws->getComment($cell->getCoordinate())
							->getText()->createTextRun($helpcontent);
						}
					}
					if($f->bRequired)
					{
						$ws->getStyle($cell->getCoordinate())->applyFromArray(
						array(
							        'fill' => array(
							            'type' => PHPExcel_Style_Fill::FILL_SOLID,
							            'color' => array('rgb' => 'FFC0CB')
						)
						)
						);
					}
					$i ++;
				}
			}
		}
		$ws->freezePane('A2');
	}
}
class PHPExcel_Cell_MyColumnValueBinder extends PHPExcel_Cell_DefaultValueBinder implements PHPExcel_Cell_IValueBinder
{
	protected $stringColumns = array();

	public function __construct(array $stringColumnList = array()) {
		// Accept a list of columns that will always be set as strings
		$this->stringColumns = $stringColumnList;
	}

	public function bindValue(PHPExcel_Cell $cell, $value = null)
	{
		// If the cell is one of our columns to set as a string...
		if (in_array($cell->getColumn(), $this->stringColumns)) {
			// ... then we cast it to a string and explicitly set it as a string
			$cell->setValueExplicit((string) $value, PHPExcel_Cell_DataType::TYPE_STRING);
			return true;
		}
		// Otherwise, use the default behaviour
		return parent::bindValue($cell, $value);
	}
}
?>