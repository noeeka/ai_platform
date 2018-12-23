<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/17
 * Time: 9:54
 */
namespace app\app\controller;
use think\Controller;
use think\Db;

class Wxapi extends Controller{
    public function getUserInfo(){
        $openid = input('post.openid');
        $data=Db::name('patient_info')->field('id,customer_no,openid')->where(array('openid'=>$openid))->find();
        $batch = Db::name("batch")->where(array('patient_info_id'=>$data['id']))->find();
        $time = strtotime(date('Y-m-d',time()));
        if($batch && $batch['datetime'] >$time){
            $batch_id = $batch['id'];
        }else{
            Db::name("batch")->insert(array('patient_info_id'=>$patient_info_id,'datetime'=>time()));
            $batch_id =  Db::name("batch")->getLastInsID();
        }
        $data['batch_id']=$batch_id;
        if($data){
            echo json_encode(array('data'=>$data,"code"=>200,"msg"=>"成功"));
            exit;
        }else{
            echo json_encode(array('data'=>null,"code"=>202,"msg"=>"失败"));
            exit;
        }
    }
}