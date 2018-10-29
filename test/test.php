<?php

use SMS\Aliyun\AliyunSMS;

require dirname(__DIR__) . '/vendor/autoload.php';

$sms = new AliyunSMS();

$sms->accessKeyId = 'asdfghfd';
$sms->accessKeySecret = 'dasfghjrefs';
$sms->signName = '甩甩卖';
$sms->templateCode = 'SMS_149355683';

var_dump($sms->sendCode('18611111111', '1234', $error));
var_dump($error);
