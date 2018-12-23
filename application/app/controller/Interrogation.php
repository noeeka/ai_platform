<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/20
 * Time: 13:37
 */
namespace app\app\controller;
use think\Controller;
use think\Db;
class Interrogation extends Controller
{
    public function addInterrogationInfo(){
//        $illness_description = input('illness_description');//病情描述
//        $duration_time =input('duration_time');//持续时间
//        $cold_hot_condition =input('cold_hot_condition');//寒热情况
//        $sweat_condition =input('sweat_condition');//出汗情况
//        $perspire_easily_condition=input('perspire_easily_condition');//容易出汗情况
//        $head_face_condition =input('head_face_condition');//头面情况
//        $physical_condition=input('physical_condition');//身体状况
//        $chest_abdomen_condition =input('chest_abdomen_condition');//胸腹状况
//        $shit_condition =input('shit_condition');//大便情况
//        $urination_condition=input('urination_condition');//小便情况
//        $cough_condition = input('cough_condition');//咳嗽情况
//        $diet_condition =input('diet_condition');//饮食情况
//        $sleep_memory_condition =input('sleep_memory_condition');//睡眠及记忆
//        $gynaecology_condition =input('gynaecology_condition');//妇科情况
        $patient_info_id = input('id');
        Db::startTrans();
        Db::name('interrogation_batch')->insert(array('patient_info_id'=>$patient_info_id,'datetime'=>time()));
        $batch_id = Db::name('interrogation_batch')->getLastInsID();
//        $data = array(
//            array('interr_batch_id'=>$batch_id,'patient_info_id'=>$patient_info_id,'type_id'=>1,'content'=>$illness_description),
//        );
        $data=array();
        $interrogation_type =  Db::name('interrogation_type')->select();
        foreach ($interrogation_type as $k=>$v){
            $data[$k]['interr_batch_id']=$batch_id;
            $data[$k]['patient_info_id']=$patient_info_id;
            $data[$k]['type_id']=$v['id'];
            if($v['en_name'] =='illness_description' && $v['en_name'] =='duration_time' ){
                $data[$k]['content']=input($v['en_name']);
            }else{
                $data[$k]['content']=json_encode(input($v['en_name']));
            }
        }
        $res = Db::name('interrogation_info')->insertAll($data);
        if($res){
            Db::commit();
            echo json_encode(array("code"=>200,"msg"=>"成功"));
            exit;
        }else{
            Db::rollback();
            echo json_encode(array("code"=>202,"msg"=>"失败"));
            exit;
        }
    }
}