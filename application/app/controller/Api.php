<?php
namespace app\app\controller;
use think\Session;
use think\Controller;
use think\Request;
use think\Db;
use think\Model;
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods:  POST, PUT, DELETE');
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept");
class Api extends Controller
{	

    public function index(){
       
        print_r("hello");
		
    }
	
    public function getinfo(){
        $department=input('department');
        $data=Db::name('hospital_doctor')->where('department',$department)->select();
        print_r(json_encode($data));
    }
	

	
	public function addinfo(){
		$customer_no = "10".time().rand(1111,9999);
		$image = input('file');
		$patient_info_data = array(
			'name'=>input('name'),
			'sex'=>input('sex'),
			'customer_no'=>$customer_no,
			'age'=>input('age'),
			'birthday'=>input('birthday'),
			'identify'=>input('identify'),
			'callphone'=>input('callphone'),
			'address'=>input('address'),
			'career'=>input('career'),
			'nationality'=>input('nationality'),
			'birthplace'=>input('birthplace'),
			'workplace'=>input('workplace'),
		);
        $data = Db::name("patient_info")->where(array('identify'=>input('identify')))->find();
        if($data){
            unset($patient_info_data['customer_no']);
            $res = Db::name("patient_info")->where(array('id'=>$data['id']))->update($patient_info_data);
            $patient_info_id =$data['id'];
        }else{
            $res = Db::name("patient_info")->insert($patient_info_data);
            $patient_info_id = Db::name('patient_info')->getLastInsID();
        }
        $batch = Db::name("batch")->where(array('patient_info_id'=>$patient_info_id))->find();
        $time = strtotime(date('Y-m-d',time()));
        if($batch && $batch['datetime'] >$time){
            $batch_res =true;
            $batch_id = $batch['id'];
        }else{
            $batch_res = Db::name("batch")->insert(array('patient_info_id'=>$patient_info_id,'datetime'=>time()));
            $batch_id =  Db::name("batch")->getLastInsID();
        }
		$report_data= array(
		    'patient_info_id'=>$patient_info_id,
		    'content'=>input('content'),
            'filepath'=>input('filepath'),
            'batch_id'=>$batch_id,
		);
		$report_res = Db::name("report")->insert($report_data);
		$this->upimgs($image,$patient_info_id,$batch_id);

		$medical_current_data= array(
		    'case'=>input('current_case'),
		    'hospital'=>input('hospital'),
            'datetime'=>input('datetime'),
            'content'=>input('content'),
            'method'=>input('method'),
			'medicine'=>input('medicine'),
        );
		$medical_current_res = Db::name("medical_current")->insert($medical_current_data);
		$medical_current_id = Db::name('medical_current')->getLastInsID();
		$medical_history_data= array(
		    'medical_name'=>json_encode(input('medical_name')),
		);
		$medical_history_res = Db::name("medical_history")->insert($medical_history_data);
		$medical_history_id = Db::name('medical_history')->getLastInsID();
		
		$base_case_data = array(
            'patient_info_id'=>$patient_info_id,
            'times'=>'',
            'project'=>'',
            'medical_history_id'=>$medical_history_id,
            'medical_current_id'=>$medical_current_id,
            'personal_history'=>json_encode(input('personal_history')),
            'obsterical_history'=>json_encode(input('obsterical_history')),
            'family_history'=>json_encode(input('family_history')),
            'onset_season'=>json_encode(input('onset_season')),
		);
		$base_case_res = Db::name("base_case")->insert($base_case_data);
		if($res && $batch_res && $report_res && $medical_current_res && $medical_history_res && $base_case_res){
			return json(['data'=>array('customer_no'=>$customer_no,'batch_id'=>$batch_id),"code"=>200,"msg"=>"成功"]);
		}else{
			return json(['data'=>null,"code"=>202,"msg"=>"失败"]); 
		}
    }
	
	public function add_patient_info(){
        $customer_no = "10".time().rand(1111,9999);
		$userdata=Db::name("patient_info")->where('customer_no',$customer_no)->find();
		if(!empty($userdata)){
			$this->assign("userdata",$userdata);
		}
        
    }
	
	
	public function upimgs($image,$patient_info_id,$batch_id){
        
		$image_info_data= array(
		    'patient_info_id'=>$patient_info_id,
		    'batch_id'=>$batch_id,
            'taketime'=>time(),
            'type'=>1,
        );
		$path = "/upload/tongue_img";
		if (!is_dir($path)){ //判断目录是否存在 不存在就创建
//	        mkdir($path,0777,true);
            exec("mkdir ".$path." && chmod 777 ".$path." -R");
	    };

		if (strstr($image,",")){
            $image = explode(',',$image);
	    }
	    foreach ($image as $k=>$v){
			$images = $v;
            $imageName = "2018".date("His",time())."_".rand(1111,9999).".png";
            $imageSrc=  $path."/". $imageName; 
            $r = file_put_contents(ROOT_PATH .'public'.$imageSrc, base64_decode($images));
            $image_info_data['filepath'] = $imageSrc;
			Db::name("image_info")->insert($image_info_data);
        };
	}
	
	public function abc(){
		$data['1']=input('username');
		$data['2']=input('password');
		return json(['data'=>$data,"code"=>200,"msg"=>"成功"]); 
	}
}