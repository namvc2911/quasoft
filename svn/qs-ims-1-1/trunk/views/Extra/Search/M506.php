<?php
class Qss_View_Extra_Search_M506 extends Qss_View_Abstract
{
	public function __doExecute ($form)
	{
		$filter = Qss_Cookie::get('form_' . $form->FormCode . '_filter', 'a:0:{}');
		$filter = unserialize($filter);
		$this->html->selectedMaKho = (int) @$filter['makho'];
        $this->html->selectedUser  = (int) @$filter['uid'];
	}
}
?>