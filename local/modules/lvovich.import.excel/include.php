<?php if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Loader;

require_once __DIR__ . '/lib/vendor/autoload.php';

Loader::registerAutoloadClasses(
    'lvovich.import.excel',
    [
        'Lvovich\\Import\\Element' => 'lib/classes/Element.php',
    ]
);
