<?php if(!defined('B_PROLOG_INCLUDED')||B_PROLOG_INCLUDED!==true)die();
/** @global array $arResult */
?>
<div class="lvovich-import-excel">

<?php if ($arResult['ERROR']): ?>
    <span class="lie_error-message"><?= $arResult['ERROR'] ?></span>
<?php endif ?>

<?php if ($arResult['SHOW_FORM']): ?>
    <form class="lie_upload-form" method="post" action="<?=$APPLICATION->GetCurPage()?>" enctype="multipart/form-data">
        <?= bitrix_sessid_post() ?>

        <table>
            <tr>
                <td class="adm-detail-content-cell-l">
                    <input type="text" name="lie-filename" size="30" maxlength="255" value="">
                </td>
                <td class="adm-detail-content-cell-r">
                    <label class="adm-input-file">
                        <span>Выбрать файл</span>
                        <input type="file" name="lie-file" class="adm-designed-file">
                    </label>
                </td>
            </tr>
        </table>

        <input type="submit" value="Импорт" class="adm-btn-save">
    </form>

<?php elseif ($arResult['SUCCESS']): ?>
    <span class="lie_success-message">Success</span>

<?php endif ?>

</div>
