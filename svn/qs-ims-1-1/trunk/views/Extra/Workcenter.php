<?php
class Qss_View_Extra_Workcenter extends Qss_View_Abstract
{
    /**
     * @param $form
     * @param $selected
     * Lấy danh mục đơn vị thực hiện M125 sắp xếp theo hình cây (order by left)
     */
    public function __doExecute ($form,$selected)
    {
        $mEmp = Qss_Model_Db::Table('ODonViSanXuat');
        $mEmp->orderby('lft');
        $this->html->workcenters = $mEmp->fetchAll();
    }
}
?>