<?php
/**
 * 
 * @author Thinh
 */ 
class Qss_Lib_Maintenance_Electric_Calculate
{
    Const TI_LE_KI_1 = 35;
    Const TI_LE_KI_2 = 37;
    Const TI_LE_KI_3 = 28;
    Const PX_TUYEN_2_DEPT_ID = 3;
    Const PX_TUYEN_2_LEVEL   = 2;
    Const PX_TUYEN_2_CODE    = 'TT2';
    Const PX_TUYEN_2_NAME    = 'PX Tuyển than 2';
    Const TUYEN2_AN_THEO_PGD_CTD_06_ID = '_VPT2-AN-THEO-PGD-CTD-06';
    Const DEPARTMENT_CTY     = 'CTY';
    Const DEPARTMENT_VANTAI  = 'VT';
    
    // Thang & Nam
    protected $_thang;
    protected $_nam;
    
    // Query dung chung
    public $datasetBanRa;   // Group theo cong to
    public $datasetBanNoiBo;// Group theo cong to
    public $datasetMuaVao;  // Group theo cong to
    public $datasetBanDotXuat;    
    public $datasetKhoanDienNang;
    
    
    public $datasetChiTietMuaVao; // Khong group theo cong to
    public $datasetChiTietBanRa;  // Khong group theo cong to
    public $datasetChiTietNoiBo;  // Khong group theo cong to
    public $datasetChiTietNoiBoMuaNgoai; // Cac cong to mua noi bo co mua tu ben ngoai 
    
    // Gia mua va ban theo cong to
    protected $giaMua = array();
    protected $giaBan = array();    

    // Quang Ninh
    protected $dienMuaCuaQuangNinh     = 0;
    protected $tienDienMuaCuaQuangNinh = 0;
    protected $cospiMuaCuaQuangNinh    = 0;
    protected $dienBanChoQuangNinh     = 0;
    protected $tienDienBanChoQuangNinh = 0;
    protected $cospiBanChoQuangNinh    = 0;
    
    // Con lai cho su dung: mua tu qn <+cospi doi voi tien> - ban cho qn <+cospi doi voi tien>
    protected $tongDienConLaiChoSuDung     = 0;    
    protected $tongTienDienConLaiChoSuDung = 0;
    
    // Tong dien nang mua vao
    protected $tongDienMuaVao     = 0;
    protected $tongTienDienMuaVao = 0; 
    protected $tongDienMuaVaoKy1  = 0;
    protected $tongTienMuaVaoKy1  = 0;
    protected $tongDienMuaVaoKy2  = 0;
    protected $tongTienMuaVaoKy2  = 0;
    protected $tongDienMuaVaoKy3  = 0;
    protected $tongTienMuaVaoKy3  = 0;        
    
    // Tong dien nang ban ra
    protected $tongDienBanRa      = 0;
    protected $tongTienDienBanRa  = 0;
    
    // Chenh lech mua va ban:
    // Tong tien ban ra ko bao gom QN - (Tien con lai cho sd/dien con lai cho sd) * Tong dien ban ra ko bao gom QN
    protected $chenhLechMuaBan    = 0;    
    
    // San luong don vi [key=Ref_DonVi] = value
    protected $donGia                 = 0;
    protected $tongSanLuong           = 0;
    protected $chiTietDonViMuaVao     = array();
    protected $chiTietDonViBanRa      = array();
    protected $tiLeTheoDonVi          = array(); 
    protected $tonHaoTheoDonVi        = array();
    protected $sanLuongDonVi          = array(); // San luong chua cong voi ton hao
    protected $sanLuongNghiemThuDonVi = array(); // San luong da cong voi ton hao
    protected $thanhTienDonVi         = array(); // Thanh tien tinh bang san luong nghiem thu <Da bao gom ton hao>
    protected $donViCapChoDonViKhac   = array(); // So dien don vi cap cho don vi khac
    protected $donViMuaVao            = array();
    protected $donVi                  = array();
    protected $dienMuaTuDonViKhac     = array();
    protected $heSoTonHao             = array();
    protected $titleDonVi             = array();
    protected $titleDonViMuaNgoai     = array();
    
    
    public static function createInstance($thang = 0 , $nam = 0)
    {
        return new self($thang, $nam);
    }
    
    public function __construct($thang = 0 , $nam = 0)
    {
        $this->setThang($thang);
        $this->setNam($nam);
    }
	
	/**
	 * Set month
	 * @param int $thang
	 */
	public function setThang($thang)
	{
	    $this->thang = $thang;
	}
	
	/**
	 * Set year
	 * @param int $nam
	 */
	public function setNam($nam)
	{
	    $this->nam = $nam;
	}
	
	/**
	 * @return int Month
	 */
	public function getThang()
	{
	    return $this->thang;
	}
	
	/**
	 * @return int Year
	 */
	public function getNam()
	{
	    return $this->nam;
	}	
	
	public function setDatasetMuaVao()
	{
	    $electric              = new Qss_Model_Maintenance_Electric();
	    $this->datasetMuaVao   = $electric->getTongDienNangMuaVao($this->thang,  $this->nam);
	}
	
	public function setDatasetBanRa()
	{
	    $electric              = new Qss_Model_Maintenance_Electric();
	    $this->datasetBanRa    = $electric->getDienNangBanRa($this->thang,  $this->nam);
	}
	
	public function setDatasetBanRaDotXuat()
	{
	    $electric                = new Qss_Model_Maintenance_Electric();
	    $this->datasetBanDotXuat = $electric->getDienNangBanRaDotXuat($this->thang,  $this->nam);
	}
	
	public function setDatasetNoiBo()
	{
	    $electric              = new Qss_Model_Maintenance_Electric();
	    $this->datasetBanNoiBo = $electric->getDienNangBanRaNoiBo($this->thang,  $this->nam);
	}	
	
	public function setDatasetChiTietMuaVao()
	{
	    $electric              = new Qss_Model_Maintenance_Electric();
	    $this->datasetChiTietMuaVao   = $electric->getChiSoDienNangMuaVao($this->thang,  $this->nam);
	}
	
	public function setDatasetChiTietBanRa()
	{
	    $electric              = new Qss_Model_Maintenance_Electric();
	    $this->datasetChiTietBanRa    = $electric->getChiSoDienNangBanRa($this->thang,  $this->nam);
	}
	
	public function setDatasetChiTietNoiBo()
	{
	    $electric              = new Qss_Model_Maintenance_Electric();
	    $this->datasetChiTietNoiBo = $electric->getChiSoDienNangBanRaNoiBo($this->thang,  $this->nam);
	}	
	
	public function setDatasetKhoanDienNang()
	{
	    $electric                   = new Qss_Model_Maintenance_Electric();
	    $this->datasetKhoanDienNang = $electric->getKhoanDienNang($this->thang,  $this->nam);	    
	}
	
	public function setDatasetChiTietNoiBoMuaNgoai()
	{
	    $electric                   = new Qss_Model_Maintenance_Electric();
	    $this->datasetChiTietNoiBoMuaNgoai = $electric->getChiSoDienNangBanRaNoiBoCoMuaNgoai($this->thang,  $this->nam);	    
	}
	
    /**
     * Giá mua theo công tơ
     */
	public function setGiaMua()
	{
	    $electric = new Qss_Model_Maintenance_Electric();
	    $giaMua   = $electric->getGiaDienNangMuaVao();
	
	    // Thiết lập mảng giá mua cho từng công tơ <Dùng để tính giá tiền điện mua>
	    foreach($giaMua as $item)
	    {
	        if($item->EMIOID)
	        {
	            $this->giaMua[$item->EMIOID]['Code']      = $item->MaCongTo;
	            $this->giaMua[$item->EMIOID]['Name']      = $item->TenCongTo;
	            $this->giaMua[$item->EMIOID]['PriceType'] = $item->LoaiGia;
	            $this->giaMua[$item->EMIOID]['Gen']       = $item->DonGiaChung?$item->DonGiaChung:0;
	            $this->giaMua[$item->EMIOID]['B1']        = $item->DonGiaB1?$item->DonGiaB1:0;
	            $this->giaMua[$item->EMIOID]['B2']        = $item->DonGiaB2?$item->DonGiaB2:0;
	            $this->giaMua[$item->EMIOID]['B3']        = $item->DonGiaB3?$item->DonGiaB3:0;
	        }
	    }
	}
	
    /**
     * @return array Giá mua theo công tơ
     */
	public function getGiaMua()
	{
	    return $this->giaMua;
	}
	
