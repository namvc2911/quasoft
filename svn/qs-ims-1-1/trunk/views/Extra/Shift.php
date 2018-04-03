<?php
class Qss_View_Extra_Shift extends Qss_View_Abstract
{
    public function __doExecute ($form , $selected)
    {
        $modelCommon = new Qss_Model_Extra_Extra();
        $this->html->htmlShifts   = $modelCommon->getTableFetchAll('OCa', array(), array('*'), array('MaCa'));
    }
}
?>