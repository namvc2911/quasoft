<?php
/**
 *
 * @author HuyBD
 *
 */
class System_DatabaseController extends Qss_Lib_Controller
{
	protected $_model;

	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		$this->i_SecurityLevel = 1;
		parent::init();
		$this->_model = new Qss_Model_System_Database();
		$this->headScript($this->params->requests->getBasePath() . '/js/database-list.js');
	}

	/**
	 *
	 * @return void
	 */
	public function indexAction ()
	{
		$this->html->config = Qss_Session::get('copydatabase');//copy from
	}
	
	public function connectAction ()
	{
		$database = $this->params->requests->getParam('database');
		$username = $this->params->requests->getParam('username');
		$password = $this->params->requests->getParam('password');
		$adapter = $this->params->requests->getParam('adapter');
		$host = $this->params->requests->getParam('host');
		
		$config = array('username'=>$username
					,'host'=>$host
					,'password'=>$password
					,'database'=>$database
					,'persistent'=>false
					,'adapter'=>$adapter);
		$classname = 'Qss_Db_' . $adapter;
		$retval = array('error'=>false);
		if ( class_exists($classname) )
		{
			try 
			{
				$adapter = new $classname((array) $config);
				Qss_Session::set('copydatabase', $config);
				$retval['error'] = false;
			}
			catch (Exceoption $e)
			{
				$retval['error'] = true;
				$retval['message'] = $e->getMessage();
				Qss_Session::set('copydatabase', null);
			}
		}
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}

	public function menuAction()
    {
        $database = $this->params->requests->getParam('database');
        $replace = $this->_model->replaceMenus($database);

        if ($replace)
        {
            $retval['error']   = false;
            $retval['message'] = 'Cập nhật menu thành công!';
        }
        else
        {
            $retval['error']   = true;
            $retval['message'] = 'Cập nhật menu thất bại!';
        }

        echo Qss_Json::encode($retval);
        $this->setHtmlRender(false);
        $this->setLayoutRender(false);
    }

	public function formAction()
	{
		$this->html->viewall =  $this->params->requests->getParam('viewall');
		$forms = $this->_model->getForms();
		$copyForms = $this->_model->getCopyForms();
		//merge to array
		$arrForms = array();
		foreach($forms as $item)
		{
			if(!isset($arrForms[$item->FormCode]))
			{
				$arrForms[$item->FormCode] = array();
			}
			if(!isset($arrForms[$item->FormCode]['current']))
			{
				$arrForms[$item->FormCode]['current'] = array();
			}
			if($item->ObjectCode)
			{
				$arrForms[$item->FormCode]['current'][$item->ObjectCode] = $item;
			}
			$arrForms[$item->FormCode]['current_infor'] = $item;
		}
		foreach($copyForms as $item)
		{
			if(!isset($arrForms[$item->FormCode]))
			{
				$arrForms[$item->FormCode] = array();
			}
			if(!isset($arrForms[$item->FormCode]['copy']))
			{
				$arrForms[$item->FormCode]['copy'] = array();
			}
			if($item->ObjectCode)
			{
				$arrForms[$item->FormCode]['copy'][$item->ObjectCode] = $item;
			}
			$arrForms[$item->FormCode]['copy_infor'] = $item;
		}
		$this->html->forms = $arrForms;
		$this->setLayoutRender(false);
	}
	public function formEditAction()
	{
		$fid = $this->params->requests->getParam('fid'); 
		$form = $this->_model->getFormByCode($fid);
		$copyForm = $this->_model->getCopyFormByCode($fid);
		$objects = $this->_model->getObjects();
		$arrForm = array();
		$arrCopyForm = array();
		$arrObjects = array();
		foreach($form as $item)
		{
			$arrForm[$item->ObjectCode] = $item;
		}
		foreach($copyForm as $item)
		{
			$arrCopyForm[$item->ObjectCode] = $item;
		}
		foreach($objects as $item)
		{
			$arrObjects[] = $item->ObjectCode;
		}
		$this->html->form = $arrForm;
		$this->html->copyform = $arrCopyForm;
		$this->html->objects = $arrObjects;
		$this->setLayoutRender(false);
	}
	public function formCopyAction()
	{
		$params = $this->params->requests->getParams();
		$service = $this->services->System->Database->CopyForm($params);
		echo $service->getMessage(); 
		$this->setLayoutRender(false);
		$this->setHtmlRender(false);
	}
	
	public function objectAction()
	{
		$objects = $this->_model->getFieldObjects();
		$copyObjects = $this->_model->getCopyFieldObjects();
		//merge to array
		$arrObjects = array();
		foreach($objects as $item)
		{
			if(!isset($arrObjects[$item->ObjectCode]))
			{
				$arrObjects[$item->ObjectCode] = array();
			}
			if(!isset($arrObjects[$item->ObjectCode]['current']))
			{
				$arrObjects[$item->ObjectCode]['current'] = array();
			}
			if($item->FieldCode)
			{
				$arrObjects[$item->ObjectCode]['current'][$item->FieldCode] = $item;
			}
			$arrObjects[$item->ObjectCode]['current_infor'] = $item;
		}
		foreach($copyObjects as $item)
		{
			if(!isset($arrObjects[$item->ObjectCode]))
			{
				$arrObjects[$item->ObjectCode] = array();
			}
			if(!isset($arrObjects[$item->ObjectCode]['copy']))
			{
				$arrObjects[$item->ObjectCode]['copy'] = array();
			}
			if($item->FieldCode)
			{
				$arrObjects[$item->ObjectCode]['copy'][$item->FieldCode] = $item;
			}
			$arrObjects[$item->ObjectCode]['copy_infor'] = $item;
		}
		$this->html->objects = $arrObjects;
		$this->setLayoutRender(false);
	}
	public function objectEditAction()
	{
		$objid = $this->params->requests->getParam('objid'); 
		$object = $this->_model->getObjectByCode($objid);
		$copyObject = $this->_model->getCopyObjectByCode($objid);
		$fields = $this->_model->getFieldsByObjectCode($objid);
		$arrObject = array();
		$arrCopyObject = array();
		$arrFields = array();
		foreach($object as $item)
		{
			$arrObject[$item->FieldCode] = $item;
		}
		foreach($copyObject as $item)
		{
			$arrCopyObject[$item->FieldCode] = $item;
		}
		foreach($fields as $item)
		{
			$arrFields[] = $item->FieldCode;
		}
		$this->html->object = $arrObject;
		$this->html->copyobject = $arrCopyObject;
		$this->html->fields = $arrFields;
		$this->setLayoutRender(false);
	}
	public function fieldCopyAction()
	{
		$params = $this->params->requests->getParams();
		$service = $this->services->System->Database->CopyField($params);
		echo $service->getMessage(); 
		$this->setLayoutRender(false);
		$this->setHtmlRender(false);
	}
	public function objectDetachAction()
	{
		/* Use same name in databse */
		$fid = $this->params->requests->getParam('formcode');
		$objid = $this->params->requests->getParam('objectcode');
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
	public function fieldDeleteAction()
	{
		/* Use same name in databse */
		$objid = $this->params->requests->getParam('objectcode');
		$fieldid = $this->params->requests->getParam('fieldcode');
		if ( $this->params->requests->isAjax())
		{
			$form = new Qss_Model_System_Field();
			$form->delete($objid,$fieldid);
			echo Qss_Json::encode(array('error'=>0));
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>