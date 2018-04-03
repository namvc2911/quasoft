<?php
class Qss_Service_Maintenance_GeneralPlan_Duplicate extends Qss_Service_Abstract
{
    /**/
    public function __doExecute($params)
    {
        $ifid          = isset($params['ifid']) ? $params['ifid'] : 0;
        $mOrder        = new Qss_Model_Extra_Extra();
        $temp          = time();
        $i             = 0;
        $main          = $mOrder->getTableFetchOne('OKeHoachTongThe', array('IFID_M838'=>$ifid));

        $insertMain    = array();
        $insertSub     = array();

        if($ifid && $main)
        {
            $maKeHoach = @(string)$main->Ma.'_'.$temp;
            $insertMain['OKeHoachTongThe'][0]['Ma']           = $maKeHoach;
            $insertMain['OKeHoachTongThe'][0]['Ten']          = @(string)$main->Ten;
            $insertMain['OKeHoachTongThe'][0]['NguoiTao']     = @(int)$main->NguoiTao;

            $insertMain['OKeHoachTongThe'][0]['NgayTao']      = date('Y-m-d');
            $insertMain['OKeHoachTongThe'][0]['NguoiPheDuyet']= @(int)$main->NguoiPheDuyet;

            $insertMain['OKeHoachTongThe'][0]['NgayPheDuyet'] = date('Y-m-d');

            $insertMain['OKeHoachTongThe'][0]['NgayBatDau']   = @$main->NgayBatDau?date('Y-m-d', strtotime('+1 years',  strtotime($main->NgayBatDau))):'';//x
            $insertMain['OKeHoachTongThe'][0]['NgayKetThuc']  = @$main->NgayKetThuc?date('Y-m-d', strtotime('+1 years', strtotime($main->NgayKetThuc))):'';//x

            $insertMain['OKeHoachTongThe'][0]['LoaiLich']     = @(int)$main->LoaiLich;

            $importGeneral = new Qss_Model_Import_Form('M838',false, false);
            $importGeneral->setData($insertMain);
            $importGeneral->generateSQL();
            $importGeneral->cleanTemp();

            $ok = $mOrder->getTableFetchOne('OKeHoachTongThe', array('Ma'=>$maKeHoach));

            if($ok) // Neu ke hoach tong the duoc tao thi tao tiep chi tiet ke hoach
            {
                $sub = $mOrder->getTableFetchAll('OKeHoachBaoTri', array('IFNULL(Ref_KeHoachTongThe, 0)'=>(int)$main->IOID));
                $importDetail  = new Qss_Model_Import_Form('M837',false, false);

                foreach ($sub as $item)
                {
                    $insertSub['OKeHoachBaoTri'][0]['KeHoachTongThe'] = (int)$ok->IOID;
                    $insertSub['OKeHoachBaoTri'][0]['MaThietBi']      = @(int)$item->Ref_MaThietBi;
                    $insertSub['OKeHoachBaoTri'][0]['TenThietBi']     = @(int)$item->Ref_TenThietBi;
                    $insertSub['OKeHoachBaoTri'][0]['BoPhan']         = @(int)$item->Ref_BoPhan;
                    $insertSub['OKeHoachBaoTri'][0]['NgayBatDau']     = $item->NgayBatDau?date('Y-m-d', strtotime('+1 years', strtotime($item->NgayBatDau))):'';
                    $insertSub['OKeHoachBaoTri'][0]['NgayKetThuc']    = $item->NgayKetThuc?date('Y-m-d', strtotime('+1 years', strtotime($item->NgayKetThuc))):'';
                    $insertSub['OKeHoachBaoTri'][0]['MucDoUuTien']    = $item->MucDoUuTien;
                    $insertSub['OKeHoachBaoTri'][0]['LoaiBaoTri']     = $item->LoaiBaoTri;
                    $insertSub['OKeHoachBaoTri'][0]['ChuKy']          = $item->ChuKy;
                    $insertSub['OKeHoachBaoTri'][0]['MaDVBT']         = $item->MaDVBT;
                    $insertSub['OKeHoachBaoTri'][0]['TenDVBT']        = $item->TenDVBT;
                    $insertSub['OKeHoachBaoTri'][0]['NguoiThucHien']  = $item->NguoiThucHien;
                    $insertSub['OKeHoachBaoTri'][0]['MoTa']           = $item->MoTa;
                    $insertSub["OKeHoachBaoTri"][0]["SoLanTrenNam"]        = @$item->SoLanTrenNam;
                    $insertSub["OKeHoachBaoTri"][0]["DuKienThucHien"]      = @$item->DuKienThucHien;
                    $insertSub["OKeHoachBaoTri"][0]["DoiTac"]              = @(int)$item->Ref_DoiTac;
                    $insertSub["OKeHoachBaoTri"][0]["NgayBaoDuongThucTe"]  = @$item->NgayBaoDuongThucTe;


                    $importDetail->setData($insertSub);
                    $i++;
                }

                if(count($insertSub))
                {
                    $importDetail->generateSQL();
                }
            }
        }
    }
}