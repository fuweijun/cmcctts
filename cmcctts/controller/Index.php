<?php

namespace addons\cmcctts\controller;

use think\addons\Controller;

/**
 * 语音通知/语音验证码
 *
 */
class Index extends Controller
{

    protected $model = null;

    public function _initialize()
    {
        if (!\app\admin\library\Auth::instance()->id) {
            $this->error('暂无权限浏览');
        }
        parent::_initialize();
    }

    public function index()
    {
        return $this->view->fetch();
    }

    public function send()
    {
        $config = get_addon_config('cmcctts');
        $mobile = $this->request->post('mobile');
        $template = $this->request->post('template');
        $replayTimes = $this->request->post('replayTimes');
        if (!$mobile) {
            $this->error('手机号不能为空');
        }
        $template = $template ? $template : $config['ttsTemplateId'];

        $replayTimes = $replayTimes ? $replayTimes : $config['replayTimes'];

        $param = $this->request->post('param');
        if ($param) {
            $param = htmlspecialchars_decode($param);
            $param = json_decode($param, true);
        }

        $cmcctts = new \addons\cmcctts\library\Cmcctts();
        $ret = $cmcctts->template($template)->replay($replayTimes)->cmccVoice($mobile, $param);
        if ($ret) {
            $this->success("操作成功");
        } else {
            $this->error("操作失败！失败原因：" . $cmcctts->getError());
        }
    }

}