    /**
     * Giá bán theo công tơ
     */
	public function setGiaBan()
	{
	    $electric = new Qss_Model_Maintenance_Electric();
	    $giaBan   = $electric->getGiaDienNangBanRa();
	
	    // Thiết lập mảng giá bán cho từng công tơ <Dùng để tính giá tiền điện bán>
	    foreach($giaBan as $item)
	    {
	        if ($item->EMIOID) {
	            $this->giaBan[$item->EMIOID]['Code']      = $item->MaCongTo;
	            $this->giaBan[$item->EMIOID]['Name']      = $item->TenCongTo;
	            $this->giaBan[$item->EMIOID]['PriceType'] = $item->LoaiGia;
	            $this->giaBan[$item->EMIOID]['Gen']       = $item->DonGiaChung?$item->DonGiaChung:0;
	            $this->giaBan[$item->EMIOID]['B1']        = $item->DonGiaB1?$item->DonGiaB1:0;
	            $this->giaBan[$item->EMIOID]['B2']        = $item->DonGiaB2?$item->DonGiaB2:0;
	            $this->giaBan[$item->EMIOID]['B3']        = $item->DonGiaB3?$item->DonGiaB3:0;
	        }
	    }
	}	

	/**
	 * @return array Giá bán theo công tơ
	 */	
	public function getGiaBan()
	{
	    return $this->giaBan;
	}
	
	/**
	 * Lấy giá mua chung theo công tơ
	 * @param int $CongToIOID
	 * @return int Đơn giá chung
	 */
	public function getGiaMuaChung($CongToIOID)
	{
	    return round((isset($this->giaMua[$CongToIOID]['Gen']) && $this->giaMua[$CongToIOID]['Gen'])?$this->giaMua[$CongToIOID]['Gen']:0);
	}
	
	/**
	 * Lấy giá mua B1 <Trung Binh> theo công tơ
	 * @param int $CongToIOID
	 * @return int Đơn giá trung binh
	 */	
	public function getGiaMuaB1($CongToIOID)
	{
	    return round((isset($this->giaMua[$CongToIOID]['B1']) && $this->giaMua[$CongToIOID]['B1'])?$this->giaMua[$CongToIOID]['B1']:0);
	}
	
	/**
	 * Lấy giá mua B2 <Cao diem> theo công tơ
	 * @param int $CongToIOID
	 * @return int Đơn giá cao diem
	 */	
	public function getGiaMuaB2($CongToIOID)
	{
	    return round((isset($this->giaMua[$CongToIOID]['B2']) && $this->giaMua[$CongToIOID]['B2'])?$this->giaMua[$CongToIOID]['B2']:0);
	}
	
	/**
	 * Lấy giá mua B3 <Thấp diem> theo công tơ
	 * @param int $CongToIOID
	 * @return int Đơn giá thap diem
	 */	
	public function getGiaMuaB3($CongToIOID)
	{
	    return round((isset($this->giaMua[$CongToIOID]['B3']) && $this->giaMua[$CongToIOID]['B3'])?$this->giaMua[$CongToIOID]['B3']:0);
	}
	
	/**
	 * Lấy giá bán chung theo công tơ
	 * @param int $CongToIOID
	 * @return int Đơn giá chung
	 */	
	public function getGiaBanChung($CongToIOID)
	{
	    return round((isset($this->giaBan[$CongToIOID]['Gen']) && $this->giaBan[$CongToIOID]['Gen'])?$this->giaBan[$CongToIOID]['Gen']:0);
	}
	
	/**
	 * Lấy giá bán B1 <Trung Binh> theo công tơ
	 * @param int $CongToIOID
	 * @return int Đơn giá trung binh
	 */	
	public function getGiaBanB1($CongToIOID)
	{
	    return round((isset($this->giaBan[$CongToIOID]['B1']) && $this->giaBan[$CongToIOID]['B1'])?$this->giaBan[$CongToIOID]['B1']:0);
	}
	
	
	/**
	 * Lấy giá bán B2 <Cao diem> theo công tơ
	 * @param int $CongToIOID
	 * @return int Đơn giá cao diem
	 */	
	public function getGiaBanB2($CongToIOID)
	{
	    return round((isset($this->giaBan[$CongToIOID]['B2']) && $this->giaBan[$CongToIOID]['B2'])?$this->giaBan[$CongToIOID]['B2']:0);
	}
	
	/**
	 * Lấy giá bán B3 <Thấp diem> theo công tơ
	 * @param int $CongToIOID
	 * @return int Đơn giá thap diem
	 */	
	public function getGiaBanB3($CongToIOID)
	{
	    return round((isset($this->giaBan[$CongToIOID]['B3']) && $this->giaBan[$CongToIOID]['B3'])?$this->giaBan[$CongToIOID]['B3']:0);
	}	
	
	/**
	 * Tinh tong tien dien + so dien + tien phat cospi mua tu Quang Ninh
	 */
	public function calculateDienMuaTuQuangNinh()
	{
	    // @Lưu ý: Để chạy hàm này cần setGiaMua trước <chỉ khi cần tính tiền>
	    // $this->setGiaMua();
	    // $this->setDatasetMuaVao();   

	    if(!$this->datasetMuaVao)
	    {
	        $this->setDatasetMuaVao();
	    }
	    
	    if(!$this->giaMua)
	    {
	        $this->setGiaMua();
	    }
	    
	    foreach($this->datasetMuaVao as $item)
	    {
	        if($item->DoiTuong == 1) // Chi tinh cho Quang Ninh
	        {
	            // Don gia
	            $CongToIOID       = @(int)$item->CongToIOID;
//				$giaDienChung     = round($this->getGiaBanChung($CongToIOID));
//				$giaDienB1        = round($this->getGiaMuaB1($CongToIOID));
//				$giaDienB2        = round($this->getGiaMuaB2($CongToIOID));
//				$giaDienB3        = round($this->getGiaMuaB3($CongToIOID));
//
//				$item->TongSoDienChung     = round($item->TongSoDienChung);
//				$item->TongSoDienTrungBinh = round($item->TongSoDienTrungBinh);
//				$item->TongSoDienThapDiem  = round($item->TongSoDienThapDiem);
//				$item->TongSoDienCaoDiem   = round($item->TongSoDienCaoDiem);


	            $giaDienChung     = $this->getGiaMuaChung($CongToIOID);
	            $giaDienB1        = $this->getGiaMuaB1($CongToIOID);
	            $giaDienB2        = $this->getGiaMuaB2($CongToIOID);
	            $giaDienB3        = $this->getGiaMuaB3($CongToIOID);
	            
	            // Thanh tien
	            $tongGiaChung     = $giaDienChung * $item->TongSoDienChung;
	            $tongGiaTrungBinh = $giaDienB1 * $item->TongSoDienTrungBinh;
	            $tongGiaThapDiem  = $giaDienB3 * $item->TongSoDienThapDiem;
	            $tongGiaCaoDiem   = $giaDienB2 * $item->TongSoDienCaoDiem;
	            $tongBaGia        = $tongGiaTrungBinh + $tongGiaThapDiem + $tongGiaCaoDiem;
	            
	            // Tong ba so dien cong to ba gia
	            $tongBaSoDien     = $item->TongSoDienThapDiem + $item->TongSoDienTrungBinh + $item->TongSoDienCaoDiem;	 

	            $this->tienDienMuaCuaQuangNinh += ($item->LoaiGia == 1)?$tongGiaChung:$tongBaGia;
	            $this->dienMuaCuaQuangNinh     += ($item->LoaiGia == 1)?$item->TongSoDienChung:$tongBaSoDien;
	            $this->cospiMuaCuaQuangNinh    += $item->TongTienPhatCosPi;
	        }
	    }	    
	}
	
	/**
	 *
	 * @return money Dien mua cua quang ninh
	 */
	public function getDienMuaCuaQuangNinh()
	{
	    return $this->dienMuaCuaQuangNinh;
	}
	
	/**
	 *
	 * @return money Tien dien mua tu QN
	 */
	public function getTienDienMuaCuaQuangNinh()
	{
	    return  $this->tienDienMuaCuaQuangNinh;
	}
	
	/**
	 * cospi Quang Ninh
	 * @return number
	 */
	public function getCospiMuaCuaQuangNinh()
	{
	    return $this->cospiMuaCuaQuangNinh;
	}	
	
