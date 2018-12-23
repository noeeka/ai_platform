<?php
namespace app\app\controller;

use think\Controller;
use think\Session;
use think\Cache;
use think\Db;

class Patient extends Controller
{
    //用户列表（病人）
	public function patientList(){
        $page = input('post.page')?input('post.page'):1;
        $page_size = input('post.page_size')?input('post.page_size'):6;
        $content = input('post.content');
        $where = '';
        if($content){
            $where.='( customer_no like "%'.$content.'%" or name like "%'.$content.'%" )';
        }
        $data=Db::name('patient_info')->field('id,name,customer_no')->where($where)->limit(intval($page_size*($page - 1)),intval($page_size))->select();
//        $data=Db::name('patient_info')->field('id,name,customer_no')->select();

        $count = Db::name('patient_info')->field('id,name,customer_no')->where($where)->count();
        if($data){
            echo json_encode(array('data'=>$data,"code"=>200,"msg"=>"成功",'page'=>$page,'page_size'=>$page_size,'total_count'=>$count));
            exit;
        }else{
            echo json_encode(array('data'=>null,"code"=>202,"msg"=>"失败"));
            exit;
        }
    }

    //病人基本信息
    public function patientInfo(){
        $id = input('post.id');
        $data=Db::name('patient_info')->field('id,name,customer_no,sex,identify,callphone,age,nationality,career,birthplace,address,workplace')->where(array('id'=>$id))->find();
        if($data){
            echo json_encode(array('data'=>$data,"code"=>200,"msg"=>"成功"));
            exit;
        }else{
            echo json_encode(array('data'=>null,"code"=>202,"msg"=>"失败"));
            exit;
        }
    }

    //病人基础病历
    public function basicMedicalRecord(){
        $id = input('post.id');
        $result = array();
        //获取病人基本信息
        $patient_base_info = Db::name('base_case')->where(array('patient_info_id'=>$id))->find();
        if(!$patient_base_info){
            $patient_base_info = Db::name('base_case')->where(array('patient_info_id'=>$id))->find();
        }
        $result['personal_history']=json_decode($patient_base_info['personal_history'],1);//个人史
        $result['obsterical_history']=json_decode($patient_base_info['obsterical_history'],1);//婚育史
        $result['family_history']=json_decode($patient_base_info['family_history'],1);//家族史
        $result['onset_season']=json_decode($patient_base_info['onset_season'],1);//发病节气
        //获取病人现病症
        $current = Db::name('medical_current')->where(array('id'=>$patient_base_info['medical_current_id']))->find();
        $result['case']=$current['case'];  //疾病
        $result['hospital']=$current['hospital'];  //治疗地点
        $result['datetime']=date('Y年m月d日',$current['datetime']);  //治疗时间
        $result['content']=$current['content'];  //检查内容
        $result['method']=$current['method'];  //治疗方法
        $result['medicine']=$current['medicine'];  //用药
        //获取病人历史病历
        $history = Db::name('medical_history')->where(array('id'=>$patient_base_info['medical_history_id']))->find();
        $result['medical_history']=json_decode($history['medical_name'],1);  //既往病史
        //舌诊图
        $batch= Db::name('batch')->where(array('patient_info_id'=>$id))->find();
        $result['batch_id']=$batch['id'];//批次id
        $tongue =  Db::name('image_info')->field('id,filepath')->where(array('batch_id'=>$batch['id'],'patient_info_id'=>$id,'type'=>1))->select();
        foreach ($tongue as $k=>$v){
            $result['tongue'][]='http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$v['filepath'];
        }
        //舌诊结果
        $tongue_exam_info = Db::name("tongue_exam_info")->where(array('patient_info_id'=>$id,'batch_id'=>$batch['id']))->find();
        if($tongue_exam_info){
            $result['tongue_color']=json_decode($tongue_exam_info['tongue_color'],1);
            $result['tongue_shape']=json_decode($tongue_exam_info['tongue_shape'],1);
            $result['tongue_coat_texture']=json_decode($tongue_exam_info['tongue_coat_texture'],1);
            $result['tongue_coat_color']=json_decode($tongue_exam_info['tongue_coat_color'],1);
        }
        //热成像
        $face =  Db::name('image_info')->field('id,filepath')->where(array('batch_id'=>$batch['id'],'patient_info_id'=>$id,'type'=>2))->select();
        foreach ($face as $k1=>$v1){
            $result['face'][]='http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$v1['filepath'];
        }
        if( isset($result['face'])){
            //温度
            //心区温度
            $result['face_exam']['heart_area']=array(array('area'=>'鼻根部','value'=>'37.37'),array('area'=>'左胸膺区','value'=>'36.77'),array('area'=>'左小肠区','value'=>'35.57'),array('area'=>'右小肠区','value'=>'35.52'),array('area'=>'额部','value'=>'31.52'));
            //肝区温度
            $result['face_exam']['liver_area']=array(array('area'=>'肝区','value'=>'37.32'),array('area'=>'右肝区','value'=>'36.97'),array('area'=>'左肝区','value'=>'36.92'));
            //脾区温度
            $result['face_exam']['spleen_area']=array(array('area'=>'脾_鼻尖区','value'=>'37.37'),array('area'=>'右胃区','value'=>'36.42'),array('area'=>'左胃区','value'=>'36.27'));
            //肺区温度
            $result['face_exam']['lung_area']=array(array('area'=>'印堂部','value'=>'36.97'),array('area'=>'右胸膺区','value'=>'36.87'),array('area'=>'左大肠区','value'=>'32.62'),array('area'=>'右大肠区','value'=>'32.27'));
            //肾区温度
            $result['face_exam']['renal_area']=array(array('area'=>'子宫膀胱_人中区','value'=>'37.87'),array('area'=>'下泌尿生殖区','value'=>'35.92'),array('area'=>'上泌尿生殖区','value'=>'35.87'));
            //脏腑能量值
            $result['face_exam']['viscera_energy_value']['heart']=29; //心区能量值
            $result['face_exam']['viscera_energy_value']['liver']=25; //肝区能量值
            $result['face_exam']['viscera_energy_value']['spleen']=30; //脾区能量值
            $result['face_exam']['viscera_energy_value']['lung']=34;//肺区能量值
            $result['face_exam']['viscera_energy_value']['renal']=36;//肾区能量值
        }
        //体制类型
        $physical_characteristics =  Db::name('physical_characteristics')->field('physical_type,physical_desc,mainpoints_desc')->where(array('batch_id'=>$batch['id'],'patient_info_id'=>$id))->find();
        $result['physical_desc']= $physical_characteristics['physical_type'].':'.$physical_characteristics['physical_desc'];
        $result['mainpoints_desc']= $physical_characteristics['mainpoints_desc'];
        echo json_encode(array('data'=>$result,"code"=>200,"msg"=>"成功"));
        exit;
    }
    //公益行客户录入
    public function addCommonwealPatient(){
        $customer_no = "10".time().rand(1111,9999);
        $name = input('post.name');
        $callphone = input('post.callphone');
        $identify = input('post.identify');
        $openid = input('post.openid');
        $res = Db::name("patient_info")->where(array('identify'=>$identify))->find();
        if($res){
            $customer_no = $res['customer_no'];
            $id = $res['id'];
            $res_new = Db::name("patient_info")->where(array('identify'=>$identify))->update(array('name'=>$name,'callphone'=>$callphone,'openid'=>$openid));
        }else{
            $res_new = Db::name("patient_info")->insert(array('name'=>$name,'callphone'=>$callphone,'identify'=>$identify,'customer_no'=>$customer_no,'openid'=>$openid));
            $id = Db::name("patient_info")->getLastInsID();
        }
        Db::name("batch")->insert(array('patient_info_id'=>$patient_info_id,'datetime'=>time()));
        $batch_id =  Db::name("batch")->getLastInsID();
        if($res_new){
            echo json_encode(array('data'=>array('customer_no'=>$customer_no,'id'=>$id,'batch_id'=>$batch_id),"code"=>200,"msg"=>"成功"));
            exit;
        }else{
            echo json_encode(array('data'=>null,"code"=>202,"msg"=>"失败"));
            exit;
        }
    }

