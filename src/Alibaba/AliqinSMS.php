<?php

namespace SMS\Alibaba;

require_once __DIR__ . "/TopSdk.php";

use Leaf\Log;
use SMS\SmsTemplateInterface;
use SMS\SmsVerifyInterface;

/**
 * 阿里大鱼通信能力包
 *
 * $app['sms'] = function () {
 *      $sms = new \SMS\Alibaba\AliqinSMS();
 *      $sms->appkey = '32330799877';
 *      $sms->secretKey = '118175b9ef25dea79d837ea2800542';
 *      $sms->templateId = 'SMS_000000';
 *      $sms->signName = 'XX公司';
 *
 *      //语音验证码
 *      $sms->calledShowNum = '051482043268';
 *      $sms->ttsCode = 'TTS_10276397';
 *
 *      return $sms;
 * };
 *
 * 发手机验证码
 * $bool = $app['sms']->sendCode('18688888888', '1234');
 *
 * http://open.taobao.com/
 *
 * @author  Zou Yiliang
 * @since   1.0
 */
class AliqinSMS implements SmsVerifyInterface, SmsTemplateInterface
{
    //https://my.open.taobao.com/app/app_list.htm
    public $appkey;
    public $secretKey;
    // http://www.alidayu.com/admin/service/sign
    public $signName;

    //发验证码时，需要指定模板id
    //http://www.alidayu.com/admin/service/tpl
    //您的验证码是${code}
    public $templateId;


    //语音验证码
    public $calledShowNum; //语音号码 http://www.alidayu.com/admin/service/num
    public $ttsCode; //文本转语音模板 http://www.alidayu.com/admin/service/tts


    /**
     * 发验证码
     * @param string $phone
     * @param string $code 需要发送到手机上的验证码字符串
     * @return bool
     */
    public function sendCode($phone, $code, &$error = null)
    {
        return $this->templateSMS($phone, $this->templateId, ['code' => "$code"], $error);
    }

    /**
     * 发送模板短信
     * @param $phone
     * @param $templateId
     * @param array $params
     * @return bool
     */
    public function templateSMS($phone, $templateId = '', $params = array(), &$error = null)
    {
        //date_default_timezone_set('Asia/Shanghai');
        //$params = array('orderId' => 'abcddef');

        $c = new \TopClient;
        $c->appkey = $this->appkey;
        $c->secretKey = $this->secretKey;
        $req = new \AlibabaAliqinFcSmsNumSendRequest;
        $req->setExtend("");
        $req->setSmsType("normal");
        $req->setSmsFreeSignName($this->signName);
        //$req->setSmsParam("{\"orderId\":\"abcd\"}");
        $req->setSmsParam(json_encode($params));
        $req->setRecNum($phone);
        $req->setSmsTemplateCode($templateId);
        $resp = $c->execute($req);

        //var_dump($resp);
        //object(SimpleXMLElement)#4 (2) { ["result"]=> object(SimpleXMLElement)#3 (3) { ["err_code"]=> string(1) "0" ["model"]=> string(26) "100653573991^1101056656784" ["success"]=> string(4) "true" } ["request_id"]=> string(12) "z24c5xyshwlp" }

        if (!$resp->code && $resp->result->err_code == 0) {
            return true;
        } else {
            $error = json_encode((array)$resp);
            return false;
        }
    }


    /**
     * 发送语音验证码
     * @param string $phone
     * @param string $code 需要发送到手机上的验证码字符串
     * @return bool
     */
    public function sendVoiceCode($phone, $code, &$error = null)
    {

        $params = ['code' => "$code"];

        $c = new \TopClient;
        $c->appkey = $this->appkey;
        $c->secretKey = $this->secretKey;
        $req = new \AlibabaAliqinFcTtsNumSinglecallRequest;
        $req->setCalledNum($phone);
        $req->setCalledShowNum($this->calledShowNum);
        $req->setTtsCode($this->ttsCode);
        $req->setTtsParam(json_encode($params));
        $req->setExtend("");
        $resp = $c->execute($req);

        if (!$resp->code && $resp->result->err_code == 0) {
            return true;
        } else {
            $error = json_encode((array)$resp);
            return false;
        }

    }


}