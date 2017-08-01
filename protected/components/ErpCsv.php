<?php
/**
 * Csv数据导入导出
 *
 *
 *
 * @category   	xzhuang
 * @package    	application.extensions
 * @author		chenfenghua
 * @copyright  	Copyright 2008-2013 erp.xzhuang.com
 * @version    	1.0
 */
class ErpCsv
{
    /**
     * 导入csv
     * @param $file
     * @internal param $filename
     * @return array|bool
     */
    public static function importCsv($file)
    {
        $handle = fopen($file['tmp_name'], 'r');
        $result = self::input_csv($handle); //解析csv
        fclose($handle);                                                //关闭指针
        return $result;
    }

    /**
     * 导入csv
     * @param $filename
     * @return array|bool
     */
    public static function importCsvData($filename)
    {
        $uploaddir = Yii::getPathOfAlias('application.data');
        $uploadfile = $uploaddir .'/'.basename($filename);
        $handle = fopen($uploadfile, 'r');
        $result = self::input_csv($handle); //解析csv
        fclose($handle);                                                //关闭指针
        return $result;
    }

    /**
     * 导出csv
     * @param $csv_header
     * @param array $data
     * @param $filename
     */
    public static function exportCsv($csv_header,Array $data,$filename='')
    {
        $filename =$filename?$filename:date('Ymd').'.csv'; //设置文件名
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=".$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo iconv('utf-8','gb2312',$csv_header) . "\n";
        foreach($data as $v){
            foreach ($v as $key => $col){
                $v[$key] = iconv('utf-8','gb2312//IGNORE',$col);
            }
            $str = implode(',',$v);
            echo $str. "\n";
        }
    }


    /**
     * 处理导入数据
     * @param $handle
     * @return array
     */
    public static function input_csv($handle) {
        $out = array ();
        $n = 0;
        while ($data = fgetcsv($handle)) {
            $num = count($data);
            for ($i = 0; $i < $num; $i++) {
                $out[$n][$i] = mb_convert_encoding($data[$i],'UTF-8','GBK');
            }
            $n++;
        }
        return $out;
    }

}