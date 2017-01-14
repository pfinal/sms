<?php

namespace SMS;

/**
 * 手机发送验证码接口
 */
interface SmsVerifyInterface
{
    public function sendCode($phone, $code, &$error = null);
}