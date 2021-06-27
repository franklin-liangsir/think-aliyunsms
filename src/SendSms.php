<?php

namespace FranklinLiangsir\ThinkAliyunsms;

//引入sdk的命名空间
use FranklinLiangsir\ThinkAliyunsms\Core\Configsms;
use FranklinLiangsir\ThinkAliyunsms\Core\Profile\DefaultProfile;
use FranklinLiangsir\ThinkAliyunsms\Core\DefaultAcsClient;
use FranklinLiangsir\ThinkAliyunsms\Api\Sms\Request\V20170525\SendSmsRequest;
use FranklinLiangsir\ThinkAliyunsms\Api\Sms\Request\V20170525\SendBatchSmsRequest;
use think\Config;


//加载区域结点配置
Configsms::load();


class SendSms
{
    // +----------------------------------------------------------------------
    // | @Des:成员属性：关键配置
    // | @Des:阿里云可创建若干个AccessKey，每个AccessKey可设置不同的权限。
    // | @Des:故需要先创建有阿里云短信调用权限的AccessKey。
    // | @Des:创建步骤：短信服务-AccessKey(右侧)-创建AccessKey-创建成功添加短信权限
    // +----------------------------------------------------------------------
    // +----------------------------------------------------------------------
    // | @Des:发送一条短信
    // | @Param $mobile {number} 发送电话号码 
    // | @Param $templateParam {JSON} 验证码：{"code":"123"}
    // | @Return json
    // +----------------------------------------------------------------------
    public function sendSms($mobile, $templateParam)
    {
        // 短信配置文件
        $Config = Config::get("aliyunsms");

        // 阿里云平台中获取的accessKeyId（RAM控制台-人员管理-用户管理-点击用户名称）
        $accessKeyId     = $Config['accessKeyId'];

        // 阿里云平台中获取的accessKeySecret（RAM控制台-人员管理-用户管理-点击用户名称，AccessKeySecret只在创建时显示，不提供查询，可在当前界面重新创建）
        $accessKeySecret = $Config['accessKeySecret'];

        // 阿里云平台中获取的短信签名，审核通过才生效（短信服务-国内消息-签名名称-签名管理）
        $signName        = $Config['signName'];

        // 阿里云平台中获取短信模板ID，审核通过才生效（短信服务-国内消息-签名名称-模版管理）  
        $templateCode    = $Config['templateCode'];

        // 短信API产品名（短信产品名固定，无需修改）
        $product = "Dysmsapi";

        // 短信API产品域名（接口地址固定，无需修改）
        $domain = "dysmsapi.aliyuncs.com";

        // 暂时不支持多Region（目前仅支持cn-hangzhou请勿修改）
        $region = "cn-hangzhou";

        // 初始化用户Profile实例
        $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

        // 增加服务结点
        DefaultProfile::addEndpoint("cn-hangzhou", "cn-hangzhou", $product, $domain);

        // 初始化AcsClient用于发起请求
        $acsClient = new DefaultAcsClient($profile);

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendSmsRequest();

        // 必填，设置雉短信接收号码
        $request->setPhoneNumbers($mobile);

        // 必填，设置签名名称
        $request->setSignName($signName);

        // 必填，设置模板CODE
        $request->setTemplateCode($templateCode);

        // 可选，设置模板参数
        if ($templateParam) {
            $request->setTemplateParam($templateParam);
        }

        // 发起访问请求
        $acsResponse = $acsClient->getAcsResponse($request);

        // 返回请求结果，这里为为数组格式
        $result = json_decode(json_encode($acsResponse), true);
        return $result;
    }


    // +----------------------------------------------------------------------
    // | @Des:批量发送短信
    // | @Param $mobile_json {json} 发送的批量电话号码，待发送手机号['15800000000','15800000000']。
    // | 支持JSON格式的批量调用，批量上限为100个手机号码,批量调用相对于单条调用及时性稍有延迟,验证码类型的短信推荐使用单条调用的方式
    // | @Param $sign_json {json} 发送的批量短信签名，支持不同的号码发送不同的短信签名['云通信','云通信']
    // | @Param $content_json {json} 发送的批量短信的内容，[{'name':'小王','code':'123456'},{'name':'小张','code':'123456'}]
    // | @Return json
    // +----------------------------------------------------------------------
    public function sendBatchSms($mobile_json, $sign_json, $content_json)
    {
        //获取成员属性
        $accessKeyId     = $this->accessKeyId;
        $accessKeySecret = $this->accessKeySecret;

        // 短信API产品名（短信产品名固定，无需修改）
        $product = "Dysmsapi";

        // 短信API产品域名（接口地址固定，无需修改）
        $domain = "dysmsapi.aliyuncs.com";

        // 暂时不支持多Region（目前仅支持cn-hangzhou请勿修改）
        $region = "cn-hangzhou";

        // 服务结点
        $endPointName = "cn-hangzhou";

        // 初始化用户Profile实例
        $profile = DefaultProfile::getProfile($region, $accessKeyId, $accessKeySecret);

        // 增加服务结点
        DefaultProfile::addEndpoint($endPointName, $region, $product, $domain);

        // 初始化AcsClient用于发起请求
        $acsClient = new DefaultAcsClient($profile);

        // 初始化SendSmsRequest实例用于设置发送短信的参数
        $request = new SendBatchSmsRequest();

        // 可选-启用https协议
        //$request->setProtocol("https");

        // 必填:待发送手机号。支持JSON格式的批量调用，批量上限为100个手机号码,批量调用相对于单条调用及时性稍有延迟,验证码类型的短信推荐使用单条调用的方式
        $request->setPhoneNumberJson($mobile_json, JSON_UNESCAPED_UNICODE);

        // 必填:短信签名-支持不同的号码发送不同的短信签名
        $request->setSignNameJson($sign_json, JSON_UNESCAPED_UNICODE);

        // 必填:短信模板-可在短信控制台中找到
        $request->setTemplateCode("SMS_1000000");

        // 必填:模板中的变量替换JSON串,如模板内容为"亲爱的${name},您的验证码为${code}"时,此处的值为
        // 友情提示:如果JSON中需要带换行符,请参照标准的JSON协议对换行符的要求,比如短信内容中包含\r\n的情况在JSON中需要表示成\\r\\n,否则会导致JSON在服务端解析失败
        $request->setTemplateParamJson($content_json, JSON_UNESCAPED_UNICODE);

        // 发起访问请求
        $acsResponse = $acsClient->getAcsResponse($request);
        return $acsResponse;
    }
}
