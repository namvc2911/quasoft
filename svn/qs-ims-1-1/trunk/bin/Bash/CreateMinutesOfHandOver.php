<?php
class Qss_Bin_Bash_CreateMinutesOfHandOver extends Qss_Lib_Bin
{
    public function __doExecute()
    {
        $ioid   = $this->_form->o_fGetObjectByCode('OTraLaiTaiSanCaNhan')->i_IOID;
        $deptid = $this->_form->o_fGetObjectByCode('OTraLaiTaiSanCaNhan')->intDepartmentID;


        // echo '<pre>'; print_r($this->_form->o_fGetObjectByCode('OTraLaiTaiSanCaNhan')); die;
        // vào bash em ->_form rồi o_fGetObjectByCode là ra object có IOID sna ồi đấy

        $dataSql = $this->_db->fetchOne(
            sprintf('
                SELECT
                    OTraLaiTaiSanCaNhan.*
                    , OTaiSanCaNhan.MaTaiSan
                    , OTaiSanCaNhan.NguyenGia
                    , IFNULL(OTaiSanCaNhan.SoLuong, 0) AS SoLuongDaGiao
                    , IFNULL(OTraLaiTaiSanCaNhan.SoLuong, 0) AS SoLuongDaTra
                FROM OTraLaiTaiSanCaNhan
                INNER JOIN OTaiSanCaNhan ON OTraLaiTaiSanCaNhan.IFID_M419 = OTaiSanCaNhan.IFID_M419
                WHERE OTraLaiTaiSanCaNhan.IOID = %1$d'
            , $ioid)
        );

        if($dataSql)
        {
            if($dataSql->Ref_MaNhanVien)
            {
                $nguyenGia = $dataSql->SoLuongDaGiao?$dataSql->NguyenGia/$dataSql->SoLuongDaGiao:0;
                $nguyenGia = $nguyenGia * $dataSql->SoLuongDaTra;

                $insert = array();
                $insert['OTaiSanCaNhan'][0]['MaNhanVien']    = (string)$dataSql->MaNhanVien;
                $insert['OTaiSanCaNhan'][0]['MaTaiSan']      = (string)$dataSql->MaTaiSan;
                $insert['OTaiSanCaNhan'][0]['NgayGiao']      = date('Y-m-d');
                $insert['OTaiSanCaNhan'][0]['SoLuong']       = $dataSql->SoLuongDaTra;
                $insert['OTaiSanCaNhan'][0]['NguyenGia']     = $nguyenGia/1000;
                $insert['OTaiSanCaNhan'][0]['GiaTriConLai']  = $dataSql->GiaTriConLai/1000;
                $insert['OTaiSanCaNhan'][0]['KhauHaoConLai'] = $dataSql->KhauHaoConLai;

                $service = $this->services->Form->Manual('M419', 0, $insert, false);
                if($service->isError())
                {
                    $this->setError();
                    $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
                }

                if(!$this->isError())
                {
                    //$this->setMessage('Nhân đôi bản ghi thành công!');
                    $service->setRedirect('/user/form/edit?ifid='.$service->getData().'&deptid='.$deptid);
                }
            }
            else
            {
                $this->setError();
                $this->setMessage('Để tạo bàn giao nhân viên bàn giao yêu cầu bắt buộc');
            }
        }
        else
        {

        }
    }
}