<?php
// Автоматическое перенаправление на нужный раздел
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

// Перенаправляем с главной на section_candidate
LocalRedirect("/section_candidate/", false, "301 Moved Permanently");

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>