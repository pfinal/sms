<?php

namespace SMS;

/**
 * 企讯通（分享科技）
 */
class QiXunTongSMS implements SmsTemplateInterface
{
    public $username;
    public $password;

    public $signName; // 例如: 某某公司

    /**
     * @param string $phone 多个手机号，用英文逗号分隔，最多1000个
     * @param null $templateId
     * @param array $params ['content'=>'内容']
     * @param null $error
     * @return bool
     */
    public function templateSMS($phone, $templateId = null, $params = array(), &$error = null)
    {
        $content = $params['content'];

        //短信内容，手机限70字，小灵通限54字，超长系统自动拆分
        $content = $content . " 退订回N【" . $this->signName . "】";

        $res = $this->sendMessage($this->username, $this->password, $phone, $content);

        if ($res === 'SUCCESS') {
            return true;
        }

        $error = $res;

        return false;
    }


    private function sendMessage($username, $password, $phones, $contents, $scode = '', $setTime = '')
    {
        $srv_ip = 'www.82009668.com';
        $srv_port = 888;
        $url = '/sdk/Service.asmx/sendMessage';
        $fp = '';
        $resp_str = '';
        $errno = 0;
        $errstr = '';
        $timeout = 10;
        $post_str = "username=" . $username . "&pwd=" . $password . "&phones=" . $phones . "&contents=" . $contents . "&scode=" . $scode . "&setTime=" . $setTime;
        $err = '';
        if ($srv_ip == '' || $url == '') {
            return 'ip or dest url empty';
        }
        $fp = fsockopen($srv_ip, $srv_port, $errno, $errstr, $timeout);
        if (!$fp) {
            return 'fp fail';
        }
        $content_length = strlen($post_str);
        $post_header = "POST $url HTTP/1.1\r\n";
        $post_header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $post_header .= "User-Agent: MSIE\r\n";
        $post_header .= "Host: " . $srv_ip . "\r\n";
        $post_header .= "Content-Length: " . $content_length . "\r\n";
        $post_header .= "Connection: close\r\n\r\n";
        $post_header .= $post_str . "\r\n\r\n";
        fwrite($fp, $post_header);
        $inheader = 1;
        while (!feof($fp)) {
            $line = fgets($fp, 512);
            if ($inheader && ($line == "\n" || $line == "\r\n")) {
                $inheader = 0;
            }
            if ($inheader == 0) {
                $resp_str .= $line;
            }
        }
        $bodytag = trim($resp_str);
        fclose($fp);

        $dom = new \DOMDocument('1.0');
        $dom->loadXML($bodytag);
        $xml = simplexml_import_dom($dom);
        $res = $xml;
        unset ($resp_str);
        if ("$res" === '1') {
            return 'SUCCESS';
        } else {
            return 'ERROR ' . $res;
        }
    }
}
