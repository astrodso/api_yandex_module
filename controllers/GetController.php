<?php

namespace app\modules\api_yandex\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\QueryParamAuth;
use yii\data\ActiveDataProvider;
use yii\web\Response;
use \app\modules\api_yandex\models\YandexMetrika;

class GetController extends ActiveController {

    public $modelClass = 'app\modules\api_yandex\models\YandexMetrika';

    public function behaviors() {
        $behaviors = parent::behaviors();
        $behaviors['authenticator'] = [
            'class' => QueryParamAuth::className(),
        ];
        $behaviors['authenticator']['tokenParam'] = 'token';
        $behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_JSON;
        return $behaviors;
    }

    public function actions() {
        $actions = parent::actions();

        // disable the "delete" and "create" actions
        unset($actions['delete'], $actions['create']);

        return $actions;
    }

    //рекламные системы - посетители Yandex Директ
    public function actionYdusers() {

        $yandexMetrika = YandexMetrika::getStatisticsByKey('advertising-users-yandeks-direkt')->one();

        if (empty($yandexMetrika)) {
            $this->modelClass::updateStatistics();
        }

        return new ActiveDataProvider([
            'query' => YandexMetrika::getStatisticsByKey('advertising-users-yandeks-direkt')
        ]);
    }

    //рекламные системы - посетители Google Adwords
    public function actionGawusers() {

        $yandexMetrika = YandexMetrika::getStatisticsByKey('advertising-users-google-adwords')->one();

        if (empty($yandexMetrika)) {
            $this->modelClass::updateStatistics();
        }

        return new ActiveDataProvider([
            'query' => YandexMetrika::getStatisticsByKey('advertising-users-google-adwords')
        ]);
    }

    //рекламные системы - визиты Yandex Директ
    public function actionYdvisits() {

        $yandexMetrika = YandexMetrika::getStatisticsByKey('advertising-visits-yandeks-direkt')->one();

        if (empty($yandexMetrika)) {
            $this->modelClass::updateStatistics();
        }

        return new ActiveDataProvider([
            'query' => YandexMetrika::getStatisticsByKey('advertising-visits-yandeks-direkt')
        ]);
    }

    //рекламные системы - визиты Google Adwords
    public function actionGavisits() {

        $yandexMetrika = YandexMetrika::getStatisticsByKey('advertising-visits-google-adwords')->one();

        if (empty($yandexMetrika)) {
            $this->modelClass::updateStatistics();
        }

        return new ActiveDataProvider([
            'query' => YandexMetrika::getStatisticsByKey('advertising-visits-google-adwords')
        ]);
    }

    //посещаемость основного сайта
    public function actionSite() {

        $yandexMetrika = YandexMetrika::getStatisticsByKey('totals-users')->one();

        if (empty($yandexMetrika)) {
            $this->modelClass::updateStatistics();
        }

        return new ActiveDataProvider([
            'query' => YandexMetrika::getStatisticsByKey('totals-users')
        ]);
    }

    //Посещаемость из Яндекса
    public function actionYandex() {

        $yandexMetrika = YandexMetrika::getStatisticsByKey('users-yandeks-rezultaty-poiska')->one();

        if (empty($yandexMetrika)) {
            $this->modelClass::updateStatistics();
        }

        return new ActiveDataProvider([
            'query' => YandexMetrika::getStatisticsByKey('users-yandeks-rezultaty-poiska')
        ]);
    }

    //Посещаемость из Google
    public function actionGoogle() {

        $yandexMetrika = YandexMetrika::getStatisticsByKey('users-google-rezultaty-poiska')->one();

        if (empty($yandexMetrika)) {
            $this->modelClass::updateStatistics();
        }

        return new ActiveDataProvider([
            'query' => YandexMetrika::getStatisticsByKey('users-google-rezultaty-poiska')
        ]);
    }

    //Рост процента возвратов
    public function actionPercentage() {

        $yandexMetrika = YandexMetrika::getStatisticsByKey('totals-percentage-of-returns')->one();

        if (empty($yandexMetrika)) {
            $this->modelClass::updateStatistics();
        }

        return new ActiveDataProvider([
            'query' => YandexMetrika::getStatisticsByKey('totals-percentage-of-returns')
        ]);
    }

    //Снижение показателя отказов
    public function actionBounce() {

        $yandexMetrika = YandexMetrika::getStatisticsByKey('totals-bounce-rate')->one();

        if (empty($yandexMetrika)) {
            $this->modelClass::updateStatistics();
        }

        return new ActiveDataProvider([
            'query' => YandexMetrika::getStatisticsByKey('totals-bounce-rate')
        ]);
    }

    //Рост глубины просмотра
    public function actionDepth() {

        $yandexMetrika = YandexMetrika::getStatisticsByKey('totals-page-depth')->one();

        if (empty($yandexMetrika)) {
            $this->modelClass::updateStatistics();
        }

        return new ActiveDataProvider([
            'query' => YandexMetrika::getStatisticsByKey('totals-page-depth')
        ]);
    }

    //Рост времени нахождения на сайте
    public function actionDuration() {

        $yandexMetrika = YandexMetrika::getStatisticsByKey('totals-avg-visit-duration-seconds')->one();

        if (empty($yandexMetrika)) {
            $this->modelClass::updateStatistics();
        }

        return new ActiveDataProvider([
            'query' => YandexMetrika::getStatisticsByKey('totals-avg-visit-duration-seconds')
        ]);
    }

}
