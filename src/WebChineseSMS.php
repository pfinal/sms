<?php

namespace SMS;

use Leaf\Log;

/**
 * 中国网站SMS短信平台
 *
 * $app['sms'] = function () {
 *       $sms = new \SMS\WebChineseSMS();
 *       $sms->uid = '项目';
 *       $sms->key = '7751456621d14802135';
 *       return $sms;
 *   };
 *
 * 发手机验证码
 * $bool = $app['sms']->sendCode('18688888888', '1234');
 *
 * http://sms.webchinese.com.cn/
 *
 * @author  Ma Yanlong
 * @since   1.0
 */
class WebChineseSMS implements SmsVerifyInterface
{
    //账号
    public $uid;

    //接口秘钥
    public $key;

    //GBK编码url
    private $urlGbk = 'http://gbk.sms.webchinese.cn/';

    //UTF-8编码url
    private $urlUtf8 = 'http://utf8.sms.webchinese.cn/';


    /**
     * 发验证码
     * @param string $phone 多个手机号请用半角,隔开
     * @param string $code 需要发送到手机上的验证码字符串
     * @return bool
     */
    public function sendCode($phone, $code, &$error = null)
    {
        //发送验证码消息模板 必须两个参数
        $data = "您本次操作的验证码是{$code}，请于1分钟内输入";
        return $this->sendTemplateSMS($phone, $data);
    }

    /**
     * 发送模板短信
     * @param $to   多个手机号请用半角,隔开
     * @param $data    发送内容数据 格式为字符串
     * @return bool
     */
    function sendTemplateSMS($to, $data)
    {
        $url = "http://utf8.sms.webchinese.cn/?Uid={$this->uid}&Key={$this->key}&smsMob={$to}&smsText={$data}";

        if (function_exists('file_get_contents')) {
            $file_contents = file_get_contents($url);
        } else {
            $ch = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $file_contents = curl_exec($ch);
            curl_close($ch);
        }

        //大于0为发送短信数量 小于零的全部是错误
        if ($file_contents > 0) {
            return true;
        }

        return false;
    }

}