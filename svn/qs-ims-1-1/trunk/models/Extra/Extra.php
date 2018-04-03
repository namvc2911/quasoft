<?php

class Qss_Model_Extra_Extra extends Qss_Model_Abstract
{
    public $joinType = array('inner join', 'left join', 'right join');

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $strTable
     * @param $arrFieldList
     * @param $strDat
     * @param array $arrOrderBy
     * @return mixed
     */
    public function getDataLikeString($strTable, $arrFieldList, $strDat, $arrOrderBy = array()) {
        $where = '';
        $order = '';

        if(count($arrFieldList)) {
            foreach($arrFieldList as $field) {
                $where .= $where?' OR ':'';
                $where .= $strDat?sprintf(' %1$s like \'%%%2$s%%\' ', $field, $strDat):'';
            }
        }

        if(count($arrOrderBy)) {
            foreach($arrOrderBy as $field) {
                $order .= $order?' , ':'';
                $order .= $field;
            }
        }

        $where = $where?' WHERE '.$where:'';
        $order = $order?' ORDER BY '.$order:'';
        $sql   = sprintf('SELECT * FROM %1$s %2$s %3$s LIMIT 50', $strTable, $where, $order);
        return $this->_o_DB->fetchAll($sql);
    }

    public function getTableFetchOne($table, $where = array(), $customField = array('*')){
       return $this->getTable($customField, $table, $where, array(), 'NO_LIMIT',  1);
    }

    public function getTableFetchAll($table, $where = array(), $customField = array('*'), $customOrder = array()){
       return $this->getTable($customField, $table, $where, $customOrder, 100000,  2);
    }

    public function countRows($table, $where = array()) {
        return $this->getTable(1, $table, $where, array(), 'NO_LIMIT',  1);
    }

    /**
     * @param mixed $field [array('field1', ..., 'fieldN') | field1, ..., fieldN]
     * @param string $table table name
     * @param mixed $where [array('column1'=>'val1', ..., 'columnN'=>'valN') | column1 = val1 and ... collumnN = valN]
     * @param mixed $order [array('column1 ASC|DESC|NULL', ..., 'columnN') | column1, column2 ASC,..., columnN DESC]
     * @param mixed $limit [array('display'=>number, 'page'=>number) | number | string (no limit)]
     * @param int $returnType [1 => fetchOne | 2 => fetchAll | 3 => print sql]
     * @return object
     */
    public function getTable(
        $field      = array(),
        $table      = '',
        $where      = array(),
        $order      = array(),
        $limit      = 1000,
        $returnType = 2
    ) {
        $count  = false; // Kiểm tra xem có đếm số bản ghi không để chuyển sang trả về số lượng bản ghi
        $sWhere = '';
        $sLimit = '';

        if($field == 1) {
            $field = ' count(1) AS Total ';
            $count = true;
        }
        else {
            $field = (is_array($field))? (count($field) ? implode(',', $field) : ' * ') : ($field? $field: ' * ');
        }

        if(is_array($where)) {
            foreach ($where as $col => $val) {
                $sWhere .= $sWhere?' and ':'';
                $sWhere .= (is_numeric($val) ? " {$col} = {$val} " : " {$col} = '{$val}' ");
            }
        }
        else {
            $sWhere = $where?" {$where} ":'';
        }

        if($returnType != 1) {
            if(is_array($limit)) {
                $display = isset($limit['display'])?@(int)$limit['display']:0;
                $page    = isset($limit['page'])?@(int)$limit['page']:0;
                $start   = ceil(($page - 1) * $display);
                $sLimit   = " limit {$start}, {$display}";
            }
            elseif(is_numeric($limit)) {
                $sLimit = " limit {$limit}";
            }
        }

        $sWhere = $sWhere?" where {$sWhere} ":"";
        $order  = (is_array($order))? (count($order) ? sprintf(' order by %1$s ',implode(',', $order)) : '') : ($order?' order by '.$order:'');
        $sql    = sprintf(' select %1$s from %2$s %3$s %4$s %5$s ', $field, $table, $sWhere, $order, $sLimit);

        if($returnType == 3) {
            return $sql;
        }
        else {
            if($count) {
                $dat = $this->_o_DB->fetchOne($sql);
                return $dat?$dat->Total:0;
            }
            else {
                if($returnType == 2) {
                    return $this->_o_DB->fetchAll($sql);
                }
                elseif ($returnType == 1) {
                    return $this->_o_DB->fetchOne($sql);
                }
            }
        }
    }