	/**
	 * Tinh tong tien dien + so dien + tien phat cospi mua tu Quang Ninh
	 */
	public function calculateDienMuaTuDonViKhac()
	{
	    // @Lưu ý: Để chạy hàm này cần setGiaMua trước <chỉ khi cần tính tiền>
	    // $this->setGiaMua();
	    // $this->setDatasetMuaVao();
	
	    if(!$this->datasetMuaVao)
	    {
	        $this->setDatasetMuaVao();
	    }
	    
	    if(!$this->giaMua)
	    {
	        $this->setGiaMua();
	    }	    
	     
	    foreach($this->datasetMuaVao as $item)
	    {
	        if($item->DoiTuong != 1) // Chi tinh cho Quang Ninh
	        {
	            if(!isset($this->dienMuaTuDonViKhac[$item->Ref_DonViCungCap]['Qty']))
	            {
	                $this->dienMuaTuDonViKhac[$item->Ref_DonViCungCap]['Qty'] = 0;
	            }
	            
	            if(!isset($this->dienMuaTuDonViKhac[$item->Ref_DonViCungCap]['Amount']))
	            {
	                $this->dienMuaTuDonViKhac[$item->Ref_DonViCungCap]['Amount'] = 0;
	            }	  

	            if(!isset($this->dienMuaTuDonViKhac[$item->Ref_DonViCungCap]['Detail'][$item->Ref_DonViMua]['Qty']))
	            {
	                $this->dienMuaTuDonViKhac[$item->Ref_DonViCungCap]['Detail'][$item->Ref_DonViMua]['Qty'] = 0;
	            }	    

	            if(!isset($this->dienMuaTuDonViKhac[$item->Ref_DonViCungCap]['Detail'][$item->Ref_DonViMua]['Amount']))
	            {
	                $this->dienMuaTuDonViKhac[$item->Ref_DonViCungCap]['Detail'][$item->Ref_DonViMua]['Amount'] = 0;
	            }	            
	            
	            // Don gia
	            $CongToIOID       = @(int)$item->CongToIOID;
//				$giaDienChung     = round($this->getGiaMuaChung($CongToIOID));
//				$giaDienB1        = round($this->getGiaMuaB1($CongToIOID));
//				$giaDienB2        = round($this->getGiaMuaB2($CongToIOID));
//				$giaDienB3        = round($this->getGiaMuaB3($CongToIOID));
//
//
//				$item->TongSoDienChung = round($item->TongSoDienChung);
//				$item->TongSoDienTrungBinh = round($item->TongSoDienTrungBinh);
//				$item->TongSoDienThapDiem = round($item->TongSoDienThapDiem);
//				$item->TongSoDienCaoDiem = round($item->TongSoDienCaoDiem);

	            $giaDienChung     = $this->getGiaMuaChung($CongToIOID);
	            $giaDienB1        = $this->getGiaMuaB1($CongToIOID);
	            $giaDienB2        = $this->getGiaMuaB2($CongToIOID);
	            $giaDienB3        = $this->getGiaMuaB3($CongToIOID);
	             
	            // Thanh tien
	            $tongGiaChung     = $giaDienChung * $item->TongSoDienChung;
	            $tongGiaTrungBinh = $giaDienB1 * $item->TongSoDienTrungBinh;
	            $tongGiaThapDiem  = $giaDienB3 * $item->TongSoDienThapDiem;
	            $tongGiaCaoDiem   = $giaDienB2 * $item->TongSoDienCaoDiem;
	            $tongBaGia        = $tongGiaTrungBinh + $tongGiaThapDiem + $tongGiaCaoDiem;
	             
	            // Tong ba so dien cong to ba gia
	            $tongBaSoDien     = $item->TongSoDienThapDiem + $item->TongSoDienTrungBinh + $item->TongSoDienCaoDiem;
	            
	            $soDien           = ($item->LoaiGia == 1)?$item->TongSoDienChung:$tongBaSoDien;
	            $soTien           = (($item->LoaiGia == 1)?$tongGiaChung:$tongBaGia) + $item->TongTienPhatCosPi;
	
	            $this->dienMuaTuDonViKhac[$item->Ref_DonViCungCap]['Name']            = $item->DonViCungCap;
	            $this->dienMuaTuDonViKhac[$item->Ref_DonViCungCap]['Qty']            += $soDien ;
	            $this->dienMuaTuDonViKhac[$item->Ref_DonViCungCap]['Amount']         += $soTien ;	   
	                     
	            $this->dienMuaTuDonViKhac[$item->Ref_DonViCungCap]['Detail'][$item->Ref_DonViMua]['Name']    = $item->DonViMua;
	            $this->dienMuaTuDonViKhac[$item->Ref_DonViCungCap]['Detail'][$item->Ref_DonViMua]['Qty']    += $soDien ;
	            $this->dienMuaTuDonViKhac[$item->Ref_DonViCungCap]['Detail'][$item->Ref_DonViMua]['Amount'] += $soTien;
	        }
	    }
	}	
	
	public function setTitleDonViMuaNgoai()
	{
	    if(!$this->datasetChiTietNoiBoMuaNgoai)
	    {
	        $this->setDatasetChiTietNoiBoMuaNgoai();
	    }
	    
	    foreach($this->datasetChiTietNoiBoMuaNgoai as $item)
	    {
	        $code = $item->Ref_DonViMuaNgoai.'-'.$item->Ref_DonViBan;
	        
	        if(!isset($this->titleDonViMuaNgoai[$item->Ref_DonViMuaNgoai][$item->Ref_DonViBan])) 
	        {
	            $ctmvIndex[$code] = 0;
	            $this->titleDonViMuaNgoai[$item->Ref_DonViMuaNgoai][$item->Ref_DonViBan] = '';
	        }
	        
	        ++$ctmvIndex[$code];
	        
	        $this->titleDonViMuaNgoai[$item->Ref_DonViMuaNgoai][$item->Ref_DonViBan] .= "  {$ctmvIndex[$code]}. Bán cho {$item->NameDonViMuaNoiBo} qua công tơ {$item->MaCongTo}: {$item->TongSo} \n";
	    }
	}
	
	public function getTitleDonViMuaNgoai()
	{
	    return $this->titleDonViMuaNgoai;
	}
	
	/**
	 * Tinh tong tien dien + so dien + tien phat cospi ban cho Quang Ninh
	 */
	public function calculateDienBanChoQuangNinh()
	{
	    // @Lưu ý: Để chạy hàm  này cần setGiaBan trước <chỉ khi cần tính tiền>
	    // $this->setGiaBan();	    
        // $this->setDatasetBanRa

	    if(!$this->datasetBanRa)
	    {
	        $this->setDatasetBanRa();
	    }
	    
	    if(!$this->giaBan)
	    {
	        $this->setGiaBan();
	    }
	    
	    foreach($this->datasetBanRa as $item)
	    {
	        if($item->DoiTuong == 1) // Chi tinh cho Quang Ninh
	        {

				$CongToIOID       = @(int)$item->CongToIOID;
//				$giaDienChung     = round($this->getGiaBanChung($CongToIOID));
//				$giaDienB1        = round($this->getGiaBanB1($CongToIOID));
//				$giaDienB2        = round($this->getGiaBanB2($CongToIOID));
//				$giaDienB3        = round($this->getGiaBanB3($CongToIOID));
//
//				$item->TongSoDienChung     = round($item->TongSoDienChung);
//				$item->TongSoDienTrungBinh = round($item->TongSoDienTrungBinh);
//				$item->TongSoDienThapDiem  = round($item->TongSoDienThapDiem);
//				$item->TongSoDienCaoDiem   = round($item->TongSoDienCaoDiem);

	            // Don gia
	            $giaDienChung     = $this->getGiaBanChung($CongToIOID);
	            $giaDienB1        = $this->getGiaBanB1($CongToIOID);
	            $giaDienB2        = $this->getGiaBanB2($CongToIOID);
	            $giaDienB3        = $this->getGiaBanB3($CongToIOID);
	             
	            // Thanh tien
	            $tongGiaChung     = $giaDienChung * $item->TongSoDienChung;
	            $tongGiaTrungBinh = $giaDienB1 * $item->TongSoDienTrungBinh;
	            $tongGiaThapDiem  = $giaDienB3 * $item->TongSoDienThapDiem;
	            $tongGiaCaoDiem   = $giaDienB2 * $item->TongSoDienCaoDiem;
	            $tongBaGia        = $tongGiaTrungBinh + $tongGiaThapDiem + $tongGiaCaoDiem;
	             
	            // Tong ba so dien cong to ba gia
	            $tongBaSoDien     = $item->TongSoDienThapDiem + $item->TongSoDienTrungBinh + $item->TongSoDienCaoDiem;
	
	            $this->tienDienBanChoQuangNinh += ($item->LoaiGia == 1)?$tongGiaChung:$tongBaGia;
	            $this->dienBanChoQuangNinh     += ($item->LoaiGia == 1)?$item->TongSoDienChung:$tongBaSoDien;
	            $this->cospiBanChoQuangNinh    += $item->TongTienPhatCosPi;
	        }
	    }
	}	
	
