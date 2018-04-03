<?php
class Qss_View_Object_MNotifyGroup extends Qss_View_Abstract
{
    public function __doExecute($sql, $form, $object, $currentpage, $limit, $fieldorder, $i_GroupBy)
    {
        $ifid   = $form->i_IFID;

        $mTable = Qss_Model_Db::Table('qsgroups');
        $mTable->join(sprintf('LEFT JOIN MNotifyGroup ON qsgroups.GroupID = MNotifyGroup.ID AND MNotifyGroup.IFID_C005 = %1$d', $ifid));
        $mTable->select(' qsgroups.* ');
        $mTable->select(' IFNULL(MNotifyGroup.IOID, 0) AS SelectedID ');

        $this->html->data = $mTable->fetchAll();
        $this->html->ifid = $ifid;
    }
}
?>