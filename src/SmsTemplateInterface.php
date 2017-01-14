<?php

namespace SMS;

/**
 * 手机发送模板短信接口
 */
interface SmsTemplateInterface
{
    public function templateSMS($phone, $templateId = null, $params = array(), &$error = null);
}