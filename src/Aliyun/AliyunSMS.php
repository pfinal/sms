<?php

namespace SMS\Aliyun;

use SMS\SmsVerifyInterface;
use SMS\SmsTemplateInterface;

/**
 * $app['sms'] = function () {
 *     $sms = new \SMS\Aliyun\AliyunSMS();
 *     $sms->accessKeyId = 'AUAIiJlAPQ3hufbe';
 *     $sms->accessKeySecret = 'a4FpqiCMCYWJNVwgfMOqAb3';
 *     $sms->templateCode = 'SMS_149355683';
 *     $sms->signName = '甩甩卖';
 *     return $sms;
 * };
 */
class AliyunSMS implements SmsVerifyInterface, SmsTemplateInterface
{
    public $accessKeyId;
    public $accessKeySecret;
    public $signName;
    public $templateCode;

    public function templateSMS($phone, $templateId = null, $params = array(), &$error = null)
    {
        return $this->sendSms($phone, $templateId, $params, $error);
    }

    public function sendCode($phone, $code, &$error = null)
    {
        return $this->sendSms($phone, $this->templateCode, array('code' => $code), $error);
    }

    /**
     * 发送短信
     */
    private function sendSms($phoneNumbers, $templateCode, $templateParam, &$error = null)
    {
        $params = array();

        // fixme 必填：是否启用https
        $security = false;

        // fixme 必填: 请参阅 https://ak-console.aliyun.com/ 取得您的AK信息
        $accessKeyId = $this->accessKeyId;
        $accessKeySecret = $this->accessKeySecret;

        // fixme 必填: 短信接收号码
        $params["PhoneNumbers"] = $phoneNumbers;

        // fixme 必填: 短信签名，应严格按"签名名称"填写，请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/sign
        $params["SignName"] = $this->signName;

        // fixme 必填: 短信模板Code，应严格按"模板CODE"填写, 请参考: https://dysms.console.aliyun.com/dysms.htm#/develop/template
        $params["TemplateCode"] = $templateCode;

        // fixme 可选: 设置模板参数, 假如模板中存在变量需要替换则为必填项
        // $params['TemplateParam'] = Array(
        //   "code" => "12345",
        // );
        $params['TemplateParam'] = $templateParam;

        // fixme 可选: 设置发送短信流水号
        //$params['OutId'] = "12345";

        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        //$params['SmsUpExtendCode'] = "1234567";

        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }

        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();

        try {
            // 此处可能会抛出异常，注意catch
            $content = $helper->request(
                $accessKeyId,
                $accessKeySecret,
                "dysmsapi.aliyuncs.com",
                array_merge($params, array(
                    "RegionId" => "cn-hangzhou",
                    "Action" => "SendSms",
                    "Version" => "2017-05-25",
                )),
                $security
            );

            $error = $content->Message;
            return $content->Code == 'OK';

        } catch (\Exception $ex) {
            $error = $ex->getMessage();
            return false;
        }
    }
}
