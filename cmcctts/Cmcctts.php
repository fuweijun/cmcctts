<?php

namespace addons\cmcctts;

use app\common\library\Menu;
use think\Addons;

/**
 * 插件
 */
class Cmcctts extends Addons
{

    /**
     * 插件安装方法
     * @return bool
     */
    public function install()
    {
        return true;
    }

    /**
     * 插件卸载方法
     * @return bool
     */
    public function uninstall()
    {
        return true;
    }

    /**
     * 插件启用方法
     * @return bool
     */
    public function enable()
    {
        return true;
    }

    /**
     * 插件禁用方法
     * @return bool
     */
    public function disable()
    {
        return true;
    }


    /**
     * 发送行为
     * @param Sms $params
     * @return  boolean
     */
    public function smsSend(&$params)
    {
        $config = get_addon_config('cmcctts');
        if (!$config['sendsyscode']) {
            return true;
        }
        $cmcctts = new library\Cmcctts();
        if (!empty($config['template'][$params->event])) {//模板未配置则使用默认模板
            $ttsTemplateId = $config['template'][$params->event];
        } else {
            $ttsTemplateId = $config['ttsTemplateId'];
        }
        $result = $cmcctts->template($ttsTemplateId)->cmccVoice(
            $params->mobile,
            ['code' => $params->code]
        );
        return $result;
    }


    /**
     * 检测验证是否正确
     * @param Sms $params
     * @return  boolean
     */
    public function smsCheck(&$params)
    {
        return true;
    }
}
