<?php
/**
 * Created by PhpStorm.
 * User: zangmiao
 * Date: 2015/7/29
 * Time: 10:04
 */
class Color extends BaseModel
{


    /**
     * @return string
     */
    public function tableName()
    {
        return '{{color}}';
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

    /**
     * 获取颜色
     */
    public function getColor(){
        $result = Yii::app()->cache->get("color");
        if(!$result) {
            $sql = "SELECT color_id, color_no, color_name FROM {{color}} GROUP BY color_id";
            $result = $this->QueryAll($sql);
            Yii::app()->cache->set("color", $result, 86400 );
        }
        return $result;
    }

    /**
     * 根据color_id转color_no
     * @return array
     */
    public function transColor(){
        $result = $this->getColor();
        $item = array();
        foreach( $result as  $k => $v) {
            $item[$v['color_id']] = $v['color_no'];
        }
        return $item;
    }

    public function schemeTransColor(){

    }
}