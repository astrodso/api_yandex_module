<?php

namespace app\modules\api_yandex\models;

use Yii;
use app\modules\api_yandex\models\YandexApi;

/**
 * This is the model class for table "yandex_metrika".
 *
 * @property integer $id
 * @property string $name
 * @property string $key
 * @property string $value
 * @property string $time
 */
class YandexMetrika extends \yii\db\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%yandex_metrika}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
                [['name', 'key', 'value', 'time'], 'required'],
                [['time'], 'safe'],
                [['name', 'key', 'value'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'    => 'ID',
            'name'  => 'Name',
            'key'   => 'Key',
            'value' => 'Value',
            'time'  => 'Time',
        ];
    }

    public static function updateStatistics()
    {

        $module = Yii::$app->controller->module;
        $params = Yii::$app->request->queryParams;

        $metrika_api = new YandexApi($module->params['token']);

        //получаем все счетчики
        $counters = $metrika_api->getCounters();

        $counter_id = $counters->getCounter($params['domain']);

        //если получили id счетчика
        if ($counter_id > 0) {

            $metrika_api->setCounterId($counter_id);

            $date      = new \DateTime(date('d-m-Y', $params['time']));
            $date->modify('previous month');
            $startDate = clone $date->modify('first day of this month');
            $endDate   = $date->modify('last day of this month');

            //посещаемость
            $metrika_api->getVisitsUsersSearchEngineForPeriod($startDate,
                $endDate)->adapt();

            $users = $metrika_api->adaptData;

            $metrika_api->getVisitsSearchEngineForPeriod($startDate, $endDate)->adapt();

            $visits = $metrika_api->adaptData;

            $metrika_api->getBounceRateSearchEngineForPeriod($startDate,
                $endDate)->adapt();

            $bounceRate = $metrika_api->adaptData;

            $metrika_api->getPageDepthSearchEngineForPeriod($startDate, $endDate)->adapt();

            $pageDepth = $metrika_api->adaptData;

            $metrika_api->getAvgVisitDurationSecondsSearchEngineForPeriod($startDate,
                $endDate)->adapt();

            $avgVisitDurationSeconds = $metrika_api->adaptData;

            $metrika_api->getAdvertisingSystemsVisitsForPeriod($startDate,
                $endDate)->adapt();

            $advertising_visits = $metrika_api->adaptData;

            $metrika_api->getAdvertisingSystemsUsersForPeriod($startDate,
                $endDate)->adapt();

            $advertising_users = $metrika_api->adaptData;

            self::insertStatistics($users, 'users', $params['domain'],
                $startDate);

            self::insertStatistics($visits, 'visits', $params['domain'],
                $startDate);

            self::insertStatistics($bounceRate, 'bounce-rate',
                $params['domain'], $startDate);

            self::insertStatistics($pageDepth, 'page-depth', $params['domain'],
                $startDate);

            self::insertStatistics($avgVisitDurationSeconds,
                'avg-visit-duration-seconds', $params['domain'], $startDate);

            self::insertStatistics($advertising_visits, 'advertising-visits',
                $params['domain'], $startDate);

            self::insertStatistics($advertising_users, 'advertising-users',
                $params['domain'], $startDate);

            //Рост процента возвратов
            $percentageOfReturns = [
                'data'   => [],
                'totals' =>
                    [
                    'users' => ( $visits['totals']['users'] - $users['totals']['users'] )
                    / $users['totals']['users']
                ]
            ];
            self::insertStatistics($percentageOfReturns,
                'percentage-of-returns', $params['domain'], $startDate);
        }
    }

    public static function insertStatistics($data = [], $type, $domain, $date)
    {

        $module = Yii::$app->controller->module;

        foreach ($data['data'] as $counter) {

            $key = $type.'-'.Yii::$app->transliter->translate($counter['searchEngine']);

            //если нужно посмотреть все что приходит, убери это условие епт
            if (in_array($key, $module->params['valid_keys'])) {
                $metrika        = new YandexMetrika();
                $metrika->key   = $key;
                $metrika->name  = $domain;
                $metrika->value = $counter['users'];
                $metrika->time  = $date->format('Y-m-d');
                $metrika->save(false);
            }
        }

        if (in_array('totals-'.$type, $module->params['valid_keys'])) {
            $metrika        = new YandexMetrika();
            $metrika->key   = 'totals-'.$type;
            $metrika->name  = $domain;
            $metrika->value = $data['totals']['users'];
            $metrika->time  = $date->format('Y-m-d');
            $metrika->save(false);
        }
    }

    public static function getStatisticsByKey($key)
    {
        $params = \Yii::$app->request->queryParams;
        $date   = new \DateTime(date('d-m-Y', $params['time']));
        $date->modify('previous month');
        $date->modify('first day of this month');
        $where  = [
            'key'  => $key,
            'time' => $date->format('Y-m-d'),
            'name' => $params['domain']
        ];

        return $yandexMetrika = self::find()
            ->where($where);
    }
}