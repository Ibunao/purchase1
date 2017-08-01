<?php
/**
 * Created by PhpStorm.
 * User: zangmiao
 * Date: 2015/7/27
 * Time: 11:13
 */
class Brand extends BaseModel{
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{brand}}';
    }




    public function getBrand(){
        $result = Yii::app()->cache->get("brand");
        if(!$result){
            $sql = "SELECT brand_id, brand_name FROM {{brand}}";
            $result = $this->QueryAll($sql);
            Yii::app()->cache->set("brand", $result, 86400);
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