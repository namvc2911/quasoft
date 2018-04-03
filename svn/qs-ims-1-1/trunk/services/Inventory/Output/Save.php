<?php
class Qss_Service_Inventory_Output_Save extends Qss_Lib_Service
{

    public function __doExecute ($params)
    {
        $insert = array();

        $ifid = (isset($params['ifid']) && $params['ifid'])?$params['ifid']:0;

        $insert['OXuatKho'][0]['Kho']            = (int)$params['OXuatKho_Kho'];
        $insert['OXuatKho'][0]['LoaiXuatKho']    = (int)$params['OXuatKho_LoaiXuatKho'];
        $insert['OXuatKho'][0]['NgayChungTu']    = Qss_Lib_Date::displaytomysql($params['OXuatKho_NgayChungTu']);
        $insert['OXuatKho'][0]['NgayChuyenHang'] = Qss_Lib_Date::displaytomysql($params['OXuatKho_NgayChuyenHang']);

        $service = $this->services->Form->Manual('M506', $ifid, $insert, false);

        if($service->isError())
        {
            $this->setError();
            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
        }

        if(!$this->isError() && (!isset($params['back']) || !$params['back']))
        {
            $newIFID = $service->getData();
            Qss_Service_Abstract::$_redirect = '/mobile/m506/myoutputs/edit?fid=M506&ifid='.$newIFID.'&deptid='.$params['user']->user_dept_id;
        }
    }
}
?>