	/**
	 *
	 * @return money Dien ban cho quang ninh
	 */
	public function getDienBanChoQuangNinh()
	{
	    return $this->dienBanChoQuangNinh;
	}
	
	/**
	 *
	 * @return money Tien dien ban cho QN
	 */
	public function getTienDienBanChoQuangNinh()
	{
	    return  $this->tienDienBanChoQuangNinh;
	}	
	
	public function getCospiBanChoQuangNinh()
	{
	    return $this->cospiMuaCuaQuangNinh;
	}	
	
	
	/**
	 * 
	 * @return number So dien con lai cho su dung: Mua - ban cua rieng dien luc
	 */
	public function getTongDienQuangNinhConLaiChoSuDung()
	{
	    return $this->dienMuaCuaQuangNinh - $this->dienBanChoQuangNinh;;
	}
	
	
	/**
	 * 
	 * @return money Tong tien con lai cho su dung
	 */
	public function getTongTienDienQuangNinhConLaiChoSuDung()
	{
	    // @lưu ý: để chạy được hàm cần tính trước điện mua và bán cho Quảng Ninh
	    // $this->calculateDienMuaTuQuangNinh();
	    // $this->calculateDienBanChoQuangNinh();
	    $mua = $this->tienDienMuaCuaQuangNinh + $this->cospiMuaCuaQuangNinh;
	    $ban = $this->tienDienBanChoQuangNinh + $this->cospiBanChoQuangNinh;
	    return $mua - $ban;
	}
	
	/**
	 * Tinh toan dien nang ban ra <bao gom ca ban dot xuat>
	 */
	public function calculateDienNangBanRa()
	{
	    // @Lưu ý: Để chạy hàm này cần setDatasetBanRa <va setGiaBan khi cần tính tiền>
	    // $this->setGiaBan();
	    // $this->setDatasetBanRa();
	    // $this->setDatasetBanRaDotXuat();
	    
	    if(!$this->datasetBanDotXuat)
	    {
	        $this->setDatasetBanRaDotXuat();
	    }
	    
	    if(!$this->datasetBanRa)
	    {
	        $this->setDatasetBanRa();
	    }
	     
	    // Dien ban dinh ky cho cac don vi
	    foreach($this->datasetBanRa as $item)
	    {
            // Don gia
            $CongToIOID       = @(int)$item->CongToIOID;
//			$giaDienChung     = round($this->getGiaBanChung($CongToIOID));
//			$giaDienB1        = round($this->getGiaBanB1($CongToIOID));
//			$giaDienB2        = round($this->getGiaBanB2($CongToIOID));
//			$giaDienB3        = round($this->getGiaBanB3($CongToIOID));
//
//			$item->TongSoDienChung = round($item->TongSoDienChung);
//			$item->TongSoDienTrungBinh = round($item->TongSoDienTrungBinh);
//			$item->TongSoDienThapDiem = round($item->TongSoDienThapDiem);
//			$item->TongSoDienCaoDiem = round($item->TongSoDienCaoDiem);


            $giaDienChung     = $this->getGiaBanChung($CongToIOID);
            $giaDienB1        = $this->getGiaBanB1($CongToIOID);
            $giaDienB2        = $this->getGiaBanB2($CongToIOID);
            $giaDienB3        = $this->getGiaBanB3($CongToIOID);
    
            // Thanh tien
            $tongGiaChung     = $giaDienChung * $item->TongSoDienChung;
            $tongGiaTrungBinh = $giaDienB1 * $item->TongSoDienTrungBinh;
            $tongGiaThapDiem  = $giaDienB3 * $item->TongSoDienThapDiem;
            $tongGiaCaoDiem   = $giaDienB2 * $item->TongSoDienCaoDiem;
            $tongBaGia        = $tongGiaTrungBinh + $tongGiaThapDiem + $tongGiaCaoDiem;
    
            // Tong ba so dien cong to ba gia
            $tongBaSoDien     = $item->TongSoDienThapDiem + $item->TongSoDienTrungBinh + $item->TongSoDienCaoDiem;
            
            // Dien + tien
            $soDien           = ($item->LoaiGia == 1)?$item->TongSoDienChung:$tongBaSoDien;
            $soTien           = ($item->LoaiGia == 1)?$tongGiaChung:$tongBaGia;
            
            $this->tongTienDienBanRa  += $soTien + $item->TongTienPhatCosPi;
            $this->tongDienBanRa      += $soDien;
	    }	

	    // Dien ban khoan ban dot xuat
	    foreach($this->datasetBanDotXuat as $item)
	    {
	        $this->tongTienDienBanRa  += $item->ThanhTien;
	        $this->tongDienBanRa      += $item->SanLuong;
	    }	    
	}
	
	public function getTongDienBanRa()
	{
	    return $this->tongDienBanRa - $this->getDienBanChoQuangNinh();
	}
	
	public function getTongTienDienBanRa()
	{
	    return $this->tongTienDienBanRa - $this->getTienDienBanChoQuangNinh();
	}
	
	public function calculateDienNangMuaVao()
	{
	    // @Lưu ý: Để chạy hàm này cần setDatasetMuaVao <va setGiaMua khi cần tính tiền>
	    // $this->setGiaMua();
        // $this->setDatasetMuaVao(); 
        // Can chay ham tinh toan dien nang ban ra cho QN truoc $this->calculateDienBanChoQuangNinh();
	     
	    if(!$this->datasetMuaVao)
	    {
	        $this->setDatasetMuaVao();
	    }	    
	    
	    foreach($this->datasetMuaVao as $item)
	    {
            // Don gia
            $CongToIOID       = @(int)$item->CongToIOID;
//			$giaDienChung     = round($this->getGiaMuaChung($CongToIOID));
//			$giaDienB1        = round($this->getGiaMuaB1($CongToIOID));
//			$giaDienB2        = round($this->getGiaMuaB2($CongToIOID));
//			$giaDienB3        = round($this->getGiaMuaB3($CongToIOID));
//
//			$item->TongSoDienChung     = round($item->TongSoDienChung);
//			$item->TongSoDienTrungBinh = round($item->TongSoDienTrungBinh);
//			$item->TongSoDienThapDiem  = round($item->TongSoDienThapDiem);
//			$item->TongSoDienCaoDiem   = round($item->TongSoDienCaoDiem);


            $giaDienChung     = $this->getGiaMuaChung($CongToIOID);
            $giaDienB1        = $this->getGiaMuaB1($CongToIOID);
            $giaDienB2        = $this->getGiaMuaB2($CongToIOID);
            $giaDienB3        = $this->getGiaMuaB3($CongToIOID);
             
            // Thanh tien
            $tongGiaChung     = $giaDienChung * $item->TongSoDienChung;
            $tongGiaTrungBinh = $giaDienB1 * $item->TongSoDienTrungBinh;
            $tongGiaThapDiem  = $giaDienB3 * $item->TongSoDienThapDiem;
            $tongGiaCaoDiem   = $giaDienB2 * $item->TongSoDienCaoDiem;
            $tongBaGia        = $tongGiaTrungBinh + $tongGiaThapDiem + $tongGiaCaoDiem;
             
            // Tong ba so dien cong to ba gia
            $tongBaSoDien     = $item->TongSoDienThapDiem + $item->TongSoDienTrungBinh + $item->TongSoDienCaoDiem;
            
            // Dien + tien
            $soDien           = ($item->LoaiGia == 1)?$item->TongSoDienChung:$tongBaSoDien;
            $soTien           = ($item->LoaiGia == 1)?$tongGiaChung:$tongBaGia;            

            $this->tongTienDienMuaVao += $soTien + $item->TongTienPhatCosPi;
            $this->tongDienMuaVao     += $soDien;
	    }
	    
	    $this->tongDienMuaVao     = $this->tongDienMuaVao - $this->getDienBanChoQuangNinh();
	    $this->tongTienDienMuaVao = $this->tongTienDienMuaVao - $this->getTienDienBanChoQuangNinh();
	}	
	
	public function getTongDienMuaVao()
	{
	    return $this->tongDienMuaVao;
	}
	