    //获取养生调养
    public function getNurseTreatment(){
        $id = input('post.id');
        $batch_id = input('post.batch_id');
        $data_nurse = Db::name("nurse_treatment")->where(array('patient_info_id'=>$id))->find();
        if($data_nurse){
            if( $data_nurse['doctor_signature']){
                $data_nurse['doctor_signature'] ='http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$data_nurse['doctor_signature'];
            }else{
                $data_nurse['doctor_signature']='';
            }

            $physical_characteristics_id = $data_nurse['physical_characteristics_id'];
            $data_nurse['physical_characteristics']=Db::name("physical_characteristics")->where(array('id'=>$physical_characteristics_id))->value('physical_type');
            echo json_encode(array('data'=>$data_nurse,"code"=>200,"msg"=>"成功"));
            exit;
        }else{
            echo json_encode(array('data'=>null,"code"=>202,"msg"=>"失败"));
            exit;
        }
    }

    //获取人体辨识
    public function getPhysicalCharacteristics(){
        $id = input('post.id');
        $batch_id = input('post.batch_id');
        $data_physical = Db::name("physical_characteristics")->where(array('patient_info_id'=>$id,'batch_id'=>$batch_id))->find();
        if($data_physical){
            unset($data_physical['physical_desc'],$data_physical['mainpoints_desc']);
            if(is_null(json_decode($data_physical['image_info_id']))){
                $data_physical['image'][]='http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].Db::name("image_info")->where(array('id'=>$data_physical['image_info_id']))->value('filepath');
            }else{
                $image_ids = json_decode($data_physical['image_info_id'],1);
                $images_data = Db::name("image_info")->field('filepath')->where(array('id'=>array('in',$image_ids)))->select();
                foreach ($images_data as $k=>$v){
                    $data_physical['image'][]='http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$v['filepath'];
                }
            }
            if( $data_physical['doctor_signature']){
                $data_physical['doctor_signature'] ='http://'.$_SERVER['SERVER_NAME'].':'.$_SERVER["SERVER_PORT"].$data_physical['doctor_signature'];
            }else{
                $data_physical['doctor_signature']='';
            }
            unset($data_physical['image_info_id']);
            echo json_encode(array('data'=>$data_physical,"code"=>200,"msg"=>"成功"));
            exit;
        }else{
            echo json_encode(array('data'=>null,"code"=>202,"msg"=>"失败"));
            exit;
        }
    }


}