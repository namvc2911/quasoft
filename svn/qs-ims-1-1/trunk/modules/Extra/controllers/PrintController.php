<?php
/**
 * @author: ThinhTuan
 * @component:
 * @place: modules/Extra/Controllers/PrintController.php
 */
class Extra_PrintController extends Qss_Lib_PrintController
{
	public function init()
	{
		//$this->i_SecurityLevel = 15;
		$this->layout = Qss_Config::get(QSS_CONFIG_FILE)->layout->path
		. '/print.php';
		parent::init();
	}

    /**
     * MUA HÀNG: (-MENU-)
     * + purchaseRequireformaintainAction: Mẫu in nhu cầu mua hàng (Trong bảo trì)
     * + poAction: Phieu mua hang
     * + purchaseInvoiceAction: Mẫu in hóa đơn
     * + returnToVendorAction: Trả hàng cho nhà cung cấp
     */

    /**
     * In nhu cau mua hang danh cho phan bao tri
     */
    public function purchaseRequireformaintainAction()
    {
        $this->html->print = $this->_params->ODSNhuCauMuaHang;
        $this->html->date  = $this->_params->NgayDeXuat;
    }

    /**
     * Mẫu in phiếu mua hàng
     */
	public function poAction()
	{
		//dung bien $this->_form
		//$ifid = $this->params->requests->getParam('ifid',0);

		$list = $this->_params->ODSDonMuaHang;
		$this->html->DanhSach = (is_array($list)) ? $list : array();
		$this->html->SoDonHang = $this->_params->SoDonHang;
		$this->html->NgayYCNH = $this->_params->NgayYCNH;
		$this->html->NgayDatHang = $this->_params->NgayDatHang;
		$this->html->LoaiTien = $this->_params->LoaiTien;
		$this->html->TenNCC = $this->_params->TenNCC;
		$this->html->MaNCC = $this->_params->MaNCC;
		$this->html->Thue = $this->_params->Thue;
		$this->html->ThanhTien = $this->_params->GiaTriDonHang;
		$this->html->ChiPhiVC = $this->_params->ChiPhiVanChuyen;
		$this->html->GiamTru = $this->_params->GiamTru;
		$this->html->TongTienDH = $this->_params->TongTienDH;
	}

    /**
     * Mẫu in hóa đơn mặc định
     */
    public function purchaseInvoiceAction()
    {
        $list = $this->_params->ODanhSachHoaDon;
        $this->html->DanhSach = (is_array($list)) ? $list : array();
        $this->html->SoChungTu = $this->_params->SoChungTu;
        $this->html->SoHD = $this->_params->SoHoaDon;
        $this->html->NgayHD = $this->_params->NgayHoaDon;
        $this->html->LoaiTien = $this->_params->LoaiTien;
        $this->html->TenNCC = $this->_params->NCC;
        $this->html->Thue = $this->_params->Thue;
        $this->html->ThanhTien = $this->_params->GiaTri;
        $this->html->GiamTru = $this->_params->GiamTru;
        $this->html->TongTienHD = $this->_params->TongTienHD;
    }

    /**
     * Mẫu in trả hàng cho nhà cung cấp
     */
    public function returnToVendorAction()
    {
        $list = $this->_params->ODanhSachTraHang;
        $this->html->DanhSach = (is_array($list)) ? $list : array();
        $this->html->SoChungTu = $this->_params->SoChungTu;
        $this->html->NgayYCNH = $this->_params->NgayTraHang;
        $this->html->LoaiTien = $this->_params->LoaiTien;
        $this->html->TenNCC = $this->_params->TenNhaCungCap;
        $this->html->Thue = $this->_params->Thue;
        $this->html->ThanhTien = $this->_params->GiaTriDonHang;
        $this->html->ChiPhiVC = $this->_params->ChiPhiVanChuyen;
        $this->html->GiamTru = $this->_params->GiamTru;
        $this->html->TongTienDH = $this->_params->TongTienDonHang;
        $this->html->LyDoTraLai = $this->_params->LyDoTraLai;
    }
    /*****************************************************************************************************************/

