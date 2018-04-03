<?php
/**
 *
 * @author HuyBD
 *
 */
class System_FieldController extends Qss_Lib_Controller
{
	/**
	 *
	 * @return unknown_type
	 */
	public function init ()
	{
		$this->i_SecurityLevel = 15;
		parent::init();
		$this->headScript($this->params->requests->getBasePath() . '/js/system-list.js');
	}
	public function indexAction()
	{
		$objid = $this->params->requests->getParam('objid',0);
		$fieldModel = new Qss_Model_System_Field();
		$this->html->fields = $fieldModel->getFieldByObjectCode($objid);
		$this->html->objid = $objid;
	}
	public function editAction()
	{
		$this->headScript($this->params->requests->getBasePath() . '/js/tag.js');
		$objid = $this->params->requests->getParam('objid',0);
		$fieldid = $this->params->requests->getParam('fieldid',0);
		$field = new Qss_Model_System_Field();
		$form = new Qss_Model_System_Form();
		$field->init($objid,$fieldid);
		$lang = new Qss_Model_System_Language();
		$objectmodel = new Qss_Model_System_Object();
		$this->html->languages = $lang->getAll(1);
		$this->html->field = $field;
		$this->html->objid = $objid;
		$this->html->data = $field->getByCode($objid,$fieldid);
		$this->html->forms = $form->a_fGetAll();
		$this->html->objects = $objectmodel->a_fGetAll();
		$this->html->steprights = $field->getStepRights($objid,$fieldid);
	}
	public function loadObjectAction()
	{
		$fid = $this->params->requests->getParam('fid',0);
		$selected = $this->params->requests->getParam('selected',0);
		$object = new Qss_Model_System_Object();
		$objects = $object->a_fGetByForm($fid);
		$option = '<option value="">Chọn đối tượng tham chiếu';
		foreach($objects as $data)
		{
			$select = '';
			if($selected === $data->ObjectCode)
			{
				$select = 'selected';
			}
			$option .= '<option value="'.$data->ObjectCode.'" '. $select.'>'.$data->ObjectName;
		}
		echo $option;
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function loadFieldAction()
	{
		$objid = $this->params->requests->getParam('objid',0);
		$selected = $this->params->requests->getParam('selected',0);
		$field = new Qss_Model_System_Field();
		$fields = $field->getFieldByObjectCode($objid);
		$option = '<option value="0">Chọn thuộc tính tham chiếu';
		foreach($fields as $data)
		{
			$select = '';
			if($selected === $data->FieldCode)
			{
				$select = 'selected';
			}
			$option .= '<option value="'.$data->FieldCode.'" '. $select.'>'.$data->FieldName;
		}
		echo $option;
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
			$service = $this->services->System->Field->Save($a_Params);
			echo $service->getMessage();
		}

		/* We process in ajax, no need to render view and layout */
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function taggingAction()
	{
		$tag = $this->params->requests->getParam('tag');
		$objid = $this->params->requests->getParam('objid',0);
		$fid = $this->params->requests->getParam('fid',0);
		$arr = explode('{', $tag);
		$field = new Qss_Model_System_Field();
		$object = new Qss_Model_System_Object();
		$form = new Qss_Model_System_Form();
		if(count($arr) == 2)
		{
			$retval = array();
			$text = $arr[1];
			$arrTag = explode(':', $text);
			$arrTag = array_reverse($arrTag);
			$currenttext = @array_shift($arrTag);
			$selected = '{'.@implode(':', array_reverse($arrTag));
			$lastobject = @array_shift($arrTag);
			$obj_id = 0;
			if($lastobject == 'system')
			{
				/*Add system params*/
				$param = new Qss_Model_System_Param();
				$params = $param->getAllParams();
				foreach ($params as $item)
				{
					$retval[] = array('id'=>0,'value'=>$selected . ':' .$item->PID.'}');
				}
			}
			else
			{
				if($lastobject == 'this')
				{
					$obj_id = $objid;
				}
				else
				{
					$obj_id = $object->getObjIDByName($lastobject);
				}
				if($obj_id)
				{
					$arrObjects = $object->a_fGetObjectByRef($obj_id);
					$arrFields = $field->getFieldByObjectCode($obj_id);
					foreach ($arrObjects as $item)
					{
						$retval[] = array('id'=>0,'value'=>$selected . ':' .$item->ObjectCode);
					}
					foreach ($arrFields as $item)
					{
						$retval[] = array('id'=>0,'value'=>$selected . ':' .$item->ObjectCode . '_' . $item->FieldCode);
					}
				}
			}
			echo Qss_Json::encode($retval);
		}
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function filterAction ()
	{
		$fid = $this->params->requests->getParam('fid', 0);
		$params = $this->params->requests->getParam('params', '');
		$o_Form = new Qss_Model_Form();
		$o_Form->init($fid, $this->_user->user_dept_id, $this->_user->user_id);
		$stepmodel = new Qss_Model_System_Step($o_Form->i_WorkFlowID);
		$this->html->steps = $stepmodel->getAll();
		$this->html->form = $o_Form;
		$this->html->params = array();
		if($params)
		{
			$arr = unserialize($params);
			$newArr = array();
			foreach($arr as $key=>$val)
			{
				if($key == 'status')
				{
					$newArr[$key] = $val;
				}
				else
				{
					$key = 'filter_' . $key;
					$newArr[$key] = $val;
				}
			}
			$this->html->params = $newArr;
		}
	}
	public function filterSaveAction ()
	{
		//$params = $this->params->requests->getParams();
		//$data = serialize($params);
		$params = $this->a_fGetFilter();
		$params['status'] = $this->params->requests->getParam('status', '');
		$data = serialize($params);
		$retval = array('error'=>0,'message'=>$params?$data:'');
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
	public function deleteAction()
	{
		$objid = $this->params->requests->getParam('objid',0);
		$fieldid = $this->params->requests->getParam('fieldid',0);
		$field = new Qss_Model_System_Field();
		$field->delete($objid, $fieldid);
		$retval = array('error'=>0);
		echo Qss_Json::encode($retval);
		$this->setHtmlRender(false);
		$this->setLayoutRender(false);
	}
}
?>