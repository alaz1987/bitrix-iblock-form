<?php /** @noinspection SpellCheckingInspection */
$_SERVER['DOCUMENT_ROOT'] = realpath(dirname(__FILE__) . '/../../');

define('LANG', 's1');
define('SITE_ID', 's1');
define("NO_KEEP_STATISTIC", true);
define("NEED_AUTH", false);
define("NOT_CHECK_PERMISSIONS", true);

require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php");

/********************************************** INSTALL **********************************************/

global $USER, $DB;

if (!$USER->IsAuthorized()) {
    $USER->Authorize(1);
}
if (!CModule::IncludeModule("form")) {
    die("Модуль 'Веб-формы' не установлен");
}
if (!CModule::IncludeModule("iblock")) {
    die("Модуль 'Инфоблоки' не установлен");
}

// create web form
$arFields = array(
    "NAME" => "Форма заявки",
    "SID" => "FORM_RESULTS",
    "C_SORT" => 100,
    "BUTTON" => "Сохранить",
    "DESCRIPTION" => "",
    "DESCRIPTION_TYPE" => "text",
    "STAT_EVENT1" => "form",
    "STAT_EVENT2" => "",
    "arSITE" => array(SITE_ID),
    "arMENU" => array("ru" => "Форма заявки", "en" => "Feedback form"),
    "arGROUP" => array("2" => "10", "3" => "10", "4" => "10"),
    "arIMAGE" => array()
);

$WEB_FORM_ID = CForm::Set($arFields);

if ($WEB_FORM_ID > 0) {
    print("Добавлена веб-форма с ID = {$WEB_FORM_ID}\n");
} else {
    global $strError;
    die("При добавлении веб-формы произошла ошибка -> $strError\n");
}

// create status for web form
$arFields = array(
    "FORM_ID" => $WEB_FORM_ID,           // ID веб-формы
    "C_SORT" => 100,                    // порядок сортировки
    "ACTIVE" => "Y",                    // статус активен
    "TITLE" => "DEFAULT",              // заголовок статуса
    "DESCRIPTION" => "DEFAULT",              // описание статуса
    "CSS" => "statusgreen",          // CSS класс
    "HANDLER_OUT" => "",                     // обработчик
    "HANDLER_IN" => "",                     // обработчик
    "DEFAULT_VALUE" => "Y",                    // не по умолчанию
    "arPERMISSION_VIEW" => array(2),               // право просмотра для всех
    "arPERMISSION_MOVE" => array(),                // право перевода только админам
    "arPERMISSION_EDIT" => array(),                // право редактирование для админам
    "arPERMISSION_DELETE" => array(),                // право удаления только админам
);

$WEB_FORM_STATUS_ID = CFormStatus::Set($arFields);

if ($WEB_FORM_STATUS_ID > 0) {
    print("Статус для веб-формы успешно добавлен с ID = {$WEB_FORM_STATUS_ID}\n");
} else {
    global $strError;
    die("При добавлении веб-статуса для веб-формы произошла ошибка -> $strError\n");
}