    /**
     * KHO: (-MENU-)
     * + goodReceiptAction: Mẫu in nhập kho
     * + warehouseInputOrder2Action: Mẫu in danh sách nhập kho tại công ty của POS
     * + warehouseInputOrder3Action: Mẫu in danh sách nhập kho tại đơn vị Sà Làn của POS
     * + goodsShipmentAction: Mẫu in xuất kho
     * + warehouseOutputOrder2Action: Mẫu in danh sách xuất kho tại công ty của POS
     * + warehouseOutputOrder3Action: Mẫu in danh sách xuất kho tại đơn vị Sà Làn của POS
     * + goodsMovementAction: Mẫu in chuyển kho
     * + warehouseControlAction: Mẫu in kiểm kê kho
     * + maintenanceMaterialrequireAction: Mẫu in nhu cầu vật tư (Trong bảo trì)
     * + warehouseRequestLostandbreakAction: Mẫu in báo hư hỏng mất vật tư
     */

    /**
     * Mẫu in nhập kho
     */
	public function goodReceiptAction()
	{
		$list = $this->_params->ODanhSachNhapKho;
		$this->html->DanhSach = (is_array($list)) ? $list : array();
		$this->html->SoChungTu = $this->_params->SoChungTu;
		$this->html->LoaiChungTu = $this->_params->LoaiChungTu;
		$this->html->SoDonHang = $this->_params->SoDonHang;
		$this->html->ThamChieu = $this->_params->DonHangThamChieu;
		$this->html->NgayCT = $this->_params->NgayChungTu;
		$this->html->NgayYCNH = $this->_params->NgayChuyenHang;
		//$this->html->LoaiTien   = $this->_params->LoaiTien;
		$this->html->MaNCC = $this->_params->MaNCC;
		$this->html->TenNCC = $this->_params->TenNCC;
		//$this->html->Thue       = $this->_params->Thue;
		//$this->html->ThanhTien  = $this->_params->GiaTriDonHang;
		//$this->html->ChiPhiVC   = $this->_params->ChiPhiVC;
		//$this->html->GiamTru    = $this->_params->GiamTru;
		//$this->html->TongTienDH = $this->_params->TongTienDonHang;
		$this->html->Kho = $this->_params->Kho;
	}

    /**
     * Mẫu in danh sách nhập kho tại công ty của POS
     * Mau nay lay tu mau cua cong ty dau khi bien ptsc
     */
    public function warehouseInputOrder2Action()
    {
        $this->html->params = $this->_params;
    }

    /**
     * Mẫu in danh sách nhập kho tại đơn vị Sà Làn của POS
     * Mau nay lay tu mau cua cong ty dau khi bien ptsc
     */
    public function warehouseInputOrder3Action()
    {
        $common                 = new Qss_Model_Extra_Extra();
        $this->html->params     = $this->_params;
        $this->html->workcenter = $common->getTableFetchOne('ODonViSanXuat', array('Ma'=>$this->_params->DonViThucHien));
        $this->html->partner    = $common->getTableFetchOne('ODoiTac', array('MaDoiTac'=>$this->_params->MaNCC));
    }

    /**
     * Mẫu in xuất kho (Chuyển hàng)
     */
    public function goodsShipmentAction()
    {
        $list = $this->_params->ODanhSachXuatKho;
        $this->html->DanhSach = (is_array($list)) ? $list : array();
        $this->html->SoChungTu = $this->_params->SoChungTu;
        $this->html->LoaiChungTu = $this->_params->LoaiChungTu;
        $this->html->SoDonHang = $this->_params->SoDonHang;
        $this->html->ThamChieu = $this->_params->DonHangThamChieu;
        $this->html->NgayCH = $this->_params->NgayChuyenHang;
        $this->html->NgayCT = $this->_params->NgayChungTu;
        $this->html->Kho = $this->_params->Kho;
        //$this->html->LoaiTien   = $this->_params->LoaiTien;
        $this->html->TenKH = $this->_params->TenKhachHang;
        $this->html->MaKH = $this->_params->MaKH;
        $this->html->LoaiXuatKho = $this->_params->LoaiXuatKho;
        //$this->html->Thue       = $this->_params->Thue;
        //$this->html->ThanhTien  = $this->_params->GiaTri;
        //$this->html->ChiPhiVC   = $this->_params->ChiPhiVC;
        //$this->html->GiamTru    = $this->_params->GiamTru;
        //$this->html->TongTienDH = $this->_params->TongTienDonHang;
    }

