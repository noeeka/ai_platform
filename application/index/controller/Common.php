<?php

namespace app\index\controller;
use app\app\controller\Xml;
use think\Controller;
use think\Session;
use think\Cache;
use think\Db;

class Common extends Controller {
	public function index()
	{
		print_r('hello word!');
	}

	public function upload()
	{
		$customer_no = input('customer_no');
		file_put_contents("/root/log",json_encode($customer_no)."\r\n",FILE_APPEND);
		if (empty($customer_no))
		{
			$customer_no = "9999999";
		}
        $customer_no = substr($customer_no,0,16);
		$type = input('type');
		if ($type == 'report')
		{

			//获取患者基础信息服务
			$data_patient = Db::name('patient_info')->field('id,name,customer_no,sex,identify,callphone,age,nationality,career,birthplace,address,workplace')->where(array('customer_no' => $customer_no))->find();
			//获取检查批次服务
			$data_batch = Db::name('batch')->field('id,patient_info_id,datetime')->where(array('patient_info_id' => $data_patient['id']))->find();

			$rand = rand(1111, 9999);
			$receiveFile = "upload/report_xml/" . $customer_no . "_" . $rand . ".xml";
			$streamData = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
			if (empty($streamData))
			{
				$streamData = file_get_contents('php://input');
			}
			if ($streamData != '')
			{
				$ret = file_put_contents($receiveFile, $streamData, TRUE);
				if ($ret)
				{
					//插入数据表
					$report_data = [];
					$report_data['patient_info_id'] = $data_patient['id'];
					$report_data['content'] = "/" . $receiveFile;
					$report_data['filepath'] = "/" . $receiveFile;
					$res_physical = Db::name("report")->insert($report_data);
					$xml=new Xml();
					$xml->getData($report_data['filepath']);
					echo json_encode(array('code' => '200', 'msg' => 'success', 'data' => $receiveFile));
					die;
				} else
				{
					echo json_encode(array('code' => '202', 'msg' => 'error', 'data' => ''));
					die;
				}
			} else
			{
				echo json_encode(array('code' => '202', 'msg' => '数据为空', 'data' => ''));
				die;
			}
		}
		else
		{
			//获取患者基础信息服务
			$data_patient = Db::name('patient_info')->field('id,name,customer_no,sex,identify,callphone,age,nationality,career,birthplace,address,workplace')->where(array('customer_no' => $customer_no))->find();
			//获取检查批次服务
			$data_batch = Db::name('batch')->field('id,patient_info_id,datetime')->where(array('patient_info_id' => $data_patient['id']))->find();
file_put_contents("/root/log",json_encode($data_batch)."\r\n",FILE_APPEND);
			$rand = rand(1111, 9999);
			$receiveFile = "upload/infrared_gy/" . $customer_no . "_" . $rand . ".gy";

			$streamData = isset($GLOBALS['HTTP_RAW_POST_DATA']) ? $GLOBALS['HTTP_RAW_POST_DATA'] : '';
			if (empty($streamData))
			{
				$streamData = file_get_contents('php://input');
			}
			if ($streamData != '')
			{
				$ret = file_put_contents($receiveFile, $streamData, TRUE);
				if ($ret)
				{
					$base64_data = base64_encode(file_get_contents($receiveFile));
					file_put_contents(dirname(dirname(dirname(dirname(__FILE__)))) . "/tmp/content", $base64_data);
					exec("python " . dirname(dirname(dirname(dirname(__FILE__)))) . "/lib/python/convert.py 2>&1", $output);
					echo json_encode(array('code' => '200', 'msg' => 'success', 'data' => $output));
					die;
				} else
				{
					echo json_encode(array('code' => '202', 'msg' => 'error', 'data' => ''));
					die;
				}
			} else
			{
				echo json_encode(array('code' => '202', 'msg' => '数据为空', 'data' => ''));
				die;
			}

		}

	}

	//上传医生签名服务
	public function uploadsignature()
	{
		$customer_no = input('customer_no');
		$image = input('signature');
		$imageName = $customer_no . "_" . date("His", time()) . "_" . rand(1111, 9999) . '.png';
		$r = file_put_contents(dirname(dirname(dirname(dirname(__FILE__)))) . "/public/upload/report_signature/" . $imageName, base64_decode($image));
		if ($r)
		{
			//获取病人信息服务
			$data_patient = Db::name('patient_info')->field('id,name,customer_no,sex,identify,callphone,age,nationality,career,birthplace,address,workplace')->where(array('customer_no' => $customer_no))->find();
			$signatureImg = "/upload/report_signature/" . $imageName;
			$nurse_treatment = [];
			$nurse_treatment['doctor_signature'] = $signatureImg;
			$physical_characteristics = [];
			$physical_characteristics['doctor_signature'] = $signatureImg;
			$recommend_treatment = [];
			$recommend_treatment['doctor_signature'] = $signatureImg;
			Db::startTrans();
			if ($data_patient)
			{
				$nurse_treatment_flag = Db::name("nurse_treatment")->where(array('patient_info_id' => $data_patient['id']))->update($nurse_treatment);
				$physical_characteristics_flag = Db::name("physical_characteristics")->where(array('patient_info_id' => $data_patient['id']))->update($physical_characteristics);
//				$recommend_treatment_flag = Db::name("recommend_treatment")->where(array('patient_info_id' => $data_patient['id']))->update($recommend_treatment);
                $recommend_treatment_flag = true;
                if ($nurse_treatment_flag && $physical_characteristics_flag && $recommend_treatment_flag)
				{
					Db::commit();
					echo json_encode(array('code' => '200', 'msg' => 'success', 'data' => $customer_no));
					die;
				} else
				{
					DB::rollback();
					echo json_encode(array('code' => '500', 'msg' => '数据插入失败', 'data' => ''));
					die;
				}

			} else
			{
				echo json_encode(array('code' => '202', 'msg' => '查无此病人', 'data' => ''));
				die;
			}


			echo json_encode(array('code' => '202', 'msg' => '数据为空', 'data' => ''));
			die;
		}
		else
		{
			echo json_encode(array('code' => '500', 'msg' => 'Write Error', 'data' => $customer_no));
			die;
		}
	}

}


