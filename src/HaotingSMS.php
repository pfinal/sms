<?php

namespace SMS;

use Leaf\Log;

/**
 * 豪霆云
 * http://www.haotingyun.com/
 */
class HaotingSMS implements SmsVerifyInterface
{
    public $key;
    public $signName;

    public function sendCode($phone, $code, &$error = null)
    {
        $text = "您的验证码是" . $code;
        return $this->sendMessage($phone, $text, $error);
    }

    /**
     * 批量发送短信
     * @param string $mobile 手机号 多个手机号用“,”隔开
     * @param string $content 短信内容
     * @param string $error 错误信息
     * @return array
     *
     * 返回结果
     *      return [
     *          'total_count' => 4,        总计发送计费条数
     *          'total_fee' => '0.2000',   总计扣费
     *          'unit' => 'RMB',
     *          'data' => [
     *               [
     *                  'code' => 0,
     *                  'count' => 2,
     *                  'fee' => '0.1',
     *                  'mobile' => '18888888888',
     *                  'msg' => '发送成功',
     *                  'sid' => '12373342444',
     *                  'unit' => 'RMB'
     *               ],
     *               [],
     *          ]
     *      ]
     */
    public function sendMessage($mobile, $content, &$error = '')
    {
        $apikey = $this->key;

        $content = "【" . $this->signName . "】" . $content . " 回T退订";

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
        $json_data = self::get_user($ch, $apikey);
        $array = json_decode($json_data, true);

        // 发送短信
        $data = array('text' => $content, 'apikey' => $apikey, 'mobile' => $mobile);
        $json_data = self::send($ch, $data);
        $array = json_decode($json_data, true);

        curl_close($ch);

        //判断结果
        if (!isset($array['total_count']) || $array['total_count'] == 0) {

            $error = '短信(msg)发送失败';
            if (isset($result['detail'])) {
                $error = '联系管理员:' . $result['detail'];
            }

            if (isset($result['data'])) {
                $temp = current($result['data']);

                $error = isset($temp['msg']) ? $temp['msg'] : '短信(msg)发送失败';
            }

            Log::error("短信发送接口失败\n短信内容：" . $content . "\n手机号：" . $mobile . "\n短信接口问题：" . $error);

            return false;
        }

        //返回成功的条数
        return $array['total_count'];
    }


    /**
     * 获得账户
     * @param $ch
     * @param $apikey
     * @return mixed
     */
    private function get_user($ch, $apikey)
    {
        curl_setopt($ch, CURLOPT_URL, 'http://sms.haotingyun.com/v2/user/get.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('apikey' => $apikey)));
        return curl_exec($ch);
    }

    /**
     * 批量发送
     * @param $ch
     * @param $data
     * @return mixed
     */
    private function send($ch, $data)
    {
        curl_setopt($ch, CURLOPT_URL, 'http://sms.haotingyun.com/v2/sms/batch_send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        return curl_exec($ch);
    }
}
