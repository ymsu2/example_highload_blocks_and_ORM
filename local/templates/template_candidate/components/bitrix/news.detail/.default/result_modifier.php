<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Context;
use HL\News\NewsVotesTable;

// Проверка подключения ORM-класса
if (!class_exists('HL\\News\\NewsVotesTable')) {
    $arResult['ERROR_MESSAGE'] = 'Класс голосования не найден.';
    return;
}

$request = Context::getCurrent()->getRequest();

$ip = $request->getRemoteAddress();
$newsId = (int)$arResult['ID'];

// === Обработка голосования (POST-запрос) ===
if ($request->isPost() && $request->get('vote_action') && $newsId > 0) {
    // Защита от CSRF
    if (!check_bitrix_sessid()) {
        $arResult['VOTE_ERROR'] = 'Ошибка сессии.';
    } else {
        $voteType = $request->get('vote_action'); // 'up' или 'down'

        // Проверяем, голосовал ли уже пользователь
        $existingVote = NewsVotesTable::getList([
            'filter' => [
                'UF_NEWS_ID' => $newsId,
                'UF_IP' => $ip
            ]
        ])->fetch();

        if ($existingVote) {
            $arResult['VOTE_ERROR'] = 'Вы уже голосовали за эту новость.';
        } else {
            // Добавляем голос
            try {
                $voteValue = ($voteType === 'up') ? 1 : -1;

                $result = NewsVotesTable::add([
                    'UF_NEWS_ID' => $newsId,
                    'UF_IP' => $ip,
                    'UF_VOTE' => $voteValue
                ]);

                if ($result->isSuccess()) {
                    // Успешно добавлено
                    $arResult['VOTE_SUCCESS'] = true;
                    // Очистим кэш списка новостей и этой детальной страницы
                    $cache = \Bitrix\Main\Application::getInstance()->getCache();
                    $cache->cleanDir('/custom/news.list/'); // кэш списка
                    $cache->clean('news_detail_' . $newsId); // кэш этой страницы
                } else {
                    $arResult['VOTE_ERROR'] = 'Ошибка сохранения голоса: ' . implode(', ', $result->getErrorMessages());
                }
            } catch (\Exception $e) {
                $arResult['VOTE_ERROR'] = 'Исключение: ' . $e->getMessage();
            }
        }
    }
}

// === Получаем количество голосов за новость ===
$arResult['VOTES_COUNT'] = 0;
$arResult['USER_HAS_VOTED'] = false;

$voteRes = NewsVotesTable::getList([
    'select' => ['UF_VOTE'],
    'filter' => ['UF_NEWS_ID' => $newsId]
]);

while ($vote = $voteRes->fetch()) {
    $arResult['VOTES_COUNT'] += (int)$vote['UF_VOTE'];
}

// === Проверяем, голосовал ли текущий пользователь ===
$userVoteRes = NewsVotesTable::getList([
    'filter' => [
        'UF_NEWS_ID' => $newsId,
        'UF_IP' => $ip
    ],
    'limit' => 1
]);

$arResult['USER_HAS_VOTED'] = (bool)$userVoteRes->fetch();