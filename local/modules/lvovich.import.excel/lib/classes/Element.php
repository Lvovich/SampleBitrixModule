<?php
namespace Lvovich\Import;

use Bitrix\Main\Application;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\SystemException;
use CFile;
use CIBlockElement;
use CIBlockSection;
use CUtil;
use Exception;

/**
 * Class Element
 *
 * @property string itemName
 * @property string itemPath
 * @property string itemCode
 * @property string offerName
 * @property string offerCode
 * @property int    offerArtNum
 * @property string offerDetailDescription
 * @property string offerImageURL
 *
 * @package Lvovich\Import
 */
class Element
{
    /** @var string */
    const IMAGES_TMP_SUBDIR = '/bitrix/tmp/479b9b958a3674bf286d95066a2045ca';

    /** ----------------------------------------------------------------------------------------------------------------
     * @var int
     */
    private static $itemIB;

    /** @var int */
    private static $offerIB;

    /** @var CIBlockElement */
    private static $cibe;

    /** @var Directory */
    private static $imagesTmpDir;

    /** @var array */
    private $data;

    //:::::::::::::::::::::::::::::::::::::::::::::::  Public actions  ::::::::::::::::::::::::::::::::::::::::::::::://
    /**
     * @param array $data
     *  [0] string - Наименование
     *  [1] string - Категория (путь)
     *  [2] string - Артикул
     *  [3] string - Описание
     *  [4] string - Изображение (ссылка)
     *
     * Element constructor.
     */
    public function __construct($data)
    {
        $this->data = [
            'itemName' => $data[0],
            'itemPath' => trim($data[1], '/'),
            'itemCode' => Cutil::translit($data[0], 'ru') . '_' . $data[2],

            'offerName'              => $data[2],
            'offerCode'              => $data[2],
            'offerArtNum'            => intval($data[2]),
            'offerDetailDescription' => $data[3],
            'offerImageURL'          => $data[4],
        ];
    } // -END- public function __construct()

    /** ----------------------------------------------------------------------------------------------------------------
     * @param $itemIB
     * @param $offerIB
     */
    public static function init($itemIB, $offerIB)
    {
        self::$itemIB  = $itemIB;
        self::$offerIB = $offerIB;

        self::$cibe = new CIBlockElement();

        self::$imagesTmpDir = new Directory(Application::getDocumentRoot() . self::IMAGES_TMP_SUBDIR);
        self::$imagesTmpDir->create();
    } // -END- public static function init()

    /** ----------------------------------------------------------------------------------------------------------------
     */
    public static function deleteTempDir()
    {
        self::$imagesTmpDir->delete();
    } // -END- public static function deleteTempDir()

    /** ----------------------------------------------------------------------------------------------------------------
     * @param $name
     *
     * @return mixed
     *
     * @throws SystemException
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        throw new SystemException(sprintf('Unknown property `%s` for object `%s`', $name, get_called_class()));
    } // -END- public function __get()

    /** ----------------------------------------------------------------------------------------------------------------
     * @throws Exception
     */
    public function upload()
    {
        if (!$this->validate()) {
            throw new Exception('Invalid item/offer parameters specified.');
        }

        $sectID = $this->createHierarchy();

        if (!($itemID = $this->findItem($sectID))) {
            $itemID = $this->createItem($sectID);
        }

        if (!$this->findOffer()) {
            $this->createOffer($itemID);
        }
    } // -END- public function upload()

    //::::::::::::::::::::::::::::::::::::::::::::::  Private helpers  ::::::::::::::::::::::::::::::::::::::::::::::://
    /**
     * @return bool
     */
    private function validate()
    {
        return $this->itemName && $this->itemCode && $this->offerArtNum;
    } // -END- private function validate()

    /** ----------------------------------------------------------------------------------------------------------------
     * @return int
     *
     * @throws Exception
     */
    private function createHierarchy()
    {
        if (!$this->itemPath) {
            return 0;
        }

        $arPath = explode('/', $this->itemPath);
        $parentID = 0;

        foreach ($arPath as $sectionName) {
            if ($sn = trim($sectionName)) {
                $lastID = $this->findSection($sn, $parentID);
                $parentID = $lastID ? $lastID : $this->createSection($sn, $parentID);
            }
        }

        return $parentID;
    } // -END- private function createHierarchy()

