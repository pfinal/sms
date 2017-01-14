<?php

namespace SMS;

use Leaf\Log;
use Leaf\View;

/**
 * 云片网络
 *
 * $app['sms'] = function () {
 *      $sms = new \SMS\YunPianSMS();
 *      $sms->apikey = '3b94f23458a605c038de7174555c';
 *      $sms->template = '【签名】您的验证码是{{code}}';
 *      return $sms;
 * };
 *
 * 发手机验证码
 * $bool = $app['sms']->sendCode('18688888888', 1234);
 *
 * https://www.yunpian.com
 *
 * @author  Zou Yiliang
 * @since   1.0
 */
class YunPianSMS implements SmsVerifyInterface, SmsTemplateInterface
{
    public $apikey;
    public $template = '【签名】您的验证码是{{code}}';//需要申请模板，内容与模板匹配才能发送成功

    /**
     * 发验证码
     * @param string $phone
     * @param string $code 需要发送到手机上的验证码字符串
     * @return bool
     */
    public function sendCode($phone, $code, &$error = null)
    {
        return $this->sendMessage($phone, View::renderText($this->template, ['code' => "$code"]), $error);
    }

    public function templateSMS($phone, $templateId = null, $params = array(), &$error = null)
    {
        return $this->sendMessage($phone, $templateId, $error);
    }

    /**
     * @param $phone
     * @param string $text 需要申请模板，内容与模板匹配才能发送成功
     * @return bool
     */
    protected function sendMessage($phone, $text, &$error = null)
    {
        //header("Content-Type:text/html;charset=utf-8");
        $apikey = $this->apikey; //修改为您的apikey(https://www.yunpian.com)登陆官网后获取
        $mobile = $phone; //请用自己的手机号代替

        $ch = curl_init();

        /* 设置验证方式 */

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded', 'charset=utf-8'));

        /* 设置返回结果为流 */
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        /* 设置超时时间*/
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        /* 设置通信方式 */
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // 取得用户信息
//        $json_data = self::get_user($ch, $apikey);
//        $array = json_decode($json_data, true);
//        echo '<pre>';
//        print_r($array);

        // 发送短信
        $data = array('text' => $text, 'apikey' => $apikey, 'mobile' => $mobile);
        $json_data = self::send($ch, $data);
        $array = json_decode($json_data, true);
        //echo '<pre>';
        //print_r($array);
        curl_close($ch);

        if (is_array($array) && $array['code'] === 0) {
            return true;
        } else {
            Log::error($json_data);
            $error = $json_data;
            return false;
        }


        // 发送模板短信
        // 需要对value进行编码
//        $data = array('tpl_id' => '1', 'tpl_value' => urlencode('#code#') . '=' . urlencode('1234') . '&' . urlencode('#company#') . '=' . urlencode('欢乐行'), 'apikey' => $apikey, 'mobile' => $mobile);
//        print_r($data);
//        $json_data = self::tpl_send($ch, $data);
//        $array = json_decode($json_data, true);
//        echo '<pre>';
//        print_r($array);

        // 发送语音验证码
//        $data = array('code' => '9876', 'apikey' => $apikey, 'mobile' => $mobile);
//        $json_data = self::voice_send($ch, $data);
//        $array = json_decode($json_data, true);
//        echo '<pre>';
//        print_r($array);

//        curl_close($ch);
    }



    /***************************************************************************************/
    //获得账户
    public static function get_user($ch, $apikey)
    {
        curl_setopt($ch, CURLOPT_URL, 'https://sms.yunpian.com/v1/user/get.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('apikey' => $apikey)));
        return curl_exec($ch);
    }

    public static function send($ch, $data)
    {
        curl_setopt($ch, CURLOPT_URL, 'https://sms.yunpian.com/v1/sms/send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        return curl_exec($ch);
    }

    public static function tpl_send($ch, $data)
    {
        curl_setopt($ch, CURLOPT_URL, 'https://sms.yunpian.com/v1/sms/tpl_send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        return curl_exec($ch);
    }

    public static function voice_send($ch, $data)
    {
        curl_setopt($ch, CURLOPT_URL, 'http://voice.yunpian.com/v1/voice/send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        return curl_exec($ch);
    }


}