<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Список результатов");

/* Настройки страницы */

// количество элементов, выводимых на странице
$arResult["PAGE_SIZE"] = 20;

// Код информационного блока в базе Битрикса
$arResult["IBLOCK_CODE"] = "FORM_RESULTS";

// Параметры фильтра
$arResult["FILTER_PARAMS"] = array(
    "DATE_CREATE" => array(
        'TYPE' => 'datetime',
        'CAT' => 'F'
    )
);

// Столбцы таблицы
$arResult["COLUMN_PARAMS"] = array(
    "DATE_CREATE" => array(
        'TITLE' => 'Дата создания',
        'CODE' => 'DATE_CREATE'
    )
);

// Функция для подготовки параметров фильтра Битрикс
function prepareFilter($filter_values, $filter_fields)
{
    $mods = array(1 => ">=", 2 => "<=");

    foreach ($filter_values as $k => $val) {
        if (is_array($val)) {
            $filter_values[$k] = array_filter($val);
        }
        if (!isset($filter_fields[$k]) || empty($filter_values[$k])) {
            unset($filter_values[$k]);
        }
        if (in_array($filter_fields[$k]['TYPE'], array('date', 'datetime'))) {
            $date_format = $filter_fields[$k]['CAT'] == 'F' ? 'd.m.Y' : 'Y-m-d';
            $format = $filter_fields[$k]['TYPE'] == 'date' ? $date_format : "$date_format H:i:s";

            if (is_array($filter_values[$k])) {
                foreach ($filter_values[$k] as $m => $v) {
                    $filter_values[$mods[$m] . $k] = date($format, strtotime($v));
                }
                unset($filter_values[$k]);
            } else {
                $filter_values[$k] = date($format, strtotime($filter_values[$k]));
            }
        }
        if ($filter_fields[$k]['TYPE'] == 'string') {
            $filter_values[$k] = "%$val%";
        }
    }

    $filter_values = empty($filter_values) ? array() : $filter_values;
    return $filter_values;
}

// Проверка на установку модуля инфоблоков
if (CModule::IncludeModule("iblock")) {
    // получаем еобходимый инфоблок из базы
    $sort = array("SORT" => "ASC");
    $filter = array(
        "ACTIVE" => "Y",
        "CODE" => $arResult["IBLOCK_CODE"]
    );
    $b_count = false;

    $rsIBlocks = CIBlock::GetList(
        $sort,
        $filter,
        $b_count
    );

    $arResult["IBLOCK"] = $rsIBlocks->Fetch();
} else {
    $error = "Модуль \"Инфоблоки\" не установлен";
}

if (empty($error) && empty($arResult["IBLOCK"])) {
    $error = "Инфоблок с кодом '{$arResult[IBLOCK_CODE]}' не найден или не хватает права на чтение данных из него.";
}

if (strlen($error) > 0) {
    ShowError($error);
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
    return;
}

// получаем список свойств инфоблока (все свойства с типом "Строка")
$order = array("SORT" => "ASC");
$filter = array(
    "ACTIVE" => "Y",
    "IBLOCK_ID" => $arResult["IBLOCK"]["ID"],
    "PROPERTY_TYPE" => "S"
);

$rsIBlockProps = CIBlockProperty::GetList(
    $order,
    $filter
);

while ($prop = $rsIBlockProps->GetNext()) {
    $name = $prop["NAME"];
    $pc = strtoupper($prop["CODE"]);

    $code = "PROPERTY_{$pc}_VALUE";
    $key = "PROPERTY_{$pc}";

    // добавлем в столбцы таблицы выводимы свойства
    $arResult["COLUMN_PARAMS"][$key] = array(
        'TITLE' => $name,
        'CODE' => $code
    );

    // расширяем фильтр свойств (пока что только для строковых типов)
    if ($prop["FILTRABLE"] == "Y") {
        $arResult["FILTER_PARAMS"][$key] = array(
            'TYPE' => 'string',
            'CAT' => 'P'
        );
    }
}

if ($_REQUEST['find'] == 'Y') {
    // получаем параметры фильтра из request
    $arResult['FILTER'] = $_REQUEST["FILTER"];

    // и формируем фильтр
    foreach ($arResult['FILTER'] as $k => $val) {
        if (is_array($val)) {
            $arResult['FILTER'][$k] = array_filter($val);
        }
        if (!isset($arResult["FILTER_PARAMS"][$k]) || empty($arResult['FILTER'][$k])) {
            unset($arResult['FILTER'][$k]);
        }
    }
}
if ($_REQUEST['reset'] == 'Y') {
    // очищаем фильтр в случае нажатия кнопки "Отмена"
    $arResult['FILTER'] = array();
}

// подготавливаем фильтр пользователя для запроса через API
$filter = prepareFilter($arResult["FILTER"], $arResult["FILTER_PARAMS"]);

