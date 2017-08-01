<?php

/**
 * "ectools_order_bills" 数据表模型类.
 *
 * @author        chenfenghua <843958575@qq.com>
 * @copyright     Copyright (c) 2011-2015 octmami. All rights reserved.
 * @link          http://mall.octmami.com
 * @package       mall.model
 * @license       http://www.octmami.com/license
 * @version       v1.0.0
 */
class SeasonBig extends BaseModel
{


    /**
     * @return string
     */
    public function tableName()
	{
		return '{{season_big}}';
	}



    public function getList($select=array(),$order='',$group='' ){
        $criteria=new CDbCriteria;
        $criteria->order = $order;
        $criteria->group = $group;
        //$criteria->params($select);
        $list = self::model()->findAll($criteria);
        return $list;
    }

	public function getSeasonBig(){
		$result = Yii::app()->cache->get("season_big");
		if(!$result){
			$sql = "SELECT big_name, season_name FROM {{season_big}}";
			$result = $this->QueryAll($sql);
			Yii::app()->cache->set("season", $result, 86400);
		}
		return $result;
	}

	public function getTrans(){
		$result = $this->getSeasonBig();
		foreach($result as $val) {
			$arr[$val['big_name'] . "_" . $val['season_name']][] = $val['big_name'] . "_" . $val['season_name'];
		};
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
