<?php
class Qss_View_Instance_Form_Pager extends Qss_View_Abstract
{

	public function __doExecute ($sql, Qss_Model_Form $form, 
						$currentpage = 1, 
						$limit = 200,
						$groupby = 0,
						$status = '',
						$uid = '')
	{
		if($limit == 20)
		{
			$this->html->limit = 200;
			$limit = 200;
		}
		$object = $form->o_fGetMainObject();
		$this->html->mainobjects = $form->o_fGetMainObjects();
		$pagecount = $object->i_fGetPageCount($sql[0], $currentpage, $limit);
		$this->html->pagecount = $pagecount;
		if($currentpage > $pagecount)
		{
			$this->html->currentpage = 1;
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