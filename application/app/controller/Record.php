<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/21
 * Time: 16:22
 */
namespace app\app\controller;
use think\Controller;
use think\Db;
class Record extends Controller
{
    /*
     * 修改病历
     * 暂时只有舌诊的数据
     */
    public function editMedicalRecord(){
        $patient_info_id = input('post.patient_info_id');
        $batch_id = input('post.batch_id');
        $tongue_color = json_encode(input('post.tongue_color'));  //舌色
        $tongue_shape = json_encode(input('post.tongue_shape'));  //舌型
        $tongue_coat_texture = json_encode(input('post.tongue_coat_texture'));  //苔质
        $tongue_coat_color = json_encode(input('post.tongue_coat_color'));     //苔色
        $data = array('tongue_color'=>$tongue_color,'tongue_shape'=>$tongue_shape,'tongue_coat_texture'=>$tongue_coat_texture,'tongue_coat_color'=>$tongue_coat_color);
        $res = Db::name("tongue_exam_info")->where(array('batch_id'=>$batch_id,'patient_info_id'=>$patient_info_id))->find();
        if($res){
            $result = Db::name("tongue_exam_info")->where(array('id'=>$res['id']))->update($data);
        }else{
            $data['batch_id']=$batch_id;
            $data['patient_info_id']=$patient_info_id;
            $result = Db::name("tongue_exam_info")->insert($data);
        }
        if($result){
            echo json_encode(array("code"=>200,"msg"=>"成功"));
            exit;
        }else{
            echo json_encode(array("code"=>202,"msg"=>"失败"));
            exit;
        }
    }
}