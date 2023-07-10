<?php

// +----------------------------------------------------------------------
// | EasyAdmin
// +----------------------------------------------------------------------
// | PHP交流群: 763822524
// +----------------------------------------------------------------------
// | 开源协议  https://mit-license.org 
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zhongshaofa/EasyAdmin
// +----------------------------------------------------------------------

namespace app\admin\controller;


use Telegram\Bot\Api;



/**
 * Class Telegram
 * @package app\admin\controller
 */
class Telegram extends Base
{

    public $telegram;

    public function __construct()
    {
        $this->telegram = new Api('6299876389:AAF8QSEztrGOUZ5WDvhNjyyLiOd_dKyUt0E');
    }


    public function webhook()
    {
        try {
            $post = request()->post();
            if (isset($post['message']) && !empty($post['message']['text']) && $post['message']['chat']['type'] == 'group') {
                $text = $post['message']['text'];
                $chatid = abs($post['message']['chat']['id']);
                if ($chatid != '' && $chatid) {
                    switch ($text) {
                        case "群聊id":
                            $this->telegram->sendMessage([
                                'chat_id' => $post['message']['chat']['id'],
                                'text' => abs($post['message']['chat']['id'])
                            ]);
                            break;
                        case "查询余额":
                            $MerchUser = new MerchUser();
                            $balance = $MerchUser->where(['robotid' => $chatid])->find();
                            if (isset($balance) && !empty($balance)) {
                                $this->telegram->sendMessage([
                                    'chat_id' => $post['message']['chat']['id'],
                                    'text' => '当前余额：' . $balance['money'] . "\n当前冻结金额：" . $balance['frozen']
                                ]);
                            }
                        default:
                            $pattern = "/^查单\ \w{12,46}$/";
                            $orderStatus = ['新建', '接单', '待验证', '成功', '失败', '超时', '取消'];
                            if (preg_match($pattern, $text)) {
                                $parts = explode(" ", $text);
                                $oid = $parts[1];
                                $MerchOrder = new MerchOrder();
                                $data = $MerchOrder->where(['orid' => $oid])->find();
                                if (isset($data) && !empty($data)) {
                                    $this->telegram->sendMessage([
                                        'chat_id' => $post['message']['chat']['id'],
                                        'reply_to_message_id' => $post['message']['message_id'],
                                        'text' => '当前订单状态：' . $orderStatus[abs($data['status'])]
                                    ]);
                                } else {
                                    $this->telegram->sendMessage([
                                        'chat_id' => $post['message']['chat']['id'],
                                        'reply_to_message_id' => $post['message']['message_id'],
                                        'text' => '查无此订单'
                                    ]);
                                }
                            }
                    }
                }
            }
        } catch (\Exception $e) {
            file_put_contents('/tmp/tglog', $e->getMessage());
        }
        file_put_contents('/tmp/tglog', json_encode($post));
        return $this->success('success');
    }
}
