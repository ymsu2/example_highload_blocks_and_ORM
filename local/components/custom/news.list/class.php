<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Application;
use HL\News\NewsVotesTable;

class CustomNewsListComponent extends CBitrixComponent
{
    private $cacheTime = 3600; // 1 час кэширования
    private $ip;

    public function onPrepareComponentParams($params)
    {
        $params['CACHE_TIME'] = isset($params['CACHE_TIME']) ? $params['CACHE_TIME'] : $this->cacheTime;
        $params['NEWS_IBLOCK_ID'] = (int)$params['NEWS_IBLOCK_ID'];
        return $params;
    }

    public function executeComponent()
    {
        if (!$this->initComponentTemplate()) {
            return;
        }

       // === Исправленное получение $request ===
       $context = \Bitrix\Main\Application::getInstance()->getContext();
       $request = $context->getRequest();

       // === Сохраняем IP пользователя ===
       $this->ip = $request->getRemoteAddress();

       // === Обработка POST-запроса (голосование) ===
       if ($request->isPost() && $request->get('vote') && $request->get('news_id')) {
           if (!check_bitrix_sessid()) {
               $this->error = 'Неверная сессия.';
           } else {
               $newsId = (int)$request->get('news_id');
               $voteType = $request->get('vote');

               $this->vote($newsId, $voteType);

               // Очистка кэша
               $cache = \Bitrix\Main\Application::getInstance()->getCache();
               $cache->cleanDir('/custom/news.list/');

               // Редирект
               global $APPLICATION;
               LocalRedirect($APPLICATION->GetCurPage());
           }
       }

       // Получаем список новостей
       $this->arResult['NEWS'] = $this->getNewsList();
       $this->arResult['ERROR_MESSAGE'] = $this->error;

       $this->includeComponentTemplate();
    }

    public function getNewsList()
    {
        $cache = Application::getInstance()->getCache();
        $cacheKey = 'news_list_' . md5(serialize($this->arParams));
        $cacheDir = '/custom/news.list/';

        if ($cache->initCache($this->arParams['CACHE_TIME'], $cacheKey, $cacheDir)) {
            $vars = $cache->getVars();
            return $vars['newsList'];
        }

        if ($cache->startDataCache()) {
            $newsList = [];

            // Проверяем, включён ли модуль iblock
            if (!Loader::includeModule('iblock')) {
                $this->error = 'Модуль iblock не подключён';
                $cache->abortDataCache();
                return [];
            }

            // Получаем новости из инфоблока
            $res = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                [
                    'IBLOCK_ID' => $this->arParams['NEWS_IBLOCK_ID'],
                    'ACTIVE' => 'Y'
                ],
                false,
                false,
                ['ID', 'NAME', 'DETAIL_PAGE_URL', 'PREVIEW_TEXT', 'DATE_CREATE']
            );

            while ($news = $res->GetNext()) {
                $votes = $this->getVoteCount($news['ID']);
                $userVoted = $this->hasUserVoted($news['ID']);

                $newsList[] = [
                    'ID' => $news['ID'],
                    'NAME' => $news['NAME'],
                    'URL' => $news['DETAIL_PAGE_URL'],
                    'PREVIEW_TEXT' => $news['PREVIEW_TEXT'],
                    'DATE_CREATE' => $news['DATE_CREATE'],
                    'VOTES' => $votes,
                    'USER_VOTED' => $userVoted
                ];
            }

            $cache->endDataCache(['newsList' => $newsList]);
            return $newsList;
        }

        return [];
    }

    private function getVoteCount($newsId)
    {
        $res = NewsVotesTable::getList([
            'select' => ['UF_VOTE'],
            'filter' => ['UF_NEWS_ID' => $newsId]
        ]);

        $total = 0;
        while ($vote = $res->fetch()) {
            $total += (int)$vote['UF_VOTE'];
        }
        return $total;
    }

    private function hasUserVoted($newsId)
    {
        $res = NewsVotesTable::getList([
            'select' => ['ID'],
            'filter' => [
                'UF_NEWS_ID' => $newsId,
                'UF_IP' => $this->ip
            ]
        ]);
        return (bool)$res->fetch();
    }

    public function vote($newsId, $voteType)
    {
        if (!Loader::includeModule('highloadblock')) {
            $this->error = 'Модуль highloadblock не установлен';
            return;
        }

        $newsId = (int)$newsId;
        $voteValue = $voteType === 'up' ? 1 : -1;

        // Проверка, голосовал ли уже пользователь
        $res = NewsVotesTable::getList([
            'filter' => [
                'UF_NEWS_ID' => $newsId,
                'UF_IP' => $this->ip
            ]
        ]);

        if ($res->fetch()) {
            $this->error = 'Вы уже голосовали за эту новость.';
            return;
        }

        try {
            $result = NewsVotesTable::add([
                'UF_NEWS_ID' => $newsId,
                'UF_IP' => $this->ip,
                'UF_VOTE' => $voteValue
            ]);

            if (!$result->isSuccess()) {
                $this->error = 'Ошибка при добавлении голоса: ' . implode(', ', $result->getErrorMessages());
            }
        } catch (\Exception $e) {
            $this->error = 'Исключение: ' . $e->getMessage();
        }
    }
}