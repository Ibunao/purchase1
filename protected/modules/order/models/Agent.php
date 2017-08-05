<?php
/**
 * Created by PhpStorm.
 * User: zangmiao
 * Date: 2015/7/27
 * Time: 11:13
 */
class Agent extends BaseModel{
    /**
     * @return string
     */
    public function tableName()
    {
        return '{{agent}}';
    }


    /**
     * 获取代理列表
     * @return mixed
     */
    public function getAgent(){
        $result = Yii::app()->cache->get("agent");
        if(!$result){
            $sql = "SELECT agent_name, agent_code FROM {{agent}}";
            $result = $this->QueryAll($sql);
            Yii::app()->cache->set("agent", $result, 86400);
        }
        return $result;
    }

    /**
     * 转换  code => name
     *
     * @return mixed
     */
    public function transAgentCode(){
        $agentList = $this->getAgent();
        foreach($agentList as $val){
            $item[$val['agent_code']."_".$val['agent_name']] = $val['agent_name'];
        }
        return $item;
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