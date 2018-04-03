<?php
/**
 * 
 * @author HuyBD
 *
 */
class Qss_Lib_Const
{
//    public static $WEEKDAY_BITWISE = array(
//        0 => 2
//        , 1 => 4
//        , 2 => 8
//        , 3 => 16
//        , 4 => 32
//        , 5 => 64
//        , 6 => 1
//    );

    public static $WEEKDAY_BITWISE = array(
        0 => 1
        , 1 => 2
        , 2 => 4
        , 3 => 8
        , 4 => 16
        , 5 => 32
        , 6 => 64
    );




	/**
	 * 
	 * @var array of data table 
	 */
	public static $DATABASE_TABLES = array("datshortdesc", 
											"datmediumdesc", 
											"datlongdesc", 
											"dattime", 
											"datint", 
											"datfloat", 
											"datboolean", 
											"datfile", 
											"datpicture", 
											"datdate", 
											"datmoney", 
											"datmail", 
											"datshortdesc", 
											"qsdepartments",
											"qscurrencies",
											"qsusers");

	/**
	 * 
	 * @var array of data table 
	 */
	public static $FIELD_TYPE = array(1=>"Short Description"
									,2=>"Medium Description"
									,3=>"Long Description"
									,4=>"Time"
									,5=>"Integer"
									,6=>"Float"
									,7=>"Boolean"
									,8=>"File"
									,9=>"Picture"
									,10=>"Date"
									,11=>"Money"
									,12=>"Mail"
									,13=>"BarCode"
									,14=>"Company"
									,15=>"Currency"
									,16=>"User"
									,17=>"Display"
									,18=>"Datetime");
	
	/**
	 * 
	 * @var array of data table 
	 */
	public static $FIELD_INPUT = array(1=>"Textbox"
									,2=>"Editor"
									,3=>"Combobox"
									,4=>"Listbox"
									,5=>"Radio"
									,6=>"Picture"
									,7=>"File"
									,8=>"Date"
									,9=>"CheckBox"
									,10=>"Mail"
									,11=>"Popup select"
									,12=>"Calculate"
									,13=>"Display"
									,14=>"Datetime");//12=>"Attribute Code(Product)"
	
	/**
	 * 1:Done owner 2: done other 3: Owner 4: reference 5: share read 6: share write 7: lock
	 * 1:onwer can not delete 2: other cannot delete 3: onwer can delete 4: other can delete 5: share read 6: share write 7: lock
	 * c u r d 
	 * @var array
	 */
	//public static $USER_RIGHTS = array(1 => 1495, 2 => 1303, 3 => 2047, 4 => 1301, 5 => 1301, 6 => 1303, 7 => 5);
	//public static $USER_RIGHTS = array(1 => 55, 2 => 19, 3 => 63, 4 => 19, 5 => 18, 6 => 22, 7 => 3);
	//1: Create 2: Read 3: Moddify 4: Delete 5: Control 6:  Owner
	const FORM_TYPE_PUBLIC_LIST = 1;
	const FORM_TYPE_PRIVATE_LIST = 2;
	const FORM_TYPE_MODULE = 3;
	const FORM_TYPE_REPORT = 4;
	const FORM_TYPE_PROCESS = 5;
	
	const USER_TYPE_SYSTEM_ADMIN = 1;

	const USER_TYPE_ADMIN = 2;

	const USER_TYPE_DESIGNER = 4;

	const USER_TYPE_USER = 8;

	const USER_TYPE_EXTEND_USER = 16;

	const FORM_DESIGN_FORM = '/forms/';

	const FORM_DESIGN_OBJECT = '/objects/';

	const TINY_PLUGIN_MODULE = 'module';
	
	const TINY_PLUGIN_OBJECT = 'object';
	const TINY_PLUGIN_ELEMENT = 'element';
	const TINY_PLUGIN_MEDIA = 'media';
	const TINY_PLUGIN_IMAGE = 'image';
	const TINY_PLUGIN_FILE = 'file';
	