// объединяем результат в массив
$filter = array_merge(array(
    'IBLOCK_ID' => $arResult["IBLOCK"]["ID"],
    'ACTIVE' => 'Y',
), $filter);

$group_by = false;
$nav_params = array(
    'nPageSize' => $arResult["PAGE_SIZE"],
    'bShowAll' => false
);
$select = array_keys($arResult["COLUMN_PARAMS"]);

// получаем элементы из БД
$rsElements = CIBlockElement::GetList($sort, $filter, $group_by, $nav_params, $select);

while ($elem = $rsElements->Fetch()) {
    $arResult["ITEMS"][] = $elem;
}

// строка с постраничной навигацией
$arResult["NAV_STRING"] = $rsElements->GetPageNavString('Результаты', '');

// плагины
CJSCore::RegisterExt("bootstrap", array(
    "js" => "/local/js/bootstrap/js/bootstrap.js",
    "css" => array(
        "/local/js/bootstrap/css/bootstrap.css",
        "/local/js/bootstrap/css/bootstrap-theme.css",
    ),
    "rel" => array('jquery2')
));
CJSCore::RegisterExt("momemtjs", array(
    "js" => "/local/js/momentjs/moment-with-locales.js",
    "rel" => array('jquery2')
));
CJSCore::RegisterExt("daterangepicker", array(
    "js" => "/local/js/daterangepicker/js/daterangepicker.js",
    "css" => "/local/js/daterangepicker/css/daterangepicker.css",
    "rel" => array('momemtjs')
));
CJSCore::RegisterExt("stickytableheaders", array(
    "js" => "/local/js/stickytableheaders/jquery.stickytableheaders.js",
    "rel" => array('jquery2')
));

// дополнительные скрипты со стилями для страницы
$CUR_DIR = $APPLICATION->GetCurDir();
CJSCore::RegisterExt("assets", array(
    "js" => "$CUR_DIR/assets/script.js",
    "css" => "$CUR_DIR/assets/style.css",
    "rel" => array('jquery2')
));

// подключаем JS & CSS на страницу
CJSCore::Init(array(
    "bootstrap",
    "momentjs",
    "daterangepicker",
    "stickytableheaders",
    "assets"
)); ?>

<? /* Выводим шаблон */ ?>
    <div class="container-fluid">
        <div class="row">
            <h2><?= $APPLICATION->GetTitle() ?></h2>

            <form role="form" action="<?= POST_FORM_ACTION_URI ?>" method="GET">
                <? foreach ($arResult["FILTER_PARAMS"] as $code => $item): ?>
                    <div class="form-group">
                        <label for="<?= $code ?>"><?= $arResult['COLUMN_PARAMS'][$code]['TITLE'] ?></label>
                        <? if (in_array($item['TYPE'], array('date', 'datetime'))): ?>
                            <div id="<?= $code ?>" class="daterange-picker">
                                <i class="glyphicon glyphicon-calendar fa fa-calendar"></i>&nbsp;
                                <span></span> <b class="caret"></b>
                                <input type="hidden" name="FILTER[<?= $code ?>][1]"
                                       value="<?= $arResult["FILTER"][$code][1] ?>">
                                <input type="hidden" name="FILTER[<?= $code ?>][2]"
                                       value="<?= $arResult["FILTER"][$code][2] ?>">
                            </div>
                        <? elseif ($item['TYPE'] == 'string'): ?>
                            <input type="text" class="form-control" id="<?= $code ?>" name="FILTER[<?= $code ?>]"
                                   value="<?= $arResult["FILTER"][$code] ?>" placeholder="<?= $item['HINT'] ?>">
                        <? else: ?>
                            <span class="text-error">Для этого типа поля не предусмотрено визуального предсталения</span>
                        <? endif ?>
                    </div>
                <? endforeach; ?>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary" name="find" value="Y">Найти</button>
                    <button type="submit" class="btn btn-default" name="reset" value="Y">Отмена</button>
                    <a href="/">Форма заявки</a>
                </div>
            </form>

            <? if (empty($arResult["ITEMS"])): ?>
                <div class="alert alert-danger"><strong>Результатов не найдено.</strong></div>
            <? else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-condensed table-main table-sticky-headers fixed">
                        <thead>
                        <? foreach ($arResult["COLUMN_PARAMS"] as $code => $column): ?>
                            <th><?= $column['TITLE'] ?></th>
                        <? endforeach; ?>
                        </thead>
                        <tbody>
                        <? foreach ($arResult["ITEMS"] as $item): ?>
                            <tr>
                                <? foreach ($arResult["COLUMN_PARAMS"] as $column): ?>
                                    <td><?= $item[$column['CODE']] ?></td>
                                <? endforeach ?>
                            </tr>
                        <? endforeach; ?>
                        </tbody>
                    </table>

                    <?= $arResult["NAV_STRING"] ?>
                </div>
            <? endif; ?>
        </div>
    </div>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>