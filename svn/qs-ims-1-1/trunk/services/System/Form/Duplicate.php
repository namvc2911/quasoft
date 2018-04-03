<?php

class Qss_Service_System_Form_Duplicate extends Qss_Service_Abstract
{

	public function __doExecute (Qss_Model_System_Form $form,$params)
	{
		$newform = new Qss_Model_System_Form($params['type']);
		$params['fid'] = 0;
		if(!$newform->b_fSave($params))
		{
			$this->setError();
			$this->setMessage($this->_translate(170));
			return;
		}
		/*Duplicate config*/
		$fobjects = $form->a_fGetFormObjects();
		foreach($fobjects as $fobject)
		{
			$data = array(
					'object'=>$fobjectinitData ,/* */
					'parent'=>$fobject->ParentID ,/* */
					'main'=>$fobject->Main ,/* */
					'public'=>$fobject->Public ,/* */
					'editing'=>$fobject->Editing ,/* */
					'track'=>$fobject->Track /* */
			);
			$newform->b_fSaveFormObject($data);
		}
		/*Duplicate rights*/
		if(@$params['DupRights'])
		{
			$group = new Qss_Model_Admin_Group();
			$group->duplicate($form->FormCode, $newform->FormCode);
		}
		/*Duplicate menu*/
		if(@$params['DupMenu'])
		{
			$form->duplicateMenu($form->FormCode, $newform->FormCode);
		}
		/*Duplicate workflow*/
		if(@$params['DupWorkflow'])
		{
			$workflow = new Qss_Model_System_Workflow($form->FormCode);
			$workflow->duplicate($form->FormCode, $newform->FormCode);
		}
		/*Duplicate design*/
		if(@$params['DupDesign'])
		{
			$design = new Qss_Model_Admin_Design(0);
			$designs = $design->getFormDesigns($form->FormCode);
			$department = new Qss_Model_Admin_Department();
			$depts = $department->getAll();
			$arr_search = array("content_id='{$form->FormCode}'",/* */
							"content_id={$form->FormCode}&",/* */
							"cms_{$form->FormCode}_",/* */
							"pager_{$form->FormCode}_",/* */
							"content_{$form->FormCode}_");
			$arr_replace = array("content_id='{$newform->FormCode}'",/* */
							"content_id={$newform->FormCode}&",/* */
							"cms_{$newform->FormCode}_",/* */
							"pager_{$newform->FormCode}_",/* */
							"content_{$newform->FormCode}_");
			foreach($designs as $data)
			{
				$old_design = $data->FDID;
				$data->FID = $newform->FormCode;
				$new_design = $design->duplicate($data);
				$folder = QSS_DATA_DIR . Qss_Lib_Const::FORM_DESIGN_FORM ;
				foreach ($depts as $dept)
				{
					$old_fn = $folder . $dept->DepartmentID . '/' . $old_design . '.html';
					$new_fn = $folder . $dept->DepartmentID . '/' . $new_design . '.html';
					if(file_exists($old_fn))
					{
						$content = file_get_contents($old_fn);
						$content = str_replace($arr_search, $arr_replace, $content);
						$handle = fopen($new_fn, 'wb');
						fwrite($handle, $content);
						fclose($handle);
					}
				}
			}
		}
		/*Duplicate inherit*/
		if(@$params['DupInherit'])
		{
			$form->duplicateInherit($form->FormCode, $newform->FormCode);
		}
	}
}
?>