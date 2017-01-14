<?php

namespace SMS;

use Leaf\Log;

/**
 * 开发环境模拟发送手机短信
 *
 * $app['sms'] = function () {
 *      return new \SMS\NullSMS();
 * };
 *
 * 发手机验证码
 * $bool = $app['sms']->sendCode('18688888888', '1234');
 * 发送成功后，在runtime/logs/debug.log 中查看内容
 *
 * @author  Zou Yiliang
 * @since   1.0
 */
class NullSMS implements SmsVerifyInterface, SmsTemplateInterface
{
    /**
     * 发验证码
     * @param string $phone
     * @param string $code 需要发送到手机上的验证码字符串
     * @return bool
     */
    public function sendCode($phone, $code, &$error = null)
    {
        Log::debug("$phone 验证码是 $code");
        return true;
    }

    /**
     * 发模板短信
     * @param string $phone
     * @param int $templateId
     * @param string $param
     * @return bool
     */
    public function templateSMS($phone, $templateId = null, $param = '', &$error = null)
    {
        return true;
    }
}
