<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новости кандидата");

$APPLICATION->IncludeComponent(
    "custom:news.list",
    ".default",
    [
        "NEWS_IBLOCK_ID" => 1, // ← Укажите правильный ID инфоблока новостей
        "CACHE_TIME" => 3600,
    ],
    false
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>