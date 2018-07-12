<?php
/**
 * Description of Yandex
 *
 * @author Max
 */

namespace app\modules\api_yandex\models;

use GuzzleHttp\Client as GuzzleClient;
use Yii;
use DateTime;
use app\modules\api_yandex\models\YandexMetrika;

class YandexApi extends \Alexusmai\YandexMetrika\YandexMetrika
{

    public function __construct($token)
    {
        $this->token = $token;
    }
    /*     * ----------------------------------------------------------------------
     * GET запрос данных и кэширование
     * @param $url
     * @return bool|mixed
     */

    protected function request($url, $cacheName)
    {
        try {
            $client   = new GuzzleClient();
            $response = $client->request('GET', $url, ['verify' => false]);

            //Получаем массив с данными
            $result = json_decode($response->getBody(), true);
        } catch (ClientException $e) {
            //Логируем ошибку
            Log::error('Yandex Metrika: '.$e->getMessage());

            //Данные не получены
            $result = null;
        }

        return $result;
    }

    protected function getCounters()
    {

        //Вычисляем даты
        list($startDate, $endDate) = $this->calculateDays(30);

        $result = [];

        $cacheName = md5(serialize('visits-views-users'.$startDate->format('Y-m-d').$endDate->format('Y-m-d')));

        //Параметры запроса
        $urlParams = [
            'oauth_token' => $this->token
        ];

        //Формируем url для запроса
        $requestUrl = $this->url.'management/v1/counters?'.urldecode(http_build_query($urlParams));

        //Запрос данных - возвращает массив или false,если данные не получены
        $array = $this->request($requestUrl, $cacheName);

        foreach ($array['counters'] as $item) {
            $result[] = [
                'id'   => $item['id'],
                'name' => $item['name']
            ];
        }

        $this->data = $result;
    }

    public function getCounter($name)
    {

        foreach ($this->data as $counter) {
            if ($counter['name'] == $name) {
                return $counter['id'];
            }
        }

        return 0;
    }

    public function setCounterId($counter_id)
    {
        $this->counter_id = $counter_id;
    }

    /**
     * Количество визитов с учетом поисковых систем за период
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param int $maxResult
     */
    protected function getVisitsSearchEngineForPeriod(DateTime $startDate,
                                                      DateTime $endDate,
                                                      $maxResult = 10)
    {
        $cacheName = md5(serialize('visits-users-searchEngine'.$startDate->format('Y-m-d').$endDate->format('Y-m-d').$maxResult));

        //Параметры запроса
        $urlParams = [
            'ids'         => $this->counter_id,
            'oauth_token' => $this->token,
            'date1'       => $startDate->format('Y-m-d'),
            'date2'       => $endDate->format('Y-m-d'),
            'metrics'     => 'ym:s:visits',
            'dimensions'  => 'ym:s:searchEngine',
            'filters'     => "ym:s:trafficSource=='organic'",
            'limit'       => $maxResult
        ];

        //Формируем url для запроса
        $requestUrl = $this->url.'stat/v1/data?'.urldecode(http_build_query($urlParams));

        //Запрос данных - возвращает массив или false,если данные не получены
        $this->data = $this->request($requestUrl, $cacheName);
    }

    /**
     * Количество визитов с учетом поисковых систем
     */
    protected function adaptVisitsSearchEngine()
    {
        $dataArray = [];

        //Формируем массив
        foreach ($this->data['data'] as $item) {
            $dataArray['data'][] = [
                'searchEngine' => $item['dimensions'][0]['name'],
                'users'        => $item['metrics'][0]              //Юзеры
            ];
        }

        //Итого
        $dataArray['totals'] = [
            'users' => $this->data['totals'][0]
        ];

        $this->adaptData = $dataArray;
    }

