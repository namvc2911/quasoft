<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_IReport_DungMayTheoNguyenNhan extends Qss_Model_IReport_Abstract implements Qss_Lib_IReport
{
	public $name = 'Dừng máy theo nguyên nhân';
	
	public $view = 'DungMayTheoNguyenNhan';
    
	public $columns = array(
        'SoPhieu'=>'Số phiếu'
        ,'NgayYeuCau'=>'Ngày yêu cầu'
        ,'MaThietBi' => 'Mã thiết bị'
        ,'TenThietBi' => 'Tên thiết bị'
        ,'LoaiThietBi' => 'Loại thiết bị'
        ,'NhomThietBi' => 'Nhóm thiết bị'
        ,'MaKhuVuc' => 'Mã khu vực'
        ,'TenKhuVuc' => 'Tên khu vực'
        ,'MaDonViBaoTri' => 'Mã đơn vị bảo trì'
        ,'TenDonViBaoTri' => 'Tên đơn vị bảo trì'
        ,'MaKhuVucDungMay' => 'Mã khu vực dừng máy'
        ,'TenKhuVucDungMay' => 'Tên khu vực dừng máy'
        ,'NgayBatDauDungMay' => 'Ngày bắt đầu dừng máy'
        ,'GioBatDauDungMay' => 'Giờ bắt đầu dừng máy'
        ,'NgayKetThucDungMay' => 'Ngày kết thúc dừng máy'
        ,'GioKetThucDungMay' => 'Giờ kết thúc dừng máy'
        ,'ThoiGianDungMay' => 'Thời gian dừng máy'
        ,'ThoiGianXuLy'=>'Thời gian xử lý'
        ,'MaNguyenNhanLoi' => 'Mã nguyên nhân lỗi'
        ,'TenNguyenNhanLoi' => 'Tên nguyên nhân lỗi'
    );
	
	public $widths = array(
        'SoPhieu'=> 2
        ,'NgayYeuCau'=> 2
        ,'MaThietBi' => 5
        ,'TenThietBi' => 5
        ,'LoaiThietBi' => 4
        ,'NhomThietBi' => 4
        ,'MaKhuVuc' => 5
        ,'TenKhuVuc' => 5
        ,'MaDonViBaoTri' => 5
        ,'TenDonViBaoTri' => 5
        ,'MaKhuVucDungMay' => 5
        ,'TenKhuVucDungMay' => 5
        ,'NgayBatDauDungMay' => 2
        ,'GioBatDauDungMay' => 2
        ,'NgayKetThucDungMay' => 2
        ,'GioKetThucDungMay' => 2
        ,'ThoiGianDungMay' => 3
        ,'ThoiGianXuLy'=>3
        ,'MaNguyenNhanLoi' => 3
        ,'TenNguyenNhanLoi' => 3
    );
    
    public $fieldtypes = array(
        'SoPhieu'=> 1
        ,'NgayYeuCau'=> 10
        ,'MaThietBi' => 1
        ,'TenThietBi' => 2
        ,'LoaiThietBi' => 2
        ,'NhomThietBi' => 2
        ,'MaKhuVuc' => 1
        ,'TenKhuVuc' => 2
        ,'MaDonViBaoTri' => 1
        ,'TenDonViBaoTri' => 2
        ,'MaKhuVucDungMay' => 1
        ,'TenKhuVucDungMay' => 2
        ,'NgayBatDauDungMay' => 10
        ,'GioBatDauDungMay' => 4
        ,'NgayKetThucDungMay' => 10
        ,'GioKetThucDungMay' => 4
        ,'ThoiGianDungMay' => 6
        ,'ThoiGianXuLy' => 6
        ,'MaNguyenNhanLoi' => 1
        ,'TenNguyenNhanLoi' => 2        
    );    
           

	public function __doExecute()
	{
		$where = '';
		$where .= $this->getLocationFilter('location','khuvuc');
		$where .= $this->getTypeFilter('type','loaithietbi');
        $where .= $this->getNormalFilter('equip', 'IOID', 'thietbi');
		$sql = sprintf('
                SELECT 
                    phieubt.SoPhieu
                    , phieubt.Ngay as NgayYeuCau
                    , thietbi.MaThietBi
                    , thietbi.TenThietBi
                    , thietbi.LoaiThietBi
                    , thietbi.NhomThietBi
                    , khuvuc.MaKhuVuc
                    , khuvuc.Ten AS TenKhuVuc
                    , donvi.Ma AS MaDonViBaoTri
                    , donvi.Ten AS TenDonViBaoTri
                    , khuvucdung.MaKhuVuc AS MaKhuVucDungMay
                    , khuvucdung.Ten AS TenKhuVucDungMay
                    , phieubt.NgayDungMay AS NgayBatDauDungMay
                    , phieubt.ThoiGianBatDauDungMay AS GioBatDauDungMay
                    , phieubt.NgayKetThucDungMay AS NgayKetThucDungMay
                    , phieubt.ThoiGianKetThucDungMay AS GioKetThucDungMay
                    , phieubt.ThoiGianDungMay AS ThoiGianDungMay
                    , phieubt.ThoiGianXuLy AS ThoiGianXuLy
                    , nguyennhan.Ma AS MaNguyenNhanLoi
                    , nguyennhan.Ten AS TenNguyenNhanLoi                    
				FROM OPhieuBaoTri as phieubt
				LEFT JOIN ODonViSanXuat AS donvi on phieubt.Ref_MaDVBT = donvi.IOID
                LEFT JOIN OKhuVuc AS khuvucdung ON phieubt.Ref_KhuVucDungMay = khuvucdung.IOID
				LEFT JOIN ODanhSachThietBi as thietbi on thietbi.IOID = phieubt.Ref_MaThietBi
                LEFT JOIN OLoaiThietBi AS loaithietbi ON thietbi.Ref_LoaiThietBi = loaithietbi.IOID
                LEFT JOIN OKhuVuc AS khuvuc on thietbi.Ref_MaKhuVuc = khuvuc.IOID
				LEFT JOIN ONguyenNhanSuCo as nguyennhan on nguyennhan.IOID = ycbt.Ref_MaNguyenNhanSuCo 
				WHERE ifnull(ycbt.Ref_MaNguyenNhanSuCo, 0) != 0 %1$s
				',
				$where);
		$sql = $this->getSQL($sql);
		return $this->_o_DB->fetchAll($sql);
	}
}
?>