<?php
namespace Lvovich\Import\Component;

use Bitrix\Main;
use CBitrixComponent;
use CFile;
use Lvovich\Import\Element;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Class ExcelComponent
 *
 * @package Lvovich\Import
 */
class ExcelComponent extends CBitrixComponent
{
    //:::::::::::::::::::::::::::::::::::::::::::::::  Public actions  ::::::::::::::::::::::::::::::::::::::::::::::://
    /**
     * @inheritDoc
     */
    public function onPrepareComponentParams($arParams)
    {
        $arParams['ITEMS_IBLOCK_ID'] =!empty($arParams['ITEMS_IBLOCK_ID'])  ? intval($arParams['ITEMS_IBLOCK_ID'])  : 0;
        $arParams['OFFERS_IBLOCK_ID']=!empty($arParams['OFFERS_IBLOCK_ID']) ? intval($arParams['OFFERS_IBLOCK_ID']) : 0;

        if (!$arParams['OFFERS_IBLOCK_ID']) {
            $arParams['OFFERS_IBLOCK_ID'] = $arParams['ITEMS_IBLOCK_ID'];
        }

        return $arParams;
    } // -END- public function onPrepareComponentParams()

    /** ----------------------------------------------------------------------------------------------------------------
     * @inheritDoc
     */
    public function executeComponent()
    {
        $this->arResult = [
            'ERROR'     => '',
            'SHOW_FORM' => true,
            'SUCCESS'   => false,
        ];

        if ($this->arParams['ITEMS_IBLOCK_ID']) {
            /** @var array|null $tmpfile */
            $tmpfile = Main\Context::getCurrent()->getRequest()->getFile('lie-file');

            if ($tmpfile) {
                if (($error = CFile::CheckFile($tmpfile)) || ($error = $this->importItems($tmpfile['tmp_name']))) {
                    $this->arResult['ERROR'] = $error;
                }
            }

            $this->arResult['SHOW_FORM'] = !$tmpfile;
            $this->arResult['SUCCESS'] = $tmpfile && !$this->arResult['ERROR'];
        }
        else {
            $this->arResult['ERROR'] = 'Invalid parameter "ITEMS_IBLOCK_ID"';
        }

        $this->includeComponentTemplate();
    } // -END- public function onPrepareComponentParams()

    //::::::::::::::::::::::::::::::::::::::::::::::  Private helpers  ::::::::::::::::::::::::::::::::::::::::::::::://
    /**
     * @param string $file
     *
     * @return string
     */
    private function importItems($file)
    {
        try {
            $reader = IOFactory::createReaderForFile($file);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($file);
            $data = $spreadsheet->getActiveSheet()->toArray(null, false, false);

            unlink($file);
        }
        catch (Exception $e) {
            return $e->getMessage();
        }

        Element::init($this->arParams['ITEMS_IBLOCK_ID'], $this->arParams['OFFERS_IBLOCK_ID']);

        $rowsTotal = count($data);

        for ($i=1; $i<$rowsTotal; $i++) {
            try {
                (new Element($data[$i]))->upload();
            }
            catch (\Exception $e) {
                return "{$e->getMessage()}Row $i";
            }
        }

        Element::deleteTempDir();

        return '';
    } // -END- private function importItems()
} // -END- class ExcelComponent
