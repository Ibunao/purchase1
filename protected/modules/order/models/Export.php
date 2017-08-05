<?php

/**
 * Created by PhpStorm.
 * User: zangmiao
 * Date: 2016/1/7
 * Time: 19:24
 */
class Export extends BaseModel
{

    public $purchase;
    public $brand;
    public $color;
    public $size;
    public $cat_b;
    public $cat_m;
    public $cat_s;
    public $season;
    public $level;
    public $scheme;
    public $wave;
    public $parice_level;
    public $type;

    public function init()
    {
        $tableModel = new Table();
        $this->purchase = $tableModel->purchaseList();
        $this->brand = $tableModel->brandList();
        $this->cat_b = $tableModel->bigCatList();
        $this->cat_m = $tableModel->middleCatList();
        $this->cat_s = $tableModel->smallCatList();
        $this->wave = $tableModel->waveList();
        $this->scheme = $tableModel->schemeList();
        $this->season = $tableModel->seasonList();
        $this->color = $tableModel->colorListNo();
        $this->size = $tableModel->sizeList();
        $this->level = $tableModel->levelList();
        $this->type = $tableModel->typeList();

        $this->parice_level = array(
            '0-99' => 1,
            '100-199' => 2,
            '200-299' => 3,
            '300-399' => 4,
            '400-499' => 5,
            '500-999' => 6,
            '1000-1499' => 7,
            '1500-2000' => 8,
            '2000以上' => 9,
        );
    }

    /**
     * @return string
     */
    public function tableName()
    {
        return '{{cat_big}}';
    }


    /**
     * @return array
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }

    /**
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Admin the static model class
     */
    public static function model($className = __CLASS__)
    {

        return parent::model($className);
    }
}