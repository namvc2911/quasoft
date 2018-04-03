<?php
class Qss_Service_Maintenance_GeneralPlan_Update extends Qss_Service_Abstract
{
    /**/
    public function __doExecute($params)
    {
        $equipIOIDs = isset($params['Equip']) ? $params['Equip'] : array();
        $mOrder     = new Qss_Model_Maintenance_Workorder();
        $i          = 0;
        $insert     = array();

        $insert['OKeHoachTongThe'][0]['Ma']           = @(string)$params['OKeHoachTongThe_Ma'];
        $insert['OKeHoachTongThe'][0]['Ten']          = @(string)$params['OKeHoachTongThe_Ten'];
        $insert['OKeHoachTongThe'][0]['NguoiTao']     = @(int)$params['OKeHoachTongThe_NguoiTao'];
        $insert['OKeHoachTongThe'][0]['NgayTao']      = @$params['OKeHoachTongThe_NgayTao'];
        $insert['OKeHoachTongThe'][0]['NguoiPheDuyet']= @(int)$params['OKeHoachTongThe_NguoiPheDuyet'];
        $insert['OKeHoachTongThe'][0]['NgayPheDuyet'] = @$params['OKeHoachTongThe_NgayPheDuyet'];
        $insert['OKeHoachTongThe'][0]['NgayBatDau']   = @$params['OKeHoachTongThe_NgayBatDau'];
        $insert['OKeHoachTongThe'][0]['NgayKetThuc']  = @$params['OKeHoachTongThe_NgayKetThuc'];
        $insert['OKeHoachTongThe'][0]['LoaiLich']     = @(int)$params['OKeHoachTongThe_LoaiLich'];
        $insert['OKeHoachTongThe'][0]['ioid']         = $params['ioid'];
        $insert['OKeHoachTongThe'][0]['ifid']         = $params['ifid'];

        $service = $this->services->Form->Manual('M838',  $params['ifid'],  $insert, false);
        if ($service->isError())
        {
            $this->setError();
            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
        }
    }
}