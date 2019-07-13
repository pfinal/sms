<?php

namespace SMS;

/**
 * 功能：创蓝接口请求类
 * 详细：构造创蓝短信接口请求，获取远程HTTP数据
 * 版本：1.3
 *
 * $sms = new ChuanglanSMS();
 * $sms->signName = '甩甩卖';
 * $sms->apiAccount = 'N12345678';
 * $sms->apiPassword = 'abcdefg';
 *
 * if (!$sms->sendCode('18610089516', 1234, $error)) {
 *     echo $error;
 * } else {
 *     echo 'success';
 * }
 *
 */
class ChuanglanSMS implements SmsVerifyInterface
{
    //参数的配置 请登录zz.253.com 获取信息

    public $apiSendUrl = 'http://smssh1.253.com/msg/send/json'; //创蓝发送短信接口URL
    public $apiVariableUrl = 'http://smssh1.253.com/msg/variable/json';//创蓝变量短信接口URL
    public $apiBalanceQueryUrl = 'http://smssh1.253.com/msg/balance/json';//创蓝短信余额查询接口URL

    public $apiAccount = 'xxx'; // 创蓝API账号
    public $apiPassword = 'xxx';// 创蓝API密码
    public $signName = '';
    public $template = '验证码是%s，您正在进行身份验证，请勿将验证码告诉他人。哈哈';

    public function sendCode($phone, $code, &$error = null)
    {
        $content = sprintf($this->template, $code);

        $result = $this->sendSMS($phone, '【' . $this->signName . '】' . $content);
        if (!is_null(json_decode($result))) {
            $output = json_decode($result, true);
            if (isset($output['code']) && $output['code'] == '0') {
                return true;
            } else {
                $error = $result;
            }
        } else {
            $error = $result;
        }
        return false;
    }

    /**
     * 发送短信
     *
     * @param string $mobile 手机号码
     * @param string $msg 短信内容
     * @param string $needstatus 是否需要状态报告
     */
    protected function sendSMS($mobile, $msg, $needstatus = 'true')
    {

        //创蓝接口参数
        $postArr = array(
            'account' => $this->apiAccount,
            'password' => $this->apiPassword,
            'msg' => urlencode($msg),
            'phone' => $mobile,
            'report' => $needstatus,
        );
        $result = $this->curlPost($this->apiSendUrl, $postArr);
        return $result;
    }

    /**
     * 发送变量短信
     *
     * @param string $msg 短信内容
     * @param string $params 最多不能超过1000个参数组
     */
    public function sendVariableSMS($msg, $params)
    {

        //创蓝接口参数
        $postArr = array(
            'account' => $this->apiAccount,
            'password' => $this->apiPassword,
            'msg' => $msg,
            'params' => $params,
            'report' => 'true'
        );

        $result = $this->curlPost($this->apiVariableUrl, $postArr);
        return $result;
    }

    /**
     * 查询额度
     *
     *  查询地址
     */
    public function queryBalance()
    {

        //查询参数
        $postArr = array(
            'account' => $this->apiAccount,
            'password' => $this->apiPassword,
        );
        $result = $this->curlPost($this->apiBalanceQueryUrl, $postArr);
        return $result;
    }

    /**
     * 通过CURL发送HTTP请求
     * @param string $url //请求URL
     * @param array $postFields //请求参数
     * @return mixed
     *
     */
    private function curlPost($url, $postFields)
    {
        $postFields = json_encode($postFields);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json; charset=utf-8'   //json版本需要填写  Content-Type: application/json;
            )
        );
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //若果报错 name lookup timed out 报错时添加这一行代码
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $ret = curl_exec($ch);
        if (false == $ret) {
            $result = curl_error($ch);
        } else {
            $rsp = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 != $rsp) {
                $result = "请求状态 " . $rsp . " " . curl_error($ch);
            } else {
                $result = $ret;
            }
        }
        curl_close($ch);
        return $result;
    }
}

