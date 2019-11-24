<?php
use Bitrix\Main;

require_once($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/prolog_admin_before.php');

/** @global CMain $APPLICATION */
$APPLICATION->SetTitle('Импорт из файла эксель');

require($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/prolog_admin_after.php');

try {
    if (Main\Loader::includeModule('lvovich.import.excel')) {
        $APPLICATION->IncludeComponent('lvovich:import.excel', '', ['ITEMS_IBLOCK_ID' => 2, 'OFFERS_IBLOCK_ID' => 3]);
    }
    else {
        echo "Error: 'Failed to load module'";
    }
}
catch (Exception $e) {
    echo "Error: '{$e->getMessage()}'";
}

require($_SERVER["DOCUMENT_ROOT"] . '/bitrix/modules/main/include/epilog_admin.php');