    /**
     * Процент отказов с учетом поисковых систем за период
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param int $maxResult
     */
    protected function getBounceRateSearchEngineForPeriod(DateTime $startDate,
                                                          DateTime $endDate,
                                                          $maxResult = 10)
    {
        $cacheName = md5(serialize('visits-users-searchEngine'.$startDate->format('Y-m-d').$endDate->format('Y-m-d').$maxResult));

        //Параметры запроса
        $urlParams = [
            'ids'         => $this->counter_id,
            'oauth_token' => $this->token,
            'date1'       => $startDate->format('Y-m-d'),
            'date2'       => $endDate->format('Y-m-d'),
            'metrics'     => 'ym:s:bounceRate',
            'dimensions'  => 'ym:s:searchEngine',
            'filters'     => "ym:s:trafficSource=='organic'",
            'limit'       => $maxResult
        ];

        //Формируем url для запроса
        $requestUrl = $this->url.'stat/v1/data?'.urldecode(http_build_query($urlParams));

        //Запрос данных - возвращает массив или false,если данные не получены
        $this->data = $this->request($requestUrl, $cacheName);
    }

    /**
     * Процент отказов с учетом поисковых систем
     */
    protected function adaptBounceRateSearchEngine()
    {
        $dataArray = [];

        //Формируем массив
        foreach ($this->data['data'] as $item) {
            $dataArray['data'][] = [
                'searchEngine' => $item['dimensions'][0]['name'],
                'users'        => $item['metrics'][0]              //Юзеры
            ];
        }

        //Итого
        $dataArray['totals'] = [
            'users' => $this->data['totals'][0]
        ];

        $this->adaptData = $dataArray;
    }

    /**
     * Глубина просмотров с учетом поисковых систем за период
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param int $maxResult
     */
    protected function getPageDepthSearchEngineForPeriod(DateTime $startDate,
                                                         DateTime $endDate,
                                                         $maxResult = 10)
    {
        $cacheName = md5(serialize('visits-users-searchEngine'.$startDate->format('Y-m-d').$endDate->format('Y-m-d').$maxResult));

        //Параметры запроса
        $urlParams = [
            'ids'         => $this->counter_id,
            'oauth_token' => $this->token,
            'date1'       => $startDate->format('Y-m-d'),
            'date2'       => $endDate->format('Y-m-d'),
            'metrics'     => 'ym:s:pageDepth',
            'dimensions'  => 'ym:s:searchEngine',
            'filters'     => "ym:s:trafficSource=='organic'",
            'limit'       => $maxResult
        ];

        //Формируем url для запроса
        $requestUrl = $this->url.'stat/v1/data?'.urldecode(http_build_query($urlParams));

        //Запрос данных - возвращает массив или false,если данные не получены
        $this->data = $this->request($requestUrl, $cacheName);
    }

    /**
     * Глубина просмотров с учетом поисковых систем
     */
    protected function adaptPageDepthSearchEngine()
    {
        $dataArray = [];

        //Формируем массив
        foreach ($this->data['data'] as $item) {
            $dataArray['data'][] = [
                'searchEngine' => $item['dimensions'][0]['name'],
                'users'        => $item['metrics'][0]              //Юзеры
            ];
        }

        //Итого
        $dataArray['totals'] = [
            'users' => $this->data['totals'][0]
        ];

        $this->adaptData = $dataArray;
    }

    /**
     * Время на сайте с учетом поисковых систем за период В СЕКУНДАХ !!!
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param int $maxResult
     */
    protected function getAvgVisitDurationSecondsSearchEngineForPeriod(DateTime $startDate,
                                                                       DateTime $endDate,
                                                                       $maxResult
    = 10)
    {
        $cacheName = md5(serialize('visits-users-searchEngine'.$startDate->format('Y-m-d').$endDate->format('Y-m-d').$maxResult));

        //Параметры запроса
        $urlParams = [
            'ids'         => $this->counter_id,
            'oauth_token' => $this->token,
            'date1'       => $startDate->format('Y-m-d'),
            'date2'       => $endDate->format('Y-m-d'),
            'metrics'     => 'ym:s:avgVisitDurationSeconds',
            'dimensions'  => 'ym:s:searchEngine',
            'filters'     => "ym:s:trafficSource=='organic'",
            'limit'       => $maxResult
        ];

        //Формируем url для запроса
        $requestUrl = $this->url.'stat/v1/data?'.urldecode(http_build_query($urlParams));

        //Запрос данных - возвращает массив или false,если данные не получены
        $this->data = $this->request($requestUrl, $cacheName);
    }

