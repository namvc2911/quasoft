<?php
class Qss_Service_Inventory_Output_SaveItem extends Qss_Lib_Service
{

    public function __doExecute ($params)
    {
        $insert = array();

        $mImport  = new Qss_Model_Import_Form('M506', false, false);
        $mCommon  = new Qss_Model_Extra_Extra();
        $ifid     = (isset($params['ifid']) && $params['ifid'])?$params['ifid']:0;
        $order    = $mCommon->getTableFetchOne('OXuatKho', array('IFID_M506'=>$ifid));

        $insert['OXuatKho'][0]['SoChungTu']         = (string)$order->SoChungTu;
        $insert['ODanhSachXuatKho'][0]['MaSP']      = (int)$params['ODanhSachXuatKho_MaSP'];
        $insert['ODanhSachXuatKho'][0]['TenSP']     = (int)$params['ODanhSachXuatKho_MaSP'];
        $insert['ODanhSachXuatKho'][0]['DonViTinh'] = (int)$params['ODanhSachXuatKho_DonViTinh'];
        $insert['ODanhSachXuatKho'][0]['SoLuong']   = $params['ODanhSachXuatKho_SoLuong'];


        $mImport->setData($insert);
        $mImport->generateSQL();

        $errorForm   = (int)$mImport->countFormError();
        $errorObject = (int)$mImport->countObjectError();
        $error       = $errorForm + $errorObject;

        if($error > 0)
        {
            $this->setError();
            $this->setMessage('Có '.$error.' dòng lỗi!');
        }


        if(!$this->isError() && (!isset($params['back']) || !$params['back'])) {
            $importedRow = $mImport->getImportRows();
            $ioid = 0;

            foreach ($importedRow as $objKey => $itemArr) {
                if ($objKey == 'ODanhSachXuatKho' && count($itemArr)) {
                    foreach ($itemArr as $item) {
                        $ioid = $item->ExistsIOID;
                        break;
                    }
                }
            }

            // echo '<pre>'; print_r($ioid); die;

            Qss_Service_Abstract::$_redirect = '/mobile/m506/myoutputs/addmaterials/index?fid=M759&ifid=' . $ifid . '&deptid=' . $params['user']->user_dept_id . '&ioid=' . $ioid;


        }
    }
}
?>