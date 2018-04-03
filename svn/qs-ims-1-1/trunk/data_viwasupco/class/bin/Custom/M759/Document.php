<?php
class Qss_Bin_Custom_M759_Document extends Qss_Lib_Bin
{
    public $_prefix   = '#';
    public $_lenth    = 3;
    public $_docField = 'SoPhieu';
    public $_object;

    /*public function __construct(Qss_Model_Object $object)
    {
        parent::__construct(null);
        //$this->_object = $object;
        //$this->configDocNo();
    }*/
    public function getPrefix($data)
    {
		$date  = date('m');
        $year  = date('Y');

        if(isset($data->NgayYeuCau) && $data->NgayYeuCau) {
            $date  = date('m', strtotime($data->NgayYeuCau));
            $year  = date('Y', strtotime($data->NgayYeuCau));
        }

        return "bd.{$date}.{$year}.";
    }
    /**
     * Lấy số chứng từ theo loại bảo trì cho viwasupco
     * Với loại là sự cố sẽ có dạng sc.092018.001
     * Còn lại sẽ có dạng bd.001
     * Mặc định là bd., nếu muốn thay đổi thì dùng set prefix để thay đổi.
     */
    /*public function configDocNo() {
        $this->_prefix    = 'bd.{m}.{Y}.'; // Mặc định là tạo ra loại bảo dưỡng
        $refMaintainType  = 0;
        $fields           = (array)$this->_object->a_Fields;

        foreach ($fields as $key=>$value)
        {
            if ( $value->FieldCode == 'LoaiBaoTri' )
                $refMaintainType = $value->getRefIOID();
        }

        // echo '<pre>'; print_r($this->_object->a_Fields); die;

        if($refMaintainType == 0) {
            $sqlMaintainTypeFromOrder = sprintf('SELECT Ref_LoaiBaoTri FROM OPhieuBaoTri WHERE IFID_M759 = %1$d', $this->_object->i_IFID);
            $objMaintainTypeFromOrder = $this->_db->fetchOne($sqlMaintainTypeFromOrder);

            if($objMaintainTypeFromOrder) {
                $refMaintainType = $objMaintainTypeFromOrder->Ref_LoaiBaoTri;
            }
        }

        if($refMaintainType) {
            $sqlMaintainType = sprintf('SELECT LoaiBaoTri FROM OPhanLoaiBaoTri WHERE IOID = %1$d', $refMaintainType);
            $objMaintainType = $this->_db->fetchOne($sqlMaintainType);

            if($objMaintainType) {
                switch ($objMaintainType->LoaiBaoTri) {
                    case Qss_Lib_Extra_Const::MAINT_TYPE_BREAKDOWN:
                        $this->_prefix = 'sc.{m}.{Y}.';
                        break;

                    default:
                        $this->_prefix = 'bd.{m}.{Y}.';
                        break;
                }
            }
        }
    }*/
}