// create questions for web form
$questions = array(
    array(
        "FORM_ID" => $WEB_FORM_ID,          // ID веб-формы
        "ACTIVE" => "Y",                   // флаг активности
        "TITLE" => "Имя",                 // текст вопроса
        "TITLE_TYPE" => "text",                // тип текста вопроса
        "SID" => "name",                // символьный идентификатор вопроса
        "C_SORT" => 0,                     // порядок сортировки
        "ADDITIONAL" => "N",                   // мы добавляем вопрос веб-формы
        "REQUIRED" => "Y",                   // ответ на данный вопрос обязателен
        "IN_RESULTS_TABLE" => "N",                   // добавить в HTML таблицу результатов
        "IN_EXCEL_TABLE" => "N",                   // добавить в Excel таблицу результатов
        "FILTER_TITLE" => "Имя",                 // подпись к полю фильтра
        "RESULTS_TABLE_TITLE" => "Имя",                 // заголовок столбца фильтра
        "arIMAGE" => array(),               // изображение вопроса
        "arFILTER_ANSWER_TEXT" => array("text"),         // тип фильтра по ANSWER_TEXT
        "arANSWER" => array(                 // набор ответов
            array(
                "MESSAGE" => " ",                      // параметр ANSWER_TEXT
                "C_SORT" => 0,                       // порядок cортировки
                "ACTIVE" => "Y",                     // флаг активности
                "FIELD_TYPE" => "text",                  // тип ответа
                "FIELD_PARAM" => ""                       // параметры ответа
            )
        )
    ),
    array(
        "FORM_ID" => $WEB_FORM_ID,          // ID веб-формы
        "ACTIVE" => "Y",                   // флаг активности
        "TITLE" => "Email",               // текст вопроса
        "TITLE_TYPE" => "text",                // тип текста вопроса
        "SID" => "email",               // символьный идентификатор вопроса
        "C_SORT" => 0,                     // порядок сортировки
        "ADDITIONAL" => "N",                   // мы добавляем вопрос веб-формы
        "REQUIRED" => "Y",                   // ответ на данный вопрос обязателен
        "IN_RESULTS_TABLE" => "N",                   // добавить в HTML таблицу результатов
        "IN_EXCEL_TABLE" => "N",                   // добавить в Excel таблицу результатов
        "FILTER_TITLE" => "Email",                 // подпись к полю фильтра
        "RESULTS_TABLE_TITLE" => "Email",                 // заголовок столбца фильтра
        "arIMAGE" => array(),               // изображение вопроса
        "arFILTER_ANSWER_TEXT" => array("text"),         // тип фильтра по ANSWER_TEXT
        "arANSWER" => array(                 // набор ответов
            array(
                "MESSAGE" => " ",                      // параметр ANSWER_TEXT
                "C_SORT" => 0,                       // порядок cортировки
                "ACTIVE" => "Y",                     // флаг активности
                "FIELD_TYPE" => "email",                 // тип ответа
                "FIELD_PARAM" => ""                       // параметры ответа
            )
        )
    ),
    array(
        "FORM_ID" => $WEB_FORM_ID,          // ID веб-формы
        "ACTIVE" => "Y",                   // флаг активности
        "TITLE" => "Телефон",             // текст вопроса
        "TITLE_TYPE" => "text",                // тип текста вопроса
        "SID" => "phone",                // символьный идентификатор вопроса
        "C_SORT" => 0,                     // порядок сортировки
        "ADDITIONAL" => "N",                   // мы добавляем вопрос веб-формы
        "REQUIRED" => "Y",                   // ответ на данный вопрос обязателен
        "IN_RESULTS_TABLE" => "N",                   // добавить в HTML таблицу результатов
        "IN_EXCEL_TABLE" => "N",                   // добавить в Excel таблицу результатов
        "FILTER_TITLE" => "Телефон",                 // подпись к полю фильтра
        "RESULTS_TABLE_TITLE" => "Телефон",                 // заголовок столбца фильтра
        "arIMAGE" => array(),               // изображение вопроса
        "arFILTER_ANSWER_TEXT" => array("text"),         // тип фильтра по ANSWER_TEXT
        "arANSWER" => array(                 // набор ответов
            array(
                "MESSAGE" => " ",                      // параметр ANSWER_TEXT
                "C_SORT" => 0,                       // порядок cортировки
                "ACTIVE" => "Y",                     // флаг активности
                "FIELD_TYPE" => "text",                  // тип ответа
                "FIELD_PARAM" => ""                       // параметры ответа
            )
        )
    )
);

// create validators for web form's questions (optional):
$validators = array(
    "name" => array(
        "WEB_FORM_ID" => $WEB_FORM_ID,    // ID веб-формы
        "FIELD_ID" => false,           // ID вопроса (аполняется далее по коду)
        "VALIDATOR_SID" => "text_len",      // Идентификатор валидатора
        "arParams" => array(           // Параметров валидатора /bitrix/modules/form/validators/val_<VALIDATOR_SID>.php
            "LENGTH_FROM" => 3,
            "LENGTH_TO" => 50
        )
    ),
    "email" => array(
        "WEB_FORM_ID" => $WEB_FORM_ID,    // ID веб-формы
        "FIELD_ID" => false,           // ID вопроса (аполняется далее по коду)
        "VALIDATOR_SID" => "text_len",      // Идентификатор валидатора
        "arParams" => array(           // Параметров валидатора /bitrix/modules/form/validators/val_<VALIDATOR_SID>.php
            "LENGTH_FROM" => 6,
            "LENGTH_TO" => 50
        )
    ),
    "phone" => array(
        "WEB_FORM_ID" => $WEB_FORM_ID,    // ID веб-формы
        "FIELD_ID" => false,           // ID вопроса (аполняется далее по коду)
        "VALIDATOR_SID" => "text_len",      // Идентификатор валидатора
        "arParams" => array(           // Параметров валидатора /bitrix/modules/form/validators/val_<VALIDATOR_SID>.php
            "LENGTH_FROM" => 6,
            "LENGTH_TO" => 50
        )
    )
);

