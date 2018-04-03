<?php
class Qss_Bin_Filter_OYeuCauMuaSam extends Qss_Lib_Filter
{
    public function getWhere() {
        $workcenter = (int) @$this->_params['workcenter']; // đơn vị yêu cầu
        $retval     = ''; // query lọc theo điều kiện

        // Lọc yêu cầu mua sắm theo đơn vị yêu cầu. Yêu cầu sẽ bao gồm đơn vị được chọn và đơn vị con của đơn vị được chọn.
        if($workcenter) {
            $mTable = Qss_Model_Db::Table('ODonViSanXuat');
            $mTable->where(sprintf('IOID = %1$d', $workcenter));
            $donViYeuCau = $mTable->fetchOne();

            if($donViYeuCau) {
                $retval = sprintf(' 
                    AND (v.Ref_DonViYeuCau IN (SELECT IOID FROM ODonViSanXuat WHERE lft <= %1$d and rgt >= %2$d))'
                , $donViYeuCau->lft, $donViYeuCau->rgt);
            }
        }

        return $retval;
    }
}