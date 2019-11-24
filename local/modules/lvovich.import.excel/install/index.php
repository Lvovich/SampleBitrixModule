<?php
use Bitrix\Main\ModuleManager;
use Bitrix\Main\IO\Directory;

class lvovich_import_excel extends CModule
{
    public $MODULE_ID = "lvovich.import.excel";

    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    //:::::::::::::::::::::::::::::::::::::::::::::::  Public actions  ::::::::::::::::::::::::::::::::::::::::::::::://
    /**
     * lvovich_import_excel constructor.
     */
    public function __construct()
    {
        @include __DIR__ . '/version.php';
        /** @var array $arModuleVersion */

        $this->MODULE_VERSION      = isset($arModuleVersion['VERSION']) ? $arModuleVersion['VERSION'] : '';
        $this->MODULE_VERSION_DATE = isset($arModuleVersion['VERSION_DATE']) ? $arModuleVersion['VERSION_DATE'] : '';

        $this->MODULE_NAME = 'Import items from excel format';
        $this->MODULE_DESCRIPTION = 'Import items from excel format extended description';
        $this->PARTNER_NAME = 'Lvovich';
    } // -END- public function __construct()

    /** ----------------------------------------------------------------------------------------------------------------
     * @inheritDoc
     */
    public function DoInstall()
    {
        if (!IsModuleInstalled($this->MODULE_ID)) {
            $this->installFiles();

            ModuleManager::registerModule($this->MODULE_ID);
        }

        return true;
    } // -END- public function DoInstall()

    /** ----------------------------------------------------------------------------------------------------------------
     * @inheritDoc
     */
    public function DoUninstall()
    {
        $this->UnInstallFiles();

        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    } // -END- public function DoUninstall()

    /** ----------------------------------------------------------------------------------------------------------------
     * @inheritDoc
     */
    public function installFiles()
    {
        copyDirFiles(
            __DIR__ . '/bitrixadmin',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin',
            true, true
        );
        copyDirFiles(
            __DIR__ . '/themes',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes',
            true, true
        );
        copyDirFiles(
            __DIR__ . '/components',
            $_SERVER['DOCUMENT_ROOT'] . '/local/components',
            true, true
        );
    } // -END- public function installFiles()

    /** ----------------------------------------------------------------------------------------------------------------
     * @inheritDoc
     */
    public function UnInstallFiles()
    {
        DeleteDirFiles(
            __DIR__ . '/bitrixadmin',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin'
        );
        DeleteDirFiles(
            __DIR__ . '/themes/.default',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default'
        );
        DeleteDirFiles(
            __DIR__ . '/themes/.default/icons',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/themes/.default/icons'
        );

        Directory::deleteDirectory($_SERVER['DOCUMENT_ROOT'] . '/local/components/lvovich/import.excel');
    } // -END- public function UnInstallFiles()
} // -END- class lvovich_import_excel
