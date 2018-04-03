<?php
require_once QSS_ROOT_DIR . '/lib/PHPExcel.php';
require_once QSS_ROOT_DIR . '/lib/PHPExcel/IOFactory.php';
class Qss_Service_Socket_Import extends Qss_Lib_Service
{
	protected $user;
	
	protected $file;
	
	protected $socket; 

	function __doExecute($user, $message)
	{
		global $domain;
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($this->socket === false) 
		{
		    return;
		} 
		socket_connect($this->socket, $domain, QSS_SOCKET_PORT);
		$this->user = $user;
		$msg = json_decode($message);
		if(is_object($msg))
		{
			$this->file = new File();
			$this->file->fileid = $msg->fileid;
			$this->file->ifid = $msg->ifid;
			$this->file->fid = $msg->fid;
			$this->file->objid = $msg->objid;
			$this->file->ignore = !$msg->ignore;
			$this->file->deptid = $user->user_dept_id;
			$this->file->status = 1;
			if(!is_dir($this->file->szFN =  QSS_DATA_DIR . "/tmp/"))
			{
				mkdir($this->file->szFN =  QSS_DATA_DIR . "/tmp/",0777);
			}
			$this->file->szFN =  QSS_DATA_DIR . "/tmp/" . $this->file->fileid;
			if(file_exists($this->file->szFN))
			{
				$this->doImport();
				unlink($this->file->szFN);
			}
		}
		socket_close($this->socket);
	}

	
	function presend($log = false)
	{
		$ret = new Response();
		$ret->command = 'importstatus';
		$ret->PHPSESSID = '*QUASOFT*';
		$ret->title = $this->file->form->sz_Name;
		$ret->fileid = substr($this->file->fileid,0,strpos($this->file->fileid,'.'));
		$ret->total = $this->file->total;
		$ret->status = $this->file->status;
		$ret->error = $this->file->intError;
		$ret->suscess = $this->file->intImported;
		$ret->log = '';
		if($log)
		{
			$ret->log = implode('<br>',array_reverse($this->file->arrLogs));
		}
		$ret->onwer = 1;
		$msg = Qss_Json::encode($ret);
		$this->send($this->socket,$msg);
	}

	function send($server,$data)
	{
		$result = socket_write($server, $data, strlen($data));
	}