    /**
     * Mẫu in xuất kho tại công ty của POS
     * Mau nay lay tu mau cua cong ty dau khi bien ptsc
     */
    public function warehouseOutputOrder2Action()
    {
        $this->html->params = $this->_params;
    }

    /**
     * Mẫu in xuất kho tại đơn vị Sà Lan của POS
     * Mau nay lay tu mau cua cong ty dau khi bien ptsc
     */
    public function warehouseOutputOrder3Action()
    {
        $common                 = new Qss_Model_Extra_Extra();
        $this->html->params     = $this->_params;
        $this->html->workcenter = $common->getTableFetchOne('ODonViSanXuat', array('Ma'=>$this->_params->DonViThucHien));
        $this->html->partner    = $common->getTableFetchOne('ODoiTac', array('MaDoiTac'=>$this->_params->MaKH));
    }

    /**
     * Mẫu in chuyển kho
     */
    public function goodsMovementAction()
    {
        $list = $this->_params->ODanhSachCK;
        $this->html->DanhSach = (is_array($list)) ? $list : array();
        $this->html->SoChungTu = $this->_params->SoChungTu;
        $this->html->NgayCT = $this->_params->NgayChungTu;
        $this->html->NgayYC = $this->_params->NgayYeuCau;
        $this->html->NgayCH = $this->_params->NgayChuyenHang;
        $this->html->KhoLH = $this->_params->KhoLH;
    }

    /**
     * Mẫu in kiểm kê kho
     */
    public function warehouseControlAction()
    {
        $this->html->print = $this->_params->OChiTietKiemKe;
    }

    /**
     * In nhu cau vat tu danh cho phan bao tri
     */
    public function maintenanceMaterialrequireAction()
    {
        $this->html->print = $this->_params->ODSNhuCauVatTu;
        $this->html->date  = $this->_params->Ngay;
    }

    /**
     * Bao hu hong mat vat tu
     */
    public function warehouseRequestLostandbreakAction()
    {
        $eqLib  = new Qss_Lib_Maintenance_Equipment();
        $common = new Qss_Model_Extra_Extra();

        //$this->html->manage  = $eqLib->getManageDepNameOfEquip($this->_params->Ref_MaThietBi);
        //$this->html->bookVal = $eqLib->getBookValue($this->_params->Ref_MaThietBi);
        //$this->html->eq      = $common->getTableFetchOne('ODanhSachThietBi', array('IOID'=>$this->_params->Ref_MaThietBi));
        $this->html->params  = $this->_params;
    }
    /*****************************************************************************************************************/

    /**
     * BÁN HÀNG: (-MENU-)
     * + soAction: Mẫu in phiếu mua hàng
     * + returnFromCustomerAction: Mẫu in nhận hàng trả lại
     * + saleInvoiceAction: Mẫu in hóa đơn bán hàng
     */
	public function soAction()
	{
		$list = $this->_params->ODSDonBanHang;
		$this->html->DanhSach = (is_array($list)) ? $list : array();
		$this->html->SoDonHang = $this->_params->SoDonHang;
		$this->html->NgayYCNH = $this->_params->NgayYCNH;
		$this->html->LoaiTien = $this->_params->LoaiTien;
		$this->html->TenKH = $this->_params->TenKH;
		$this->html->Thue = $this->_params->Thue;
		$this->html->ThanhTien = $this->_params->GiaTri;
		$this->html->ChiPhiVC = $this->_params->CPVanChuyen;
		$this->html->GiamTru = $this->_params->GiamTru;
		$this->html->TongTienDH = $this->_params->TongTienDonHang;
	}

