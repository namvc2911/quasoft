<?php

class Qss_Lib_Extra_Const
{
    // Tinh khau hao - depreciation
    const DEPRECIATION_STRAIGHT_LINE     = 1;
    const DEPRECIATION_UNITS_OF_ACTIVITY = 2;
    const DEPRECIATION_DECLINING_BALANCE = 3;
    
    // Limit
    public static $DATE_LIMIT     = array('D' => 1000, 'W' => 24, 'M' => 24, 'Q' => 24, 'Y' => 5);
    public static $SCHEDULE_LIMIT = array('D'=>7); // One week;
    public static $EQUIP_STATUS_STOP = array(1,2,3);
    public static $EQUIP_STATUS_STOP_STR = '1,2,3';
    
    // Phan loai ngung hoat dong cua thiet bi
    const EQUIP_STOP_TYPE_SALE   = 1; // ban
    const EQUIP_STOP_TYPE_LOST   = 2; // mat
    const EQUIP_STOP_TYPE_BREAK  = 3; // hong (hong su co voi pos)
    const EQUIP_STOP_TYPE_EXPIRE = 4; // hong (hong tự nhiên voi pos)
    
	// Phan loai kho
	const WAREHOUSE_TYPE_PRODUCTION = 'SANXUAT';
	const WAREHOUSE_TYPE_MATERIAL   = 'VATTU';
	const WAREHOUSE_TYPE_DRAFT      = 'PHELIEU';
	
	// Yeu cau bao tri
	const MAINT_REQUIRE_TYPE_BREAK  = 1; // hong (hong su co voi pos)
	const MAINT_REQUIRE_TYPE_LOST   = 2; // mat
    const MAINT_REQUIRE_TYPE_EXPIRE = 3; // hong (hong tu nhien voi pos)
	
	
	// Module
	const PRODUCTION_ORDER_MODULE_CODE = 'M710';
	
	// Phieu bao tri (Khong su dung nua)
// 	const REVIEW_TYPE_NONE      = 0; // Không chọn
// 	const REVIEW_TYPE_WEAK      = 1; // Yếu
// 	const REVIEW_TYPE_AVERAGE   = 2; // Trung bình
// 	const REVIEW_TYPE_GOOD      = 3; // Khá
// 	const REVIEW_TYPE_EXCELLENT = 4; // Tốt
	
	// Phan loai bao tri 
	const MAINT_TYPE_BREAKDOWN   = 'B';
	const MAINT_TYPE_CALIBRATION = 'CA';
	
	const MAINT_TYPE_PREVENTIVE   = 'P';
	const MAINT_TYPE_CORRECTIVE = 'C';
	const MAINT_TYPE_REFURBISHMENT = 'R';
	const MAINT_TYPE_INVESTMENT = 'I';
	const MAINT_TYPE_NONE_PERIODIC = 'K';
	
	
	// Loai yeu cau bao tri
	const MAINTAIN_REQUEST_BREAK =1;
	const MAINTAIN_REQUEST_LOST = 2;	
	
	// Phan loai nhap kho
	const INPUT_TYPE_PURCHASE 	= 'MUAHANG';
	const INPUT_TYPE_RETURN 	= 'TRALAI';
	const INPUT_TYPE_SCRAP 		= 'NHAPXAC';
	const INPUT_TYPE_MOVEMENT 	= 'CHUYENKHO';
	const INPUT_TYPE_OTHER    	= 'NHAPKHAC';
	
	// Phan loai xuat kho
	const OUTPUT_TYPE_MAINTAIN 			= 'BAOTRI';
	const OUTPUT_TYPE_SCRAP 			= 'XUATXAC';
	const OUTPUT_TYPE_MOVEMENT         	= 'CHUYENKHO';
	const OUTPUT_TYPE_SALE             	= 'XUATBAN';
	const OUTPUT_TYPE_OTHER            	= 'XUATKHAC';
    const OUTPUT_TYPE_DIRECT            = 'XUATTHANG';
	
	// Phan loai ky
	const PERIOD_TYPE_DAILY          = 'D';
	const PERIOD_TYPE_WEEKLY         = 'W';
	const PERIOD_TYPE_MONTHLY        = 'M';
	const PERIOD_TYPE_QUARTERLY      = 'Q';
	const PERIOD_TYPE_YEARLY         = 'Y';
	const PERIOD_TYPE_NON_RE_CURRING = 'S';

    // Can cu bao tri
    const CAUSE_PERIOD = 0;
    const CAUSE_PARAM  = 1;
    const CAUSE_BOTH   = 2;
	
	// Hinh thuc in an
	const PRINT_TYPE_PORTRAIT  = 'PORTRAIT';
	const PRINT_TYPE_LANDSCAPE = 'LANDSCAPE';
	
	// Do rong hinh thuc in an tinh bang px
	const PORTRAIT_WIDTH  = 830; // PX HTML
	const LANDSCAPE_WIDTH = 1150;// PX HTML
	
	// Do rong hinh thuc in an tinh theo don vi tinh xls - 2003
	const PORTRAIT_WIDTH_EXCEL  = 95;
	const LANDSCAPE_WIDTH_EXCEL = 145;
	
	// Chuyen doi px sang excel (xls - 2003)
	const RATE_EXCEL = 8.43;// 64px = 8.43 excel
	const RATE_PX    = 64;// 64px = 8.43 excel
    
    // Excel
    const REPORT_ORIENTATION_PORTRAIT  = 'portrait';
    const REPORT_ORIENTATION_LANDSCAPE = 'landsacpe';
    const REPORT_EXCEL_FONT_SIZE       = 9;
    const REPORT_EXCEL_FONT_NAME       = 'Arial';
    const REPORT_EXCEL_MARGIN_LEFT     = 0.5;
    const REPORT_EXCEL_MARGIN_RIGHT    = 0.5;
    const REPORT_EXCEL_MARGIN_TOP      = 0.5;
    const REPORT_EXCEL_MARGIN_BOTTOM   = 0.5;
    const REPORT_EXCEL_TITLE_FONT_SIZE = 12;
    const REPORT_EXCEL_THEAD_COLOR     = '#EEEEEE';
	
	// Item
	const DEFAULT_LOT_LENGTH    = 6;
	const DEFAULT_SERIAL_LENGTH = 6;
	
	// FID
	const WORK_ORDER_FID        = 'M759';
	const PURCHASE_REQUEST_FID  = 'M716';
	const MATERIAL_REQUEST_FID  = 'M709';
	
	// FIELDS
	const REVIEW_FIELD_IN_WORK_ORDER          = 'NguoiThucHien';
	const REVIEW_FIELD_IN_TASKS_OF_WORK_ORDER = 'NguoiThucHien';
	
	// Permission
	const ALL_PERMISSION        = 2;
	
	// Lang
	// Trung voi csdl, vd: Name cua step hien tai ghi bang tieng viet
	const DEFAULT_LANG          = 'vn';
    
    
	

}