	public function doImport()
	{
		//check db connection for long time
		Qss_Db::factory((array) Qss_Register::get('configs')->database);
		$db = Qss_Db::getAdapter('main');
		$this->file->arrConfig = array();
		$form = new Qss_Model_Form();
		if ( $this->file->ifid && $this->file->deptid && $form->initData($this->file->ifid, $this->file->deptid) )
		{
			$rights = $form->i_fGetRights($this->user->user_group_list);
			if ( ($rights & 4) )
			{
				$object = $form->o_fGetObjectById($this->file->objid);
				$object->initData($this->file->ifid, $this->file->deptid, 0);
				$object->loadFields($this->user);
			}
			else
			{
				$this->file->arrLogs[] = $this->_translate(146);
				$this->setError();
			}
		}
		elseif ( $form->init($this->file->fid, $this->file->deptid, $this->user->user_id) )
		{
			$rights = $form->i_fGetRights($this->user->user_group_list);
			if ( ($rights & 1) )
			{
				$object = $form->o_fGetMainObject();
				$object->loadFields();
			}
			else
			{
				$this->file->arrLogs[] = $this->_translate(146);
				$this->setError();
			}
		}
		else
		{
			$this->file->arrLogs[] = $this->_translate(145);
			$this->setError();
		}
		$this->file->form = $form;
		$this->file->rights = $rights;
		
		if ( !$this->isError() && file_exists($this->file->szFN) )
		{
			$classname = 'Qss_Bin_Import_'.$form->FormCode;
			if ( class_exists($classname))
			{
				$trigger = new $classname($form);
				Qss_Register::set('userinfo',$this->user);
				$trigger->__doExecute($this->file);
			}
			else
			{
				$objReader = PHPExcel_IOFactory::createReader('Excel5');
				try
				{
					$objPHPExcel = $objReader->load($this->file->szFN);
					$ws = $objPHPExcel->getSheet(0);
					$this->file->total = $ws->getHighestRow()-1;
					if($this->fetchHeader($ws, $object,$this->user,$this->file))//Normal
					{
						$i = 1;
						foreach ($ws->getRowIterator() as $row)
						{
							if($this->file->status == 0)// || ($i % 5) == 0
							{
								$this->presend();
								break;
							}
							if($this->file->crow > $row->getRowIndex())
							{						
								continue;
							}
							$this->file->crow = $row->getRowIndex()+1;
							if ( $row->getRowIndex() != 1 )
							{
								$rowid = (int) ($ws->getCell("A" . $row->getRowIndex())->getValue());
								$iobject = clone $object;
								//$iobject instanceof Qss_Model_Object;
								$fields = Qss_Lib_Util::cloneData($iobject->a_Fields);
								if($this->importRow($ws, $row, $iobject, $this->file->ignore,$this->user,$this->file))
								{
									// check if import form
									if($iobject->b_Main)
									{
										for($oi = 1;$oi < $objPHPExcel->getSheetCount();$oi++)
										{
											$subws = $objPHPExcel->getSheet($oi);
											$subsheetname = $subws->getTitle();
											$subobject = $this->file->form->o_fGetObjectByName($subsheetname);
											if($subobject)
											{
												if ( $this->fetchHeader($subws, $subobject,$this->user,$this->file) )
												{
													//init form
													if($this->file->form->initData($iobject->i_IFID, $iobject->intDepartmentID))
													{
														$subobject = $this->file->form->o_fGetObjectByName($subsheetname);
														foreach ($subws->getRowIterator() as $subrow)
														{
															if ( $subrow->getRowIndex() != 1 )
															{
																$subrowid = (int) ($subws->getCell("A" . $subrow->getRowIndex())->getValue());
																if($rowid == $subrowid)
																{
																	$isubobject = clone $subobject;
																	$subfields = Qss_Lib_Util::cloneData($isubobject->a_Fields);
																	$this->importRow($subws, $subrow, $isubobject, $this->file->ignore,$this->user,$this->file);
																	unset($isubobject);
																	$subobject->a_Fields = $subfields;
																}
															}
														}
													}
												}
												else
												{
													$this->file->arrLogs[] = "Sheet " .$subsheetname." không đúng định dạng" ;
												}
											}
										}
									}
									$this->file->intImported++;
								}
								else 
								{
									$this->file->intError++;
								}
								unset($iobject);
								$object->a_Fields = $fields;//@TODO reset causes reload fields next time
								//$object->v_fResetFields();
							}
							$this->presend();
							$i++;
						}
						$this->file->arrLogs[] = "{$this->file->intImported} dòng được import, {$this->file->intError} dòng bị lỗi";
					}
					else
					{
						$this->setError();
						$this->file->arrLogs[] = $this->_translate(164);
					}
					$objPHPExcel->disconnectWorksheets();
					unset($objPHPExcel);
				}
				catch (Exception $e)
				{
					$this->setError();
					$this->file->arrLogs[] = $e->getMessage(Qss_Service_Abstract::TYPE_HTML);
				}
			}
			//return $this->getImportedGrid();
		}
		if($this->file->total == $this->file->crow - 2)
		{
			$this->file->status=2;
		}
		else 
		{
			$this->file->status=1;
		}
		$this->presend(true);
		if($db->isOpen())
		{
			$db->close();
		}
		unset($db);
		$db = null;
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
			if ( isset($this->file->arrConfig[$f->ObjectCode][$f->FieldCode])  && !$f->bReadOnly)
			{
				$onload->{$f->FieldCode}();
				$cell = $ws->getCell($this->file->arrConfig[$object->ObjectCode][$f->FieldCode] . $row->getRowIndex());
				$val = $cell->getValue();
				$val = ($val === null)?'':$val;
				if ( $f->intInputType == 3 || $f->intInputType == 4 || $f->intInputType == 11 || $f->intInputType == 12)
				{
					if(is_int($val) && ($f->RefFieldCode 
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
		if ( $this->b_fValidate($ws, $row, $object,$ignore,$errorMessage)  )
		{
			if($this->b_fCheckRequire($object))
			{
				if($object->i_IFID)
				{
					$this->file->form->i_IFID = $object->i_IFID; 
				}
				$classname = 'Qss_Bin_Trigger_'.$object->ObjectCode;
				$ioid = $object->i_IOID;
				if(class_exists($classname))
				{
					$trigger = new $classname($this->file->form);
					$trigger->init();
					if($ioid)
					{
						$trigger->onUpdate($object);
					}
					else
					{
						$trigger->onInsert($object);
					}
					$errorMessage .= $trigger->getMessage(Qss_Service_Abstract::TYPE_HTML);
				}
				if((!isset($trigger) || !$trigger->isError()) && $object->b_fSave())
				{
					if($object->i_IFID)
					{
						$this->file->form->i_IFID = $object->i_IFID; 
					}
					if(class_exists($classname))
					{
						$trigger = new $classname($this->file->form);
						$trigger->init();
						if($ioid)
						{
							$trigger->onUpdated($object);
						}
						else
						{
							$trigger->onInserted($object);
						}
						$errorMessage .= $trigger->getMessage(Qss_Service_Abstract::TYPE_HTML);
					}
				}
				else
				{
					$errorMessage .= 'Dữ liệu không thay đổi so với bản cũ';
					//$this->file->intError ++;
					//$retval = false;
				}
			}
			else
			{
				//$this->file->intError ++;
				$retval = false;
			}
		}
		else
		{
			//$this->file->intError ++;
			$retval = false;
		}
		if($errorMessage)
		{
			$this->file->arrLogs[] = 'Line '.($row->getRowIndex() - 1) . ':'.$errorMessage;
		}
		if($this->getMessage(Qss_Service_Abstract::TYPE_HTML))
		{
			$this->file->arrLogs[] = $object->sz_Name . ' Line '.($row->getRowIndex() - 1) . ':'.$this->getMessage(Qss_Service_Abstract::TYPE_HTML);
		}
		$this->_message = array();
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
		$this->file->arrConfig[$object->ObjectCode] = array();
		$row = $this->getRow($ws, 1);
		$cellIterator = $row->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
		foreach ($cellIterator as $cell)
		{
			if ( !is_null($cell) )
			{
				foreach ($object->loadFields() as $f)
				{
					if ( $f->FieldCode == $cell->getValue() || $f->szFieldName == $cell->getValue())
					{
						$this->file->arrConfig[$object->ObjectCode][$f->FieldCode] = $cell->getColumn();
					}
				}
			}
		}
		if ( !sizeof($this->file->arrConfig[$object->ObjectCode]) )
		{
			return false;
		}
		foreach ($object->loadFields() as $f)
		{
			if ( $f->bRequired && isset($this->file->arrConfig[$f->ObjectCode][$f->FieldCode]) )
			{
				return true;
			}
		}
		return false;
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
			if(class_exists($classname) 
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
					$f->setValue($val);//,false,$this->user
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
				&& !$f->intRefIOID && 
				!$f->getValue())//
			{
				if($f->RefFieldCode)
				{
					$onload->{$f->FieldCode}();//$filter
					$f->setDefaultLookupValue($this->user);
				}
				elseif($f->intFieldType == 10)
				{
					$f->setValue(date('d-m-Y'));
				}
				else 
				{
					// $f->setValue($f->getMaxValue($f->getMaxValue($object->FormCode,$object->i_IFID) + 1,false,$this->user);
                    $f->setValue($f->getMaxValue($object->FormCode,$object->i_IFID) + 1);
				}
			}
			if ( $f->bUnique )
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
					$rights = $this->file->rights;
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
							$errorMessage .= $item->sz_fGetDisplay();
						}
						$message .= ("{$errorMessage} ". $this->_translate(146));
					}
					else
					{
						//ok
						$object->fetchData($dup);
						foreach ($object->loadFields() as $f)
						{
							if ( isset($this->file->arrConfig[$f->ObjectCode][$f->FieldCode]) && !$f->bReadOnly)
							{
								$cell = $ws->getCell($this->file->arrConfig[$object->ObjectCode][$f->FieldCode] . $row->getRowIndex());
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
						$errorMessage .= $item->sz_fGetDisplay();
					}
					$message .= ("{$errorMessage} " . $this->_translate(166));
				}
			}
		}
		elseif(!($this->file->rights & 1))
		{
			$ret = false;
			$message .= ($this->_translate(146));
		}
		return $ret;
	}

} //class WebSocket

class User{
	var $uid;
	var $id;
	var $socket;
	var $handshake;
	var $userinfo;
	//var $files = array();
}
class File{
	var $uid;
	var $fileid;
	var $fid;
	var	$ifid;
	var	$deptid;
	var	$objid;
	var	$ignore;
	var $arrLogs = array();
	var $intImported = 0;
	var $intError = 0;
	var $szFN;
	var $rights;
	var $form;
	var $arrConfig;
	var $status = 1;
	var $total = 0;
	var $crow = 1;
}
class Response{
	var $title;
	var $onwer;
	var $fileid;
	var $total;
	var $error;
	var $suscess;
	var $log;
	var $status;
}
?>