	public function getTongTienDienMuaVao()
	{
	    return $this->tongTienDienMuaVao;
	}	
	
	
	public function calculateDienNangMuaVaoTheoKy()
	{
	    if(!$this->datasetChiTietMuaVao)
	    {
	        $this->setDatasetChiTietMuaVao();
	    }
	    
	    if(!$this->datasetChiTietBanRa)
	    {
	        $this->setDatasetChiTietBanRa();
	    }
	    
	    // echo '<pre>'; print_r($this->datasetChiTietBanRa); die;
	    
	    // Mua vao
	    foreach($this->datasetChiTietMuaVao as $item)
	    {
	        //if($item->DoiTuong == 1) // Chi tinh cho Quang Ninh
	        {
	            $ky              = $item->Ky?$item->Ky:1;
	            // Don gia
	            $CongToIOID       = @(int)$item->CongToIOID;
//				$giaDienChung     = round($this->getGiaMuaChung($CongToIOID));
//				$giaDienB1        = round($this->getGiaMuaB1($CongToIOID));
//				$giaDienB2        = round($this->getGiaMuaB2($CongToIOID));
//				$giaDienB3        = round($this->getGiaMuaB3($CongToIOID));
//
//				$item->TongSoCoTonHao     = round($item->TongSoCoTonHao);
//				$item->TongSoTrungBinhCoTonHao = round($item->TongSoTrungBinhCoTonHao);
//				$item->TongSoThapDiemCoTonHao  = round($item->TongSoThapDiemCoTonHao);
//				$item->TongSoCaoDiemCoTonHao   = round($item->TongSoCaoDiemCoTonHao);


	            $giaDienChung     = $this->getGiaMuaChung($CongToIOID);
	            $giaDienB1        = $this->getGiaMuaB1($CongToIOID);
	            $giaDienB2        = $this->getGiaMuaB2($CongToIOID);
	            $giaDienB3        = $this->getGiaMuaB3($CongToIOID);
	        
	            // Thanh tien
	            $tongGiaChung     = $giaDienChung * $item->TongSoCoTonHao;
	            $tongGiaTrungBinh = $giaDienB1 * $item->TongSoTrungBinhCoTonHao;
	            $tongGiaThapDiem  = $giaDienB3 * $item->TongSoThapDiemCoTonHao;
	            $tongGiaCaoDiem   = $giaDienB2 * $item->TongSoCaoDiemCoTonHao;
	            $tongBaGia        = $tongGiaTrungBinh + $tongGiaThapDiem + $tongGiaCaoDiem;
	        
	            // Tong ba so dien cong to ba gia
	            $tongBaSoDien     = $item->TongSoThapDiemCoTonHao + $item->TongSoTrungBinhCoTonHao + $item->TongSoCaoDiemCoTonHao;
	        
	            // So dien da nhan he so ton hao
	            $soDien           = ($item->LoaiGia == 1)?$item->TongSoCoTonHao:$tongBaSoDien;
	            
	            // So tien = tien ghi <da nhan he so ton hao> + tien phat cospi
	            $soTien           = (($item->LoaiGia == 1)?$tongGiaChung:$tongBaGia) + $item->TongTienPhatCosPi;
	            
	            
	            if($ky == 1)
	            {
	                $this->tongDienMuaVaoKy1 += $soDien;
	                $this->tongTienMuaVaoKy1 += $soTien;	                
	            }
	            elseif($ky == 2)
	            {
	                $this->tongDienMuaVaoKy2 += $soDien;
	                $this->tongTienMuaVaoKy2 += $soTien;	                
	            }
	            elseif($ky == 3)
	            {
	                $this->tongDienMuaVaoKy3 += $soDien;
	                $this->tongTienMuaVaoKy3 += $soTien;	                
	            }
	        }	        
	    }
	    
	    // Ban ra
	    foreach($this->datasetChiTietBanRa as $item)
	    {
	        if($item->DoiTuong == 1) // Chi tinh cho Quang Ninh
	        {
	            $ky              = $item->Ky?$item->Ky:1;
	            // Don gia
	            $CongToIOID       = @(int)$item->CongToIOID;
//				$giaDienChung     = round($this->getGiaBanChung($CongToIOID));
//				$giaDienB1        = round($this->getGiaBanB1($CongToIOID));
//				$giaDienB2        = round($this->getGiaBanB2($CongToIOID));
//				$giaDienB3        = round($this->getGiaBanB3($CongToIOID));
//
//				$item->TongSoCoTonHao     = round($item->TongSoCoTonHao);
//				$item->TongSoTrungBinhCoTonHao = round($item->TongSoTrungBinhCoTonHao);
//				$item->TongSoThapDiemCoTonHao  = round($item->TongSoThapDiemCoTonHao);
//				$item->TongSoCaoDiemCoTonHao   = round($item->TongSoCaoDiemCoTonHao);

	            $giaDienChung     = $this->getGiaBanChung($CongToIOID);
	            $giaDienB1        = $this->getGiaBanB1($CongToIOID);
	            $giaDienB2        = $this->getGiaBanB2($CongToIOID);
	            $giaDienB3        = $this->getGiaBanB3($CongToIOID);
	            
	            // Thanh tien
	            $tongGiaChung     = $giaDienChung * $item->TongSoCoTonHao;
	            $tongGiaTrungBinh = $giaDienB1 * $item->TongSoTrungBinhCoTonHao;
	            $tongGiaThapDiem  = $giaDienB3 * $item->TongSoThapDiemCoTonHao;
	            $tongGiaCaoDiem   = $giaDienB2 * $item->TongSoCaoDiemCoTonHao;
	            $tongBaGia        = $tongGiaTrungBinh + $tongGiaThapDiem + $tongGiaCaoDiem;
	            
	            // Tong ba so dien cong to ba gia
	            $tongBaSoDien     = $item->TongSoThapDiemCoTonHao + $item->TongSoTrungBinhCoTonHao + $item->TongSoCaoDiemCoTonHao;
	            
	            // So dien da nhan he so ton hao
	            $soDien           = ($item->LoaiGia == 1)?$item->TongSoCoTonHao:$tongBaSoDien;
	            
	            // So tien = tien ghi <da nhan he so ton hao> + tien phat cospi
	            $soTien           = (($item->LoaiGia == 1)?$tongGiaChung:$tongBaGia) + $item->TongTienPhatCosPi;
	            
	            if($ky == 1)
	            {
	                $this->tongDienMuaVaoKy1 -= $soDien;
	                $this->tongTienMuaVaoKy1 -= $soTien;
	            }
	            elseif($ky == 2)
	            {
	                $this->tongDienMuaVaoKy2 -= $soDien;
	                $this->tongTienMuaVaoKy2 -= $soTien;
	            }
	            elseif($ky == 3)
	            {
	                $this->tongDienMuaVaoKy3 -= $soDien;
	                $this->tongTienMuaVaoKy3 -= $soTien;
	            }	            
	        }
	        
	    }
	}
	
	
	public function getDienNangMuaVaoTheoKy($ky)
	{
	    if($ky == 1)
	    {
            return $this->tongDienMuaVaoKy1;
	    }
	    elseif($ky == 2)
	    {
            return $this->tongDienMuaVaoKy2;
	    }
	    elseif($ky == 3)
	    {
            return $this->tongDienMuaVaoKy3;
	    }	    
	}
	
	public function getTienMuaVaoTheoKy($ky)
	{
	    if($ky == 1)
	    {
	        return $this->tongTienMuaVaoKy1;
	    }
	    elseif($ky == 2)
	    {
	        return $this->tongTienMuaVaoKy2;
	    }
	    elseif($ky == 3)
	    {
	        return $this->tongTienMuaVaoKy3;
	    }	    
	}	
	
	/**
	 * Chech lech = Tong tien ban ra ko bao gom QN - (Tien con lai cho sd/dien con lai cho sd) * Tong dien ban ra ko bao gom QN
	 */
	public function calculateChechLechMuaBan()
	{
	    $dienBanRa              = $this->getTongDienBanRa();
	    $tienBanRa              = $this->getTongTienDienBanRa();
	    $tienConLaiChoSuDung    = $this->getTongTienDienQuangNinhConLaiChoSuDung();
	    $dienConLaiChoSuDung    = $this->getTongDienQuangNinhConLaiChoSuDung();
	    $this->chenhLechMuaBan  = $tienBanRa - (($tienConLaiChoSuDung / $dienConLaiChoSuDung) * $dienBanRa);
	}
	
	public function getChenhLechMuaBan()
	{
	    return $this->chenhLechMuaBan;
	}
	
	public function getTongTienBanTruChenhLech()
	{
	    return $this->getTongTienDienBanRa() - $this->getChenhLechMuaBan();
	}
	
	public function getDienConLaiSuDungCuoiCung()
	{
	    return $this->getTongDienMuaVao() - $this->getTongDienBanRa();
	}
	
	public function getTienConLaiSuDungCuoiCung()
	{
	    return $this->getTongTienDienMuaVao() - $this->getTongTienBanTruChenhLech();
	}
	
	public function getDonGia()
	{
	    $tienConLaiChoSuDung    = $this->getTongTienDienQuangNinhConLaiChoSuDung();
	    $dienConLaiChoSuDung    = $this->getTongDienQuangNinhConLaiChoSuDung();
	    
	    return $tienConLaiChoSuDung/$dienConLaiChoSuDung;
	}
	
