<?php
// export_votes.php — экспорт Highload-инфоблока NewsVotes в XML

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;

// Проверка прав (только для администраторов)
if (!CModule::IncludeModule('highloadblock') || !$USER->IsAdmin()) {
    die('Доступ запрещён.');
}

// === Шаг 1: Получить Highload-инфоблок по имени ===
$hlblock = HL\HighloadBlockTable::getList([
    'filter' => ['NAME' => 'NewsVotes']
])->fetch();

if (!$hlblock) {
    die('Highload-инфоблок "NewsVotes" не найден.');
}

// === Шаг 2: Получить сущность и класс данных ===
$entity = HL\HighloadBlockTable::compileEntity($hlblock);
$entity_data_class = $entity->getDataClass();

// === Шаг 3: Получить все записи ===
$votes = $entity_data_class::getList([
    'select' => ['ID', 'UF_NEWS_ID', 'UF_IP', 'UF_VOTE'],
    'order' => ['ID' => 'ASC']
]);

// === Шаг 4: Создать XML ===
header('Content-Type: text/xml; charset=UTF-8');
header('Content-Disposition: attachment; filename="news_votes_export_' . date('Y-m-d_H-i-s') . '.xml"');

echo '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
echo '<votes>' . PHP_EOL;

while ($vote = $votes->fetch()) {
    echo '  <vote>' . PHP_EOL;
    echo '    <id>' . htmlspecialchars($vote['ID']) . '</id>' . PHP_EOL;
    echo '    <news_id>' . htmlspecialchars($vote['UF_NEWS_ID']) . '</news_id>' . PHP_EOL;
    echo '    <ip>' . htmlspecialchars($vote['UF_IP']) . '</ip>' . PHP_EOL;
    echo '    <vote_value>' . htmlspecialchars($vote['UF_VOTE']) . '</vote_value>' . PHP_EOL;
    echo '  </vote>' . PHP_EOL;
}

echo '</votes>' . PHP_EOL;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');