    /**
     * Mẫu in hóa đơn bán hàng
     */
    public function saleInvoiceAction()
    {
        $list = $this->_params->ODSHDBanHang;
        $this->html->DanhSach = (is_array($list)) ? $list : array();
        $this->html->SoChungTu = $this->_params->SoChungTu;
        $this->html->SoHD = $this->_params->SoHoaDon;
        $this->html->NgayHD = $this->_params->NgayHoaDon;
        $this->html->LoaiTien = $this->_params->LoaiTien;
        $this->html->TenNCC = $this->_params->KhachHang;
        $this->html->Thue = $this->_params->Thue;
        $this->html->ThanhTien = $this->_params->GiaTri;
        $this->html->GiamTru = $this->_params->GiamTru;
        $this->html->TongTienHD = $this->_params->TongTienDH;
    }

    /**
     * Mẫu in nhận hàng trả lại
     */
	public function returnFromCustomerAction()
	{
		$list = $this->_params->ODanhSachHangTL;
		$this->html->DanhSach = (is_array($list)) ? $list : array();
		$this->html->SoChungTu = $this->_params->SoChungTu;
		$this->html->NgayYCNH = $this->_params->NgayNhan;
		$this->html->LoaiTien = $this->_params->LoaiTien;
		$this->html->TenKH = $this->_params->TenKH;
		$this->html->Thue = $this->_params->Thue;
		$this->html->ThanhTien = $this->_params->GiaTri;
		$this->html->ChiPhiVC = $this->_params->ChiPhiVC;
		$this->html->GiamTru = $this->_params->GiamTru;
		$this->html->TongTienDH = $this->_params->TongTienDonHang;
		$this->html->LyDoTraLai = $this->_params->LyDo;
	}
    /*****************************************************************************************************************/

    /**
     * In BÁO CÁO BẢO DƯỠNG, SỬA CHỮA, NGHIỆM THU THIẾT BỊ
     */
    public function maintenanceWorkorderAcceptanceAction()
    {
        $this->html->params = $this->_params;
    }
    /*****************************************************************************************************************/

    /**
     * YÊU CẦU BẢO TRÌ: (-MENU-)
     * + maintenanceRequireLostandbreakAction: Báo hỏng mất thiết bị
     */

    /**
     * In bao cao mat hong thiet bi trong phan yeu cau bao tri
     */
    public function maintenanceRequireLostandbreakAction()
    {
        $eqLib  = new Qss_Lib_Maintenance_Equipment();
        $common = new Qss_Model_Extra_Extra();


        $this->html->manage  = $eqLib->getManageDepNameOfEquip($this->_params->Ref_MaThietBi);
        $this->html->bookVal = $eqLib->getBookValue($this->_params->Ref_MaThietBi);
        $this->html->eq      = $common->getTableFetchOne('ODanhSachThietBi', array('IOID'=>$this->_params->Ref_MaThietBi));
        $this->html->params  = $this->_params;
    }
    /*****************************************************************************************************************/

    /**
     * YÊU CẦU HIỆU CHUẨN KIỂM ĐỊNH THỬ TẢI: (-MENU-)
     * + maintenanceRequireTestAction: Mẫu in yêu cầu hiệu chuẩn kiểm định thử tải
     */

    /**
     * Mẫu in yêu cầu hiệu chuẩn kiểm định thử tải
     */
    public function maintenanceRequireTestAction()
    {
        $eqLib  = new Qss_Lib_Maintenance_Equipment();
        $common = new Qss_Model_Extra_Extra();


        $this->html->manage  = $eqLib->getManageDepNameOfEquip($this->_params->Ref_MaThietBi);
        $this->html->bookVal = $eqLib->getBookValue($this->_params->Ref_MaThietBi);
        $this->html->eq      = $common->getTableFetchOne('ODanhSachThietBi', array('IOID'=>$this->_params->Ref_MaThietBi));
        $this->html->params  = $this->_params;
    }