	public function calculateSanLuongDonVi()
	{
	    foreach($this->donVi as $dept=>$item)
	    {
	        $muaVaSuDung     = isset($this->donViMuaVao[$dept])?$this->donViMuaVao[$dept]:0;
	        $banChoDonViKhac = isset($this->donViCapChoDonViKhac[$dept])?$this->donViCapChoDonViKhac[$dept]:0;
	        $sanluong        = $muaVaSuDung - $banChoDonViKhac; // San luong
	        
	        $this->sanLuongDonVi[$dept] = $sanluong;
	    }	    
	}
	
	public function calculateSanLuongNghiemThuDonVi()
	{
	    $temp                       = array();
	    $tongSanLuongChuaCongTonHao = 0;
	    
	    //echo '<pre>'; print_r($this->donViCapChoDonViKhac); die;
	    $tongSanLuongChuaCongTonHao = $this->getTongSanLuongDonVi();

	    foreach($this->donVi as $dept=>$item)
	    {
	        $muaVaSuDung     = isset($this->donViMuaVao[$dept])?$this->donViMuaVao[$dept]:0;
	        $banChoDonViKhac = isset($this->donViCapChoDonViKhac[$dept])?$this->donViCapChoDonViKhac[$dept]:0;
	        
	        $sanluong          = $muaVaSuDung - $banChoDonViKhac; // San luong
	        $tile              = $tongSanLuongChuaCongTonHao?($muaVaSuDung - $banChoDonViKhac)/$tongSanLuongChuaCongTonHao:0; // Ti le
	        
	        if($item['Code'] == 'MDV')
	        {
	            $tonhao = 0;
	        }
	        else 
	        {
	            $tonhao = $sanluong * $this->getHeSoTonHao();
	        }
	        
	        // ($this->getDienConLaiSuDungCuoiCung() - $tongSanLuongChuaCongTonHao) * $tile; // Ton hao
	        $sanLuongNghiemThu = $sanluong + $tonhao;
	        $thanhTien         = ($this->getDonGia() * $sanLuongNghiemThu);	        
	        
	        
	        $this->sanLuongNghiemThuDonVi[$dept] = $sanLuongNghiemThu;
	        $this->thanhTienDonVi[$dept]         = $thanhTien;
	        $this->tiLeTheoDonVi[$dept]          = $tile;
	        $this->tonHaoTheoDonVi[$dept]        = $tonhao;
        }
        //secho '<pre>'; print_r($this->donVi); die;
        
	}

    public function setDonVi($deptID, $DeptName, $DeptCode, $DeptLevel)
    {
        if(!isset($this->donVi[$deptID]) && $DeptCode != self::DEPARTMENT_CTY && $DeptCode != self::DEPARTMENT_VANTAI)
        {
            $this->donVi[$deptID]['Name']  = $DeptName;
            $this->donVi[$deptID]['Code']  = $DeptCode;
            $this->donVi[$deptID]['Level'] = $DeptLevel;
            $this->donVi[$deptID]['ID']    = $deptID;
        }
    }
	
	public function getDonVi()
	{
        Qss_Lib_Extra::array_sort_by_column($this->donVi, 'Level');
	    return $this->donVi;
	}
	
	public function getSanLuongNghiemThuDonVi()
	{
	    return $this->sanLuongNghiemThuDonVi;
	}
	
	public function getThanhTienDonVi()
	{
	    return $this->thanhTienDonVi;
	}	
	
    public function calculateTongDienBanRaTheoDonVi()
    {
        $temp = array();
        
        if(!$this->datasetChiTietBanRa)
        {
            $this->setDatasetChiTietBanRa();
        }
        
        if(!$this->datasetChiTietNoiBo)
        {
            $this->setDatasetChiTietNoiBo();
        }    

        if(!$this->datasetBanDotXuat)
        {
            $this->setDatasetBanRaDotXuat();
        }
         
        // Ban ra
        foreach($this->datasetChiTietBanRa as $item)
        {
//            if($item->CodeDonViNoiBo == 'CTY' || $item->CodeDonViNoiBo == 'VT')
//            {
//                continue;
//            }
            
            if(!isset($this->donViCapChoDonViKhac[$item->Ref_DonViBan]))
            {
                $this->donViCapChoDonViKhac[$item->Ref_DonViBan] = 0;
            }            
             
            // Tong ba so dien cong to ba gia
            $tongBaSoDien     = $item->TongSoThapDiemCoTonHao + $item->TongSoTrungBinhCoTonHao + $item->TongSoCaoDiemCoTonHao;
             
            // So dien da nhan he so ton hao
            $soDien           = ($item->LoaiGia == 1)?$item->TongSoCoTonHao:$tongBaSoDien;
            
            $this->donViCapChoDonViKhac[$item->Ref_DonViBan] += $soDien;

			$this->setDonVi($item->Ref_DonViBan, $item->NameDonViNoiBo, $item->CodeDonViNoiBo, $item->LevelDonViNoiBo);

            $this->setChiTietDonViBanRa(
                $item->Ref_DonViBan
                , $item->CongToIOID
                , $item->MaCongTo
                , $item->TenCongTo
                , $item->ChiSoDau
                , $item->ChiSoCuoi
                , $item->HeSo
                , $item->HeSoTonHao
                , $soDien);
        }
        
        // Noi bo
        foreach ($this->datasetChiTietNoiBo as $reading)
        {
            if($reading->Ref_DonViBan && ($reading->Ref_DonViMua != $reading->Ref_DonViBan))
            {
//                if($reading->CodeDonViBan == 'CTY' || $reading->CodeDonViBan == 'VT')
//                {
//                    continue;
//                }
                
                $soDien = $reading->TongSo;
                
                
                if(!isset($this->donViCapChoDonViKhac[$reading->Ref_DonViBan]))
                {
                    $this->donViCapChoDonViKhac[$reading->Ref_DonViBan] = 0;
                }
                
                $this->donViCapChoDonViKhac[$reading->Ref_DonViBan] += $soDien;


				$this->setDonVi($reading->Ref_DonViBan, $reading->NameDonViBan, $reading->CodeDonViBan, $reading->LevelDonViBan);

                $this->setChiTietDonViBanRa(
                    $reading->Ref_DonViBan
                    , $reading->CongToIOID
                    , $reading->MaCongTo
                    , $reading->TenCongTo
                    , $reading->ChiSoDau
                    , $reading->ChiSoCuoi
                    , $reading->HeSo
                    , $reading->HeSoTonHao
                    , $soDien);

//                $this->setDonVi($reading->Ref_DonViMua, $reading->NameDonViMuaNoiBo, $reading->CodeDonViMuaNoiBo, $reading->LevelDonViMuaNoiBo);
//
//                $this->setChiTietMuaVao(
//                    $reading->Ref_DonViMua
//                    , $reading->CongToIOID
//                    , $reading->MaCongTo
//                    , $reading->TenCongTo
//                    , $reading->ChiSoDau
//                    , $reading->ChiSoCuoi
//                    , $reading->HeSo
//                    , $reading->HeSoTonHao
//                    , $soDien);
            }
        }

        // Dot xuat
        foreach ($this->datasetBanDotXuat as $reading)
        {
            if($reading->Ref_DonViBan )
            {
//                if($reading->DeptCode == 'CTY' || $reading->DeptCode == 'VT')
//                {
//                    continue;
//                }
        
        
                if(!isset($this->donViCapChoDonViKhac[$reading->Ref_DonViBan]))
                {
                    $this->donViCapChoDonViKhac[$reading->Ref_DonViBan] = 0;
                }
        
                $this->donViCapChoDonViKhac[$reading->Ref_DonViBan] += $reading->SanLuong;

				$this->setDonVi($reading->Ref_DonViBan, $reading->DeptName, $reading->DeptCode, $reading->DeptLevel);
                
                $this->setChiTietDonViBanRa(
                    $reading->Ref_DonViBan
                    , 'CONGTO_'.$reading->Ref_DonViBan
                    , ' Bán đột xuất cho '. $reading->DonViMua
                    , ' Bán đột xuất cho '. $reading->DonViMua
                    , ''
                    , ''
                    , ''
                    , ''
                    , $reading->SanLuong);
            }
        }  
              
    }	
    
