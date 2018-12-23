<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/17
 * Time: 15:33
 */

namespace app\app\controller;

use think\Controller;
use think\Db;

class Xml extends Controller {
	public function getData($file)
	{


		$xml = simplexml_load_file(dirname(dirname(dirname(dirname(__FILE__)))) . "/public" . $file);

		//$xml = simplexml_load_file($file);
		$jsonStr = json_encode($xml);
		$jsonArray = json_decode($jsonStr, TRUE);
		$customer_no = $jsonArray['userinfos']['@attributes']['id'];

		$patient_info_id = Db::name("patient_info")->where(array('customer_no' => $customer_no))->value('id');
        $batch_id = Db::name("batch")->where(array('patient_info_id' => $patient_info_id))->value('id');
//        print_r($jsonArray['ImageInfos']['image'][0]['data']);
//        die;
		/**
		 * 测试需要把 $patient_info_id 写死，后期需删除
		 */
//		$patient_info_id = 1;
		//转换图片服务
		$imageName = $customer_no . "_" . date("His", time()) . "_" . rand(1111, 9999) . '.png';
		$r = file_put_contents(dirname(dirname(dirname(dirname(__FILE__)))) . "/public/upload/infrared_img/".$imageName, base64_decode($jsonArray['ImageInfos']['image'][0]['data']));
////插入数据服务
		$image_info_data = [];
		$image_info_data['patient_info_id'] = $patient_info_id;

		$arr_arr = explode("_", end($jsonArray['ImageInfos']['image'])['@attributes']['name']);

		$image_info_data['taketime'] = strtotime($arr_arr[0] . "-" . $arr_arr[1] . "-" . $arr_arr[2] . " " . $arr_arr[3] . ":" . $arr_arr[4] . ":" . current(explode(".", $arr_arr[5])));

		$image_info_data['batch_id'] = $batch_id;
        $image_info_data['filepath'] = "/upload/infrared_img/" . $imageName;
        $image_info_data['type'] = 2;


		$data = Db::name("nurse_treatment")->where(array('patient_info_id' => $patient_info_id))->find();

		$treatment_data = array();
		$treatment_data['living_nurse'] = $jsonArray['reportInfos']['medical']['living'];//起居调养
		$treatment_data['sporting_nurse'] = $jsonArray['reportInfos']['medical']['sport'];//运动调养
		$treatment_data['diet_nurse'] = $jsonArray['reportInfos']['medical']['food'];//饮食调养
		$treatment_data['medicine_nurse'] = $jsonArray['reportInfos']['medical']['medicine_food'];//药膳调养
		$treatment_data['meridian_nurse'] = $jsonArray['reportInfos']['medical']['collateral'];//经络调养
		$physical_characteristics['ills_fever_state'] = $jsonArray['reportInfos']['medical']['@attributes']['code_hot'];

		$physical_characteristics['profits_losses_state'] = $jsonArray['reportInfos']['medical']['@attributes']['profit_loss'];
		$physical_characteristics['body_shape_state'] = $jsonArray['reportInfos']['medical']['@attributes']['bodily_form'];
		$physical_characteristics['body_fluid_state'] = $jsonArray['reportInfos']['medical']['@attributes']['spirit_blood'];
		$physical_characteristics['internal_state'] = $jsonArray['reportInfos']['medical']['@attributes']['internal_sate'];
		$physical_characteristics['physical_type'] = $jsonArray['reportInfos']['medical']['@attributes']['physical'];   //体质类型
		$physical_characteristics['physical_desc'] = $jsonArray['reportInfos']['medical']['description'];   //体质类型描述
		$physical_characteristics['mainpoints_desc'] = $jsonArray['reportInfos']['medical']['mainpoints'];   //原则要点描述

		if ($data)
		{
			$res_imgInfo = Db::name('image_info')->where(['patient_info_id' => $patient_info_id, 'batch_id' =>$batch_id,'type'=>2])->update($image_info_data);

			$physical_characteristics['image_info_id'] = Db::name('image_info')->where(['patient_info_id' => $patient_info_id, 'batch_id' => $batch_id,'type'=>2])->value('id');
			$res_physical = Db::name("physical_characteristics")->where(array('patient_info_id' => $patient_info_id))->update($physical_characteristics);

			$treatment_data['physical_characteristics_id'] = Db::name("physical_characteristics")->where(array('patient_info_id' => $patient_info_id))->value('id');
			$res_nurse = Db::name("nurse_treatment")->where(array('patient_info_id' => $patient_info_id))->update($treatment_data);


		} else
		{
			$image_info_data['batch_id'] = $batch_id;
			$res_imgInfo = Db::name('image_info')->insert($image_info_data);
			$physical_characteristics['image_info_id'] = Db::name('image_info')->getLastInsID();
			$treatment_data['patient_info_id'] = $patient_info_id;
			$res_physical = Db::name("physical_characteristics")->where(array('patient_info_id' => $patient_info_id))->insert($physical_characteristics);
			$treatment_data['physical_characteristics_id'] = Db::name("physical_characteristics")->getLastInsID();
			$res_nurse = Db::name("nurse_treatment")->where(array('patient_info_id' => $patient_info_id))->insert($treatment_data);


		}


	}
}