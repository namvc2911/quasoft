<?php
/**
 *
 * @author HuyBD
 *
 */
class User_FieldController extends Qss_Lib_Controller
{

	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/form-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{

	}

	/**
	 *
	 * @return void
	 */
	public function uploadfileAction ()
	{
		$retval = new stdClass();
		$retval->error = 0;
		$retval->message = '';
		$destfile = '';
		if ( sizeof($_FILES) )
		{
			$file = array_shift($_FILES);
			if ( $file['error'] == UPLOAD_ERR_OK )
			{
				$filename = uniqid();
				$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
				//$limitedext = array("application/vnd.ms-excel","application/msexcel","application/vnd.ms-word","application/msword","application/pdf","image/png", "image/gif", "image/jpg", "image/jpeg", "image/pjpeg", "image/bmp");
				$scan = Qss_Lib_Util::scanFile($file["tmp_name"], $virusname);
				if ($scan != 0)
				{
					$retval->error = 1;
					$retval->message = ($scan > 0)?'File bị nhiễmm ' . $virusname:'Server không thể quét virus trên file ngày';
				}
				else
				{
					$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
					$destfile = QSS_DATA_DIR . "/tmp/" . $filename . "." . $ext;
					$ret = @move_uploaded_file($file["tmp_name"], $destfile);
				}
			}
		}
		$retval->image = $filename . "." . $ext;
		/* Endcode result to Json & push to js */
		echo (Qss_Json::encode($retval));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 *
	 * @return void
	 */
	public function uploadpictureAction ()
	{
		$retval = new stdClass();
		$retval->error = 0;
		$retval->message = '';
		$destfile = '';
		if ( sizeof($_FILES) )
		{
			$file = array_shift($_FILES);
			if ( $file['error'] == UPLOAD_ERR_OK )
			{
				$filename = uniqid();
				$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
				$limitedext = array("image/png", "image/gif", "image/jpg", "image/jpeg", "image/pjpeg", "image/bmp","tiff/it","image/tif","image/x-tif","image/tiff", "image/x-tiff","application/pdf");
				if ( in_array($file['type'], $limitedext) )
				{
					$scan = Qss_Lib_Util::scanFile($file["tmp_name"], $virusname);
					if ($scan != 0)
					{
						$retval->error = 1;
						$retval->message = ($scan > 0)?'File bị nhiễm ' . $virusname:'Server chưa hỗ trợ chương trình diệt virus';
					}
					else
					{
						$destfile = QSS_DATA_DIR . "/tmp/" . $filename . "." . $ext;
						$ret = @move_uploaded_file($file["tmp_name"], $destfile);
						$retval->image = $filename . "." . $ext;
					}
				}
				else
				{
					$retval->error = 1;
					$retval->message = 'Chỉ chấp nhập file ảnh';
				}
			}
		}
		/* Endcode result to Json & push to js */
		echo (Qss_Json::encode($retval));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 *
	 * @return void
	 */
	public function downloadfileAction ()
	{
		$file = QSS_DATA_DIR . '/documents/' . $this->params->requests->getParam('file');
		if ( file_exists($file) )
		{
			header("Content-type: application/force-download");
			header("Content-Transfer-Encoding: Binary");
			header("Content-length: " . filesize($file));
			header("Content-disposition: attachment; filename=\"" . basename($file) . "\"");
			readfile("$file");
			die();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deletefileAction ()
	{
		$file = QSS_DATA_DIR . '/documents/' . $this->params->requests->getParam('file');
		if ( file_exists($file) )
		{
			unlink($file);
		}
		echo (Qss_Json::encode(array('error'=>false)));
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 *
	 * @return void
	 */
	public function pictureAction ()
	{
		$filename = $this->params->requests->getParam('file');
		$file = QSS_DATA_DIR . '/documents/' . $this->params->requests->getParam('file');
		if ( !file_exists($file) )
		{
			$file = QSS_DATA_DIR . '/tmp/' . $this->params->requests->getParam('file');
		}
		if ( $filename == '' || !file_exists($file) )
		{
			$file = QSS_PUBLIC_DIR. '/images/no_image.jpg';
		}
        header("Content-type: application/force-download");
        header("Content-Transfer-Encoding: Binary");
        header("Content-length: " . filesize($file));
        header("Content-disposition: attachment; filename=\"" . basename($file) . "\"");
		readfile("$file");
		die();

		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	/**
	 *
	 * @return void
	 */
	public function barcodeAction ()
	{
		$barcodeobject = new Qss_Lib_Barcode_Barcode();
		$code = $this->params->requests->getParam('code');
		$style = $this->params->requests->getParam('style');
		$type = $this->params->requests->getParam('type');
		$width = $this->params->requests->getParam('width');
		$height = $this->params->requests->getParam('height');
		$xres = $this->params->requests->getParam('xres');
		$font = $this->params->requests->getParam('font');
		if ( !isset($style) )
		$style = BCD_DEFAULT_STYLE;
		if ( !isset($width) )
		$width = BCD_DEFAULT_WIDTH;
		if ( !isset($height) )
		$height = BCD_DEFAULT_HEIGHT;
		if ( !isset($xres) )
		$xres = BCD_DEFAULT_XRES;
		if ( !isset($font) )
		$font = BCD_DEFAULT_FONT;
		switch ( $type )
		{
			case "I25":
				$obj = new Qss_Lib_Barcode_I25Object($width, $height, $style, $code);
				break;
			case "C39":
				$obj = new Qss_Lib_Barcode_C39Object($width, $height, $style, $code);
				break;
			case "C128A":
				$obj = new Qss_Lib_Barcode_C128AObject($width, $height, $style, $code);
				break;
			case "C128B":
				$obj = new Qss_Lib_Barcode_C128BObject($width, $height, $style, $code);
				break;
			case "C128C":
				$obj = new Qss_Lib_Barcode_C128CObject($width, $height, $style, $code);
				break;
			default:
				echo "Need bar code type ex. C39";
				$obj = false;
		}

		if ( $obj )
		{
			$obj->SetFont($font);
			$obj->DrawObject($xres);
			$obj->FlushObject();
			$obj->DestroyObject();
			unset($obj); /* clean */
		}
		die();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 *
	 * @return void
	 */
	public function chartAction ()
	{
		$file = QSS_DATA_DIR . '/chart/' . $this->params->requests->getParam('file');
		if ( file_exists($file) )
		{
			header("Content-type: application/force-download");
			header("Content-Transfer-Encoding: Binary");
			header("Content-length: " . filesize($file));
			header("Content-disposition: attachment; filename=\"" . basename($file) . "\"");
			readfile("$file");
			die();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function tmpAction ()
	{
		$file = QSS_DATA_DIR . '/tmp/' . $this->params->requests->getParam('file');
		if ( file_exists($file) )
		{
			header("Content-type: application/force-download");
			header("Content-Transfer-Encoding: Binary");
			header("Content-length: " . filesize($file));
			header("Content-disposition: attachment; filename=\"" . basename($file) . "\"");
			readfile("$file");
			die();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function tagAction()
	{
		$retval = array();
		$params = $this->params->requests->getParams();
		$tag = $this->params->requests->getParam('tag');
		$fieldid = $this->params->requests->getParam('fieldid', 0);
		//$filter = $this->params->requests->getParam('filter', '');
		if ( $fieldid)
		{
			$fid = $this->params->requests->getParam('fid');
			if($fid)
			{
				$service = $this->services->Form->Refresh($params);
				$form = $service->getData();
				$object = $form->o_fGetMainObject();
				$object->loadFields();
			}
			else
			{
				$service = $this->services->Object->Refresh($params);;
				$object = $service->getData();
				$object->loadFields();
			}
			$classname = 'Qss_Bin_Onload_'.$object->ObjectCode;
			if(!class_exists($classname))
			{
				$classname = 'Qss_Lib_Onload';
			}
			//$onload = new $classname(null,$object);//@todo lọc 2 lần liênf
			$field = $object->getFieldByCode($fieldid);
			//$onload->{$field->FieldCode}();//$filter
			//$onload->__doExecute();
			if($field->intFieldType == 14)
			{
				$dataSQL = $field->getLookUp($tag);
				foreach ($dataSQL as $item)
				{
					$retval[] = array('id'=>$item->DepartmentID,'value'=>$item->Name);
				}
			}
			elseif($field->intFieldType == 15)
			{
				$dataSQL = $field->getLookUp($tag);
				foreach ($dataSQL as $item)
				{
					$retval[] = array('id'=>$item->CID,'value'=>$item->Code);
				}
			}
			elseif($field->intFieldType == 16)
			{
				$dataSQL = $field->getLookUp($tag);
				foreach ($dataSQL as $item)
				{
					$retval[] = array('id'=>$item->UID,'value'=>$item->UserName . ' (' . $item->UserID . ')');
				}
			}
			else
			{
				$dataSQL = $field->getLookUp($tag);
				$refRights = Qss_Lib_System::getFormRights($field->RefFormCode, $this->_user->user_group_list);
				$formObject = Qss_Lib_System::getFormObject($field->RefFormCode,$field->RefObjectCode);
				//get json to init param
				$jsonData = array();
				$json = $field->getJsonRegx();
				if($json)
				{
					foreach ($json as $key=>$value)
					{
						$fieldParam = $object->getFieldByCode($value)->getValue();
						if($fieldParam)
						{
							$jsonData[$key] = $fieldParam;
						}
					}
				}
				$jsonData = Qss_Json::encode($jsonData);
				if($refRights & 1 && $formObject->Main)
				{
					$retval[] = array('id'=>''
					,'value'=>'Tạo mới'
					,'extra'=>sprintf('onclick = "createNew(\'%6$s\',\'%5$s\',\'%1$s\',\'%2$s\',%3$d,\'%4$s\',\'%7$s\')" class="italic green"',
					$field->RefFormCode,
					$field->RefObjectCode,
					$field->RefFieldCode,
					$field->intRefIFID,
					$field->FieldCode,
					$field->ObjectCode,
					htmlspecialchars($jsonData)));
				}
				$selectedlft = 0;
				$selectedrgt = 0;
				foreach ($dataSQL as $item)
				{
					$level = (int)@$item->level;
					$level = ($level > 0)?($level -1):0;
					if($field->ObjectCode == $field->RefObjectCode && $field->intIOID == $item->IOID)
					{
						$selectedlft = @$item->lft;
						$selectedrgt = @$item->rgt;
					}
					if($selectedlft && $selectedrgt && $item->lft >= $selectedlft && $item->rgt <= $selectedrgt)
					{
						$retval[] = array('id'=>$item->IOID,
									'value'=>str_repeat('&nbsp;&nbsp;',$level) . $item->Name . (isset($item->DisplayName)?" ({$item->DisplayName})":''),
									'extra'=>'disabled');
						
					}
					else 
					{
						$retval[] = array('id'=>$item->IOID,'value'=>str_repeat('&nbsp;&nbsp;',$level) . $item->Name . (isset($item->DisplayName)?" ({$item->DisplayName})":''));
					}
				}
			}
		}
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function optionAction()
	{
		$params = $this->params->requests->getParams();
		$fid = $this->params->requests->getParam('fid');
		if($fid)
		{
			$service = $this->services->Form->Refresh($params);
			$form = $service->getData();
			$object = $form->o_fGetMainObject();
			$object->loadFields();
		}
		else
		{
			$service = $this->services->Object->Refresh($params);;
			$object = $service->getData();
			$object->loadFields();
		}
		$classname = 'Qss_Bin_Onload_'.$object->ObjectCode;
		if(!class_exists($classname))
		{
			$classname = 'Qss_Lib_Onload';
		}
		//$onload = new $classname(null,$object);
		$retval = array();
		$fieldid = $this->params->requests->getParam('fieldid', 0);
		$selected = $this->params->requests->getParam('selected', '');
		$field = $object->getFieldByCode($fieldid);
		//$onload->{$field->FieldCode}();//$filter
		//$onload->__doExecute();
		$jsondata = $field->getJsonRegx();
		if(is_array($jsondata) && count($jsondata))
		{
			$ret = "<option value='-1'>";
			$selected = ($selected == '')?$field->getValue():$selected;
			foreach ($jsondata as $key=>$value)
			{
				if($selected == $key)
				{
					$ret .= "<option selected value='{$key}'>{$value}";
				}
				else
				{
					$ret .= "<option value='$key'>{$value}";
				}
			}
		}
		elseif($field->intFieldType == 14)
		{
			$dataSQL = $field->a_fGetReference();
			$ret = "<option value='0'>";
			$selected = ($selected == '')?$field->getRefIOID():$selected;
			foreach ($dataSQL as $item)
			{
				if($selected == $item->DepartmentID)
				{
					$ret .= "<option selected value='{$item->DepartmentID}'>{$item->Name}";
				}
				else
				{
					$ret .= "<option value='{$item->DepartmentID}'>{$item->Name}";
				}
			}
		}
		elseif($field->intFieldType == 15)
		{
			$dataSQL = $field->a_fGetReference();
			$ret = "<option value='0'>";
			$selected = ($selected == '')?$field->getValue():$selected;
			foreach ($dataSQL as $item)
			{
				if($selected == $item->CID)
				{
					$ret .= "<option selected value='{$item->CID}'>{$item->Code}";
				}
				else
				{
					$ret .= "<option value='{$item->CID}'>{$item->Code}";
				}
			}
		}
		elseif($field->intFieldType == 16)
		{
			$dataSQL = $field->a_fGetReference();
			$ret = "<option value='0'>";
			$selected = ($selected == '')?$field->getValue():$selected;
			foreach ($dataSQL as $item)
			{
				if($selected == $item->UID)
				{
					$ret .= "<option selected value='{$item->UID}'>{$item->UserName}";
				}
				else
				{
					$ret .= "<option value='{$item->UID}'>{$item->UserName}";
				}
			}
		}
		else
		{
			$dataSQL = $field->a_fGetReference($this->_user);
			$ret = "<option value='0'>";
			$selectedlft = 0;
			$selectedrgt = 0;
			foreach ($dataSQL as $item)
			{
				$disabled = '';
				$level = (int)@$item->level;
				$level = ($level > 0)?($level -1):0;
				if($field->ObjectCode == $field->RefObjectCode && $field->intIOID == $item->IOID)
				{
					$selectedlft = @$item->lft;
					$selectedrgt = @$item->rgt;
				}
				if($selectedlft && $selectedrgt && $item->lft >= $selectedlft && $item->rgt <= $selectedrgt)
				{
					$disabled = 'disabled';
				}
				if($field->intRefIOID == $item->IOID)
				{
					$ret .= "<option selected {$disabled} val='{$item->IOID}' value='{$item->IOID}'>".str_repeat('&nbsp;&nbsp;',$level)."{$item->Name}".(isset($item->DisplayName)?" ({$item->DisplayName})":'');
				}
				else
				{
					$ret .= "<option {$disabled} val='{$item->IOID}' value='{$item->IOID}'>".str_repeat('&nbsp;&nbsp;',$level)."{$item->Name}".(isset($item->DisplayName)?" ({$item->DisplayName})":'');
				}
			}
		}
		echo $ret;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function tagMailAction()
	{
		$retval = array();
		$tag = $this->params->requests->getParam('tag');
		$field = new Qss_Model_Field();
		$modules = $field->searchMail($this->_user,$tag);
		if($tag)
		{
			foreach ($modules as $item)
			{
				$retval[] = array('id'=>$item->IFID.','.$item->IOID.','.$item->Data,'value'=>$item->Data.' ('.$item->Name.')');
			}
		}
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function lookupAction()
	{
		$retval = array();
		$org_objid = $this->params->requests->getParam('org_objid');
		$org_fieldid = $this->params->requests->getParam('org_fieldid');
		$fieldid = $this->params->requests->getParam('fielid');
		$ifid = $this->params->requests->getParam('ifid');
		$ioid = $this->params->requests->getParam('ioid');
		$objid = $this->params->requests->getParam('objid');
		$field = new Qss_Model_Field();
		$field->b_fInit($org_objid,$org_fieldid);
		$data = $field->getRefOption($ifid,$ioid);
		if(count($data))
		{
			$retval = array('id'=>$field->ObjectCode.'_'.$field->FieldCode,"value"=>$data['ioid'],"name"=>$data['name']);
		}
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deletePictureAction()
	{
		$ifid = $this->params->requests->getParam('ifid', 0);
		$deptid = $this->params->requests->getParam('deptid', 1);
		$fieldid = $this->params->requests->getParam('fieldid', 0);
		$id = $this->params->requests->getParam('id', 0);
		$ext = $this->params->requests->getParam('ext', '');
		$service = $this->services->Field->Picture->Delete($ifid, $fieldid, $id, $ext);
		echo $service->getMessage();
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function orderAction()
	{
		$retval = array();
		$params = $this->params->requests->getParams();
		$tag = $this->params->requests->getParam('tag');
		$fieldid = $this->params->requests->getParam('fieldid', 0);
		if ( $fieldid)
		{
			$fid = $this->params->requests->getParam('fid');
			if($fid)
			{
				$service = $this->services->Form->Refresh($params);
				$form = $service->getData();
				$object = $form->o_fGetMainObject();
				$object->loadFields();
			}
			else
			{
				$service = $this->services->Object->Refresh($params);;
				$object = $service->getData();
				$object->loadFields();
			}
			
			$field = $object->getFieldByCode($fieldid);
			$dataSQL = $field->getOrderLookUp($tag);
			foreach ($dataSQL as $item)
			{
				$retval[] = array('id'=>$item->IOID,'value'=>$item->Name . (isset($item->DisplayName)?" ({$item->DisplayName})":''));
			}
		}
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>