    // Doi voi tuyen 2 van phong tuyen 2 lay them 1/3 tu cong to PGD-CTD-06, cong to PGD-CTD-06 chi nhan 2/3 so luong dien
    public function calculateTongDienMuaVaoTheoDonVi()
    {
        $temp = array();
    
        if(!$this->datasetChiTietMuaVao)
        {
            $this->setDatasetChiTietMuaVao();
        }
    
        if(!$this->datasetChiTietNoiBo)
        {
            $this->setDatasetChiTietNoiBo();
        }
        
        if(!$this->datasetKhoanDienNang)
        {
            $this->setDatasetKhoanDienNang();
        }        
         
        // Chi tinh cho quang ninh
        foreach($this->datasetChiTietMuaVao as $item)
        {
            if($item->DoiTuong == 1)
            {
//                if($item->CodeDonViNoiBo == 'CTY' || $item->CodeDonViNoiBo == 'VT')
//                {
//                    continue;
//                }
                
                if(!isset($this->donViMuaVao[$item->Ref_DonViMua]))
                {
                    $this->donViMuaVao[$item->Ref_DonViMua] = 0;
                }
                 
                // Tong ba so dien cong to ba gia
                $tongBaSoDien     = $item->TongSoThapDiemCoTonHao + $item->TongSoTrungBinhCoTonHao + $item->TongSoCaoDiemCoTonHao;
                 
                // So dien da nhan he so ton hao
                $soDien           = ($item->LoaiGia == 1)?$item->TongSoCoTonHao:$tongBaSoDien;
        
                $this->donViMuaVao[$item->Ref_DonViMua] += $soDien;

				$this->setDonVi($item->Ref_DonViMua, $item->NameDonViNoiBo, $item->CodeDonViNoiBo, $item->LevelDonViNoiBo);

				$this->setChiTietMuaVao(
					$item->Ref_DonViMua
					, $item->CongToIOID
					, $item->MaCongTo
					, $item->TenCongTo
					, $item->ChiSoDau
					, $item->ChiSoCuoi
					, $item->HeSo
					, $item->HeSoTonHao
					, $soDien);
            }
        }        
    
        // Noi bo
        foreach ($this->datasetChiTietNoiBo as $reading)
        {
//            if($reading->CodeDonViMuaNoiBo == 'CTY' || $reading->CodeDonViMuaNoiBo == 'VT')
//            {
//                continue;
//            }
            
            
            if($reading->Ref_DonViMua)
            {
                $soDien = $reading->TongSo;
                
                if(!isset($this->donViMuaVao[$reading->Ref_DonViMua]))
                {
                    $this->donViMuaVao[$reading->Ref_DonViMua] = 0;
                }
                
                
                // Cong to PGD-CTD-06 nhan 2/3 so dien 1/3 thi tuyen hai nhan, them mot dong cho tuyen 2
                if($reading->MaCongTo == 'PGD-CTD-06')
                {
                    $soDienPGD_CTD_06Nhan         = 2/3 * $soDien;
                    $dienVPtuyen2AnTheoPGD_CTD_O6 = 1/3 * $soDien;
                    
                    
                    // Them don vi mua cua cong to PGD-CTD-06
					$this->setDonVi($reading->Ref_DonViMua, $reading->NameDonViMuaNoiBo, $reading->CodeDonViMuaNoiBo, $reading->LevelDonViMuaNoiBo);
                    
                    if(!isset($this->donViMuaVao[$reading->Ref_DonViMua]))
                    {
                        $this->donViMuaVao[$reading->Ref_DonViMua] = 0;
                    }
                    
                    $this->donViMuaVao[$reading->Ref_DonViMua] += $soDienPGD_CTD_06Nhan;


                    // PGD-CTD-06
					$this->setChiTietMuaVao(
						$reading->Ref_DonViMua
						, $reading->CongToIOID
						, $reading->MaCongTo
						, $reading->TenCongTo
						, $reading->ChiSoDau
						, $reading->ChiSoCuoi
						, $reading->HeSo
						, $reading->HeSoTonHao
						, $soDienPGD_CTD_06Nhan);


                    if(!isset($this->donViMuaVao[self::PX_TUYEN_2_DEPT_ID]))
                    {
                        $this->donViMuaVao[self::PX_TUYEN_2_DEPT_ID] = 0;
                    }
                    

                    $this->donViMuaVao[self::PX_TUYEN_2_DEPT_ID] += $dienVPtuyen2AnTheoPGD_CTD_O6;

                    $this->setDonVi(self::PX_TUYEN_2_DEPT_ID, self::PX_TUYEN_2_NAME, self::PX_TUYEN_2_CODE, self::PX_TUYEN_2_LEVEL);

                    // VP Tuyen 2 nhan
                    $this->setChiTietMuaVao(
                        self::PX_TUYEN_2_DEPT_ID
                        , self::TUYEN2_AN_THEO_PGD_CTD_06_ID
                        , 'VP tuyển 2 (Giám định bàn giao)'
                        , 'VP tuyển 2 (Giám định bàn giao)'
                        , ''
                        , ''
                        , ''
                        , ''
                        , $dienVPtuyen2AnTheoPGD_CTD_O6);
                }
                else // Cac cong to khac
                {
                    $this->donViMuaVao[$reading->Ref_DonViMua] += $soDien;

					$this->setDonVi($reading->Ref_DonViMua, $reading->NameDonViMuaNoiBo, $reading->CodeDonViMuaNoiBo, $reading->LevelDonViMuaNoiBo);

                    $this->setChiTietMuaVao(
                        $reading->Ref_DonViMua
                        , $reading->CongToIOID
                        , $reading->MaCongTo
                        , $reading->TenCongTo
                        , $reading->ChiSoDau
                        , $reading->ChiSoCuoi
                        , $reading->HeSo
                        , $reading->HeSoTonHao
                        , $soDien);
                }

            }
        }
        
        // Tinh cong mua khoan don vi duoc khoan (Khoang cong ban cho don vi ban cong o ham tinh toan ban cua don vi)
    }
    
    public function calculateKhoanDienNangChoDonVi()
    {
        if(!$this->datasetKhoanDienNang)
        {
            $this->setDatasetKhoanDienNang();
        }        
        
        $i = 0;
        
        //echo '<pre>'; print_r($this->datasetKhoanDienNang); die;
        
        foreach ($this->datasetKhoanDienNang as $item)
        {
            // Mua vao
            if($item->IDDonViDuocKhoan)
            {
        
                
                // Them vao cac don vi co su dung dien
				$this->setDonVi($item->IDDonViDuocKhoan, $item->TenDonViDuocKhoan, $item->MaDonViDuocKhoan, $item->LevelDonViDuocKhoan);

                // Cong san luong vao don vi mua vao
                if(!isset($this->donViMuaVao[$item->IDDonViDuocKhoan]))
                {
                    $this->donViMuaVao[$item->IDDonViDuocKhoan] = 0;
                }
                
                $this->donViMuaVao[$item->IDDonViDuocKhoan] += $item->SoDienNang;
                
                // Them mua vao chi tiet mua cua don vi
                $this->setChiTietMuaVao(
                    $item->IDDonViDuocKhoan
                    , 'KHOAN_'.$item->IDDonViDuocKhoan.'_'.++$i
                    , 'Khoán cho '. $item->ViTriKhoan
                    , 'Khoán cho '. $item->ViTriKhoan
                    , ''
                    , ''
                    , ''
                    , ''
                    , $item->SoDienNang);
            }
            
            // Ban ra
            if($item->IDDonViBan)
            {
                // Them vao cac don vi co su dung dien
				$this->setDonVi($item->IDDonViBan, $item->TenDonViBan, $item->MaDonViBan, $item->LevelDonViBan);

                // Cong vao tong ban ra cua don vi
                if(!isset($this->donViCapChoDonViKhac[$item->IDDonViBan]))
                {
                    $this->donViCapChoDonViKhac[$item->IDDonViBan] = 0;
                }
                
                $this->donViCapChoDonViKhac[$item->IDDonViBan] += $item->SoDienNang;
                
                
                // Them vao chi tiet ban ra cua don vi
                $this->setChiTietDonViBanRa(
                    $item->IDDonViBan
                    , 'CONGTO_'.$item->IDDonViBan.'_'.++$i
                    , 'Khoán cho '.$item->TenDonViDuocKhoan
                    , 'Khoán cho '.$item->TenDonViDuocKhoan
                    , ''
                    , ''
                    , ''
                    , ''
                    , $item->SoDienNang);
            }            
        }        
    }
    
        
    

