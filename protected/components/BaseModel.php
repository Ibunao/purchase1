<?php
/**
 * 模型基础类，所有模型均需继承此类
 * @author chenfenghua <843958575@qq.com>
 */

class BaseModel extends CActiveRecord
{
    public $site_url;
    private $connection = null;

    public function __construct()
    {
        parent::__construct();
        $this->site_url = Yii::app()->request->getHostInfo();
        $this->connection = Yii::app()->db;
    }

    /**
     *
     * 连接状态
     */
    public function stats ()
    {
        return $this->connection ? 'connection successful!' : 'connection fail!';
    }

    /**
     * Controller 直接执行SQL
     *
     * @param $sql
     * @return mixed
     */
    public function QueryRow($sql)
    {
        $command = $this->connection->createCommand($sql);
        $result = $command->queryRow();
        return $result;
    }

    /**
     * Controller 直接执行SQL
     *
     * @param $sql
     * @return mixed
     */
    public function QueryAll($sql)
    {
        $command = $this->connection->createCommand($sql);
        $result = $command->queryAll();
        return $result;
    }

    /**
     * Controller 直接执行SQL
     *
     * @param $sql
     * @return mixed
     */
    public function RowCount($sql)
    {
        $command = $this->connection->createCommand($sql);
        $result = $command->queryScalar();

        return $result;
    }

    /**
     * Controller 直接执行SQL
     *
     * @param $sql
     * @return mixed
     */
    public function Execute($sql)
    {
        $command = $this->connection->createCommand($sql);
        $result = $command->execute();
        return $result;
    }

    /**
     * 组合对象列表信息
     *
     * @param $data
     * @param $val
     * @return array
     */
    public static function getFindAllids($data,$val)
    {
        $items = array();
        foreach ($data as $v) {
            $items[] = $v->$val;
        }

        return $items;
    }


    /**
     * 列表
     *
     * @param $model
     * @param $pageIndex
     * @param $pagesize
     * @param $condition
     * @param $order
     * @return array('item','count')
     */
    public function lists($model,$pageIndex,$pagesize,$order='',$condition="disabled='false'")
    {
        $criteria=new CDbCriteria;
        if ($condition) $criteria->addCondition($condition);
        if ($order) $criteria->order = $order;
        // 求总的记录数
        $count = $model::model()->count($criteria);

        $criteria->limit = $pagesize;
        $criteria->offset = ($pageIndex - 1) * $pagesize;

        $list = $model::model()->findAll($criteria);

        return array('item'=>$list,'count'=>$count);
    }


    /**
     * Insert操作
     *
     * @param $table
     * @param $params
     * @return mixed
     */
    public function ModelInsert($table,$params)
    {
        $insert_data = $this->_ArrToSqlInsert($params);
        return $this->ModelExecute("INSERT INTO {$table} ({$insert_data['key']}) VALUE ({$insert_data['val']})");
    }

    /**
     * 获取要插入的值
     * @param $params
     * @return string
     */
    public function ModelInsertValue( $params ){
        foreach ($params as $k=>$v) {
            $val[] = "'".$v."'";
        }
        $val_str = implode(',',$val);
        $value_array = ",( ".$val_str. " ) ";
        return $value_array;
    }

    /**
     * 获取要插入的key
     * @param $params
     * @return string
     */
    public function ModelInsertKey( $params ){
        foreach ($params as $k=>$v) {
            $key[] = $k;
        }
        $key_str = implode(',',$key);
        return $key_str;
    }

    /**
     * 插入数据数组转SQL
     *
     * @param $data
     * @return array
     */
    private function _ArrToSqlInsert($data)
    {
        foreach ($data as $k=>$v) {
            $key[] = $k;
            $val[] = "'".$v."'";
        }
        $key_str = implode(',',$key);
        $val_str = implode(',',$val);
        return array(
            'key'=>$key_str,
            'val'=>$val_str
        );
    }


    /**
     * 直接执行SQL
     *
     * @param $sql
     * @return mixed
     */
    public function ModelExecute($sql)
    {
        $command = $this->connection->createCommand($sql);
        $result = $command->execute();
        return $result;
    }

    /**
     * Update操作
     *
     * @param $table
     * @param $conditionArr
     * @param $params
     * @return mixed
     */
    public function ModelEdit($table,$conditionArr,$params)
    {
        $condition = $this->_ArrToSql($conditionArr);
        $set_str = $this->ModelInsertValue($params);
        $sql = "UPDATE {$table} SET $set_str WHERE $condition";
        return $this->ModelExecute($sql);
    }

    /**
     * 修改操作
     * @param $table
     * @param $condition
     * @param $params
     * @return mixed
     */
    public function ModelThisEdit($table, $condition, $params){
        $set_str = $this->_ArrToSqlUpdate($params);
        $sql = "UPDATE {$table} SET $set_str WHERE $condition";
        return $this->ModelExecute($sql);
    }
    /**
     *  数组转SQL
     *
     * @param $data
     * @return string
     */
    private function _ArrToSql($data)
    {
        foreach ($data as $k=>$v) {
            $condition_arr[] = $k.'='."'".$v."'";
        }
        $condition = implode(' AND ',$condition_arr);

        return $condition;
    }

    /**
     *  数组转SQL
     *
     * @param $data
     * @return string
     */
    private function _ArrToSqlUpdate($data)
    {
        foreach ($data as $k=>$v) {
            $condition_arr[] = $k.'='."'".$v."'";
        }
        $condition = implode(' , ',$condition_arr);
        return $condition;
    }
} 