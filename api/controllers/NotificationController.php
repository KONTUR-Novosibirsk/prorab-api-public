<?php

namespace app\controllers;

use app\components\bots\TelegramBot;
use app\components\QueryActiveController;
use app\models\User;
use Throwable;
use Yii;

class NotificationController extends QueryActiveController
{
    public $modelClass = 'app\models\Notification';

    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['authenticator']['except'] = ['options', 'get-updates', 'tg-updates', 'send', 'set-tg-webhook'];
        $behaviors['access']['rules'] = array_merge($behaviors['access']['rules'], [
            [
                'allow' => true,
                'actions' => ['tg-updates', 'send', 'set-tg-webhook', 'get-updates']
            ],
            [
                'allow' => true,
                'actions' => ['index', 'view', 'amount-new'],
                'roles' => ['user']
            ]
        ]);
        return $behaviors;
    }

    public function actionSend($text)
    {
        // if(!Yii::$app->user->chat_id){
        //     return false;
        // }

        $botClass = new TelegramBot($text /* , Yii::$app->user->chat_id */ );

        return $botClass->sendMessage();
    }

	// public function actionGetUpdates()
  	// {
    // 	$botName = User::getMessengerBot(1);
    // 	$botClass = new $botName();
    // 	return $botClass->getUpdates();
  	// }

    public function actionSetTgWebhook()
    {
        $botClass = new TelegramBot();

        return $botClass->setWebhook();
    }

    public function beforeAction($action)
    {
        if (in_array($action->id, ['view'])) {
            $result = $action->run();
            if ($result->new) {
                $result->new = 0;
                $result->save();
            }
        }
        return parent::beforeAction($action);
    }

    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        if (in_array($action->id, ['index'])) {
            $ids = [];
            foreach ($result as $item) {
                $ids[] = $item['id'];
            }
            if (count($ids)) {
                $sql = "UPDATE notification SET new = 0 WHERE id IN (" . implode(', ', $ids) . ")";
                \Yii::$app->db->createCommand($sql)->execute();
            }
        }
        return $result;
    }

    public function actionTgUpdates()
    {
      	try {
          $data = file_get_contents('php://input');

          if($data){
              $data = json_decode($data, true);
              
              $user = User::find()->where('telegram = :t', [':t' => $data['message']['from']['username']])->one();

              if(!$user) return true;

              $user->chat_id = $data['message']['chat']['id'];

              $user->save(false);
              
          }
        } catch (Throwable $e) {
        	file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/tg/pogg.log", $e->getMessage());
        }
      
      	if(YII_DEBUG){
        	file_put_contents($_SERVER['DOCUMENT_ROOT'] . "/tg/pogg.txt", print_r($data, true));
        }
      
        return true;
    }

    public function actionAmountNew()
    {
        return [
            'amountNew' => $this->modelClass::find()->where(['user_id' => \Yii::$app->user->id, 'new' => 1])->count()
        ];
    }
}
