<?php
Class Qss_Service_Extra_News_Plantasks_Move extends  Qss_Service_Abstract
{
    public function __doExecute($params)
    {
        $insert = array();
        $line   = isset($params['ReqDate'])?count($params['ReqDate']):0;
        $ifid   = 0;

        if($line)
        {
            for($i = 0; $i < $line; $i++)
            {
                if($params['Status'][$i] == 1) // Soan thao cap nhat lai ngay
                {
                    $insert['OKeHoachSuaChuaTheoNgay'][$i]['Ngay']             = date('d-m-Y');
                    $insert['OKeHoachSuaChuaTheoNgay'][$i]['NoiDung']          = @$params['Content'][$i];
                    $insert['OKeHoachSuaChuaTheoNgay'][$i]['ThoiGianThucHien'] = @$params['ExecuteTime'][$i];
                    $insert['OKeHoachSuaChuaTheoNgay'][$i]['ThoiGianDungMay']  = @$params['BreakdownTime'][$i];
                    $insert['OKeHoachSuaChuaTheoNgay'][$i]['ioid']             = $params['IOID'][$i];
                    $ifid = $params['IFID'][$i];
                }
                elseif($params['Status'][$i] == 3) // Huy tao ban ghi moi
                {
                    $insert['OKeHoachSuaChuaTheoNgay'][$i]['Ngay']             = date('d-m-Y');
                    $insert['OKeHoachSuaChuaTheoNgay'][$i]['NoiDung']          = @$params['Content'][$i];
                    $insert['OKeHoachSuaChuaTheoNgay'][$i]['ThoiGianThucHien'] = @$params['ExecuteTime'][$i];
                    $insert['OKeHoachSuaChuaTheoNgay'][$i]['ThoiGianDungMay']  = @$params['BreakdownTime'][$i];
                }

                if($insert)
                {
                    $manual =  $this->services->Form->Manual('M148', $ifid, $insert, false);
                    if($manual->isError())
                    {
                        $this->setError();
                        $this->setMessage($manual->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                    }
                }
            }
        }
        else
        {
            $this->setError();
            $this->setMessage('Phải có ít nhất một dòng kế hoạch được chọn!');
        }


    }
}