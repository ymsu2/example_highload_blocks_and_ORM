<?php
// /bitrix/php_interface/classes/Hl/News/NewsVotesTable.php

namespace HL\News;

use Bitrix\Main\Entity;

class NewsVotesTable extends Entity\DataManager
{
    public static function getTableName(): string
    {
        // Укажите имя таблицы из настроек HL-инфоблока
        return 'b_hlbd_news_votes';
    }

    public static function getMap(): array
    {
        return [
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new Entity\IntegerField('UF_NEWS_ID', [
                'required' => true,
            ]),
            new Entity\StringField('UF_IP', [
                'required' => true,
                'validation' => [__CLASS__, 'validateIp'],
            ]),
            new Entity\IntegerField('UF_VOTE', [
                'required' => true,
            ]),
        ];
    }

    public static function validateIp()
    {
        return [
            new Entity\Validator\Length(null, 45), // IPv6 поддержка
        ];
    }
}