<?php
class Qss_View_Extra_MaintainType extends Qss_View_Abstract
{
    public function __doExecute ($form,$selected)
    {
        $mEmp        = Qss_Model_Db::Table('OPhanLoaiBaoTri');
        $mEmp->orderby('LoaiBaoTri');

        $this->html->htmlData    = $mEmp->fetchAll();
    }
}
?>