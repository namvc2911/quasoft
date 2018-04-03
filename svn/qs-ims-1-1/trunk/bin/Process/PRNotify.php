<?php
class Qss_Bin_Process_PRNotify extends Qss_Lib_Bin
{
	public function __doExecute()
	{
        $insert = array();

        // Lấy ra các mặt hàng có tồn kho nhỏ hơn tối thiểu
        $sql = sprintf('
            SELECT
                TonKho.*
            FROM
            (
                SELECT
                    MatHang.IOID AS ItemIOID
                    , TonKho.Ref_Kho
                    , ifnull(TonKho.Ref_ThuocTinh, 0) AS Ref_ThuocTinh
                    , ifnull(TonKho.Ref_DonViTinh, 0) AS Ref_DonViTinh
                    , MatHang.MaSanPham
                    , MatHang.TenSanPham
                    , MatHang.DonViTinh AS DonViTinhCoSo
                    , ifnull(MatHang.SoLuongMua, 0) AS SoLuongMua
                    , sum(ifnull(TonKho.SoLuongHC, 0) * ifnull(DonViTinh.HeSoQuyDoi,1)) AS SoLuongHienCoTheoCoSo
                    , sum(ifnull(HanMuc.SoLuongToiDa, 0) * ifnull(DonViTinh.HeSoQuyDoi,1)) AS SoLuongToiDaTheoCoSo
                    , sum(ifnull(HanMuc.SoLuongToiThieu, 0) * ifnull(DonViTinh.HeSoQuyDoi,1)) AS SoLuongToiThieuTheoCoSo
                FROM OKho AS TonKho
                INNER JOIN ODanhSachKho AS DSKho ON TonKho.Ref_Kho = DSKho.IOID
                INNER JOIN OSanPham AS MatHang ON TonKho.Ref_MaSP = MatHang.IOID
                INNER JOIN ODonViTinhSP AS DonViTinh ON MatHang.IFID_M113 = DonViTinh.IFID_M113 AND TonKho.Ref_DonViTinh = DonViTinh.IOID
                INNER JOIN OHanMucLuuTru AS HanMuc ON MatHang.IFID_M113 = HanMuc.IFID_M113 AND HanMuc.Ref_MaKho = TonKho.Ref_Kho
                GROUP BY MatHang.IOID, ifnull(TonKho.Ref_ThuocTinh, 0)
                ORDER BY MatHang.MaSanPham
            ) AS TonKho
            WHERE TonKho.SoLuongHienCoTheoCoSo < TonKho.SoLuongToiThieuTheoCoSo

        ');

        $dataSql = $this->_db->fetchAll($sql);

        if(count($dataSql))
        {
            $insert['OYeuCauMuaSam'][0]['Ngay']    = date('Y-m-d');
            $insert['OYeuCauMuaSam'][0]['NoiDung'] = 'Hệ thống tự sinh từ đặt lịch tạo yêu cầu mua hàng tự động';
            $i = 0;

            foreach($dataSql as $item)
            {
                $soLuongMua = $item->SoLuongMua?$item->SoLuongMua:0;
                $soLuongMua = $soLuongMua?$soLuongMua:($item->SoLuongToiThieuTheoCoSo - $item->SoLuongHienCoTheoCoSo)?($item->SoLuongToiThieuTheoCoSo - $item->SoLuongHienCoTheoCoSo):0;

                if($soLuongMua > 0)
                {
                    $insert['ODSYeuCauMuaSam'][$i]['MaSP']      = (int)$item->ItemIOID;
                    $insert['ODSYeuCauMuaSam'][$i]['DonViTinh'] = (int)$item->Ref_DonViTinh;
                    $insert['ODSYeuCauMuaSam'][$i]['SoLuong']   = $soLuongMua;
                    $i++;
                }
            }

            $service = $this->services->Form->Manual('M412', 0 , $insert, false);

            if($service->isError())
            {
                $this->setError();
                $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
            }
        }

//        $sql = sprintf('select * from OKy where IOID = %1$d',$this->_params->Ref_Ky);
//        $periodSQL = $this->_db->fetchOne($sql);
//        if($periodSQL)
//        {
//            $period = $periodSQL->MaKy;
//            $next   = $this->_params->KyKeTiep;
//            switch ($period)
//            {
//                case 'D':
//                    $date = date_create();
//                    if($next)
//                    {
//                        $date = Qss_Lib_Date::add_date($date, 1);
//                    }
//
//                    $sql   = sprintf('select OKeHoachMuaHang.*,OSanPham.ThoiGianCho from OKeHoachMuaHang
//							inner join qsiforms on qsiforms.IFID = OKeHoachMuaHang.IFID_M901
//							inner join OSanPham on OKeHoachMuaHang.Ref_MaSP = OSanPham.IOID
//							where (SoLuong!=0 or SoLuong is not null)
//							and Ngay = %1$s and qsiforms.Status <> -1
//						',$this->_db->quote($date->format('Y-m-d')));
//                    $dataSQL = $this->_db->fetchAll($sql);
//                    $params  = array();
//                    foreach($dataSQL as $item)
//                    {
//                        //manual to notification
//
//                        $params['OYeuCauMuaHang'][0]['MaSP']       = $item->MaSP;
//                        $params['OYeuCauMuaHang'][0]['DonViTinh']  = $item->DonViTinh;
//                        $params['OYeuCauMuaHang'][0]['ThuocTinh']  = $item->ThuocTinh;
//                        $params['OYeuCauMuaHang'][0]['SoLuong']    = $item->SoLuong;
//                        $leadtime = (int)$item->ThoiGianCho;
//                        $orderdate = Qss_Lib_Date::add_date($date, 0-$leadtime);
//                        $params['OYeuCauMuaHang'][0]['NgayCanCo']  = $date->format('d-m-Y');
//                        $params['OYeuCauMuaHang'][0]['NgayYeuCau'] =   $orderdate->format('d-m-Y');
//                        $params['OYeuCauMuaHang'][0]['NoiDung']    = "MRP";
//                        $params['OYeuCauMuaHang'][0]['ioidlink']   = $item->IOID;
//
//
//
//                        $service = $this->services->Form->Manual('M405',0,$params,true,false);
//
//                        if($service->isError())
//                        {
//                            $this->setError();
//                            $this->setMessage($service->getMessage(Qss_Service_Abstract::TYPE_TEXT));
//                        }
//                    }
//                    break;
//            }
//        }
	}
}
?>