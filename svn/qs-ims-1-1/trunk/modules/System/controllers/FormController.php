<?php
/**
 *
 * The system form management
 *
 * @author HuyBD
 *
 */
class System_FormController extends Qss_Lib_Controller
{
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		$this->i_SecurityLevel = 1;
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/system-list.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/jquery.bgiframe.min.js');
		$this->headScript($this->params->requests->getBasePath() . '/js/ajaxfileupload.js');
	}
	public function indexAction()
	{
		$o_FormModel = new Qss_Model_System_Form();
		$this->html->FormList = $o_FormModel->a_fGetAll();
		// $this->html->type = $i_Type;
        $this->html->type = '';
	}
	/**
	 * Edit action
	 *
	 * @return void
	 */
	public function editAction()
	{
		$this->headScript($this->params->requests->getBasePath() . '/js/tag.js');
		$fid = $this->params->requests->getParam('fid',0);
        $form = new Qss_Model_System_Form('');
        //$form = new Qss_Model_System_Form($type);
		$document					= new Qss_Model_Admin_Document();
		$activity					= new Qss_Model_Event();
		$form->b_fInit($fid);
		// $iform = new Qss_Model_Form($type);
        $iform = new Qss_Model_Form();
		$iform->init($fid, 1, 0);
		$step  						= new Qss_Model_System_Step(0);
		$step->FormCode 				= $form->FormCode;
		$lang 						= new Qss_Model_System_Language();
		$this->html->languages 		= $lang->getAll(1);
		$this->html->objid 			= $iform->o_fGetMainObject()->ObjectCode;
		$this->html->form 			= $form;
		$this->html->data 			= $form->getByCode($fid);
		$this->html->type 			= '';
		$this->html->documenttypes  = $document->getAll();
		$this->html->activitytype   = $activity->getAllType();
		$this->html->documents      = $step->getStepDocuments();
		$this->html->activities     = $step->getStepActivities();
	}
	public function duplicateAction()
	{
		$this->headScript($this->params->requests->getBasePath() . '/js/tag.js');
		$fid = $this->params->requests->getParam('fid',0);
		$form = new Qss_Model_System_Form();
		$form->b_fInit($fid);
		$iform = new Qss_Model_Form();
		$iform->init($fid, 1, 0);
		$lang = new Qss_Model_System_Language();
		$this->html->languages = $lang->getAll(1);
		$this->html->objid = $iform->o_fGetMainObject()->ObjectCode;
		$this->html->form = $form;
		$this->html->data = $form->getByCode($fid);
		//$this->html->type = $type;
	}
	public function duplicateSaveAction()
	{
		$fid = $this->params->requests->getParam('fid',0);
		$a_Params = $this->params->requests->getParams();
		// $form = new Qss_Model_System_Form($type);
        $form = new Qss_Model_System_Form('');
		$form->b_fInit($fid);
		if ( $this->params->requests->isAjax())
		{
			$service = $this->services->System->Form->Duplicate($form,$a_Params);
			echo $service->getMessage();
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	/**
	 * Save action that call via ajax
	 *
	 * @return void
	 */
	public function saveAction()
	{
		/* Use same name in databse */
		$a_Params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax())
		{
			$service = $this->services->System->Form->Save($a_Params);
			echo $service->getMessage();
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function reloadAction()
	{
		$sz_Search            = $this->params->requests->getParam('search',0);
        $o_FormModel          = new Qss_Model_System_Form();
        $this->html->FormList = $o_FormModel->a_fGetAll($sz_Search);
        $this->html->type     = '';
	}
	public function objectAction()
	{
		$fid = $this->params->requests->getParam('fid',0);
		// $form = new Qss_Model_System_Form($type);
        $form = new Qss_Model_System_Form('');
		$form->b_fInit($fid);
		$this->html->fobjects = $form->a_fGetFormObjects();
		$this->html->type = $form->i_Type;
		$this->html->fid = $fid;
	}
	public function objectEditAction()
	{
		$fid = $this->params->requests->getParam('fid',0);
		$objid = $this->params->requests->getParam('objid',0);
		// $form = new Qss_Model_System_Form($type);
        $form = new Qss_Model_System_Form('');
		$object = new Qss_Model_System_Object();
		$form->b_fInit($fid);
		$this->html->fobject = $form->a_fGetFormObject($objid);
		$this->html->objects = $object->a_fGetAll();
		$this->html->formobjects = $object->a_fGetByForm($fid);
		$this->html->refforms = $form->getRefForms($fid);
		//$this->html->type = $type;
        $this->html->type = '';
	}
	public function objectSaveAction()
	{
		/* Use same name in databse */
		$a_Params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax())
		{
			$service = $this->services->System->Form->Object->Save($a_Params);
			echo $service->getMessage();
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function objectDeleteAction()
	{
		/* Use same name in databse */
		$fid = $this->params->requests->getParam('fid');
		$objid = $this->params->requests->getParam('objid');
		if ( $this->params->requests->isAjax())
		{
			$form = new Qss_Model_System_Form();
			$form->b_fDeteleFormObject($fid,$objid);
			echo Qss_Json::encode(array('error'=>0));
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function transferAction()
	{
		$form = new Qss_Model_System_Form();
		$this->html->transfers = $form->getTransfers();
	}
	public function transferEditAction()
	{
		$this->headScript($this->params->requests->getBasePath() . '/js/tag.js');
		$trid = $this->params->requests->getParam('trid',0);
		$form = new Qss_Model_System_Form();
		$this->html->transfer = $form->getTransfer($trid);
		$this->html->modules = $form->a_fGetByType(Qss_Lib_Const::FORM_TYPE_MODULE);
		$this->html->lists = $form->a_fGetAll();
	}
	public function transferSaveAction()
	{
		/* Use same name in databse */
		$a_Params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax())
		{
			$service = $this->services->System->Form->Transfer->Save($a_Params);
			echo $service->getMessage();
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function transferDeleteAction()
	{
		/* Use same name in databse */
		$ftid = $this->params->requests->getParam('ftid',0);
		if ( $this->params->requests->isAjax())
		{
			$form = new Qss_Model_System_Form();
			$form->deleteTransfer($ftid);
			echo Qss_Json::encode(array('error'=>0));
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function inheritAction()
	{
		$form = new Qss_Model_System_Form();
		$this->html->inherits = $form->getInherits();
	}
	public function inheritEditAction()
	{
		$id = $this->params->requests->getParam('fiid',0);
		$form = new Qss_Model_System_Form();
		$this->html->inherit = $form->getToInheritForm();
		$data = $form->getInherit($id);
		if(!$data)
		{
			$data = new stdClass();
			$data->FIIAD = 0;
			$data->FromFID = 0;
			$data->ToFID = 0;
			$data->ObjectCode = '';
			$data->Step = 0;
			$data->Type = 0;
		}
		$this->html->data = $data;
	}
	public function inheritEditLoadFormAction()
	{
		/* Use same name in databse */
		$toid = $this->params->requests->getParam('toid');
		$selected = $this->params->requests->getParam('selected',0);
		if ( $this->params->requests->isAjax())
		{
			$form = new Qss_Model_System_Form();
			$from = $form->getFromInheritForm($toid);
			$ret = '<option value=""> ----- select one ------';
			foreach ($from as $object)
			{
				if($object->fromfid = $selected)
				$ret .= '<option selected value="'.$object->fromfid.'">'.$object->fromname;
				else
				$ret .= '<option value="'.$object->fromfid.'">'.$object->fromname;
			}
		}
		echo $ret;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function inheritEditLoadObjectAction()
	{
		/* Use same name in databse */
		$toid = $this->params->requests->getParam('toid');
		$fromid = $this->params->requests->getParam('fromid');
		$selected = $this->params->requests->getParam('selected',0);
		if ( $this->params->requests->isAjax())
		{
			$form = new Qss_Model_System_Form();
			$from = $form->getFromInheritObject($fromid, $toid);
			$ret = '<option value=""> ----- select one ------';
			foreach ($from as $object)
			{
				if($object->objid = $selected)
				$ret .= '<option selected value="'.$object->objid.'">'.$object->objectname;
				else
				$ret .= '<option value="'.$object->objid.'">'.$object->objectname;
			}
		}
		echo $ret;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function inheritDeleteAction()
	{
		/* Use same name in databse */
		$fiid = $this->params->requests->getParam('fiid',0);
		if ( $this->params->requests->isAjax())
		{
			$form = new Qss_Model_System_Form();
			$form->deleteInherit($fiid);
			echo Qss_Json::encode(array('error'=>0));
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function inheritSaveAction()
	{
		/* Use same name in databse */
		$a_Params = $this->params->requests->getParams();
		if ( $this->params->requests->isAjax())
		{
			$service = $this->services->System->Form->Inherit->Save($a_Params);
			echo $service->getMessage();
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function exportAction()
	{
		$fid = $this->params->requests->getParam('fid',0);
		$retval = "[qss]\n";
		$io = new Qss_Model_System_IO();
		if(!is_array($fid))
		{
			$fid = array($fid);
		}
		$i = 0;
		foreach ($fid as $id)
		{
			$retval .= $io->export($id,$i);
			$i++;
		}
		header('Content-Type: text/html; charset=utf-8');
		header("Content-type: application/force-download");
		header("Content-Transfer-Encoding: Binary");
		//header("Content-length: " . sizeof($retval));
		$name = implode('-',$fid);
		header("Content-disposition: attachment; filename=module_{$name}.ini");
		echo $retval;
		die;
		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function doImportAction()
	{
		$file = $this->params->requests->getParam('module_import',0);
		$fn = QSS_DATA_DIR . '/tmp/' . $file;
		if(file_exists($fn))
		{
			$config = Qss_Config::loadIniFile($fn);
			unlink($fn);
			if(isset($config['qss']))
			{
				$io = new Qss_Model_System_IO();
				$io->import($config);
				echo Qss_Json::encode(array('error'=>false,'message'=>'Bạn đã import thành công'));
			}
			else
			{
				echo Qss_Json::encode(array('error'=>true,'message'=>'File không đúng định dạng'));
			}
		}
		else
		{
			echo Qss_Json::encode(array('error'=>true,'message'=>'File không tồn tại'));
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function importAction()
	{

	}
}
?>