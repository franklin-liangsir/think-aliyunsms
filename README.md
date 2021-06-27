# think-aliyunsms
thinkphp5 阿里云短信发送插件
## 安装
```
composer require franklin-liangsir/think-aliyunsms
```
## 强调
>本插件为阿里云官方插件的二次封装短信验证码，如有更为复杂的短信通知，可直接参考阿里云官网文档以及本插件的src/SendSms.php进行您的业务

## 使用
### 配置-创建扩展配置文件aliyunsms.php（位置：tp5\config\extra）
>相关参数需在阿里云短信控制台配置，配置步骤已附在参数后面
```php
return [
    // 阿里云平台中获取的accessKeyId（RAM控制台-人员管理-用户管理-点击用户名称）
    'accessKeyId' => '',

    // 阿里云平台中获取的accessKeySecret（RAM控制台-人员管理-用户管理-点击用户名称，AccessKeySecret只在创建时显示，不提供查询，可在当前界面重新创建）
    'accessKeySecret' => '',

    // 阿里云平台中获取的短信签名，审核通过才生效（短信服务-国内消息-签名名称-签名管理）
    'signName' => '',

    // 阿里云平台中获取短信模板ID，审核通过才生效（短信服务-国内消息-签名名称-模版管理）
    'templateCode' => ''
];
```

### 调用
```php
use FranklinLiangsir\ThinkAliyunsms\SendSms;
class demo extends Base{
    //发送单条
    public function sendAliYunSms(){
        //跨域
        header('content-type:text/html;charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type,token,Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT');

        $sms = new SendSms();
        $mobile = input("tel");

        //入参拦截
        $rules = array(
            array($mobile, '/^1[3|4|5|6|7|8|9][0-9]{9}$/', '请输入正确的手机号码'),
        );
        $result = valid($rules);
        if (!$result['status']) {
            return json(resFmt("100", $result['message'], "", ""));
        };

        //业务
        $code = mt_rand(1000, 9999);
        $res = $sms->sendSms($mobile, json_encode(array("code" => $code)));
        if ($res['Message'] == 'OK') {
            return json(resFmt(0, "发送状态", $code));
        } else {
            return json(resFmt(100, $res['Message'], ""));
        }
    }

    //批量发送
     public function sendAliYunSmsMore(){
        //跨域
        header('content-type:text/html;charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type,token,Accept");
        header('Access-Control-Allow-Methods: GET, POST, PUT');

         $mobile_json="['15800000000','15800000000']";
         $sign_json="['云通信','云通信']";
         $content_json="[{'name':'小王','code':'123456'},{'name':'小张','code':'123456'}]";
         $res = $this->sendBatchSms($mobile_json,$sign_json,$content_json);
         if ($res['Message'] == 'OK') {
            return json(resFmt(0, "发送状态", $code));
        } else {
            return json(resFmt(100, $res['Message'], ""));
        }
     }
}
```