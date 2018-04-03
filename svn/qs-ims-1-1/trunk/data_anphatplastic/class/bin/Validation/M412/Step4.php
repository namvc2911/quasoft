<?php
class Qss_Bin_Validation_M412_Step4 extends Qss_Lib_Warehouse_WValidation
{
    public function next()
    {
        parent::init();

        $this->_db->execute(sprintf('
		    UPDATE ODSYeuCauMuaSam
		    SET MoTaMatHang = CONCAT(TenSP, " (", DonViTinh, ")")
		    WHERE IFID_M412 = %1$d
		', $this->_form->i_IFID));
    }
}
