<?php
class Qss_Bin_Calculate_ODanhSachXuatKho_DonGia extends Qss_Lib_Calculate
{
	public function __doExecute()
	{
//		return $this->OSanPham->GiaMua(1); // GiaBan
	  // Chinh lai don gia
        //$donGia = $this->_object->getFieldByCode('DonGia')->getValue();
        //$soLuong = $this->_object->getFieldByCode('SoLuong')->getValue();
        $refIOID = $this->_object->getFieldByCode('MaSP')->getRefIOID();
        $common = new Qss_Model_Extra_Extra();
        $sql = sprintf('SELECT sanpham.GiaMua
                        FROM OSanPham AS sanpham
						where sanpham.IOID = %1$d', $refIOID);
        $item = $this->_db->fetchOne($sql);
        $sql = sprintf('
            SELECT sum(kho.`SoLuongKhoiTao` * donvitinh.`HeSoQuyDoi`) AS SoLuongKhoiTao,
            GiaTriKhoiTao
            FROM OKho AS kho
            INNER JOIN ODonViTinhSP AS donvitinh ON kho.`Ref_DonViTinh` = donvitinh.`IOID`
            WHERE kho.`Ref_MaSP` = %1$d
            AND ifnull(kho.`Ref_ThuocTinh`,0) = %2$d
            GROUP BY kho.`Ref_MaSP`, kho.`Ref_ThuocTinh`'
            , $refIOID
            , $this->_object->getFieldByCode('ThuocTinh')->getRefIOID());
        $inv = $this->_db->fetchOne($sql);
       // echo '<pre>'; print_r($inv);

        $sql = sprintf('
            SELECT
                SUM(ifnull(giaodich.SoLuong, 0)) AS TongSoLuongNhap,
                SUM(ifnull(giaodich.SoLuong, 0) * ifnull(giaodich.DonGia, 0)) AS TongGiaNhap
            FROM ODanhSachNhapKho AS giaodich
            INNER JOIN ONhapKho as nk on nk.IFID_M402 = giaodich.IFID_M402
            INNER JOIN qsiforms on qsiforms.IFID = nk.IFID_M402
            WHERE qsiforms.Status = 2
            AND giaodich.Ref_MaSanPham = %1$d
            AND ifnull(giaodich.`Ref_ThuocTinh`,0) = %2$d
            GROUP BY giaodich.Ref_MaSanPham, giaodich.Ref_ThuocTinh'
            , $refIOID
            , $this->_object->getFieldByCode('ThuocTinh')->getRefIOID());
        $trans = $this->_db->fetchOne($sql);
        //echo '<pre>'; print_r($trans); die;

        /*$sql = sprintf('
            SELECT * FROM ODanhSachXuatKho WHERE IOID = %1$d'
            , $this->_object->i_IOID );
        $line = $this->_db->fetchOne($sql);*/

        $khoitao = $inv?$inv->SoLuongKhoiTao:0;
        if($khoitao && $inv->GiaTriKhoiTao)
        {
        	$giamua  = round($inv->GiaTriKhoiTao/$khoitao,0);
        }
        else
        {
        	$giamua  = $item?$item->GiaMua:0;	
        }
        //return $giamua;
        $tongsoluongnhap = $trans?$trans->TongSoLuongNhap:0;
        $tonggianhap = $trans?$trans->TongGiaNhap:0;
        $khoitao     = $khoitao?$khoitao:0;
        $tongsoluong = $tongsoluongnhap + $khoitao;
        $giabinhquan = $tongsoluong?(($khoitao * $giamua) + $tonggianhap)/$tongsoluong:0;
        $giabinhquan = $giabinhquan?$giabinhquan:$giamua;
        //$itemInLine  = $line?$line->Ref_MaSP:0;
        $currentItem = $refIOID;

//        echo '<pre>'; print_r($tongsoluong);
//        echo '<pre>'; print_r($tonggianhap);
//
//        echo '<pre>'; print_r($khoitao);
//        echo '<pre>'; print_r($giamua);die;

        //if(!$this->_object->i_IOID || $itemInLine != $currentItem)
        {
            $giabinhquan = $giabinhquan/1000;
            $giabinhquan = round($giabinhquan,0);
            //$thanhtien   = $giabinhquan * $soLuong;
            return $giabinhquan?$giabinhquan:'';
            //$this->_object->getFieldByCode('ThanhTien')->setValue($thanhtien);
        }
	}
}
?>