	public static $SHARING_STATUS = array('Đang làm','Hoàn thành','Từ chối','Chưa nhận');
	public static $SHARING_COLOR = array('blue','green','red','orange');
	public static $FIELD_ALIGN = array("left", 
								"left", 
								"left", 
								"left", 
								"center", 
								"center", 
								"center", 
								"center", 
								"center", 
								"center", 
								"center", 
								"left", 
								"center", 
								"left", 
								"center", 
								"center", 
								"center");
	public static $EXCEL_COLUMN = array('A'=>1,
								'B'=>2,
								'C'=>3,
								'D'=>4,
								'E'=>5,
								'F'=>6,
								'G'=>7,
								'H'=>8,
								'I'=>9,
								'J'=>10,
								'K'=>11,
								'L'=>12,
								'M'=>13,
								'N'=>14,
								'O'=>15,
								'P'=>16,
								'Q'=>17,
								'R'=>18,
								'S'=>19,
								'T'=>20,
								'U'=>21,
								'V'=>22,
								'W'=>23,
								'X'=>24,
								'Y'=>25,
								'Z'=>26,
								'AA'=>27,
								'AB'=>28,
								'AC'=>29,
								'AD'=>30,
								'AE'=>31,
								'AF'=>32,
								'AG'=>33,
								'AH'=>34,
								'AI'=>35,
								'AJ'=>36,
								'AK'=>37,
								'AL'=>38,
								'AM'=>39,
								'AN'=>40,
								'AO'=>41,
								'AP'=>42,
								'AQ'=>43,
								'AR'=>44,
								'AS'=>45,
								'AT'=>46,
								'AU'=>47,
								'AV'=>48,
								'AW'=>49,
								'AX'=>50,
								'AY'=>51,
								'AZ'=>52,
								'BA'=>53,
								'BB'=>54,
								'BC'=>55,
								'BD'=>56,
								'BE'=>57,
								'BF'=>58,
								'BG'=>59,
								'BH'=>60,
								'BI'=>61,
								'BJ'=>62,
								'BK'=>63,
								'BL'=>64,
								'BM'=>65,
								'BN'=>66,
								'BO'=>67,
								'BP'=>68,
								'BQ'=>69,
								'BR'=>70,
								'BS'=>71,
								'BT'=>72,
								'BU'=>73,
								'BV'=>74,
								'BW'=>75,
								'BX'=>76,
								'BY'=>77,
								'BZ'=>78,
								'CA'=>79,
								'CB'=>80,
								'CC'=>81,
								'CD'=>82,
								'CE'=>83,
								'CF'=>84,
								'CG'=>85,
								'CH'=>86,
								'CI'=>87,
								'CJ'=>88,
								'CK'=>89,
								'CL'=>90,
								'CM'=>91,
								'CN'=>92,
								'CO'=>93,
								'CP'=>94,
								'CQ'=>95,
								'CR'=>96,
								'CS'=>97,
								'CT'=>98,
								'CU'=>99,
								'CV'=>100,
								'CW'=>101,
								'CX'=>102,
								'CY'=>103,
								'CZ'=>104,
								'DA'=>105,
								'DB'=>106,
								'DC'=>107,
								'DD'=>108,
								'DE'=>109,
								'DF'=>110,
								'DG'=>111,
								'DH'=>112,
								'DI'=>113,
								'DJ'=>114,
								'DK'=>115,
								'DL'=>116);
	public static function getIDField($fieldtype)
	{
		switch($fieldtype)
		{
			case 1: 
			case 2: 
			case 3: 
			case 4: 
			case 5: 
			case 6: 
			case 7: 
			case 8: 
			case 9: 
			case 10: 
			case 11: 
			case 12: 
			case 13: 
				return 'ID';
				break;
			case 14: 
				return 'DepartmentID';
				break;
			case 15: 
				return 'CID';
				break;
			case 15: 
				return 'UID';
				break;
		}
	}
	public static function getDataField($fieldtype)
	{
		switch($fieldtype)
		{
			case 1: 
			case 2: 
			case 3: 
			case 4: 
			case 5: 
			case 6: 
			case 7: 
			case 8: 
			case 9: 
			case 10: 
			case 11: 
			case 12: 
			case 13: 
				return 'Data';
				break;
			case 14: 
				return 'Name';
				break;
			case 15: 
				return 'Code';
				break;
			case 15: 
				return 'UserName';
				break;
		}
	}
	public static $TK19 = array(
	0x30baa3, 0x56ab50, 0x422ba0, 0x2cab61, 0x52a370, 0x3c51e8, 0x60d160, 0x4ae4b0, 0x376926, 0x58daa0,
	0x445b50, 0x3116d2, 0x562ae0, 0x3ea2e0, 0x28e2d2, 0x4ec950, 0x38d556, 0x5cb520, 0x46b690, 0x325da4,
	0x5855d0, 0x4225d0, 0x2ca5b3, 0x52a2b0, 0x3da8b7, 0x60a950, 0x4ab4a0, 0x35b2a5, 0x5aad50, 0x4455b0,
	0x302b74, 0x562570, 0x4052f9, 0x6452b0, 0x4e6950, 0x386d56, 0x5e5aa0, 0x46ab50, 0x3256d4, 0x584ae0,
	0x42a570, 0x2d4553, 0x50d2a0, 0x3be8a7, 0x60d550, 0x4a5aa0, 0x34ada5, 0x5a95d0, 0x464ae0, 0x2eaab4,
	0x54a4d0, 0x3ed2b8, 0x64b290, 0x4cb550, 0x385757, 0x5e2da0, 0x4895d0, 0x324d75, 0x5849b0, 0x42a4b0,
	0x2da4b3, 0x506a90, 0x3aad98, 0x606b50, 0x4c2b60, 0x359365, 0x5a9370, 0x464970, 0x306964, 0x52e4a0,
	0x3cea6a, 0x62da90, 0x4e5ad0, 0x392ad6, 0x5e2ae0, 0x4892e0, 0x32cad5, 0x56c950, 0x40d4a0, 0x2bd4a3,
	0x50b690, 0x3a57a7, 0x6055b0, 0x4c25d0, 0x3695b5, 0x5a92b0, 0x44a950, 0x2ed954, 0x54b4a0, 0x3cb550,
	0x286b52, 0x4e55b0, 0x3a2776, 0x5e2570, 0x4852b0, 0x32aaa5, 0x56e950, 0x406aa0, 0x2abaa3, 0x50ab50
	); /* Years 2000-2099 */

