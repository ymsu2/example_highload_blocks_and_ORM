<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Application;

if (!Loader::includeModule('iblock') || !Loader::includeModule('highloadblock')) {
    ShowError('Необходимые модули не установлены');
    return;
}

$component = new CustomNewsListComponent;
$component->arParams = $arParams;
$component->executeComponent();
