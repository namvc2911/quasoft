<?php
class Qss_View_Extra_Search_M705 extends Qss_View_Abstract
{
	public function __doExecute ($form)
	{
		$filter = Qss_Cookie::get('form_' . $form->FormCode . '_filter', 'a:0:{}');
		$filter = unserialize($filter);
		$this->html->selected          = (int) @$filter['makhuvuc'];
        $this->html->selectedTinhTrang = (int) @$filter['trangthaithietbi'];
        $this->html->selectedDuAn      = (int) @$filter['maduan'];
	}
}
?>