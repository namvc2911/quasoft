<?php
class Qss_View_Report_ListBox extends Qss_View_Abstract
{
    public function __doExecute ( 
		$id
		, $getDataObject
		, $getFields 
		, $getLookupFilter = array()
		, $excludeObject = array()
		, $extend = ''
		, $selectedText = ''
		, $selectedValue = 0
        , $placeHolder = '')
    {
       // **********************************************************************
       // === Neu id ko phai la array thi no la $id, neu la array thi no bao gom
       // === thong so ben duoi la $id va inputSize, require
       // === $id (array name: id), ma va id (css) cua o listbox, dung de xu ly js
       // === $inputSize (array name: size): do rong the input, mac dinh la 180
       // === require (array name: require): 1/0, require chi hien them span
       // === ko check require khi submit 
       // **********************************************************************
        
        
       // **********************************************************************
       // === $getDataObject, chua cac truong so sanh tim kiem voi du lieu nhap
       // === vao tu listbox cua cac object lay data
       // === St: array
       // ===       [ObjectKey] (many)
       // ===            [fieldKey]
       // ===            [fieldKey]           
       // **********************************************************************
        
        
       // **********************************************************************
       // === $getFields, chua cac truong lam id, cac truong hien thi cua tung 
       // === object lay du lieu va chua so luong truong hien thi chung
       // === St: 
       // ===       [num] => N
       // ===       [objects]
       // ===            [objectKey]=> (many)
       // ===                   [id] => field
       // ===                   [order]=>field
       // ===                   [limit]=>number
       // ===                   [field1] => field
       // ===                   ...
       // ===                   [fieldN] => field        
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