    /// @todo: Sửa code bỏ hàm này
    /// *Function: getObjectByIDArr - lay doi tuong phu hoac chinh cua mot object
    // $idType = 1 => IOID, 2 => IFID
    // $module = array(code=>, table=>, main=>)
    // Neu ko co main nghia la tron tu table, neu co main tuc la table theo main
    public function getObjectByIDArr($idArr, $module, $idType = 1, $extIdAlias = '', $extWhere = '')
    {

            // Neu khong co mang id thi return null
            if (!is_array($idArr))
            {
                    if ($idArr == '')
                    {
                            return;
                    }
                    $temp = $idArr;
                    $idArr = array();
                    $idArr[] = $temp;
            }
            else
            {
                    if (!count($idArr))
                            return;
            }

            // Filter theo IOID hay IFID
            $ifid = sprintf('IFID_%1$s', $module['code']);
            //$idFilterColumn = ($idType == 1)?'IOID':$ifid;
            switch ($idType)
            {
                    case '1':
                            $idFilterColumn = 'IOID';
                            break;
                    case '2':
                            $idFilterColumn = $ifid;
                            break;
                    case '3':
                            $idFilterColumn = $extIdAlias;
                            break;
            }
            $idFilter = '';

            // Join main if has
            $joinMain = '';
            $filterAlias = $module['table'];
            if (isset($module['main']) && $module['main'])
            {
                    $joinMain .= sprintf(' inner join %1$s on %1$s.%2$s = %3$s.%2$s ',
                            $module['main'], $ifid, $module['table']);
                    $filterAlias = $module['main'];
            }

            // Lay gia tri filter
            foreach ($idArr as $id)
            {
                    $idFilter .= $idFilter ? ' or ' : '';
                    $idFilter .= sprintf(' %3$s.%1$s = %2$s ',
                            $idFilterColumn, $this->_o_DB->quote($id),
                            $filterAlias);
            }

            $sql = sprintf('select %1$s.*
                                    from %1$s %3$s
                                    where %2$s %4$s', $module['table'], $idFilter, $joinMain, $extWhere);
            return $this->_o_DB->fetchAll($sql);
    }

    /**
     * Lay cac bang co dung nested set model chia cap
     * @param $view
     * @param string $where
     * @param bool $inDept
     * @return mixed
     */
    public function getNestedSetTable($view, $where = '', $inDept = true)
    {
        $sWhere = '';
        if(is_array($where)){
            foreach ($where as $k=>$w) {
                $sWhere .= $sWhere?' and ':'';
                $sWhere .= $k . ' = ' . $this->_o_DB->quote($w);
            }
        }
        else {
            $sWhere = $where;
        }

        if($inDept){
            $sWhere .= ($sWhere?' and ':' ').sprintf(' DeptID in (%1$s) ', $this->_user->user_dept_list);
        }

        $sWhere   = $sWhere?" and {$sWhere} ":"";

        $sql = sprintf('
			SELECT * , (SELECT count(*) FROM %1$s AS u WHERE u.lft <= v.lft AND u.rgt >= v.rgt %2$s) AS LEVEL
			FROM %1$s AS v 
			WHERE 1=1 %2$s
			ORDER BY lft
			LIMIT 5000'
            , $view, $sWhere);
        // echo '<Pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }

    // @todo: Sửa bỏ hàm này
    /**
     * @param $table
     * @param $field
     * @return mixed
     */
    public function checkFieldExists($table, $field){
        return $this->_o_DB->fetchOne(sprintf('SHOW COLUMNS FROM  `%1$s` LIKE  \'%2$s\'', $table, $field));
    }

    /**
     * @todo: Sửa bỏ hàm này
     * Thay the ham getTable()
     * @note luu y xu ly ham ifnull trong select hoac where + khong viet cac ham non standard  khac vao select va where
     * @param array $filter
     * @param mixed $filter['select'], lay cac truong, chi hoat dong neu ko dem cac ban ghi
     * @param string $filter['IFIDField'], (TRUONG BAT BUOC), truong ifid cua module
     * @param string $filter['module'], (TRUONG BAT BUOC), bang lay comment
     * @param string $filter['countType'], kieu dem, dem het hay dem theo tung dong, neu dem theo tung dong
     * group theo ifid va chon kieu dem = 1 hoac bo trong (kieu dem tung dong la kieu mac dinh)
     * @param mix $filter['join'], join voi cac bang
     *      array
     *      0 => ['joinType'] => left join, right join, inner join
     *              ['joinTable'] => table
     *              ['joinAlias'] => alias of table
     *              ['joinCondition'] => dieu kien join
     *                      array
     *                      0 => ['matchType'] => or, and
     *                              ['joinAlias1'] => alias
     *                              ['joinCol1'] => column
     *                              ['joinAlias2'] => alias
     *                              ['joinCol1'] => column
     *    or
     *   string
     * @param int $filter['location'] ioid cua khu vuc giup loc khu vuc, yeu cau co $filter['EQField']
     * @param int $filter['locLeft'] left cua khu vuc giup loc khu vuc, yeu cau co $filter['EQField']
     * @param int $filter['locRight'] right cua khu vuc giup loc khu vuc, yeu cau co $filter['EQField']
     * @param int $filter['EQField']  truong ioid hoac ref cua thiet bi
     * @param date $filter['date']  loc theo ngay cu the dang yyyy-mm-dd, yeu cau co $filter['dateField']
     * @param date $filter['startDate']  loc theo ngay bat dau, between start and end or >= start , yeu cau co $filter['dateField']
     * @param date $filter['endDate']  loc theo ngay ket thuc, between start and end or <= end ,yeu cau co $filter['dateField']
     * @param mix $filter['where']  key=>val, and key = val; hoac mot chuoi where
     * @param mix $filter['group'] group by
     * @param mix $limit
     * @return object, tra ve comment
     *
     */
    public function getDataset($filter, $limit = 8000, $return = 2)
    {
            // Cac filter bat buoc
            // >> $filter['IFIDField'], $filter['module']

            // Khoi tao cac bien
            $select   = ''; // lua chon
            $where  = ''; // loc
            $join     = ''; // ghep bang
            $group   = ''; // nhom du lieu
            $order  = ''; // sap xep du lieu
            $limitsql   = ''; // gioi han so ban ghi // co the dung de phan trang sau nay
            $count    = (isset($filter['count']) &&  $filter['count'])?true:false;

            // neu chan ko muon tach rieng tinh chat bien thi co the dung no trong bien filter
            if(isset($filter['return']))
            {
                    $return = $filter['return'];
            }

             if(isset($filter['limit']))
            {
                    $limit = $filter['limit'];
            }

            // Dem so ban ghi
            // >> De dem so comment theo dong them group by theo ifid  - filter['group'], chon filter['countType'] =
            // >> 1 hoac bo trong,  them $filter['IFIDField']
            // Neu khong dem so ban ghi lay so ban ghi theo $filter['select']
            // >> Mac dinh lay toan bo bang comment
            if($count)
            {
                    $select = 'count(1) as Total';

                    if(isset($filter['IFIDField']) &&  $filter['IFIDField'])
                    {
                            // dem theo tung dong yeu cau phai co them cot IFID cua module $filter['IFIDField']
                            $select .= sprintf(', cm.%1$s as IFID ', $filter['IFIDField']);
                    }
            }

            if(isset($filter['select']) )
            {
                    if(is_array($filter['select']) && count($filter['select']))
                    {
                            $select = $select?$select.', ':'';
                            $select .= implode(', ', $filter['select']);
                    }
                    elseif(is_string($filter['select']) && ($filter['select']))
                    {
                            $select = $select?$select.', ':'';
                            $select  .= $filter['select'];
                    }
            }

            if(isset($filter['nestedset']) && $filter['nestedset'])
            {
                    $select = $select?$select.', ':'';
                    $select  .=
                            sprintf('(
                                SELECT
                                        count(*)
                                FROM
                                        %1$s AS u
                                WHERE
                                        u.lft <= cm.lft
                                AND u.rgt >= cm.rgt
                                ) AS LEVEL', $filter['module']);

                    $order = $order?$order.', ':'';
                    $order .= ' cm.lft';
            }

            // Neu ko co select tra ve select mac dinh
            if(!$select)
            {
                    $select = 'cm.*';
            }

            // join
            if(isset($filter['join']) )
            {
                    $temp = '';
                    if(is_array($filter['join']) && count($filter['join']))
                    {
                    foreach ($filter['join'] as $joinCondition)
                    {
                            // Xu ly cho cac goi ham xu dung key cu
                            $joinCondition['joinType'] = isset($joinCondition['joinType'])?$joinCondition['joinType']:$joinCondition['type'];
                            $joinCondition['joinTable'] = isset($joinCondition['joinTable'])?$joinCondition['joinTable']:$joinCondition['table'];
                            $joinCondition['joinAlias']  = isset($joinCondition['joinAlias'])?$joinCondition['joinAlias']:$joinCondition['alias'];
                            $joinCondition['joinCondition'] = isset($joinCondition['joinCondition'])?$joinCondition['joinCondition']:$joinCondition['condition'];

                            $temp .= sprintf(' %1$s %2$s as %3$s ON ', $this->joinType[$joinCondition['joinType']]
                                                                        , $joinCondition['joinTable']
                                                                        , $joinCondition['joinAlias']);


                            foreach ($joinCondition['joinCondition'] as $joinElement)
                            {
                                    // Xu ly cho cac goi ham xu dung key cu
                                    $joinElement['joinCol1'] = isset($joinElement['joinCol1'])?$joinElement['joinCol1']:$joinElement['col1'];
                                    $joinElement['joinAlias1'] = isset($joinElement['joinAlias1'])?$joinElement['joinAlias1']:@$joinElement['alias1'];
                                    $joinElement['joinCol2'] = isset($joinElement['joinCol2'])?$joinElement['joinCol2']:$joinElement['col2'];
                                    $joinElement['joinAlias2'] = isset($joinElement['joinAlias2'])?$joinElement['joinAlias2']:@$joinElement['alias2'];
                                    $joinElement['matchType']  = isset($joinElement['matchType'])?$joinElement['matchType']:@$joinElement['match'];

                                    // Xu ly alias va col
                                    $joinElementJoinAlias1= ($joinElement['joinAlias1'])?$joinElement['joinAlias1'].'.':'';
                                    $joinElementJoinAlias2= ($joinElement['joinAlias2'])?$joinElement['joinAlias2'].'.':'';

                                    $temp .= sprintf(' %1$s %5$s%2$s = %3$s%4$s'
                                            , @$joinElement['matchType'], $joinElement['joinCol1']
                                            , $joinElementJoinAlias2, $joinElement['joinCol2']
                                            , $joinElementJoinAlias1);
                            }
                    }
                    }
                    elseif(is_string($filter['join']) && ($filter['join']))
                    {
                            $temp = $filter['join'];
                    }
                    $join = $temp;
            }

            // Loc theo khu vuc ioid hoac left right cua khu vuc
            // Chi xuat hien khi lay may moc
            // >> loc theo khu vuc yeu cau truong IOID hoac ref cua thiet bi ($filter['EQField']) -- required if filter by location
            if(isset($filter['location']) && $filter['location'])
            {
                    $locTemp1 = sprintf('select * from OKhuVuc where IOID = %1$d LIMIT 1000', $filter['location']);
                    $locTemp2 = $this->_o_DB->fetchOne($locTemp1);
                    $where    .= sprintf('  %3$s in (select IOID from ODanhSachThietBi where Ref_MaKhuVuc
                                            in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))',
                                                    $locTemp2->lft, $locTemp2->rgt, $filter['EQField']);
            }

            if(!isset($filter['location'])
                    && (isset($filter['locLeft']) && $filter['locLeft'])
                    && (isset($filter['locRight']) && $filter['locRight']))
            {
                    $where    .= sprintf('  %3$s in (select IOID from ODanhSachThietBi where Ref_MaKhuVuc
                                            in (select IOID from OKhuVuc where lft>= %1$d and  rgt <= %2$d))',
                                                    $filter['locLeft'], $filter['locRight'], $filter['EQField']);
            }


