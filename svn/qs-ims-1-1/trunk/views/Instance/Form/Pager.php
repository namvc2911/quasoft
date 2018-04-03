<?php
class Qss_View_Instance_Form_Pager extends Qss_View_Abstract
{

	public function __doExecute ($sql, Qss_Model_Form $form, 
						$currentpage = 1, 
						$limit = Qss_Lib_Const_Display::FORM_LIMIT_DEFAULT,
						$groupby = 0,
						$status = '',
						$uid = '')
	{
		$object = $form->o_fGetMainObject();
		$this->html->mainobjects = $form->o_fGetMainObjects();
		$pagecount = $object->i_fGetPageCount($sql[0], $currentpage, $limit);
		$this->html->pagecount = $pagecount;
		$this->html->recordcount = $object->i_RecordCount;
		if($currentpage > $pagecount)
		{
			$this->html->currentpage = 1;
			Qss_Params::getInstance()->cookies->set('form_' . $form->FormCode . '_currentpage', 1);
		}
		if($form->i_Type == Qss_Lib_Const::FORM_TYPE_MODULE)
		{
			$stepmodel = new Qss_Model_System_Step($form->i_WorkFlowID);
			$this->html->steps = $stepmodel->getAll();
			//$this->html->users = $form->getUsers();
		}
	}
}

?>