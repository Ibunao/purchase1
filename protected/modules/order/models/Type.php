<?php
/**
 * Created by PhpStorm.
 * User: zangmiao
 * Date: 2015/7/27
 * Time: 11:13
 */
class Type extends BaseModel{
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{type}}';
    }


    /**
     * 获取代理列表
     * @return mixed
     */
    public function getType(){
        $result = Yii::app()->cache->get("type");
        if(!$result){
            $sql = "SELECT type_id, type_name FROM {{type}}";
            $result = $this->QueryAll($sql);
            Yii::app()->cache->set("type", $result, 86400);
        }
        return $result;
    }

    /**
     * @return array 对模型的属性验证规则.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
        );
    }

    /**
     * 返回指定的AR类的静态模型.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Admin the static model class
     */
    public static function model($className=__CLASS__)
    {

        return parent::model($className);
    }
}