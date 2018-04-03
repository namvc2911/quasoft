<?php
class Qss_Service_Static_M729_SaveAssign extends Qss_Lib_Service
{
    function __doExecute($params)
    {
        $nghiemThu = (isset($params['nghiemthu']) && $params['nghiemthu'])?$params['nghiemthu']:0;
        $phuTrach  = (isset($params['phutrach']) && $params['phutrach'])?$params['phutrach']:0;
        $ifids     = (isset($params['ifid_assign']) && count($params['ifid_assign']))?$params['ifid_assign']:array();
        $insert    = array();
        $mImport   = new Qss_Model_Import_Form('M759',true, false);

        if(!$nghiemThu && !$phuTrach)
        {
            $this->setError();
            $this->setMessage('Cần chọn ít nhất một trong hai người nghiệm thu hoặc người phụ trách!');
        }

        if(!$this->isError()) {
            if ($phuTrach) {
                $insert['OPhieuBaoTri'][0]['NguoiThucHien'] = (int)$phuTrach;
            }

            if ($nghiemThu) {
                $insert['OPhieuBaoTri'][0]['NguoiNghiemThu'] = (int)$phuTrach;
            }

            foreach ($ifids as $ifid)
            {
                $insert['OPhieuBaoTri'][0]['ifid'] = $ifid;

                $mImport->setData($insert);
            }

            $mImport->generateSQL();
            $error = $mImport->countFormError() + $mImport->countObjectError();

            if($error)
            {
                $this->setError();
                $this->setMessage('Có '.$error.' dòng lỗi!');
            }
        }


    }
}