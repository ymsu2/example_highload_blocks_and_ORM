<?php
// /bitrix/php_interface/init.php

use Bitrix\Main\Loader;

AddEventHandler("main", "OnPageStart", "RegisterNewsVotesClass");

function RegisterNewsVotesClass()
{
    $classPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/php_interface/classes/Hl/News/NewsVotesTable.php';

    // Подключаем ORM-класс
    if (!class_exists('HL\\News\\NewsVotesTable')) {
        if (file_exists($classPath)) {
            require_once $classPath;
        } else {
            // Для отладки (можно убрать)
            // error_log("Файл ORM-класса не найден: " . $classPath);
        }
    }

    // Подключаем модуль highloadblock
    if (!Loader::includeModule('highloadblock')) {
        // error_log("Не удалось подключить модуль highloadblock");
    }
}