    public function calculateTongSanLuongDonVi()
    {
        foreach($this->donVi as $dept=>$item)
        {
            $muaVaSuDung     = isset($this->donViMuaVao[$dept])?$this->donViMuaVao[$dept]:0;
            $banChoDonViKhac = isset($this->donViCapChoDonViKhac[$dept])?$this->donViCapChoDonViKhac[$dept]:0;
             
            $this->tongSanLuong += $muaVaSuDung - $banChoDonViKhac;
        }    
        
        $tongSanLuong = $this->getTongSanLuongDonVi();
        $sanLuong     = $this->getSanLuongDonVi();
        $tongDienMua  = $this->getTongDienMuaVao();
        $tongBan      = $this->getTongDienBanRa();
        
        
//         // Tru di may dich vu
//         foreach($this->donVi as $deptID=>$item)
//         {
//             if($item['Code'] == 'MDV')
//             {
//                 if(isset($this->tongSanLuong[$deptID]))
//                 {
//                     $this->tongSanLuong = $this->tongSanLuong - $sanLuong[$deptID];
//                 }
//             }
//         }        
    }    
    
    public function calculateHeSoTonHao()
    {
        $tongSanLuong = $this->getTongSanLuongDonVi();
        $tongSanLuongKoCoMayDichVu = $this->getTongSanLuongDonVi();
        $sanLuong     = $this->getSanLuongDonVi();
        $conLaiChoSD  = $this->getDienConLaiSuDungCuoiCung();
        $tongBan      = $this->getTongDienBanRa();
        
        
        // Tru di may dich vu
        foreach($this->donVi as $deptID=>$item)
        {
            if($item['Code'] == 'MDV')
            {
                if(isset($sanLuong[$deptID]))
                {
                    $tongSanLuongKoCoMayDichVu = $tongSanLuongKoCoMayDichVu - $sanLuong[$deptID];
                }
                
            }
        }
        $chenLech = $conLaiChoSD - $tongSanLuong;
        
        $this->heSoTonHao = $chenLech/$tongSanLuongKoCoMayDichVu;
        
        
    }
    
    public function setTitleDonVi()
    {
        $sanLuongNghiemThu = $this->getSanLuongNghiemThuDonVi();
        $thanhTienDonVi    = $this->getThanhTienDonVi();
        $donVi             = $this->getDonVi();
        $chiTietBanRa      = $this->getChiTietDonViBanRa();
        $chiTietMuaVao     = $this->getChiTietDonViMuaVao();
        $donViMuaVao       = $this->getDonViMuaVao();
        $donViBanRa        = $this->getDonViCapChoDonViKhac();
        $sanLuong          = $this->getSanLuongDonVi();
        $tonHao            = $this->getTonHaoTheoDonVi();
        
        foreach($donVi as $deptID=>$item)
        {
            $sanLuongNT         = isset($sanLuongNghiemThu[$deptID])?$sanLuongNghiemThu[$deptID]:0;
            $sanLuongNTTemp     = Qss_Lib_Util::formatInteger($sanLuongNT);
            $chiTietBanRaDonVi  = isset($chiTietBanRa[$deptID])?$chiTietBanRa[$deptID]:array();
            $chiTietMuaVaoDonVi = isset($chiTietMuaVao[$deptID])?$chiTietMuaVao[$deptID]:array();
            $tongMua            = isset($donViMuaVao[$deptID])?$donViMuaVao[$deptID]:0;
            $tongMua            = Qss_Lib_Util::formatInteger($tongMua);
            $tongBan            = isset($donViBanRa[$deptID])?$donViBanRa[$deptID]:0;
            $tongBan            = Qss_Lib_Util::formatInteger($tongBan);
            $sanLuongDonVi      = isset($sanLuong[$deptID])?$sanLuong[$deptID]:0;
            $sanLuongDonVi      = Qss_Lib_Util::formatInteger($sanLuongDonVi);
            $tonHaoDonVi        = isset($tonHao[$deptID])?$tonHao[$deptID]:0;
            $tonHaoDonViTemp    = Qss_Lib_Util::formatInteger($tonHaoDonVi);
        
            $temp[$deptID]['Code']   = $item['Code'];
            $temp[$deptID]['Name']   = $item['Name'];
            $this->titleDonVi[$deptID]  = '';
            
            $this->titleDonVi[$deptID] .= "I. Mua: {$tongMua}   \n";
        
            $ctmvIndex = 0;
            foreach($chiTietMuaVaoDonVi as $congToMua)
            {
                foreach($congToMua as $ctmv)
                {
                    ++$ctmvIndex;
        
                    $ctmv['TongSo'] = Qss_Lib_Util::formatInteger($ctmv['TongSo']);
        
                    $this->titleDonVi[$deptID] .= "  {$ctmvIndex}. {$ctmv['MaCongTo']}: {$ctmv['TongSo']} ";
                    $this->titleDonVi[$deptID] .= "  \n";
                    
//                     if($ctmvIndex%2 == 0)
//                     {
//                         $this->titleDonVi[$deptID] .= " \n";
//                     }
                }
            }
            
            $this->titleDonVi[$deptID] .= "II. Bán: {$tongBan}   \n";
    
            $ctbrIndex = 0;
            foreach($chiTietBanRaDonVi as $congToBan)
            {
                foreach($congToBan as $ctbr)
                {
                    ++$ctbrIndex;
            
                    $ctbr['TongSo'] = Qss_Lib_Util::formatInteger($ctbr['TongSo']);
            
                    $this->titleDonVi[$deptID] .= "  {$ctbrIndex}. {$ctbr['MaCongTo']}: {$ctbr['TongSo']} ";
                    $this->titleDonVi[$deptID] .= "  \n";
                    
//                     if($ctbrIndex%2 == 0)
//                     {
//                         $this->titleDonVi[$deptID] .= "  \n";
//                     }
                }
            }
            
            $this->titleDonVi[$deptID] .= "III. Sản lượng: {$sanLuongDonVi} \n";
            $this->titleDonVi[$deptID] .= "IV. Tổn hao: {$tonHaoDonViTemp} \n";
            $this->titleDonVi[$deptID] .= "V. Sản lượng nghiệm thu: {$sanLuongNTTemp}  \n";
            
            //echo $this->titleDonVi[$deptID]; die;
        }        
    }
    
    public function getTitleDonVi()
    {
        return $this->titleDonVi;
    }
    
    public function getHeSoTonHao()
    {
        return $this->heSoTonHao;
    }
    
    public function getTongSanLuongDonVi()
    {
        return $this->tongSanLuong?$this->tongSanLuong:0;
    }

    public function setChiTietMuaVao(
        $refDonVi
        , $refCongTo
        , $maCongTo = ''
        , $tenCongTo = ''
        , $chiSoDau = ''
        , $chiSoCuoi = ''
        , $heSo = ''
        , $heSoTonHao = ''
        , $tongSo = 0)
    {
        $this->chiTietDonViMuaVao[$refDonVi][$refCongTo][] = array(
            'IDCongTo'   => $refCongTo,
            'IDDonVi'    => $refDonVi,
            'MaCongTo'   =>  $maCongTo,
            'TenCongTo'  =>  $tenCongTo,
            'ChiSoDau'   =>  $chiSoDau,
            'ChiSoCuoi'  =>  $chiSoCuoi,
            'HeSo'       =>  $heSo,
            'HeSoTonHao' =>  $heSoTonHao,
            'TongSo'     =>  $tongSo
        );
    }
    
    public function getChiTietDonViMuaVao()
    {
        return $this->chiTietDonViMuaVao;
    }
    
    public function getDonViCapChoDonViKhac()
    {
        return $this->donViCapChoDonViKhac;
    }
    
    public function getTiLeTheoDonVi()
    {
        return $this->tiLeTheoDonVi;
    }
    
    public function getSanLuongDonVi()
    {
        return $this->sanLuongDonVi;
    }
    
    public function getTonHaoTheoDonVi()
    {
        return $this->tonHaoTheoDonVi;
    }
    
    public function getDienMuaTuDonViKhac()
    {
        return $this->dienMuaTuDonViKhac;
    }

    public function setChiTietDonViBanRa(
        $refDonVi
        , $refCongTo
        , $maCongTo = ''
        , $tenCongTo = ''
        , $chiSoDau = ''
        , $chiSoCuoi = ''
        , $heSo = ''
        , $heSoTonHao = ''
        , $tongSo = 0)
    {
        $this->chiTietDonViBanRa[$refDonVi][$refCongTo][] = array(
            'MaCongTo'   =>  $maCongTo,
            'TenCongTo'  =>  $tenCongTo,
            'ChiSoDau'   =>  $chiSoDau,
            'ChiSoCuoi'  =>  $chiSoCuoi,
            'HeSo'       =>  $heSo,
            'HeSoTonHao' =>  $heSoTonHao,
            'TongSo'     =>  $tongSo
        );
    }
    
    public function getChiTietDonViBanRa()
    {
        return $this->chiTietDonViBanRa;
    }
    
    public function getDonViMuaVao()
    {
        return $this->donViMuaVao;
    }


}
?>	