	public static $TK20 = array(
	0x3c4bd8, 0x624ae0, 0x4ca570, 0x3854d5, 0x5cd260, 0x44d950, 0x315554, 0x5656a0, 0x409ad0, 0x2a55d2,
	0x504ae0, 0x3aa5b6, 0x60a4d0, 0x48d250, 0x33d255, 0x58b540, 0x42d6a0, 0x2cada2, 0x5295b0, 0x3f4977,
	0x644970, 0x4ca4b0, 0x36b4b5, 0x5c6a50, 0x466d50, 0x312b54, 0x562b60, 0x409570, 0x2c52f2, 0x504970,
	0x3a6566, 0x5ed4a0, 0x48ea50, 0x336a95, 0x585ad0, 0x442b60, 0x2f86e3, 0x5292e0, 0x3dc8d7, 0x62c950,
	0x4cd4a0, 0x35d8a6, 0x5ab550, 0x4656a0, 0x31a5b4, 0x5625d0, 0x4092d0, 0x2ad2b2, 0x50a950, 0x38b557,
	0x5e6ca0, 0x48b550, 0x355355, 0x584da0, 0x42a5b0, 0x2f4573, 0x5452b0, 0x3ca9a8, 0x60e950, 0x4c6aa0,
	0x36aea6, 0x5aab50, 0x464b60, 0x30aae4, 0x56a570, 0x405260, 0x28f263, 0x4ed940, 0x38db47, 0x5cd6a0,
	0x4896d0, 0x344dd5, 0x5a4ad0, 0x42a4d0, 0x2cd4b4, 0x52b250, 0x3cd558, 0x60b540, 0x4ab5a0, 0x3755a6,
	0x5c95b0, 0x4649b0, 0x30a974, 0x56a4b0, 0x40aa50, 0x29aa52, 0x4e6d20, 0x39ad47, 0x5eab60, 0x489370,
	0x344af5, 0x5a4970, 0x4464b0, 0x2c74a3, 0x50ea50, 0x3d6a58, 0x6256a0, 0x4aaad0, 0x3696d5, 0x5c92e0
	); /* Years 1900-1999 */

	public static $TK21 = array(
	0x46c960, 0x2ed954, 0x54d4a0, 0x3eda50, 0x2a7552, 0x4e56a0, 0x38a7a7, 0x5ea5d0, 0x4a92b0, 0x32aab5,
	0x58a950, 0x42b4a0, 0x2cbaa4, 0x50ad50, 0x3c55d9, 0x624ba0, 0x4ca5b0, 0x375176, 0x5c5270, 0x466930,
	0x307934, 0x546aa0, 0x3ead50, 0x2a5b52, 0x504b60, 0x38a6e6, 0x5ea4e0, 0x48d260, 0x32ea65, 0x56d520,
	0x40daa0, 0x2d56a3, 0x5256d0, 0x3c4afb, 0x6249d0, 0x4ca4d0, 0x37d0b6, 0x5ab250, 0x44b520, 0x2edd25,
	0x54b5a0, 0x3e55d0, 0x2a55b2, 0x5049b0, 0x3aa577, 0x5ea4b0, 0x48aa50, 0x33b255, 0x586d20, 0x40ad60,
	0x2d4b63, 0x525370, 0x3e49e8, 0x60c970, 0x4c54b0, 0x3768a6, 0x5ada50, 0x445aa0, 0x2fa6a4, 0x54aad0,
	0x4052e0, 0x28d2e3, 0x4ec950, 0x38d557, 0x5ed4a0, 0x46d950, 0x325d55, 0x5856a0, 0x42a6d0, 0x2c55d4,
	0x5252b0, 0x3ca9b8, 0x62a930, 0x4ab490, 0x34b6a6, 0x5aad50, 0x4655a0, 0x2eab64, 0x54a570, 0x4052b0,
	0x2ab173, 0x4e6930, 0x386b37, 0x5e6aa0, 0x48ad50, 0x332ad5, 0x582b60, 0x42a570, 0x2e52e4, 0x50d160,
	0x3ae958, 0x60d520, 0x4ada90, 0x355aa6, 0x5a56d0, 0x462ae0, 0x30a9d4, 0x54a2d0, 0x3ed150, 0x28e952
	); /* Years 2000-2099 */

