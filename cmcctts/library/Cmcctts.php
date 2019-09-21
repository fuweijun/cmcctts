<?php

namespace addons\cmcctts\library;

/**
 * 中国移动语音通知
 */
class Cmcctts
{
    private $_params = [];
    public $error = '';
    protected $config = [];

    public function __construct($options = [])
    {
        if ($config = get_addon_config('cmcctts')) {
            $this->config = array_merge($this->config, $config);
        }
        $this->config = array_merge($this->config, is_array($options) ? $options : []);
    }

    /**
     * 单例
     * @param array $options 参数
     * @return Cmcctts
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }

        return self::$instance;
    }

    /**
     * 设置重播次数
     * @param string $replayTimes
     * @return Cmcctts
     */
    public function replay($replayTimes = '1')
    {
        $this->config['replayTimes'] = $replayTimes;
        return $this;
    }

    /**
     * 设置模板
     * @param string $code 设置模板
     * @return Cmcctts
     */
    public function template($code = '')
    {
        $this->config['ttsTemplateId'] = $code;
        return $this;
    }


    /**
     * 获取错误信息
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /*
     * @desc 语音验证码
     * mobile 手机号
     *
     * */
    public function cmccVoice($mobile, $param = null)
    {
        // $successNum = session('successNum');
        // if ($successNum >=3){
        //   return false;
        // }
        $url = 'http://aep.api.cmccopen.cn/tropo/tts2NoteTemplate/v2';
        $appkey = $this->config['appkey'];
        $appsecret = $this->config['appsecret'];
        $time = time() - 8 * 3600;
        $Created = date('Y-m-d\TH:i:s\Z', $time);
        $Nonce = md5($Created . rand(1000000, 9999999));
        $PasswordDigest = $this->buildPasswordDigest($Nonce, $Created, $appsecret);
        if (!empty($param)) {
            $data['paramValue'] = $param;
        } else {
            $data['paramValue'] = (object)array();
        }
        $data['developerCDR'] = 'rZN1CzVoe7pL0bmHdWYh3oKzHwCMQ22wTtKLdhMyJ1O2wXRTC5';
        $data['displayNbr'] = $this->config['displayNbr'];
        $data['calleeNbr'] = '+86' . $mobile;
        $data['ttsTemplateId'] = $this->config['ttsTemplateId'];
        $data['replayTimes'] = $this->config['replayTimes'];

        $opt_data = json_encode($data);

        $header = array();
        $header[] = 'Accept:application/json';
        $header[] = 'Content-Type:application/json;charset=UTF-8';
        $header[] = 'Authorization:WSSE realm="SDP",profile="UsernameToken",type="Appkey"';
        $header[] = 'X-WSSE:UsernameToken Username="' . $appkey . '",PasswordDigest="' . $PasswordDigest . '",Nonce="' . $Nonce . '",Created="' . $Created . '"';
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $opt_data);
        curl_setopt($curl, CURLINFO_HEADER_OUT, true);
        $result = curl_exec($curl);
        //var_dump(curl_getinfo($curl));
        //var_dump($result);
        if ($result === false) {
            return curl_errno($curl);
        }
        curl_close($curl);
        $content = json_decode($result, true);
        if (is_array($content)) {
            if ($content['code'] == '0000000') {
                return true;
            } else {
                $this->error = $content['description'];
                return false;
            }
        } else {
            $this->error = '平台接口返回内容为空';
            return false;
        }
    }

    /*
     * @desc 生成参数签名
     *
     * */
    private function buildPasswordDigest($Nonce, $Created, $appsecret)
    {
        $re = $Nonce . $Created . $appsecret;
        $re = hash('sha256', $re, true);
        $re = base64_encode($re);
        return $re;
    }
}
