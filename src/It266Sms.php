<?php

namespace SMS;

use PFinal\Http\Client;

class It266Sms implements SmsVerifyInterface
{
    public $gateway;
    public $appId;
    public $appSecret;

    public function sendCode($phone, $code, &$error = null)
    {
        $client = new Client();
        $res = $client->post($this->gateway . '/api/sms/send/verify', array('mobile' => $phone, 'code' => $code, 'app_id' => $this->appId, 'sign' => $this->getSign()));
        if (!$res->getStatusCode() == 200) {
            $error = 'http error ' . $res->getStatusCode();
            return false;
        }

        $body = $res->getBody();
        $json = @json_decode($body, true);
        if (is_array($json) && array_key_exists('code', $json) && $json['code'] === 'SUCCESS') {
            return true;
        }

        $error = $body;
        return false;
    }

    private function getSign()
    {
        return strtolower(md5($this->appId . $this->appSecret));
    }
}