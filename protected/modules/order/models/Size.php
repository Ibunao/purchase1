<?php
/**
 * Created by PhpStorm.
 * User: zangmiao
 * Date: 2015/7/27
 * Time: 13:58
 */
class Size extends BaseModel{

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{size}}';
    }

    public function getSize(){
        $result = Yii::app()->cache->get("size");
        if(!$result){
            $sql = "SELECT * FROM {{size}}";
            $result = $this->QueryAll($sql);
            Yii::app()->cache->set("size", $result, 86400);
        }
        return $result;
	}

    /**
     * 获取group_id下面的尺码
     */
    public function getGroupSize(){
        $result = $this->getSize();
        foreach($result as $val){
            $arr[$val['group_id']][] = $val['size_name'];
        }
        return $arr;
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