            // Loc theo mot ngay cu the (YYYY-mm-dd)
            // >> Yeu cau truong ngay thang $filter['dateField'] -- required if filter by date
            if(isset($filter['date']) && $filter['date'])
            {
                    $where   .= $where?' and ':'';
                    $where    .= sprintf('  %1$s = %2$s', $filter['dateField'] ,$this->_o_DB->quote($filter['date']) );
            }

            // Loc theo mot khoang thoi gian (YYYY-mm-dd)
            // >> Yeu cau truong ngay thang $filter['dateField'] -- required if filter by date
            if((isset($filter['startDate']) && $filter['startDate']) && (isset($filter['endDate']) && $filter['endDate']))
            {
                    $where   .= $where?' and ':'';
                    $where    .= sprintf('  (%1$s between %2$s and %3$s)', $filter['dateField']
                            ,$this->_o_DB->quote($filter['startDate'])
                            ,$this->_o_DB->quote($filter['endDate']));
            }
            elseif ((isset($filter['startDate']) && $filter['startDate']))
            {
                    $where   .= $where?' and ':'';
                     $where    .= sprintf('  (%1$s >= %2$s)', $filter['dateField']
                            ,$this->_o_DB->quote($filter['startDate']));
            }
            elseif((isset($filter['endDate']) && $filter['endDate']))
            {
                    $where   .= $where?' and ':'';
                    $where    .= sprintf('  (%1$s <= %2$s)', $filter['dateField']
                            ,$this->_o_DB->quote($filter['endDate']));
            }


            // Loc theo mot dieu kien khac
            if(isset($filter['where']))
            {
                    if(is_array($filter['where']) && count($filter['where']))
                    {
                            $temp = '';
                            $where   .= $where?' and ':'';
                            foreach ($filter['where'] as $key => $value)
                            {
                                    $temp .= $temp?' and ':'';
                                    $key    = trim($key);
                                    if(preg_match('/([^\(\)0-9a-zA-Z_\.\,])/',$key))
                                    {
                                            $key2 = sprintf('%1$s ', $key);
                                    }
                                    else
                                    {
                                            $key2 = sprintf('%1$s =', $key);
                                    }
                                    $temp .= sprintf('  %1$s  %2$s', $key2,$this->_o_DB->quote($value) );
                            }
                            $where .= $temp;
                    }
                    elseif(is_string($filter['where']) && ($filter['where']))
                    {
                            $where   .= $where?' and ':'';
                            $where  .= $filter['where'];
                    }
            }

            // set dieu kien loc
            $where = $where?sprintf(' WHERE %1$s', $where):'';

            if(isset($filter['group']))
            {
                    // Dang 1: cot => sap xep vd cot1=>ASC
                    if(is_array($filter['group']) && count($filter['group']))
                    {
                            $group = '';
                            foreach ($filter['group'] as $col=>$ordertype)
                            {
                                    $group .= $temp?',':'';
                                    $group .= sprintf(' %1$s %2$s ', $col, $ordertype);
                            }
                    }
                    else if(is_string($filter['group']) && ($filter['group']))
                    {
                            $group = $filter['group'];
                    }
            }
            $group = ($group)?sprintf(' GROUP BY %1$s', $group):'';


            if(isset($filter['order']))
            {
                    // Dang 1: cot => sap xep vd cot1=>ASC
                    if(is_array($filter['order']) && count($filter['order']))
                    {
                            $order = $order?$order.',':'';
                            foreach ($filter['order'] as $col=>$ordertype)
                            {
                                    $order .= $temp?',':'';
                                    $order .= sprintf(' %1$s %2$s ', $col, $ordertype);
                            }
                    }
                    else if(is_string($filter['order']) && ($filter['order']))
                    {
                            $order = $order?$order.',':'';
                            $order = $filter['order'];
                    }
            }
            $order = ($order)?sprintf(' ORDER BY %1$s', $order):'';


            // gioi han so ban ghi
            if(is_array($limit))
            {
                    $display = isset($limit['display'])?@(int)$limit['display']:0;
                    $page    = isset($limit['page'])?@(int)$limit['page']:0;
                    $start    = ceil(($page - 1) * $display);
                    $limitsql = " limit {$start}, {$display}";
            }
            elseif(is_numeric($limit))
            {
                    $limitsql = " limit {$limit}";
            }
            else
            {
                    $limitsql = '';
            }

            if($count && $return == 1)
            {
                    $limitsql = '';
            }


            $sql = sprintf('
                        select %1$s
                        from  %2$s  as cm
                        %3$s
                        %4$s
                        %5$s
                        %6$s
                        %7$s'
                    , $select
                    , $filter['module'], $join
                    , $where
                    , $group
                    , $order
                    , $limitsql
                    );


            if(isset($filter['debug']))
            {
                    echo '<pre>'; print_r($filter);
                    echo '<pre>'; echo $sql;
                    die;
            }

