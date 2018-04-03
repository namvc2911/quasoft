<?php
class Qss_View_Report_ComboBox extends Qss_View_Abstract 
{
    public function __doExecute ( $id, 
    							$getFields, 
    							$getLookupFilter = array(), 
                                $excludeObject = array(),
                                $required = false,
                                $attributes = 'style="width:180px"',
                                $selectall = true,
								$selectedValue = 0)
    {
       // **********************************************************************
       // === Neu id ko phai la array thi no la $id, neu la array thi no bao gom
       // === thong so ben duoi la $id va inputSize, require
       // === $id (array name: id), ma va id (css) cua o listbox, dung de xu ly js
       // === $inputSize (array name: size): do rong the input, mac dinh la 180
       // === require (array name: require): 1/0, require chi hien them span
       // === ko check require khi submit 
       // **********************************************************************
        
       // *****************************************************************
       
       // ***************************************************************** 
        

       // **********************************************************************
       // === $getFields, chua cac truong lam id, cac truong hien thi cua tung 
       // === object lay du lieu va chua so luong truong hien thi chung
       // === St: 
       // ===       [num] => N
       // ===       [objects]
       // ===            [objectKey]=> (many)
       // ===                   [id] => field
       // ===                   [field1] => field
       // ===                   ...
       // ===                   [fieldN] => field  
	   // ===					[where] sql
	   // ===					[order] sql
	   // ===					[group] sql
	   // ===					[limit] sql     
       // **********************************************************************
        
       // **********************************************************************
       // === $getLookupFilter, chua cac du lieu loc theo mot truong lookup
       // === sang mot object khac tu object lay du lieu
       // === St:  array
       // ===       [index] (many)
       // ===           [id]=> id (css) cua field lookup
       // ===           [refField]=> 
       // ===                   [getDataObjectKey] => field (many)
       // ===           [required] => 0/1, true/false (field required?)      
       // **********************************************************************       

        
       // **********************************************************************
       // === $excludeObject, chua cac object khong lay data
       // === vao tu listbox cua cac object lay data
       // === St: array( objectKey, ..., objectKey)
       // **********************************************************************
        

    }
}

?>
<?php
/* #====== old version (call) ======#
 <?php echo $this->views->Report->ComboBox('department', 'M319', 'OPhongBan', 'IOID', array('MaPhongBan', 'TenPhongBan') );?>
 */

/* #====== old version ======#
class Qss_View_Report_ComboBox extends Qss_View_Abstract
{

	public function __doExecute ($name,$module,$object, $key,$display, $deptid=0,$order = null)
	{
		$model = new Qss_Model_Query();
		$this->html->data = $model->getViewData($module,$object, $deptid,$order);
	}
}
*/
?>