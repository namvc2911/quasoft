<?php
/**
 * Model for instanse form
 *
 */
class Qss_Model_IReport_DanhSachThietBi extends Qss_Model_IReport_Abstract implements Qss_Lib_IReport
{
	public $name = 'Danh sách thiết bị';
	
	public $view = 'DanhSachThietBi';
    
    //public $objectcode = 'ODanhSachThietBi';
	
	public $columns = array();
	
	public $widths = array();
    
    public $fieldtypes = array();

    public function __construct()
    {
        parent::__construct();

        $fields = Qss_Lib_System::getFieldsByObject('M705',  'ODanhSachThietBi');
        $this->columns = array();
        $this->widths = array();
        $this->fieldtypes = array();


        foreach ($fields->loadFields() as $item)
        {
            $this->columns[$item->FieldCode]    = $item->szFieldName;
            $this->widths[$item->FieldCode]     = $item->intFieldWidth;
            $this->fieldtypes[$item->FieldCode] = $item->intFieldType;
        }
    }

    public function __doExecute()
	{
		$where = '';
		$where .= $this->getLocationFilter('location','OKhuVuc');
		$where .= $this->getTypeFilter('type','OLoaiThietBi');
        $where .= $this->getNormalFilter('equip', 'IOID', 'ODanhSachThietBi');
		$sql = sprintf('select ODanhSachThietBi.* from ODanhSachThietBi 
					inner join OLoaiThietBi on OLoaiThietBi.IOID = Ref_LoaiThietBi
					left join OKhuVuc on OKhuVuc.IOID = ODanhSachThietBi.Ref_MaKhuVuc
					where 1=1 %1$s',
				$where);
		$sql = $this->getSQL($sql);
        $dataSql = $this->_o_DB->fetchAll($sql);
        //echo '<pre>'; print_r($dataSql); die;
        
        $trangThai = Qss_Lib_System::getFieldRegx('ODanhSachThietBi', 'TrangThai');
        
        
        foreach($dataSql as $dat)
        {
            $dat->TrangThai = ( $dat->TrangThai && isset($trangThai[$dat->TrangThai]) )?$trangThai[$dat->TrangThai]:'';
        }
        
		return $dataSql;
	}
}
?>