    /** ----------------------------------------------------------------------------------------------------------------
     * @param string $name
     * @param int    $pid  - parent section ID.
     *
     * @return int
     */
    private function findSection($name, $pid)
    {
        $params = [
            'IBLOCK_ID' => self::$itemIB,
            'SECTION_ID' => $pid,
            'NAME' => $name,
        ];

        return ($sect = CIBlockSection::GetList([], $params)->Fetch()) ? intval($sect['ID']) : 0;
    } // -END- private function findSection()

    /** ----------------------------------------------------------------------------------------------------------------
     * @param string $name
     * @param int    $pid  - parent section ID.
     *
     * @return int
     *
     * @throws Exception
     */
    private function createSection($name, $pid)
    {
        $cibs = new CIBlockSection();
        
        $sectID = $cibs->Add([
            'IBLOCK_ID' => self::$itemIB,
            'IBLOCK_SECTION_ID' => $pid,
            'NAME' => $name,
            'CODE' => Cutil::translit($name, 'ru') . '_' . md5(rand()),
        ]);

        if (!$sectID) {
            throw new Exception(
                "Failed to create section '$name' inside the section witn ID '$pid' due to error: {$cibs->LAST_ERROR}"                
            );
        }

        return $sectID;
    } // -END- private function createSection()

    /** ----------------------------------------------------------------------------------------------------------------
     * @param int $sid
     *
     * @return int
     */
    private function findItem($sid)
    {
        $dbRes = CIBlockElement::GetList(
            [],
            [
                'IBLOCK_ID' => self::$itemIB,
                'NAME' => $this->itemName,
                'IBLOCK_SECTION_ID' => $sid,
            ]
        );

        return ($elem = $dbRes->Fetch()) ? intval($elem['ID']) : 0;
    } // -END- private function findItem()

    /** ----------------------------------------------------------------------------------------------------------------
     * @param int $sid
     *
     * @return int
     *
     * @throws Exception
     */
    private function createItem($sid)
    {
        $itemFields = [
            'IBLOCK_ID'         => self::$itemIB,
            'NAME'              => $this->itemName,
            'CODE'              => $this->itemCode,
            'IBLOCK_SECTION_ID' => $sid,
        ];

        if (!($itemID = self::$cibe->Add($itemFields))) {
            throw new Exception("Failed to create item '{$this->itemName}' due to error: " . self::$cibe->LAST_ERROR);
        }

        return $itemID;
    } // -END- private function createItem()

    /** ----------------------------------------------------------------------------------------------------------------
     * @return int
     */
    private function findOffer()
    {
        $dbRes = CIBlockElement::GetList([], ['IBLOCK_ID'=>self::$offerIB, 'PROPERTY_ARTNUMBER'=>$this->offerArtNum]);

        return ($elem = $dbRes->Fetch()) ? intval($elem['ID']) : 0;
    } // -END- private function findOffer()
    
    /** ----------------------------------------------------------------------------------------------------------------
     * @param int $iid
     * 
     * @throws Exception
     */
    private function createOffer($iid)
    {
        $offerFields = [
            'IBLOCK_ID'        => self::$offerIB,
            'NAME'             => $this->offerName,
            'CODE'             => $this->offerCode,
            'DETAIL_TEXT'      => $this->offerDetailDescription,
            'DETAIL_TEXT_TYPE' => 'html',
            'PROPERTY_VALUES' => [
                'ARTNUMBER' => $this->offerArtNum,
                'CML2_LINK' => $iid,
            ]
        ];

        if ($this->offerImageURL && self::$imagesTmpDir->isExists()) {
            $imgName = basename($this->offerImageURL);
            $imgFullPath = self::$imagesTmpDir->getPhysicalPath() . "/$imgName";

            if (copy($this->offerImageURL, $imgFullPath)) {
                $img = CFile::MakeFileArray($imgFullPath);

                $offerFields['PREVIEW_PICTURE'] = $img;
                $offerFields['DETAIL_PICTURE']  = $img;
            }
            else {
                throw new Exception("Failed to get image from remote '{$this->offerImageURL}'<br>");
            }
        }

        if (!self::$cibe->Add($offerFields)) {
            throw new Exception("Failed to create offer '{$this->offerName}' due to error: " . self::$cibe->LAST_ERROR);
        }
    } // -END- private function createOffer()
} // -END- class Element
