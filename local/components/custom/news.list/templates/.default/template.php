<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

<?php if ($arResult['ERROR_MESSAGE']): ?>
    <div class="alert alert-error"><?= $arResult['ERROR_MESSAGE'] ?></div>
<?php endif; ?>

<h2>Список новостей</h2>

<?php foreach ($arResult['NEWS'] as $news): ?>
    <div class="news-item">
        <h3><a href="<?= $news['URL'] ?>"><?= $news['NAME'] ?></a></h3>
        <p><?= $news['PREVIEW_TEXT'] ?></p>
        <p><small>Дата: <?= $news['DATE_CREATE'] ?></small></p>
        <div class="votes">
            Голосов: <span class="vote-count"><?= $news['VOTES'] ?></span>
            <?php if (!$news['USER_VOTED']): ?>
                <form method="post" style="display:inline;">
                    <?= bitrix_sessid_post() ?>
                    <input type="hidden" name="news_id" value="<?= $news['ID'] ?>">
                    <button type="submit" name="vote" value="down" class="btn btn-sm btn-danger">Проголосовать -</button>
                    <button type="submit" name="vote" value="up" class="btn btn-sm btn-success">Проголосовать +</button>
                </form>
            <?php else: ?>
                <span class="text-muted">Вы уже голосовали</span>
            <?php endif; ?>
        </div>
        <hr>
    </div>
<?php endforeach; ?>
