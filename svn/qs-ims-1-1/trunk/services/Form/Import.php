<?php
require_once QSS_ROOT_DIR . '/lib/PHPExcel.php';
require_once QSS_ROOT_DIR . '/lib/PHPExcel/IOFactory.php';
class Qss_Service_Form_Import extends Qss_Lib_Service
{

	protected $szFN;

	protected $rights;

	protected $form;

	protected $arrConfig;

	protected $objid;
	
	protected $arrImage;

	public function __doExecute ($params)
	{
		$this->arrConfig = array();
		$fid = $params['fid'];
		$ifid = $params['ifid'];
		$deptid = $params['deptid'];
		$objid = $params['objid'];
		$ignore = !@$params['ignore'];
		$nocheckall = !@$params['checkall'];
		$user = Qss_Register::get('userinfo');

		if(!is_dir($this->szFN =  QSS_DATA_DIR . "/tmp/"))
		{
			mkdir($this->szFN =  QSS_DATA_DIR . "/tmp/",0777);
		}
		$this->szFN =  QSS_DATA_DIR . "/tmp/" . $params['excel_import'];

		$deptid = $deptid ? $deptid : $user->user_dept_id;
		$this->form = new Qss_Model_Import_Form($fid,$ignore,$nocheckall);
		$this->form->i_IFID = $ifid;

		$this->objid = $objid;
		if($objid)
		{
			$object = $this->form->o_fGetObjectByCode($objid);
		}
		else
		{
				
			$object = $this->form->o_fGetMainObject();
		}
		$mainobject = $this->form->o_fGetMainObject();
		//trường hợp này load data ch main để lookup
		if($ifid)
		{
			$arrImport = array();
			$table = new Qss_Model_Db_Table($mainobject->ObjectCode);
			$table->where(sprintf('IFID_%2$s = %1$d',$ifid,$this->form->FormCode));
			$dataSQL = $table->fetchOne();
			$arrImport[$mainobject->ObjectCode][0]['ifid'] = (int) $ifid;
			if($dataSQL)
			{
				foreach ($mainobject->loadFields() as $f)
				{
					if($f->intFieldType != 17)
					{
						$arrImport[$mainobject->ObjectCode][0][$f->FieldCode] = $dataSQL->{$f->FieldCode};
					}
				}
			}
			if(count($arrImport))
			{
				$this->form->setData($arrImport);
			}
		}
		if ( file_exists($this->szFN) )
		{
			$ext = pathinfo($this->szFN, PATHINFO_EXTENSION);
			if($ext == 'xlsx')
			{
				$objReader = PHPExcel_IOFactory::createReader('Excel2007');
				$objPHPExcel = $objReader->load($this->szFN);
				$ws = $objPHPExcel->getSheet(0);
				if ( $this->fetchHeader($ws, $object) )
				{
					//load image to array
					$this->loadImageToArray($objPHPExcel,$ws);
					foreach ($ws->getRowIterator() as $row)
					{
						if ( $row->getRowIndex() != 1 )
						{
							$arrImport = array();
							$rowid = (int) ($ws->getCell("A" . $row->getRowIndex())->getValue());
							$fields = $object->loadFields();
							$arrImport[$object->ObjectCode][0]['ifid'] = (int) $ifid;
							$arrImport[$object->ObjectCode][0]['ifid'] = (int) $ifid;
							$arrImport[$object->ObjectCode][0]['no'] = $row->getRowIndex();
							foreach($fields as $f)
							{
								if ( isset($this->arrConfig[$f->ObjectCode][$f->FieldCode]))
								{
									$cellID = $this->arrConfig[$object->ObjectCode][$f->FieldCode] . $row->getRowIndex();
									$cell = $ws->getCell($cellID);
									$val = $cell->getValue();
									if($val && $f->intFieldType == 11)
									{
										$val = $val * 1000;
									}
									elseif($f->intFieldType == 9)
									{
										//check if exist image then copy to tmp and set value
										if(isset($this->arrImage[$objPHPExcel->getIndex($ws)][$cellID]))
										{
											$val = $this->arrImage[$objPHPExcel->getIndex($ws)][$cellID];
										}
									}
									$arrImport[$object->ObjectCode][0][$f->FieldCode] = $val;
								}
							}
							if($object->b_Main)
							{
								for($oi = 1;$oi < $objPHPExcel->getSheetCount();$oi++)
								{
									$subws = $objPHPExcel->getSheet($oi);
									$subsheetname = $subws->getTitle();
									$subobject = $this->form->o_fGetObjectByName($subsheetname);
									if($subobject)
									{
										if ( $this->fetchHeader($subws, $subobject) )
										{
											//load image to array
											$this->loadImageToArray($objPHPExcel,$subws);
											foreach ($subws->getRowIterator() as $subrow)
											{
												if ( $subrow->getRowIndex() != 1 )
												{
													$subrowid = (int) ($subws->getCell("A" . $subrow->getRowIndex())->getValue());
													if($rowid == $subrowid)
													{
														$arrImport[$subobject->ObjectCode][$subrow->getRowIndex()]['no'] = $row->getRowIndex();
														$subfields = $subobject->loadFields();
														foreach($subfields as $f)
														{
															if ( isset($this->arrConfig[$f->ObjectCode][$f->FieldCode]))
															{
																$cellID = $this->arrConfig[$subobject->ObjectCode][$f->FieldCode] . $subrow->getRowIndex();
																$cell = $subws->getCell($cellID);
																$val = $cell->getValue();
																if($val && $f->intFieldType == 11)
																{
																	$val = $val * 1000;
																}
																elseif($f->intFieldType == 9)
																{
																	if(isset($this->arrImage[$objPHPExcel->getIndex($subws)][$cellID]))
																	{
																		$val = $this->arrImage[$objPHPExcel->getIndex($subws)][$cellID];
																	}	
																}
																$arrImport[$subobject->ObjectCode][$subrow->getRowIndex()][$f->FieldCode] = $val;
															}
														}
													}
												}
											}
										}
										else
										{
											$this->setMessage("Sheet " .$subsheetname." không đúng định dạng" );
										}
									}
								}
							}
							else
							{
								//add main here
							}
							$this->form->setData($arrImport);
						}
					}
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
			$t1 = time();
			if(!$this->isError())
			{
				$this->form->generateSQL();
			}
			if(file_exists($this->szFN))
			{
				unlink($this->szFN);
			}
			$t2 = time();
			$time = ($t2-$t1);
			if($this->form->isError())
			{
				$this->setMessage("<span class='error'>Đã xuất hiện lỗi, hệ thống không thể import</span> {$this->form->countFormImported()} dòng không lỗi, {$this->form->countFormError()} dòng bị lỗi (di chuyển con trỏ đến dòng lỗi để biết thêm chi tiết lỗi). Thời gian thực hiện: {$time} giây");
			}
			else
			{
				$classname = 'Qss_Bin_Import_'.$this->form->FormCode;
				if ( class_exists($classname))
				{
					$trigger = new $classname($this->form);
					$db = Qss_Db::getAdapter('main');
					$db->beginTransaction();
					$trigger->__doExecute();
					$db->commit();
				}
				$this->setMessage("{$this->form->countFormImported()} dòng được import, {$this->form->countFormError()} dòng bị lỗi (di chuyển con trỏ đến dòng lỗi để biết thêm chi tiết lỗi). Thời gian thực hiện: {$time} giây");
			}
			return $this->getImportedGrid();
		}
	}

	function getImportedGrid ()
	{
		$all = $this->form->getErrorRows();
		$ret = '';
		foreach ($all as $key=>$rows)
		{
			$object = $this->form->o_fGetObjectByCode($key);
			$ret .= sprintf('<div class="line-hr"><span>%1$s</span></div>',$object->sz_Name);
			$ret .= '<table class="grid">';
			$ret .= '<tr>';
			$ret .= sprintf('<th>%1$s</th>','STT');
			foreach ($this->form->o_fGetObjectByCode($key)->loadFields() as $f)
			{
				if($f->bEditStatus)
				{
					$ret .= sprintf('<th>%1$s</th>',$f->szFieldName);
				}
			}
			$ret .= '</tr>';
			//$i = 1;
			foreach ($rows as $row)
			{
				if(!$row->Error)
				{
					//$i++;
					continue;
				}
				$error = explode(',',$row->ErrorMessage);
				if($row->Error)
				{
					$ret .= sprintf('<tr title="%1$s (%2$s)">'
					,$this->_translate($row->Error)
					,$row->ErrorMessage);
				}
				else
				{
					$ret .= '<tr class="imported">';
				}
				$ret .= sprintf('<td class="center %2$s">%1$d</td>',$row->No,($row->Error == 4)?'bgred':'');
				foreach ($row as $key=>$value)
				{
					if($key != 'IOID'
					&& $key != 'DeptID'
					&& $key != 'Error'
					&& $key != 'ErrorMessage'
					&& strpos($key, 'IFID_') === false
					&& $object->getFieldByCode($key)->bEditStatus)
					{
						$class = '';
						if(in_array($key,$error))
						{
							$class = 'bgred';
						}
						$ret .= sprintf('<td class="%2$s">%1$s</td>',$value,$class);
					}
				}
				$ret .= '</tr>';
				//$i++;
			}
			$ret .= '</table>';
		}
		return $ret;
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

	function fetchHeader ($ws, $object)
	{
		$this->arrConfig[$object->ObjectCode] = array();
		$row = $this->getRow($ws, 1);
		$cellIterator = $row->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
		foreach ($cellIterator as $cell)
		{
			if ( !is_null($cell) )
			{
				foreach ($object->loadFields() as $f)
				{
					if ( $f->FieldCode == $cell->getValue() || $f->szFieldName == $cell->getValue())//$f->ObjectCode . '_' . $f->FieldCode == $cell->getValue()
					{
						$this->arrConfig[$object->ObjectCode][$f->FieldCode] = $cell->getColumn();
					}
				}
			}
		}
		if ( !sizeof($this->arrConfig[$object->ObjectCode]) )
		{
			return false;
		}
		foreach ($object->loadFields() as $f)
		{
			if ( $f->bRequired && isset($this->arrConfig[$object->ObjectCode][$f->FieldCode]) )
			{
				return true;
			}
		}
		return true;
	}
	public function loadImageToArray($objPHPExcel,PHPExcel_Worksheet $ws)
	{
		foreach ($ws->getDrawingCollection() as $drawing) 
		{
	    	if ($drawing instanceof PHPExcel_Worksheet_MemoryDrawing) 
	    	{
	    		$extension = '';
		        ob_start();
		        call_user_func(
			            $drawing->getRenderingFunction(),
			            $drawing->getImageResource()
					);
		        $imageContents = ob_get_contents();
		        ob_end_clean();
		    	switch ($drawing->getMimeType()) 
		    	{
	           		case PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_PNG :
		                    $extension = 'png'; 
						break;
		            case PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_GIF:
		                    $extension = 'gif'; 
						break;
		            case PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_JPEG :
		                    $extension = 'jpg'; 
						break;
		        }
	    	}
	    	else
	    	{
	    		 $zipReader = fopen($drawing->getPath(),'r');
		        $imageContents = '';
		        while (!feof($zipReader)) {
		            $imageContents .= fread($zipReader,1024);
		        }
		        fclose($zipReader);
		        $extension = $drawing->getExtension();
	    	}
	    	$cellID = $drawing->getCoordinates();
			$filename = uniqid();
		    $fileName = QSS_DATA_DIR . "/tmp/" . $filename . "." . $extension;
    		file_put_contents($fileName,$imageContents);
  			$this->arrImage[$objPHPExcel->getIndex($ws)][$cellID] =  $fileName;		
		}
	}
}
?>