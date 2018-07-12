<?php
/**
 * Description of ApiYandex
 *
 * @author Max
 */
namespace app\modules\api_yandex;

class ApiYandex extends \yii\base\Module
{
    public function init()
    {
        parent::init();
        // инициализация модуля с помощью конфигурации, загруженной из config.php
        \Yii::configure($this, require __DIR__ . '/config.php');

    }
}