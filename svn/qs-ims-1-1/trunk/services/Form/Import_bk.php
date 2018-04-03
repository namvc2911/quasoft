<?php
require_once QSS_ROOT_DIR . '/lib/PHPExcel.php';
require_once QSS_ROOT_DIR . '/lib/PHPExcel/IOFactory.php';
class Qss_Service_Form_Import extends Qss_Lib_Service
{

	protected $arrImportedRow;

	protected $intImported;

	protected $intError;

	protected $szFN;
	
	protected $rights;
	
	protected $form;
	
	protected $arrConfig;

	public function __doExecute ($params)
	{
		$this->arrConfig = array();
		$fid = $params['fid'];
		$ifid = $params['ifid'];
		$deptid = $params['deptid'];
		$objid = $params['objid'];
		$ignore = !@$params['ignore'];
		$user = Qss_Register::get('userinfo');

		if(!is_dir($this->szFN =  QSS_DATA_DIR . "/tmp/"))
		{
			mkdir($this->szFN =  QSS_DATA_DIR . "/tmp/",0777);
		}
		$this->szFN =  QSS_DATA_DIR . "/tmp/" . $params['excel_import'];
		
		$this->arrImportedRow = array();
		$this->intImported = 0;
		$this->intError = 0;
		$deptid = $deptid ? $deptid : $user->user_dept_id;
		$form = new Qss_Model_Form();
		if ( $ifid && $deptid && $form->initData($ifid, $deptid) )
		{
			$rights = $form->i_fGetRights($user->user_group_list);
			if ( ($rights & 4) )
			{
				$object = $form->o_fGetObjectByCode($objid);
				$object->initData($ifid, $deptid, 0);
			}
			else
			{
				$this->setMessage($this->_translate(146));
				$this->setError();
			}
		}
		elseif ( $form->init($fid, $deptid, $user->user_id) )
		{
			$rights = $form->i_fGetRights($user->user_group_list);
			if ( ($rights & 1) )
			{
				$object = $form->o_fGetMainObject();
			}
			else
			{
				$this->setMessage($this->_translate(146));
				$this->setError();
			}
		}
		else
		{
			$this->setMessage($this->_translate(145));
			$this->setError();
		}
		if ( !$this->isError() && file_exists($this->szFN) )
		{
			$this->rights = $rights;
			$this->form = $form;
			$objReader = PHPExcel_IOFactory::createReader('Excel5');
			$objPHPExcel = $objReader->load($this->szFN);
			$ws = $objPHPExcel->getSheet(0);
			if ( $this->fetchHeader($ws, $object) )
			{
				foreach ($ws->getRowIterator() as $row)
				{
					if ( $row->getRowIndex() != 1 )
					{
						$rowid = (int) ($ws->getCell("A" . $row->getRowIndex())->getValue());
						$iobject = clone $object;
						//$iobject instanceof Qss_Model_Object;
						$fields = Qss_Lib_Util::cloneData($iobject->a_Fields);
						if($this->importRow($ws, $row, $iobject, $ignore))
						{
							// check if import form
							if($iobject->b_Main)
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
											//init form
											if($this->form->initData($iobject->i_IFID, $iobject->intDepartmentID))
											{
												$subobject = $this->form->o_fGetObjectByName($subsheetname);
												foreach ($subws->getRowIterator() as $subrow)
												{
													if ( $subrow->getRowIndex() != 1 )
													{
														$subrowid = (int) ($subws->getCell("A" . $subrow->getRowIndex())->getValue());
														if($rowid == $subrowid)
														{
															$isubobject = clone $subobject;
															$subfields = Qss_Lib_Util::cloneData($isubobject->a_Fields);
															$this->importRow($subws, $subrow, $isubobject, $ignore);
															unset($isubobject);
															//print_r($fields);die;
															$subobject->a_Fields = $subfields;
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
						}
						unset($iobject);
						//print_r($fields);die;
						$object->a_Fields = $fields;//@TODO reset causes reload fields next time
						//$object->v_fResetFields();	
					}
				}
				$this->setMessage("{$this->intImported} dòng được import, {$this->intError} dòng bị lỗi (di chuyển con trỏ đến dòng lỗi để biết thêm chi tiết lỗi)");
			}
			else
			{
				$this->setError();
				$this->setMessage($this->_translate(164));
			}
			if(file_exists($this->szFN))
			{
				unlink($this->szFN);
			}
			return $this->getImportedGrid();
		}
	}

	function getImportedGrid ()
	{
		$ret = '';
		if ( sizeof($this->arrImportedRow) )
		{
			$ret = '<table class="grid">';
			foreach ($this->arrImportedRow as $val)
			{
				$ret .= $val;
			}
			$ret .= '</table>';
		}
		return $ret;
	}

	function printRow ($object)
	{
		$ret = '<tr class="imported">';
		foreach ($object->loadFields() as $f)
		{
			if ( isset($this->arrConfig[$f->ObjectCode][$f->FieldCode]) )
			{
				$ret .= '<td>';
				$ret .= $f->sz_fGetDisplay();
				$ret .= '</td>';
			}
		}
		$ret .= '</tr>';
		$this->intImported ++;
		$this->arrImportedRow[] = $ret;
	}

	function printErrorRow ($object,$message)
	{
		$ret = sprintf('<tr class="error" title="%1$s">',$message);
		foreach ($object->loadFields() as $f)
		{
			if ( isset($this->arrConfig[$object->ObjectCode][$f->FieldCode]) )
			{
				$ret .= '<td>';
				$ret .= $f->sz_fGetDisplay();
				$ret .= '</td>';
			}
		}
		$ret .= '</tr>';
		$this->intError ++;
		$this->arrImportedRow[] = $ret;
	}

	function importRow ($ws, $row, Qss_Model_Object $object, $ignore)
	{
		//$object->initData($this->i_IFID, $this->intFormDepartmentID, $object->i_IOID);
		$retval = true;
		$classname = 'Qss_Bin_Onload_'.$object->ObjectCode;
		if(!class_exists($classname))
		{
			$classname = 'Qss_Lib_Onload';
		}
		$onload = new $classname(null,$object);
		foreach ($object->loadFields() as $f)
		{
			if ( isset($this->arrConfig[$f->ObjectCode][$f->FieldCode]) && !$f->bReadOnly)
			{
				$onload->{$f->FieldCode}();
				$cell = $ws->getCell($this->arrConfig[$object->ObjectCode][$f->FieldCode] . $row->getRowIndex());
				$val = $cell->getValue();
				$val = ($val === null)?'':$val;
				if ( $f->intInputType == 3 || $f->intInputType == 4 || $f->intInputType == 11 || $f->intInputType == 12)
				{
					if(is_int($val) && ($f->RefFieldCode 
					|| $f->intFieldType == 14 
					|| $f->intFieldType == 16))
					{
						$f->setRefIOID($val);
					}
					else 
					{
						$f->setValue($val);
					}
				}
				elseif($f->intFieldType == 7 )
				{
					$f->setValue(($val?true:false));
				}
				else
				{
					$f->setValue($val);
				}
			}
		}
		$errorMessage = '';
		if ( $this->b_fValidate($ws, $row, $object,$ignore,$errorMessage) && $this->b_fCheckRequire($object) )
		{
			if($object->i_IFID)
			{
				$this->form->i_IFID = $object->i_IFID; 
			}
			$classname = 'Qss_Bin_Trigger_'.$object->ObjectCode;
			$ioid = $object->i_IOID;
			if(class_exists($classname))
			{
				$trigger = new $classname($this->form);
				$trigger->init();
				if($ioid)
				{
					$trigger->onUpdate($object);
				}
				else
				{
					$trigger->onInsert($object);
				}
				$errorMessage .= $trigger->getMessage();
			}
			if((!isset($trigger) || !$trigger->isError()) && $object->b_fSave())
			{
				if($object->i_IFID)
				{
					$this->form->i_IFID = $object->i_IFID; 
				}
				$this->printRow($object);
				if(class_exists($classname))
				{
					$trigger = new $classname($this->form);
					$trigger->init();
					if($ioid)
					{
						$trigger->onUpdated($object);
					}
					else
					{
						$trigger->onInserted($object);
					}
					$errorMessage .= $trigger->getMessage();
				}
			}
			else
			{
				$this->printErrorRow($object,'Dữ liệu không thay đổi so với bản cũ');
				//$retval = false;
			}
		}
		else
		{
			$this->printErrorRow($object,$errorMessage);
			$retval = false;
		}
		return $retval;
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

	/**
	 *
	 * @param Qss_Model_Object $object
	 * @param $data
	 * @return boolean
	 */
	public function b_fValidate ($ws,$row,Qss_Model_Object &$object,$ignore,&$message)
	{
		$ret = true;
		$user = Qss_Register::get('userinfo');
		$classname = 'Qss_Bin_Onload_'.$object->ObjectCode;
		if(!class_exists($classname))
		{
			$classname = 'Qss_Lib_Onload';
		}
		$onload = new $classname(null,$object);
		$arrDuplicate = array();
		foreach ($object->loadFields() as $key => $f)
		{
			$classname = 'Qss_Bin_Calculate_'.$object->ObjectCode.'_'.$f->FieldCode;
			if ( class_exists($classname) 
					&& (($f->szRegx == Qss_Lib_Const::FIELD_REG_RECALCULATE || ($f->getValue(false) === '' || $f->getValue(false) == null) && !$object->i_IOID)))
			{
				$autocal = new $classname($object);
				$val = $autocal->__doExecute();
				if(is_int($val) 
					&&($f->RefFieldCode 
					|| $f->intFieldType == 14 
					|| $f->intFieldType == 16))
				{
					$f->setRefIOID($val);
					$f->setValue('');
				}
				else 
				{
					$f->setValue($val);
				}
			}
			elseif ( $f->szRegx == Qss_Lib_Const::FIELD_REG_AUTO )
			{
				$object->setRefValue($f);
			}
			elseif ( $f->intFieldType != 14 
					&& $f->intFieldType != 15 
					&& $f->intFieldType != 16 
					&& $f->szDefaultVal == 'AUTO' 
					&& !$f->intIOID && !$f->getValue())//
			{
				if($f->RefFieldCode)
				{
					$onload->{$f->FieldCode}();//$filter
					$f->setDefaultLookupValue($user);
				}
				elseif($f->intFieldType == 10)
				{
					$f->setValue(date('d-m-Y'));
				}
				else 
				{
					$f->setValue($f->getMaxValue() + 1);
				}
			}
			if ( $f->szDefaultVal == 'UNIQUE' || $f->szDefaultVal == 'GUNIQUE' )
			{
				$arrDuplicate[] = $f;
			}
		}
		if(count($arrDuplicate))
		{
			$dup = $object->b_fCheckDuplicate($arrDuplicate,$object->i_IOID);
			if($dup)
			{
				if(!$ignore)
				{
					$rights = $this->rights;
					if(!($rights & 4))
					{
						//khoong cos quyen
						$ret = false;
						$errorMessage = '';
						foreach ($arrDuplicate as $item)
						{
							if($errorMessage != '')
							{
								$errorMessage .= ' + ';
							}
							$errorMessage .= $item;
						}
						$message .= ("{$errorMessage} ". $this->_translate(165)) . ' - ' . ($this->_translate(146));
					}
					else
					{
						//ok
						$object->fetchData($dup);
						foreach ($object->loadFields() as $f)
						{
							if ( isset($this->arrConfig[$f->ObjectCode][$f->FieldCode]) && !$f->bReadOnly)
							{
								$cell = $ws->getCell($this->arrConfig[$object->ObjectCode][$f->FieldCode] . $row->getRowIndex());
								$val = $cell->getValue();
								$val = ($val === null)?'':$val;
								if ( $f->intInputType == 3 || $f->intInputType == 4 || $f->intInputType == 11 || $f->intInputType == 12)
								{
									if(is_int($val) && ($f->RefFieldCode 
									|| $f->intFieldType == 14 
									|| $f->intFieldType == 16))
									{
										$f->setRefIOID($val);
									}
									else 
									{
										$f->setValue($val);
									}
								}
								elseif($f->intFieldType == 7 )
								{
									$f->setValue(($val?true:false));
								}
								else
								{
									$f->setValue($val);
								}
							}
						}
					}
				}
				else
				{
					$ret = false;
					$errorMessage = '';
					foreach ($arrDuplicate as $item)
					{
						if($errorMessage != '')
						{
							$errorMessage .= ' + ';
						}
						$errorMessage .= $item->getValue();
					}
					$message .= ("{$errorMessage} " . $this->_translate(166));
				}
			}
		}
		elseif(!($this->rights & 1))
		{
			$ret = false;
			$message .= ($this->_translate(146));
		}
		return $ret;
	}
}
?>