<?php
/**
 *
 * Created by PhpStorm.
 * User: liu
 * Date: 2018/12/19
 * Time: 15:37
 */
namespace jpush\push\controller;
use think\controller;
class Push extends controller
{
    // 极光的key和secret，在极光注册完应用后，会生成
    protected $app_key = 'aa80e1c1a8e668414adfb019'; //填入你的app_key
    protected $master_secret = '506ea7b2187cbca22a7a36f6'; //填入你的master_secret

    /**
     * 测试极光推送
     */
    public function push(){
        $alert = '您收到一条消息';
        $message = [
            "extras" => array(
                "date" => array(
                    "content"=>"",
                    "sub_content" => "",
                    "title" => '',
                    "url"=>''
                ),
                "type"=>''
            )
        ];

    }

    function sendNotifySpecial($regid,$alert,$message){
        $client = new \JPush\Client($this->app_key, $this->master_secret);
        $result = $client->push()
            ->addAllAudience() // 推送所有观众
            ->setPlatform('all')  //推送平台
            // ->message($message, $msg) // 应用内消息
            ->addAlias($regid) // 给别名推送
            ->androidNotification($alert, $message)  //安卓消息推送
            ->send();
        return $result;;

    }
}