            if($return == 1)
            {
                    if(isset($filter['debug2']))
                    {
                        echo '<pre>';
                        print_r($this->_o_DB->fetchOne($sql));
                        die;
                    }
                    // chi tra ve tong so ban ghi khi co dem, tra ve mot dong va ko phai la cong don lai
                    if($count && (!isset($filter['countType'])  || $filter['countType'] = 2))
                    {
                            $dataSql = $this->_o_DB->fetchOne($sql);
                            return $dataSql?$dataSql->Total:0;
                    }
                    return $this->_o_DB->fetchOne($sql);
            }
            elseif($return == 2)
            {
                    if(isset($filter['debug2']))
                    {
                        echo '<pre>';
                        print_r($this->_o_DB->fetchAll($sql));
                        die;
                    }
                    return $this->_o_DB->fetchAll($sql);
            }
            else
            {
                    return $sql;
            }
    }

    /**
     * lay buoc hien tai cua ban ghi
     * @todo: Sửa bỏ hàm này
     * @param $module
     * @param $object
     * @param $ifid
     * @return int
     */
    public function getStatusByIFID($module, $object, $ifid)
    {
        $sql = sprintf('select iform.Status
                                    from %1$s as ds
                                    inner join qsiforms as iform on ds.IFID_%2$s
                                    where IFID_%2$s = %3$d', $object, $module, $ifid);
            $dataSql = $this->_o_DB->fetchOne($sql);
            return $dataSql ? $dataSql->Status : 0;
    }

    // @todo: Sửa bỏ hàm này
    /// *Function: getFieldsByObject - lay tat ca field cua mot object
    public function getFieldsByObject($object)
    {
            $sql = sprintf('SELECT f.*,CONVERT( CAST( f.FieldName AS BINARY ) USING utf8 )   as FieldName1

                        FROM `qsfields` as f
                        inner join qsobjects as o on f.ObjectCode = o.ObjectCode
                        where o.ObjectCode = %1$s
                        and ifnull(f.Effect, 0) = 1
                        GROUP BY f.FieldCode
                        order by FieldNo',
                    $this->_o_DB->quote($object));
            return $this->_o_DB->fetchAll($sql);
    }
    // End getFieldsByObject

    // @todo: Sửa bỏ hàm này
    /// *Function: getObjectsByForm -lay tat ca object duoc gan vao form
    public function getObjectsByForm($formCode)
    {
            $sql = sprintf('
                        SELECT o.*, CONVERT( CAST( o.ObjectName AS BINARY ) USING utf8 )   as ObjectName1
                        FROM qsforms as f
                        inner join qsfobjects as fo on f.FormCode = fo.FormCode
                        inner join qsobjects as o on fo.ObjectCode = o.ObjectCode
                        where f.FormCode = %1$s
                        and ifnull(f.Effected, 0) = 1
                        and ifnull(fo.Public, 0) = 0
                        ORDER BY fo.ObjNo',
                    $this->_o_DB->quote($formCode));
            return $this->_o_DB->fetchAll($sql);
    }
    // End checkObjectAttachForm

    // ***********************************************************************************************
    // *** Cac ham ve xu ly thoi gian
    // ***********************************************************************************************

    /// *Function: getWorkingHoursPerWeekdays - lay thoi gian lam viec cua tung ca theo lich lam viec
    /// @Return: working hours by weekdays (not include special calendar)
    public function getWorkingHoursPerWeekdays($calendar)
    {
            $tmp = '';
            $comma = 0;
            for ($i = 0; $i < 7; $i++)
            {
                    if ($comma)
                    {
                            $tmp .= ',';
                    }
                    else
                    {
                            $comma = 1;
                    }
                    $tmp .= '(';
                    $start = 0;
                    for ($j = 1; $j <= 4; $j++)
                    {
                            if ($start)
                            {
                                    $tmp .= ' + ';
                            }
                            else
                            {
                                    $start = 1;
                            }
                            $tmp .= "(TIME_TO_SEC(ifnull(TIMEDIFF(c{$i}{$j}.GioKetThuc, c{$i}{$j}.GioBatDau),0))/3600)";
                    }
                    $tmp .= ") as Ngay{$i} ";


                    ///
                    for ($j = 1; $j <= 4; $j++)
                    {

                            $tmp .= ", (TIME_TO_SEC(ifnull(TIMEDIFF(c{$i}{$j}.GioKetThuc, c{$i}{$j}.GioBatDau),0))/3600) as Ngay{$i}_Ca{$j}";
                            $tmp .= ", llvn{$i}.Ref_Shift{$j} as Ngay{$i}_RefCa{$j}";
                    }
            }
            $tmp .= ($tmp) ? ' , llv.IOID as LLVIOID ' : ' 0 as LLVIOID '; // :' null ';
            $sql = sprintf('select
                                    %1$s
                                    from OLichLamViec as llv
                                    left join OLichLamViecNgay as llvn1
                                    on llv.Ref_ThuHai = llvn1.IOID
                                            left join OCa as c11
                                            on  c11.IOID = llvn1.Ref_Shift1
                                            left join OCa as c12
                                            on  c12.IOID = llvn1.Ref_Shift2
                                            left join OCa as c13
                                            on  c13.IOID = llvn1.Ref_Shift3
                                            left join OCa as c14
                                            on  c14.IOID = llvn1.Ref_Shift4
                                    left join OLichLamViecNgay as llvn2
                                    on llv.Ref_ThuBa = llvn2.IOID
                                            left join OCa as c21
                                            on  c21.IOID = llvn2.Ref_Shift1
                                            left join OCa as c22
                                            on  c22.IOID = llvn2.Ref_Shift2
                                            left join OCa as c23
                                            on  c23.IOID = llvn2.Ref_Shift3
                                            left join OCa as c24
                                            on  c24.IOID = llvn2.Ref_Shift4
                                    left join OLichLamViecNgay as llvn3
                                    on llv.Ref_ThuTu = llvn3.IOID
                                            left join OCa as c31
                                            on  c31.IOID = llvn3.Ref_Shift1
                                            left join OCa as c32
                                            on  c32.IOID = llvn3.Ref_Shift2
                                            left join OCa as c33
                                            on  c33.IOID = llvn3.Ref_Shift3
                                            left join OCa as c34
                                            on  c34.IOID = llvn3.Ref_Shift4
                                    left join OLichLamViecNgay as llvn4
                                    on llv.Ref_ThuNam = llvn4.IOID
                                            left join OCa as c41
                                            on  c41.IOID = llvn4.Ref_Shift1
                                            left join OCa as c42
                                            on  c42.IOID = llvn4.Ref_Shift2
                                            left join OCa as c43
                                            on  c43.IOID = llvn4.Ref_Shift3
                                            left join OCa as c44
                                            on  c44.IOID = llvn4.Ref_Shift4
                                    left join OLichLamViecNgay as llvn5
                                    on llv.Ref_ThuSau = llvn5.IOID
                                            left join OCa as c51
                                            on  c51.IOID = llvn5.Ref_Shift1
                                            left join OCa as c52
                                            on  c52.IOID = llvn5.Ref_Shift2
                                            left join OCa as c53
                                            on  c53.IOID = llvn5.Ref_Shift3
                                            left join OCa as c54
                                            on  c54.IOID = llvn5.Ref_Shift4
                                    left join OLichLamViecNgay as llvn6
                                    on llv.Ref_ThuBay = llvn6.IOID
                                            left join OCa as c61
                                            on  c61.IOID = llvn6.Ref_Shift1
                                            left join OCa as c62
                                            on  c62.IOID = llvn6.Ref_Shift2
                                            left join OCa as c63
                                            on  c63.IOID = llvn6.Ref_Shift3
                                            left join OCa as c64
                                            on  c64.IOID = llvn6.Ref_Shift4
                                    left join OLichLamViecNgay as llvn0
                                    on llv.Ref_ChuNhat = llvn0.IOID
                                            left join OCa as c01
                                            on  c01.IOID = llvn0.Ref_Shift1
                                            left join OCa as c02
                                            on  c02.IOID = llvn0.Ref_Shift2
                                            left join OCa as c03
                                            on  c03.IOID = llvn0.Ref_Shift3
                                            left join OCa as c04
                                            on  c04.IOID = llvn0.Ref_Shift4
                                    ', $tmp);
            $tmp2 = '';
            foreach ($calendar as $cl)
            {
                    $tmp2 .= ($tmp2 ? ' or ' : '') . sprintf(' llv.IOID = %1$d ',
                                    $cl);
            }
            $sql .= ($tmp2) ? ' where ' . $tmp2 : '';
            $sql .= ' order by llv.IOID ';
            //echo $sql; die;
            return ($tmp2) ? $this->_o_DB->fetchAll($sql) : array();
    }
    /// End getWorkingHoursPerWeekdays

    /// *Function: getLichDacBiet - lay thoi gian lam viec theo lich lam viec cua tung ca
    /// @Return: working hours by weekdays (not include special calendar)
    public function getLichDacBiet($refLichLamViecArray, $startDate)
    {
            $tmp = '';
            $dateTmp = '';
            $sql = sprintf('select llv.IOID as LLVIOID, ldb.Ref_LichNgay, ldb.Ngay,
                                    (TIME_TO_SEC(ifnull(TIMEDIFF(c1.GioKetThuc, c1.GioBatDau),0))/3600) as Ca1,
                                    llvn.Ref_Shift1 as RefCa1,
                                    (TIME_TO_SEC(ifnull(TIMEDIFF(c2.GioKetThuc, c2.GioBatDau),0))/3600) as Ca2,
                                    llvn.Ref_Shift2 as RefCa2,
                                    (TIME_TO_SEC(ifnull(TIMEDIFF(c3.GioKetThuc, c3.GioBatDau),0))/3600) as Ca3,
                                    llvn.Ref_Shift3 as RefCa3,
                                    (TIME_TO_SEC(ifnull(TIMEDIFF(c4.GioKetThuc, c4.GioBatDau),0))/3600) as Ca4,
                                    llvn.Ref_Shift4 as RefCa4
                                    from OLichLamViec as llv
                                    inner join OLichDacBiet as ldb on llv.IFID_M107 = ldb.IFID_M107
                                    inner join OLichLamViecNgay as llvn on ldb.Ref_LichNgay = llvn.IOID
                                    left join OCa as c1
                                    on  c1.IOID = llvn.Ref_Shift1
                                    left join OCa as c2
                                    on  c2.IOID = llvn.Ref_Shift2
                                    left join OCa as c3
                                    on  c3.IOID = llvn.Ref_Shift3
                                    left join OCa as c4
                                    on  c4.IOID = llvn.Ref_Shift4
                                    where 1 = 1
                                    ');

            if ($startDate)
            {
                    $dateTmp .= sprintf(' and ldb.Ngay >= %1$s ',
                            $this->_o_DB->quote($startDate));
            }
            $sql .= $dateTmp;

            foreach ($refLichLamViecArray as $refLichLamViec)
            {
                    $tmp .= $tmp ? ' or ' : '';
                    $tmp .= sprintf(' llv.IOID = %1$d ', $refLichLamViec);
            }

            $sql .= $tmp ? "and ({$tmp})" : '';
            //echo $sql; die;
            return (is_array($refLichLamViecArray) && count($refLichLamViecArray)) ? $this->_o_DB->fetchAll($sql) : array();
    }
    /// End getLichDacBiet

    // ***********************************************************************************************
    // *** Cac ham lien quan den chi phi, tinh gia, chuyen doi don vi tinh
    // ***********************************************************************************************

    // @todo: Sửa bỏ hàm này
     /// *Function: getDefaultCurrency - lay loai tien mac dinh cua he thong, co gia tri thay the mac dinh
    /// Get default currency.If not exist default, return replace value.
    public function getDefaultCurrency($replace = 'VND')
    {
            $sql = sprintf('select Code from qscurrencies where qscurrencies.Primary = 1 and qscurrencies.Enabled = 1');
            $dataSql = $this->_o_DB->fetchOne($sql);
            return $dataSql ? $dataSql->Code : $replace;
    }
    /// End getDefaultCurrency


    // ***********************************************************************************************
    // *** Cac ham lien quan den report
    // ***********************************************************************************************

    /// *Function: getReportListBoxData - lay listbox data
    /**
     * @param $listboxVal
     * @param $getFields
     * @param array $filterByLookupArr
     * @param array $excludeObject
     * @param int $limitObject
     * @param int $limitGen
     * @return mixed
     * // *****************************************************************
    // === $limitObject, default object limit, thay the bang limit
    // === cua $getFields
    // === $limitGen, gioi han so ban ghi tra ve sau khi ghep du lieu lay
    // === tu cac object
    // === $limit Gioi han so ban ghi tra ve
    // *****************************************************************
    // *****************************************************************
    // === $filterByVal luu tru du lieu loc theo gia tri nhap vao tai o
    // === list box, duoc loc theo nhieu fields cua nhieu object "like"
    // === voi gia tri nhap vao. Va chi lay gia tri cua cac object khong
    // === ton tai trong $excludeObject.
    // === St: objectKey => sql, objectKey.field like '%val%'
    // ===
    // =================================================================
    // === $filterByVal duoc lay gia tri tu $listboxVal
    // === St: $listboxVal
    // === [val]=>val
    // === [objects]
    // ===      [objectKey] (many)
    // ===          [
    // ===              [0]=>field1
    // ===              [1]=>field2
    // ===          ]
    // ===
    // =================================================================
    // === $excludeObject, loai bo object hay filter theo object lay gia
    // === tri
    // === St: $excludeObject = array(objectKey1, objectKey2, ..., objectKeyN)
    // Link: #getReportListBoxData1
    // *****************************************************************
    $filterByVal = array();



    // *****************************************************************
    // === $filterByLookupObject luu tru du lieu loc cac objects theo gia
    // === tri cua cac module (hay gia tri) lookup
    // === St: objectKey => sql, objectKey.field = 'val'
    // ===
    // =================================================================
    // === $filterByLookupArr
    // === St:  array (many)
    // ===      [
    // ===          [objects]=>
    // ===              [objectKey]=>field
    // ===          [required]=> true/false, 1/0
    // ===          [val]=>val
    // ===      ]
    // ===
    // =================================================================
    // === $excludeObject, loai bo object hay filter theo object lay gia
    // === tri
    // === St: $excludeObject = array(objectKey1, objectKey2, ..., objectKeyN)
    // Link: getReportListBoxData2
    // *****************************************************************
    $filterByLookupObject = array();


    // *****************************************************************
    // === $getFields, lay cac field nao tu cac object, cac field phai
    // === giong nhau de su dung union all noi cac object lay data
    // === St:
    // ===    [num] => N // so cot hien thi
    // ===    [objects]
    // ===          [objectKey] => (many)
    // ===              [id] => field
    // ===              [order]=>field (order object by field)
    // ===                  hoac array( 0 => array[Key=>, Order=>])
    // ===              [group]=>field (groub by field)
    // ===                  hoac array( 0 => array[Key=>, Order=>])
    // ===              [limit]=>number
    // ===              [display1] => field
    // ===              [display2] => field
    // ===              [displayN] => field
    // *****************************************************************
    // === $dataObject, bao gom cac object lay data
    $dataObject = array();


    // *****************************************************************
    // === $matchSql, noi lai filter theo gia tri ($filterByVal) va filter
    // === theo gia triduoc lookup den ($filterByLookupObject), tra ve
    // === cau sql voi dieu kien "and" giua hai dieu kien tren
    // *****************************************************************
    $matchSql = '';
     */
    public function getReportListBoxData(
        $listboxVal
        , $getFields
        , $filterByLookupArr = array()
        , $excludeObject = array()
        , $limitObject = 100, $limitGen = 300)
    {
        $filterByVal          = array();
        $filterByLookupObject = array();
        $dataObject           = array();
        $matchSql             = '';
        $treeselect           = '';
        $treeorder            = '';

        // Lọc theo text truyền vào khi gõ listbox (Dùng like
        if (count((array) $listboxVal)) {
            // Loại bỏ các object không dùng đến
            foreach ($excludeObject as $Obj) {
                if (isset($listboxVal[$Obj])) {
                    unset($listboxVal[$Obj]);
                }
            }

            foreach ($listboxVal['objects'] as $objectKey => $fields) {
                $dataObject[] = $objectKey;

                if (isset($listboxVal['val']) && $listboxVal['val']) {
                    $filterByVal[$objectKey] = '';
                    foreach ($fields as $field) {
                        if ($listboxVal['val']) {
                            $filterByVal[$objectKey] .= $filterByVal[$objectKey] ? ' or ' : '';
                            $filterByVal[$objectKey] .= sprintf(' %1$s.%2$s like \'%%%3$s%%\'', $objectKey, $field, $listboxVal['val']);
                        }
                    }
                }
            }
        }

        // Lọc các object theo điều kiện lookup
        if (count((array) $filterByLookupArr)) {
            foreach ($filterByLookupArr as $filter) {
                if ($filter['required'] || $filter['val']) {
                    // Loại bỏ các object không dùng đến
                    foreach ($excludeObject as $Obj) {
                        if (isset($filter['objects'][$Obj])) {
                            unset($filter['objects'][$Obj]);
                        }
                    }

                    foreach ($filter['objects'] as $objectKey => $field) {
                        if (!isset($filterByLookupObject[$objectKey])) {
                                $filterByLookupObject[$objectKey] = '';
                        }
                        $filter['val'] = @$filter['val'];
                        $filterByLookupObject[$objectKey] .= $filterByLookupObject[$objectKey] ? ' and ' : '';
                        $filterByLookupObject[$objectKey] .= $this->getLookupFilter($objectKey, $field, $filter['val']);
                    }
                }
            }
        }

        // Kiểm tra hiển thị cây nếu chỉ lấy dữ liệu từ một object duy nhất.
        if (count($dataObject) == 1) {
            $object   = new Qss_Model_System_Object();
            $code     = $dataObject[0];
            $ioid     =  $object->getObjIDByName($code);
            $checksql = sprintf('
                select qsobjects.* 
                from qsfields 
                inner join qsobjects on qsobjects.ObjectCode = qsfields.ObjectCode
                where qsfields.ObjectCode = "%1$s" and qsfields.RefObjectCode = "%1$s" and IFNULL(qsfields.Effect, 0) = 1                              
            ', $ioid);
            $dataSQL  = $this->_o_DB->fetchOne($checksql);

            if ($dataSQL && @(int)$dataSQL->NoTree == 0) {
                $extTreeSelectCond = '';

                foreach ($filterByLookupArr as $filter) {
                    if ($filter['required'] || $filter['val']) {
                        // exclude object
                        foreach ($excludeObject as $Obj) {
                            if (isset($filter['objects'][$Obj])) {
                                unset($filter['objects'][$Obj]);
                            }
                        }

                        foreach ($filter['objects'] as $objectKey => $field) {
                            if($objectKey == $code) {
                                $filter['val'] = @$filter['val'];
                                $extTreeSelectCond .= sprintf('  and u.%2$s = %3$s'
                                    , $objectKey,
                                    $field
                                    ,
                                    $this->_o_DB->quote($filter['val']));
                            }
                        }
                    }
                }

                $treeselect = sprintf(' ,(SELECT count(*) FROM %1$s as u WHERE u.lft<=t.lft and u.rgt >= t.rgt %2$s) as level ',
                        $code, $extTreeSelectCond);
                $treeorder  = ' order by lft ';
            }
        }

        // match filter
        foreach ($dataObject as $objKey) {
            $filterByValTmp          = (isset($filterByVal[$objKey]) && $filterByVal[$objKey]) ?
                sprintf(' (%1$s) ', $filterByVal[$objKey]) : ' 1=1 ';
            $filterByLookupObjectTmp = (isset($filterByLookupObject[$objKey]) && $filterByLookupObject[$objKey]) ?
                sprintf(' and (%1$s) ', $filterByLookupObject[$objKey]) : '';
            $fieldTmp                = '';
            $fieldTmp               .= sprintf(' %1$s as num ', $getFields['num']);
            $limitSubSql             = sprintf(' limit %1$d', $limitObject);
            $orderSubSql             = '';
            $groupSubSql             = '';
            $whereSubSql             = '';


            foreach ($getFields['objects'][$objKey] as $key => $select) {
                // Lấy các trường hiển thị (Trong query là select)
                if ($key != 'limit' && $key != 'order' && $key != 'group' && $key != 'where') {
                    $selectTmp = (isset($select) && $select) ? sprintf(' %1$s.%2$s ',$objKey, $select) : ' null ';
                    $fieldTmp .= sprintf(', %1$s as %2$s ', $selectTmp, $key);
                }

                $limitSubSql = ($key == 'limit' && $select)?sprintf(' limit %1$d ', @(int) $select):'';

                // Điều kiện thêm cho lấy từng object (where trong query con)
                if ($key == 'where') {
                    $select = str_replace(array("\\'", '\\"'), array("'", '"'), $select);
                    $select = unserialize($select);
                    if (is_array($select) && count($select)) {
                        $temp = '';
                        foreach ($select as $field => $val) {
                            $temp .= $temp ? ' and ' : '';
                            $temp .= sprintf('%1$s = %2$s', $field, $this->_o_DB->quote($val));
                        }
                        $whereSubSql .= sprintf(' and (%1$s) ', $temp);
                    }
                    else {
                        if ($select) {
                            $whereSubSql = sprintf(' %1$s ', $select);
                        }
                    }
                }

                // Kiểm tra xem module có phân quyền đến bản ghi không?
                // Nếu có tiến hành lọc theo phân quyền
                $fObject = $this->getTableFetchAll('qsfobjects', array('ObjectCode'=>$objKey));

                foreach ($fObject as $fForm) {
                    if(Qss_Lib_System::formSecure($fForm->FormCode)) {
                        $whereSubSql .= sprintf('
                                AND IOID IN (
                                    SELECT IOID 
                                    FROM %2$s
                                    INNER JOIN qsrecordrights ON %2$s.IFID_%3$s = qsrecordrights.IFID 
                                    WHERE UID = %1$d
                                )
                            ', $this->_user->user_id, $objKey, $fForm->FormCode);
                    }
                }

                // Sắp xếp cho từng object (order by trong query con)
                if ($key == 'order') {
                    $select = str_replace(array("\\'", '\\"'), array("'", '"'), $select);
                    $select = unserialize($select);

                    if (is_array($select)) {
                        $orderSubSql = (count($select) > 1) ? ' order by ' : '';
                        $beginOrder  = 1;

                        foreach ($select as $order)
                        {
                            $order['Order'] = isset($order['Order']) ? $order['Order'] : '';
                            $orderSubSql   .= ($beginOrder == 0) ? ',' : '';
                            $orderSubSql   .= sprintf(' %1$s.%2$s %3$s ', $objKey, $order['Key'], $order['Order']);

                            if ($beginOrder == 1) {
                                    $beginOrder = 0;
                            }
                        }
                    }
                    else
                    {
                        $orderSubSql = $select ? sprintf(' order by %1$s.%2$s ', $objKey, $select) : '';
                    }
                }

                // Group lại theo field (Group by trong query con)
                if ($key == 'group') {
                    $select = str_replace(array("\\'", '\\"'), array("'", '"'), $select);
                    $select = unserialize($select);

                    if (is_array($select)) {
                        $groupSubSql = (count($select) > 1) ? ' group by ' : '';
                        $beginGroup = 1;

                        foreach ($select as $group) {
                            $group['Order'] = isset($group['Order']) ? $group['Order'] : '';
                            $groupSubSql .= ($beginGroup == 0) ? ',' : '';
                            $groupSubSql .= sprintf(' %1$s.%2$s %3$s ', $objKey, $group['Key'], $group['Order']);

                            if ($beginGroup == 1) {
                                $beginGroup = 0;
                            }
                        }
                    }
                    else {
                        $groupSubSql = $select ? sprintf(' group by %1$s.%2$s ', $objKey, $select) : '';
                    }
                }
            }

            // Lọc theo phòng ban nếu không phải là danh mục dùng chung
            $filterByDept = '';
            $checkobject  = Qss_Lib_System::getFormsByObject($objKey);
            if($checkobject && $checkobject->Type != Qss_Lib_Const::FORM_TYPE_PUBLIC_LIST) {
                $filterByDept = sprintf(' and DeptID in (%1$s) ',$this->_user->user_dept_list);
            }

            // Sắp xếp các phần thành 1 câu query hoàn chỉnh
            $matchSql .= $matchSql ? ' UNION ALL ' : '';
            $matchSql .= sprintf(' ( select %4$s %8$s from %1$s where %2$s %10$s %3$s %9$s %7$s %5$s %6$s) '
                , $objKey, $filterByValTmp, $filterByLookupObjectTmp, $fieldTmp, $orderSubSql
                , $limitSubSql, $groupSubSql, $treeselect ? ' ,lft, rgt' : '', $whereSubSql ,$filterByDept);
        }

        $sql = sprintf(' select t.* %3$s from (%1$s) as t %4$s limit %2$d ', $matchSql, $limitGen, $treeselect, $treeorder);
        // echo '<Pre>'; print_r($sql); die;
        return $this->_o_DB->fetchAll($sql);
    }
    /// End getReportListBoxData

    /// *Function: getReportComboxboxData - lay combobox data
    public function getReportComboxboxData($getFields
            ,$filterByLookupArr = array()
            , $excludeObject = array()
            ,$limitObject = 10000
            ,$limitGen = 30000)
    {


            // *****************************************************************
            // === $getFields, bao gom cac field lay du lieu cua cac object
            // === St:
            // ===    [num] => N // so cot hien thi
            // ===    [objects]
            // ===          [objectKey] => (many)
            // ===              [id] => field
            // ===              [order]=>field (order object by field)
            // ===                  hoac array( 0 => array[Key=>, Order=>])
            // ===              [group]=>field (groub by field)
            // ===                  hoac array( 0 => array[Key=>, Order=>])
            // ===              [limit]=>number (limit object, default: $limitObject)
            // ===              [display1] => field
            // ===              [display2] => field
            // ===              [displayN] => field
            // *****************************************************************
            // *****************************************************************
            // === $filterByLookupArr, bao gom cac gia tri filter theo cac truong
            // === lookup cua cac object khac
            // === St:  array (many)
            // ===      [
            // ===          [objects]=>
            // ===              [objectKey]=>field
            // ===          [required]=> true/false, 1/0
            // ===          [val]=>val
            // ===      ]
            // ===
            // *****************************************************************
            // *****************************************************************
            // === $excludeObject, loai bo object hay filter theo object lay gia
            // === tri
            // === St: $excludeObject = array(objectKey1, objectKey2, ..., objectKeyN)
            // *****************************************************************
            // *****************************************************************
            // === $limitObject, gioi han so ban ghi chung cho cac cau query
            // === cua cac object get data, de set cho tung object gia tri rieng
            // === them limit vao $getFields
            // === St: = number
            // *****************************************************************
            // *****************************************************************
            // === $limitGen, gioi han so ban ghi tra ve sau khi ghep cac cau
            // === query cua tung object
            // === St: = number
            // *****************************************************************
            // *****************************************************************
            // === $filterByLookupObject luu tru du lieu loc cac objects theo gia
            // === tri cua cac module (hay gia tri) lookup
            // === St: objectKey => sql, objectKey.field = 'val'
            // === Rl: $filterByLookupArr
            // *****************************************************************
            $filterByLookup = array();


            // *****************************************************************
            // === $matchSql, noi cac cau sql lay data bang union all de ra cau
            // === query lay du lieu
            // *****************************************************************
            $matchSql = '';

            // *****************************************************************
            // === Get filter by lookup object, loai bo object thuoc $excludeObject :>
            // === Chi loc theo cac truong yeu cau hoac co gia tri(ko yeu cau)
            // === Re: $filterByLookup
            // *****************************************************************
            if (count((array) $filterByLookupArr))
            {
                    foreach ($filterByLookupArr as $filter)
                    {
                            if ($filter['required'] || $filter['val'])
                            {
                                    // exclude object
                                    foreach ($excludeObject as $Obj)
                                    {
                                            if (isset($filter['objects'][$Obj]))
                                            {
                                                    unset($filter['objects'][$Obj]);
                                            }
                                    }


                                    foreach ($filter['objects'] as
                                                    $objectKey => $field)
                                    {
                                            if (!isset($filterByLookup[$objectKey]))
                                            {
                                                    $filterByLookup[$objectKey] = '';
                                            }
                                            $filter['val'] = @$filter['val'];
                                            $filterByLookup[$objectKey] .= $filterByLookup[$objectKey] ? ' and ' : '';
                                            $filterByLookup[$objectKey] .= sprintf(' %1$s.%2$s = %3$s'
                                                    , $objectKey,
                                                    $field
                                                    ,
                                                    $this->_o_DB->quote($filter['val']));
                                    }
                            }
                    }
            }


            // *****************************************************************
            // === Match sql, tao va noi cac cau sql lay data cua cac object
            // === get data (exclude objects in $excludeObject)
            // *****************************************************************
            foreach ($excludeObject as $Obj)
            {
                    if (isset($getFields['objects'][$Obj]))
                    {
                            unset($getFields['objects'][$Obj]);
                    }
            }

            //check if display tree
            $treeselect = '';
            $treeorder = '';
            if (count($getFields['objects']) == 1)
            {
                    $object = new Qss_Model_System_Object();
                    $code = array_keys($getFields['objects']);
                    $code = $code[0];
                    $checksql = sprintf('
                        select qsobjects.* 
                        from qsfields 
                        inner join qsobjects on qsobjects.ObjectCode = qsfields.ObjectCode
                        where qsfields.ObjectCode = "%1$s" and qsfields.RefObjectCode = "%1$s" and IFNULL(qsfields.Effect, 0) = 1 
                        ', $code);
                    $dataSQL = $this->_o_DB->fetchOne($checksql);

                    if ($dataSQL && @(int)$dataSQL->NoTree == 0)
                    {
                        $extTreeSelectCond = '';


                        foreach ($filterByLookupArr as $filter)
                        {
                            if ($filter['required'] || $filter['val'])
                            {
                                // exclude object
                                foreach ($excludeObject as $Obj)
                                {
                                    if (isset($filter['objects'][$Obj]))
                                    {
                                        unset($filter['objects'][$Obj]);
                                    }
                                }


                                foreach ($filter['objects'] as
                                         $objectKey => $field)
                                {

                                    if($objectKey == $code) {
                                        $filter['val'] = @$filter['val'];
                                        $extTreeSelectCond .= sprintf(' and u.%2$s = %3$s'
                                            , $objectKey,
                                            $field
                                            ,
                                            $this->_o_DB->quote($filter['val']));
                                        break;
                                    }
                                }
                            }
                        }

                            $treeselect = sprintf(' ,(SELECT count(*) FROM %1$s as u WHERE u.lft<=t.lft and u.rgt >= t.rgt %2$s) as level ',
                                    $code, $extTreeSelectCond);
                            $treeorder = ' order by lft ';
                    }
            }

            foreach ($getFields['objects'] as $objKey => $conf)
            {



                    // *****************************************************************
                    // === $filterByLookupObjectTmp, loc theo lookup obj field
                    // === $fieldTmp, cac fields lay gia tri de hien thi va lam id
                    // *****************************************************************
                    $filterByLookupObjectTmp = (isset($filterByLookup[$objKey])
                            && $filterByLookup[$objKey]) ?
                            sprintf(' and (%1$s) ',
                                    $filterByLookup[$objKey]) : '';
                    $fieldTmp = '';

                    $fieldTmp .= sprintf(' %1$s as num ', $getFields['num']);
                    $limitSubSql = sprintf('limit %1$d', $limitObject);
                    $orderSubSql = '';
                    $groupSubSql = '';
                    $whereSubSql = '';


                    // Kiểm tra xem module có phân quyền đến bản ghi không?
                    // Nếu có tiến hành lọc theo phân quyền
                    foreach($getFields['objects'] as $key=>$arr) {
                        $fObject = $this->getTableFetchAll('qsfobjects', array('ObjectCode'=>$key));

                        foreach ($fObject as $fForm) {
                            if(Qss_Lib_System::formSecure($fForm->FormCode)) {
                                $whereSubSql .= sprintf('
                                    AND IOID IN (
                                        SELECT IOID 
                                        FROM %2$s
                                        INNER JOIN qsrecordrights ON %2$s.IFID_%3$s = qsrecordrights.IFID 
                                        WHERE UID = %1$d
                                    )
                                ', $this->_user->user_id, $key, $fForm->FormCode);
                            }
                        }
                    }

                    foreach ($conf as $key => $select)
                    {
                            if ($key != 'limit' && $key != 'order' && $key != 'group'
                                    && $key != 'where')
                            {
                                    $selectTmp = (isset($select) && $select) ? sprintf(' %1$s.%2$s ',
                                                    $objKey, $select) : ' null ';
                                    $fieldTmp .= sprintf(', %1$s as %2$s ',
                                            $selectTmp, $key);
                            }

                            if ($key == 'limit' && $select)
                            {
                                    $limitSubSql = sprintf(' limit %1$d ',
                                            @(int) $select);
                            }

                            if ($key == 'where')
                            {
                                    $select = str_replace(array("\\'", '\\"'),
                                            array("'", '"'), $select);
                                    $select = unserialize($select);
                                    if (is_array($select) && count($select))
                                    {
                                            $temp = '';
                                            foreach ($select as $field =>
                                                            $val)
                                            {
                                                    $temp .= $temp ? ' and ' : '';
                                                    $temp .= sprintf('%1$s = %2$s',
                                                            $field,
                                                            $this->_o_DB->quote($val));
                                            }
                                            $whereSubSql .= sprintf(' and (%1$s) ',
                                                    $temp);
                                    }
                                    else
                                    {
                                            if ($select)
                                            {
                                                    $whereSubSql = sprintf('  %1$s ',
                                                            $select);
                                            }
                                    }
                            }

                            if ($key == 'order')
                            {
                                    $select = str_replace(array("\\'", '\\"'),
                                            array("'", '"'), $select);
                                    $select = unserialize($select);
                                    if (is_array($select))
                                    {

                                            $orderSubSql = (count($select) > 1) ? ' order by ' : '';
                                            $beginOrder = 1;

                                            foreach ($select as $order)
                                            {
                                                    $order['Order'] = isset($order['Order']) ? $order['Order'] : '';
                                                    $orderSubSql .= ($beginOrder
                                                            == 0) ? ',' : '';
                                                    $orderSubSql .= sprintf(' %1$s.%2$s %3$s ',
                                                            $objKey,
                                                            $order['Key'],
                                                            $order['Order']);

                                                    // Not begin
                                                    if ($beginOrder == 1)
                                                    {
                                                            $beginOrder = 0;
                                                    }
                                            }
                                    }
                                    else
                                    {
                                            $orderSubSql = $select ? sprintf(' order by %1$s.%2$s ',
                                                            $objKey, $select) : '';
                                    }
                            }

                            if ($key == 'group')
                            {
                                    // sua loi unserialize
                                    $select = str_replace(array("\\'", '\\"'),
                                            array("'", '"'), $select);
                                    $select = unserialize($select);
                                    if (is_array($select))
                                    {
                                            $groupSubSql = (count($select) > 1) ? ' group by ' : '';
                                            $beginGroup = 1;

                                            foreach ($select as $group)
                                            {
                                                    $group['Order'] = isset($group['Order']) ? $group['Order'] : '';
                                                    $groupSubSql .= ($beginGroup
                                                            == 0) ? ',' : '';
                                                    $groupSubSql .= sprintf(' %1$s.%2$s %3$s ',
                                                            $objKey,
                                                            $group['Key'],
                                                            $group['Order']);

                                                    // Not begin
                                                    if ($beginGroup == 1)
                                                    {
                                                            $beginGroup = 0;
                                                    }
                                            }
                                    }
                                    else
                                    {
                                            $groupSubSql = $select ? sprintf(' group by %1$s.%2$s ',
                                                            $objKey, $select) : '';
                                    }
                            }
                    }

                    // match sql
                    $filterByDept = '';
                    $checkobject = Qss_Lib_System::getFormsByObject($objKey);
                    if($checkobject && $checkobject->Type != Qss_Lib_Const::FORM_TYPE_PUBLIC_LIST)
                    {
                        $filterByDept = sprintf(' and DeptID in (%1$s) ',$this->_user->user_dept_list);
                    }
                    $matchSql .= $matchSql ? ' UNION ALL ' : '';
                    $matchSql .= sprintf(' ( select %3$s %7$s
                                    from %1$s
                                    where 1=1 %9$s %2$s %8$s
                                     %6$s %4$s  %5$s) '
                            , $objKey
                            , $filterByLookupObjectTmp
                            , $fieldTmp
                            , $orderSubSql
                            , $limitSubSql
                            , $groupSubSql
                            , $treeselect ? ' ,lft, rgt' : ''
                            ,$whereSubSql
                            ,$filterByDept);
            }
            $sql = sprintf(' select t.* %3$s from (%1$s) as t %4$s limit %2$d '
                    ,$matchSql
                    ,$limitGen
                    ,$treeselect
                    ,$treeorder);
//				echo $sql; die;
            return $this->_o_DB->fetchAll($sql);
    }
    /// End getReportComboxboxData

    public function getReportDialBoxData(
        $objectCode, $keyField, $displayFields, $compareFields, $searchText, $extendCondition = array(), $orderFields = array(), $join = ''
    ) {
        $select  = $keyField;
        $select .= ', '.implode(', ', $displayFields);
        $temp    = '';
        $orderBy = count($orderFields)?implode(', ', $orderFields):'';
        $orderBy = $orderBy?' ORDER BY '.$orderBy:'';

        foreach ($compareFields as $field) {
            $temp .= ($temp?' or ':'').sprintf(' %1$s like "%%%2$s%%" ', $field, $searchText);
        }
        $where  = " WHERE ({$temp}) ";
        $where .= $extendCondition?sprintf(' and %1$s ', implode(' and ', $extendCondition)):'';

        $sql = sprintf('
            SELECT %2$s
            FROM %1$s
            %5$s
            %3$s
            %4$s
            LIMIT 100
        ', $objectCode, $select, $where, $orderBy, $join);

        // echo $sql; die;

        return $this->_o_DB->fetchAll($sql);
    }

    // ***********************************************************************************************
    // *** Cac ham lien quan den sql
    // ***********************************************************************************************

    // ***********************************************************************************************
    // *** Cac ham lien quan den xu ly tai lieu
    // ***********************************************************************************************

    // @todo: Chuyển về đúng chỗ
    public function getAllChildOfDoc($dtid, &$retvalArr = array())
    {
        $doctype = $this->getTableFetchAll('qsdocumenttype', array('ifnull(ParentID,0)'=>$dtid));

        if($doctype) {
            foreach($doctype as $dt) {
                $retvalArr[] = $dt->DTID;
                $this->getAllChildOfDoc($dt->DTID, $retvalArr);
            }
        }
        else {
            return;
        }
    }

    // @todo: Chuyển về đúng chỗ
    public function getDocuments($ifid, $type, $dtid)
    {
        $sWhere = '';
        $aWhere = array();

        if($ifid) {
            $aWhere[] = sprintf('qsfdocuments.IFID = %1$d', $ifid);
        }
        else {
            return;
        }

        if($type) {
            if($type== 1) {
                    // tai lieu chua phan loai
                    $aWhere[] = sprintf('ifnull(qsfdocuments.DTID,0) = 0');
            }
            elseif($type==2) {
                    // tai lieu da phan loai
                    $aWhere[] = sprintf('ifnull(qsfdocuments.DTID,0) <> 0');
            }
        }

        if($dtid) {
            $child = array();
            $this->getAllChildOfDoc($dtid, $child);
            $temp  = '( ';
            $temp .= sprintf(' qsfdocuments.DTID = %1$d', $dtid);

            if(count($child)) {
                $temp .= sprintf(' or qsfdocuments.DTID in (%1$s) ', implode(',', $child));
            }
            $temp .= ' ) ';

            $aWhere[] = $temp;
        }

        $sWhere = count($aWhere)?sprintf(' where %1$s', implode(' and ', $aWhere)):'';

        $sql    = sprintf('
            select qsfdocuments.*
                ,qsdocumenttype.Code
                ,qsdocumenttype.Type
                ,qsworkflowsteps.Name
                ,qsdocuments.Ext
                ,qsdocuments.DID
                ,qsdocuments.Name as docname
                ,qsdocuments.Modify
                ,qsdocuments.CDate
                , qsfdocuments.Date as DDate
            from qsfdocuments
            left join qsdocumenttype on qsdocumenttype.DTID = qsfdocuments.DTID
            left join qsdocuments on qsdocuments.DID = qsfdocuments.DID
            left join qsworkflowsteps on qsworkflowsteps.StepNo=qsfdocuments.StepNo
            %1$s
            order by qsfdocuments.StepNo,qsdocumenttype.Type
            LIMIT 1000',$sWhere);
            //echo $sql; die;
            return $this->_o_DB->fetchAll($sql);
    }

    public function getLookupFilter($ObjectCode,$LookupField, $value) {
    	$retval = sprintf(' %1$s.%2$s = %3$s', $ObjectCode, $LookupField, $this->_o_DB->quote($value));
		$sql    = sprintf('select * from qsfields where ObjectCode = "%1$s" and FieldCode = "%2$s"'
					,$ObjectCode
					,substr($LookupField,strpos($LookupField,'_')+1,strlen($LookupField)-strpos($LookupField,'_')+1));
		$dataSQL =  $this->_o_DB->fetchOne($sql);

		if($dataSQL) {
			//check tree
			$checksql = sprintf('select 1 from qsfields where ObjectCode = "%1$s" and RefObjectCode = "%1$s"',
                            $dataSQL->RefObjectCode);
			$dataTree = $this->_o_DB->fetchOne($checksql);
            if ($dataTree)
            {
            	//select ref record
            	$checksql = sprintf('select * from %1$s where IOID = %2$d',
            				$dataSQL->RefObjectCode,
                            $value);
				$dataIOID = $this->_o_DB->fetchOne($checksql);
				if($dataIOID)
				{
            		$retval = sprintf(' %1$s.%2$s in (select IOID from %5$s where lft >= %3$d and rgt <= %4$d)'
 							, $ObjectCode
                            , $LookupField
							, $dataIOID->lft
                            , $dataIOID->rgt
                            , $dataSQL->RefObjectCode);
				}
            }
		}             
		return $retval;                                                    
    } 
}
