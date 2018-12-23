<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/14
 * Time: 9:58
 */
namespace app\app\controller;

use think\Controller;
use think\Session;
use think\Cache;
use think\Db;

class Exam extends Controller
{
    public function editTongueExam(){
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

    //上传舌诊图片
    public function uploadTongueImage(){
        $image = input('file');
        $patient_info_id = input('id');
        $batch = Db::name("batch")->where(array('patient_info_id'=>$patient_info_id))->find();
        $time = strtotime(date('Y-m-d',time()));
        Db::startTrans();
        if($batch && $batch['datetime'] >$time){
            $batch_id = $batch['id'];
            $delete_data = Db::name("image_info")->where(array('patient_info_id'=>$patient_info_id,'batch_id'=>$batch_id,'type'=>1))->delete();
        }else{
            Db::name("batch")->insert(array('patient_info_id'=>$patient_info_id,'datetime'=>time()));
            $batch_id =  Db::name("batch")->getLastInsID();
            $delete_data = true;
        }
        $path = "/upload/tongue_img";
        if (!is_dir($path)){ //判断目录是否存在 不存在就创建
            exec("mkdir ".$path." && chmod 777 ".$path." -R");
        };

//        if (strstr($image,",")){
            $image = explode(',',$image);
//        }
//        var_dump($image);
        if(count($image)){
            if(count($image) == 1){
                $image_info_data=array(
                    'patient_info_id'=>$patient_info_id,
                    'batch_id'=>$batch_id,
                    'taketime'=>time(),
                    'type'=>1,
                );
                $imageName = date("YmdHis",time())."_".rand(1111,9999).".png";
                $imageSrc=  $path."/". $imageName;
                $r = file_put_contents(ROOT_PATH .'public'.$imageSrc, base64_decode($image[0]));
                $image_info_data['filepath'] = $imageSrc;
                $insert_data = Db::name("image_info")->insert($image_info_data);
            }else{
                $image_info_data=array();
                foreach ($image as $k=>$v){
                    $image_info_data[]= array(
                        'patient_info_id'=>$patient_info_id,
                        'batch_id'=>$batch_id,
                        'taketime'=>time(),
                        'type'=>1,
                    );
                    $images = $v;
                    $imageName = date("YmdHis",time())."_".rand(1111,9999).".png";
                    $imageSrc=  $path."/". $imageName;
                    $r = file_put_contents(ROOT_PATH .'public'.$imageSrc, base64_decode($images));
                    if($r){
                        $image_info_data[]['filepath'] = $imageSrc;
                    }else{
                        Db::rollback();
                        echo json_encode(array("code"=>202,"msg"=>"上传失败"));
                        exit;
                    }
                };
                $insert_data = Db::name("image_info")->insertAll($image_info_data);
            }
        }else{
            Db::rollback();
            echo json_encode(array("code"=>202,"msg"=>"上传失败"));
            exit;
        }

        if($insert_data && $delete_data){
            Db::commit();
            echo json_encode(array("code"=>200,"msg"=>"上传成功"));
            exit;
        }else{
            Db::rollback();
            echo json_encode(array("code"=>202,"msg"=>"上传失败"));
            exit;
        }
    }
}