    /**
     * Kế hoạch sủa chữa lớn TTCO
     * maintenancePlanVerifyAction: Giám định thiết bị sửa chữa lớn
     * maintenancePlanMaterialsAction: Dự toán vật tư sửa chữa lớn
     */
    public function maintenancePlanVerifyAction()
    {

        $equip = new Qss_Model_Maintenance_Equipment();
        $detail  = array();
        foreach($this->_params->OChiTietKeHoachSCL as $item)
        {
            $detail[$item->Ref_ViTri] = $item;
        }

        $this->html->params  = $this->_params;
        $this->html->com     = $equip->getComponentOfEquip($this->_params->Ref_MaThietBi);
        $this->html->detail  = $detail;



    }

    public function maintenancePlanMaterialsAction()
    {
        $equip = new Qss_Model_Maintenance_Equipment();
        $detail  = array();
        foreach($this->_params->OChiTietKeHoachSCL as $item)
        {
            $detail[$item->Ref_ViTri] = $item;
        }

        $this->html->params  = $this->_params;
        $this->html->com     = $equip->getComponentOfEquip($this->_params->Ref_MaThietBi);
        $this->html->detail  = $detail;
    }
    /*****************************************************************************************************************/

    /**
     * NHẬT TRÌNH THIẾT BỊ: (-MENU-)
     * + dailyrecordConfirmAction: Mẫu xác nhận giờ chạy máy(theo mẫu của POS)
     */
    public function dailyrecordConfirmAction()
    {
        $this->html->params  = $this->_params;
    }
    /*****************************************************************************************************************/

    /**
     * SẢN XUẤT (-MENU-)
     * + productionOrderAction: In phiếu sản xuất
     * + productionWoAction: In Phieu Giao viec
     */

    /**
     * In phiếu sản xuất
     */
	public function productionOrderAction()
	{
//		$extra = new Qss_Model_Extra_Extra();
        $mThongKe = Qss_Model_Db::Table('OThongKeSanLuong');
        $mThongKe->where(sprintf('MaLenhSX = "%1$s"', $this->_params->MaLenhSX));
        $mGiaoViec = Qss_Model_Db::Table('OPhieuGiaoViec');
        $mGiaoViec->where(sprintf('MaLenhSX = "%1$s"', $this->_params->MaLenhSX));

		$this->html->data = $this->_params;
//		$this->html->statistic = $extra->getDataset(array('module'=>'OThongKeSanLuong'
//		, 'where'=>"MaLenhSX = '{$this->_params->MaLenhSX}'"));
//		$this->html->wo = $extra->getDataset(array('module'=>'OPhieuGiaoViec'
//		, 'where'=>"MaLenhSX = '{$this->_params->MaLenhSX}'"));
        $this->html->statistic = $mThongKe->fetchAll();
        $this->html->wo = $mGiaoViec->fetchAll();
	}

    /**
     * In phiếu giao việc
     */
	public function productionWoAction()
	{
		$common = new Qss_Model_Extra_Extra();
		$this->html->print = $this->_params;
	}
    /*****************************************************************************************************************/

    /**
     * DỰ ÁN
     * + projectEquipAction: Mẫu in thiết bị trong quản lý thiết bị dự án (Bỏ)
     * + projectMaterialAction: Mẫu in vật tư trong quản lý vật tư dự án (Bỏ)
     */

    /**
     * Mẫu in thiết bị trong quản lý thiết bị dự án
     */
    public function projectEquipAction()
    {
        $this->html->params  = $this->_params;
    }

    /**
     * Mẫu in vật tư trong quản lý vật tư dự án
     */
    public function projectMaterialAction()
    {
        $this->html->params  = $this->_params;
    }

    /**
     * ĐIỀU ĐỘNG THIẾT BỊ
     * + equipDeliveryAction: Mẫu in giao nhận thiết bị DELIVERY VOUCHER - theo mẫu của pos
     */

    /**
     * Biên bản bàn giao thiết bị - theo mẫu của pos
     */
    public function equipDeliveryAction()
    {
        $this->html->params  = $this->_params;
    }
}

?>