    /**
     * Время на сайте с учетом поисковых систем
     */
    protected function adaptAvgVisitDurationSecondsSearchEngine()
    {
        $dataArray = [];

        //Формируем массив
        foreach ($this->data['data'] as $item) {
            $dataArray['data'][] = [
                'searchEngine' => $item['dimensions'][0]['name'],
                'users'        => $item['metrics'][0]              //Юзеры
            ];
        }

        //Итого
        $dataArray['totals'] = [
            'users' => $this->data['totals'][0]
        ];

        $this->adaptData = $dataArray;
    }

    /**
     * Рекламные системы - посетители за период
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param int $maxResult
     */
    protected function getAdvertisingSystemsUsersForPeriod(DateTime $startDate,
                                                      DateTime $endDate,
                                                      $maxResult = 10)
    {
        $cacheName = md5(serialize('visits-users-searchEngine'.$startDate->format('Y-m-d').$endDate->format('Y-m-d').$maxResult));

        //Параметры запроса
        $urlParams = [
            'ids'         => $this->counter_id,
            'oauth_token' => $this->token,
            'date1'       => $startDate->format('Y-m-d'),
            'date2'       => $endDate->format('Y-m-d'),
            'preset'      => 'adv_engine',
            'metrics'     => 'ym:s:users',
            'sort'     => 'ym:s:users'
        ];

        //Формируем url для запроса
        $requestUrl = $this->url.'stat/v1/data?'.urldecode(http_build_query($urlParams));

        //Запрос данных - возвращает массив или false,если данные не получены
        $this->data = $this->request($requestUrl, $cacheName);
    }

    /**
     * Рекламные системы посетители
     */
    protected function adaptAdvertisingSystemsUsers()
    {
        $dataArray = [];

        //Формируем массив
        foreach ($this->data['data'] as $item) {
            $dataArray['data'][] = [
                'searchEngine' => $item['dimensions'][0]['name'],
                'users'        => $item['metrics'][0]              //Юзеры
            ];
        }

        //Итого
        $dataArray['totals'] = [
            'users' => $this->data['totals'][0]
        ];

        $this->adaptData = $dataArray;
    }

    /**
     * Рекламные системы - визиты за период
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param int $maxResult
     */
    protected function getAdvertisingSystemsVisitsForPeriod(DateTime $startDate,
                                                      DateTime $endDate,
                                                      $maxResult = 10)
    {
        $cacheName = md5(serialize('visits-users-searchEngine'.$startDate->format('Y-m-d').$endDate->format('Y-m-d').$maxResult));

        //Параметры запроса
        $urlParams = [
            'ids'         => $this->counter_id,
            'oauth_token' => $this->token,
            'date1'       => $startDate->format('Y-m-d'),
            'date2'       => $endDate->format('Y-m-d'),
            'preset'      => 'adv_engine',
            'metrics'     => 'ym:s:visits',
            'sort'     => 'ym:s:visits'
        ];

        //Формируем url для запроса
        $requestUrl = $this->url.'stat/v1/data?'.urldecode(http_build_query($urlParams));

        //Запрос данных - возвращает массив или false,если данные не получены
        $this->data = $this->request($requestUrl, $cacheName);
    }

    /**
     * Рекламные системы визиты
     */
    protected function adaptAdvertisingSystemsVisits()
    {
        $dataArray = [];

        //Формируем массив
        foreach ($this->data['data'] as $item) {
            $dataArray['data'][] = [
                'searchEngine' => $item['dimensions'][0]['name'],
                'users'        => $item['metrics'][0]              //Юзеры
            ];
        }

        //Итого
        $dataArray['totals'] = [
            'users' => $this->data['totals'][0]
        ];

        $this->adaptData = $dataArray;
    }
}