<?php

/**
 * Class Qss_Model_Table
 * __construct($table)
 * select($select) : Thêm field cần lấy ra, mặc định nếu không set là lấy tất cả
 * join($join) : Thêm join với bảng khác
 * where($where): Thêm điều kiện lọc vào query
 * groupby($groupBy): Gộp các trường
 * orderby($orderBy): Thêm sắp xếp dữ liệu
 * display($display): Số bản ghi hiển thị giới hạn
 * page($page): Trang hiện tại
 * matchSql(): Ghép các dữ liệu thành câu sql hoàn chỉnh
 * getSql(): In ra câu sql đã ghép
 * fetchOne(): Lấy dự liệu dạng object đơn
 * fetchAll(): Lấy dữ liệu dạng mảng object đơn
 */
class Qss_Model_Db_Table extends Qss_Model_Abstract
{
    protected $sql     = '';
    protected $select  = array(); // string
    protected $table   = ''; // string (With alias if has)
    protected $join    = array(); // array(value) (Simple join)
    protected $where   = array(); // mix value (string or array[key] = value)
    protected $orderBy = array(); // mix value (string or array(value))
    protected $groupBy = array(); // mix value (string or array(value))
    protected $display = 0; // int
    protected $page    = 0; // int

    public function __construct($table)
    {
        parent::__construct();
        $this->table  = $table;
    }

    public function select($select)
    {
        $this->select[] = $select;
    }

    public function join($join)
    {
        $this->join[] = $join;
    }

    public function where($where)
    {
        $this->where[] = $where;
    }

    public function ifnullString($field, $value)
    {
        return "IFNULL({$field}, \'\') = {$value}";
    }

    public function ifnullNumber($field, $value)
    {
        return "IFNULL({$field}, 0) = {$value}";
    }

    public function orderby($orderBy)
    {
        $this->orderBy[] = $orderBy;
    }

    public function groupby($groupBy)
    {
        $this->groupBy[] = $groupBy;
    }

    public function display($display)
    {
        $this->display = $display;
    }

    public function page($page)
    {
        $this->page = $page;
    }

    public function getSql()
    {
        $this->matchSql();
        return $this->sql;
    }

    protected function matchSql()
    {
        $tempSelect  = '*';
        $tempJoin    = '';
        $tempWhere   = '';
        $tempGroupBy = '';
        $tempOrderBy = '';
        $tempLimit   = '';

        // select
        if(is_array($this->select) && count($this->select))
        {
            $tempSelect = implode(', ', $this->select);
        }

        // join
        if(is_array($this->join) && count($this->join))
        {
            $tempJoin = implode(PHP_EOL, $this->join);
        }

        // where
        if(is_array($this->where) && count($this->where))
        {
            foreach ($this->where as $where)
            {
                $tempWhere .= $tempWhere?" AND ":"";
                $tempWhere .= " {$where} ";
            }
        }
        $tempWhere = $tempWhere?'WHERE '.$tempWhere:'';

        // group by
        if(is_array($this->groupBy) && count($this->groupBy))
        {
            foreach ($this->groupBy as $groupBy)
            {
                $tempGroupBy .= $tempGroupBy?" , ":"";
                $tempGroupBy .= " {$groupBy} ";
            }
        }
        $tempGroupBy = $tempGroupBy?'GROUP BY '.$tempGroupBy:'';

        // order by
        if(is_array($this->orderBy) && count($this->orderBy))
        {
            foreach ($this->orderBy as $orderBy)
            {
                $tempOrderBy .= $tempOrderBy?" , ":"";
                $tempOrderBy .= " {$orderBy} ";
            }
        }
        $tempOrderBy = $tempOrderBy?'ORDER BY '.$tempOrderBy:'';

        // Limit
        if($this->display && $this->page)
        {
            $tmpStart  = ($this->page - 1) * $this->display;
            $tempLimit = "LIMIT {$tmpStart}, {$this->display}";
        }
        elseif($this->display)
        {
            $tempLimit = 'LIMIT '.$this->display;
        }

        $this->sql = sprintf('
            SELECT %1$s
            FROM %2$s
            %3$s
            %4$s
            %5$s
            %6$s
            %7$s
        ', $tempSelect, $this->table, $tempJoin, $tempWhere, $tempGroupBy, $tempOrderBy, $tempLimit);

    }

    public function fetchOne()
    {
        $this->matchSql();
        return $this->_o_DB->fetchOne($this->sql);
    }

    public function fetchAll()
    {
        $this->matchSql();
        return $this->_o_DB->fetchAll($this->sql);
    }
}