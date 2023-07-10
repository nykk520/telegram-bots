<?php

namespace app;

use Telegram\Bot\Api;


$telegram = new Api('6299876389:AAF8QSEztrGOUZ5WDvhNjyyLiOd_dKyUt0E');
$response = $telegram->setWebhook(['url' => 'https://earn.fengfupay.vip/admin/telegram/webhook']);
if (!$response) {
    file_put_contents('/tmp/tglog', '启动tg机器人失败！');
} else {
    $response = $telegram->getMe();
    file_put_contents('/tmp/tglog', '启动tg机器人成功：' . json_encode($response));
}
