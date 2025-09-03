<!-- /local/templates/template_candidate/components/bitrix/menu/bottom_menu/template.php -->
<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

<nav class="footer-menu">
    <ul>
        <?php foreach ($arResult as $item): ?>
            <li<?php if ($item['SELECTED']) echo ' class="selected"'; ?>>
                <a href="<?= $item['LINK'] ?>"><?= $item['TEXT'] ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>

