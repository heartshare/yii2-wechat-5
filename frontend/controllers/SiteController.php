<?php

namespace frontend\controllers;

use common\models\PhoneBook;
use Yii;
/**
 * Created by PhpStorm.
 * User: yidashi
 * Date: 16/5/20
 * Time: 下午2:58
 */
class SiteController extends Controller
{
    public function beforeAction($action)
    {
        Yii::$container->set('yii\web\XmlResponseFormatter', [
            'rootTag' => 'xml'
        ]);
        return parent::beforeAction($action);
    }

    public function actionIndex()
    {
        $xml = Yii::$app->request->getRawBody();
        $params = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $msgType = $params['MsgType'];
        if ($msgType != 'text') {
            return [
                'ToUserName' => $params['FromUserName'], //接收方帐号（收到的OpenID）
                'FromUserName' => $params['ToUserName'], //开发者微信号
                'CreateTime' => time(),
                'MsgType' => 'text',
                'Content' => '打字好么,语音暂时识别不了!'
            ];
        }
        $name = trim($params['Content']);
        $model = PhoneBook::find()->where(['true_name' => $name])->orWhere(['nick_name' => $name])->one();
        if (empty($model)) {
            return [
                'ToUserName' => $params['FromUserName'], //接收方帐号（收到的OpenID）
                'FromUserName' => $params['ToUserName'], //开发者微信号
                'CreateTime' => time(),
                'MsgType' => 'text',
                'Content' => '不认识'
            ];
        }
        return [
            'ToUserName' => $params['FromUserName'], //接收方帐号（收到的OpenID）
            'FromUserName' => $params['ToUserName'], //开发者微信号
            'CreateTime' => time(),
            'MsgType' => 'text',
            'Content' => $model->phone
        ];
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = '51siyuan';
        $tmpArr = [$token, $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }
}