	public static $TK22 = array(
	0x4eb520, 0x38d727, 0x5eada0, 0x4a55b0, 0x362db5, 0x5a45b0, 0x44a2b0, 0x2eb2b4, 0x54a950, 0x3cb559,
	0x626b20, 0x4cad50, 0x385766, 0x5c5370, 0x484570, 0x326574, 0x5852b0, 0x406950, 0x2a7953, 0x505aa0,
	0x3baaa7, 0x5ea6d0, 0x4a4ae0, 0x35a2e5, 0x5aa550, 0x42d2a0, 0x2de2a4, 0x52d550, 0x3e5abb, 0x6256a0,
	0x4c96d0, 0x3949b6, 0x5e4ab0, 0x46a8d0, 0x30d4b5, 0x56b290, 0x40b550, 0x2a6d52, 0x504da0, 0x3b9567,
	0x609570, 0x4a49b0, 0x34a975, 0x5a64b0, 0x446a90, 0x2cba94, 0x526b50, 0x3e2b60, 0x28ab61, 0x4c9570,
	0x384ae6, 0x5cd160, 0x46e4a0, 0x2eed25, 0x54da90, 0x405b50, 0x2c36d3, 0x502ae0, 0x3a93d7, 0x6092d0,
	0x4ac950, 0x32d556, 0x58b4a0, 0x42b690, 0x2e5d94, 0x5255b0, 0x3e25fa, 0x6425b0, 0x4e92b0, 0x36aab6,
	0x5c6950, 0x4674a0, 0x31b2a5, 0x54ad50, 0x4055a0, 0x2aab73, 0x522570, 0x3a5377, 0x6052b0, 0x4a6950,
	0x346d56, 0x585aa0, 0x42ab50, 0x2e56d4, 0x544ae0, 0x3ca570, 0x2864d2, 0x4cd260, 0x36eaa6, 0x5ad550,
	0x465aa0, 0x30ada5, 0x5695d0, 0x404ad0, 0x2aa9b3, 0x50a4d0, 0x3ad2b7, 0x5eb250, 0x48b540, 0x33d556
	); /* Years 2100-2199 */

	public static $THANG = array("Tháng Giêng", "Tháng Hai", "Tháng Ba", "Tháng Tư", "Tháng Năm", "Tháng Sáu", "Tháng Bảy", "Tháng Tám", "Tháng Chín", "Tháng Mười", "Tháng Một", "Tháng Chạp");
	public static $TUAN = array("Thứ hai", "Thứ ba", "Thứ tư", "Thứ năm", "Thứ sáu", "Thứ bảy","Chủ nhật");
	
	const FIELD_REG_AUTO = 'auto';
	const FIELD_REG_REFERENCE = 'reference';
	const FIELD_REG_LINK = 'link';
	const FIELD_REG_PARENT = 'parent';
	const FIELD_REG_RECALCULATE = 'recalculate';
	
	const FORM_RIGHTS_CREATE 	= 1;
	const FORM_RIGHTS_READ 		= 2;
	const FORM_RIGHTS_UPDATE 	= 4;
	const FORM_RIGHTS_DELETE 	= 8;
	const FORM_RIGHTS_SUPPER 	= 16;
	
	const FIELD_RIGHTS_READ 		= 1;
	const FIELD_RIGHTS_UPDATE 	= 2;
	const FIELD_RIGHTS_REQUIRED 	= 4;
	
	const MYSQL_COLLATION = ' COLLATE utf8_bin';
	const DATE_FORMAT = 'd-m-Y';
}
?>