foreach ($questions as $k => $question) {
    $question["C_SORT"] = ($k + 1) * 100;

    foreach ($question["arrANSWER"] as $i => $answer) {
        $question["arrANSWER"][$i]["C_SORT"] = ($i + 1) * 100;
    }

    $WEB_FORM_QUESTION_ID = CFormField::Set($question);

    if ($WEB_FORM_QUESTION_ID > 0) {
        print("Для веб-формы успешно добавлен вопрос с ID = $WEB_FORM_QUESTION_ID\n");

        $validator = $validators[$question["SID"]];

        if (!empty($validator)) {
            $validator["FIELD_ID"] = $WEB_FORM_QUESTION_ID;
            $WEB_FORM_QUESTION_VALIDATOR_RESULT = CFormValidator::Set(
                $validator["WEB_FORM_ID"],
                $validator["FIELD_ID"],
                $validator["VALIDATOR_SID"],
                $validator["arParams"]
            );

            if ($WEB_FORM_QUESTION_VALIDATOR_RESULT) {
                print("Для вопроса ID = $WEB_FORM_QUESTION_ID веб-формы успешно добавлен валидатор {$validator[VALIDATOR_SID]}\n");
            } else {
                global $strError;
                die("При добавлении валидатора вопроса с ID = $WEB_FORM_QUESTION_ID для веб-формы произошла ошибка -> $strError\n");
            }
        }
    } else {
        global $strError;
        die("При добавлении вопроса для веб-формы произошла ошибка -> $strError\n");
    }
}

// create iblock type
$IBLOCK_TYPE = "TEST";
$arFields = array(
    'ID' => $IBLOCK_TYPE,
    'SECTIONS' => 'N',
    'IN_RSS' => 'N',
    'SORT' => 100,
    'LANG' => array(
        'ru' => array(
            'NAME' => 'Результаты формы',
            'SECTION_NAME' => '',
            'ELEMENT_NAME' => ''
        )
    )
);

$obBlockType = new CIBlockType;
$DB->StartTransaction();

$res = $obBlockType->Add($arFields);

if (!$res) {
    $DB->Rollback();
    $strError = $obBlockType->LAST_ERROR;
    die("При добавлении типа инфоблока произошла ошибка -> $strError\n");
} else {
    $DB->Commit();
    print("Добавлен тип инфоблока {$IBLOCK_TYPE}\n");
}

// create iblock
$ib = new CIBlock;
$arFields = Array(
    "ACTIVE" => "Y",
    "NAME" => "Результаты формы",
    "CODE" => "FORM_RESULTS",
    "LIST_PAGE_URL" => "",
    "DETAIL_PAGE_URL" => "",
    "IBLOCK_TYPE_ID" => $IBLOCK_TYPE,
    "SITE_ID" => Array(SITE_ID),
    "SORT" => 100,
    "PICTURE" => "",
    "DESCRIPTION" => "",
    "DESCRIPTION_TYPE" => "text",
    "INDEX_ELEMENT" => "N",
    "INDEX_SECTION" => "N",
    "GROUP_ID" => Array("2"=>"R", "3"=>"R", "4"=>"R")
);
$IBLOCK_ID = $ib->Add($arFields);

if ($IBLOCK_ID > 0) {
    print("Для типа инфоблока $IBLOCK_TYPE успешно добавлен инфоблок с ID = $IBLOCK_ID\n");
} else {
    $strError = $ib->LAST_ERROR;
    die("При добавлении инфоблока с типом {$IBLOCK_TYPE} произошла ошибка -> $strError\n");
}

// create iblock properties
$properties = array(
    array(
        "NAME" => "Имя",
        "ACTIVE" => "Y",
        "SORT" => 0,
        "CODE" => "name",
        "PROPERTY_TYPE" => "S",
        "USER_TYPE" => "",
        "FILTRABLE" => "Y",
        "IBLOCK_ID" => $IBLOCK_ID
    ),
    array(
        "NAME" => "Email",
        "ACTIVE" => "Y",
        "SORT" => 0,
        "CODE" => "email",
        "PROPERTY_TYPE" => "S",
        "USER_TYPE" => "",
        "FILTRABLE" => "Y",
        "IBLOCK_ID" => $IBLOCK_ID
    ),
    array(
        "NAME" => "Телефон",
        "ACTIVE" => "Y",
        "SORT" => 0,
        "CODE" => "phone",
        "PROPERTY_TYPE" => "S",
        "USER_TYPE" => "",
        "FILTRABLE" => "Y",
        "IBLOCK_ID" => $IBLOCK_ID
    )
);

foreach ($properties as $k => $property) {
    $property["SORT"] = ($k + 1) * 100;

    $obProperty = new CIBlockProperty;
    $res = $obProperty->Add($property);

    if ($res > 0) {
        print("Для инфоблока с ID = {$property[IBLOCK_ID]} успешно добавлено свойство с ID = $res\n");
    } else {
        $strError = $obProperty->LAST_ERROR;
        die("При добавлении свойства в инфоблок с ID = {$property[IBLOCK_ID]} произошла ошибка -> $strError\n");
    }
}

$USER->Logout();
print("Миграция успешно завершена\n");
