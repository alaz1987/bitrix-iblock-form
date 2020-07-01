<?
AddEventHandler("form", "onAfterResultAdd", "afterResultAddHandler");

/* Обработчик события, вызываемый после добавления результатов веб-формы */
function afterResultAddHandler($formID, $resultID)
{
    if ($formID > 0
        &&
        $resultID > 0
        &&
        CModule::IncludeModule("form")
        &&
        CModule::IncludeModule("iblock")
    ) {
        // получим данные по всем вопросам
        $arResult = $arAnswer = array();
        $arAnswers = CFormResult::GetDataByID(
            $resultID,
            array(),
            $arResult,
            $arAnswer
        );

        // получим инфоблок
        $sort = array();
        $filter = array(
            "ACTIVE" => "Y",
            "CODE" => "FORM_RESULTS"
        );
        $b_count = false;

        $rsIBlocks = CIBlock::GetList(
            $sort,
            $filter,
            $b_count
        );
        $arIBlock = $rsIBlocks->GetNext();

        if ($arIBlock) {
            $bid = $arIBlock["ID"];

            // подготовим свойства для сохранения элемента в инфоблок
            $props = array();

            foreach ($arAnswers as $code => $answer) {
                $props[$code] = $answer[0]["USER_TEXT"];
            }

            // формируем поля для сохранения
            $fields = array(
                "IBLOCK_ID" => $bid,
                "NAME" => $props["name"],
                "PROPERTY_VALUES" => $props,
            );

            $element = new CIBlockElement;
            $eid = $element->Add($fields);

            if (empty($eid)) {
                // добавляем запись в журнал событий
                $error = $element->LAST_ERROR;
                CEventLog::Add(array(
                    'SEVERITY' => 'ERROR',
                    'AUDIT_TYPE_ID' => 'IBLOCK_ELEMENT_ADD_ERROR',
                    'MODULE_ID' => 'iblock',
                    'ITEM_ID' => $bid,
                    'DESCRIPTION' => "Ошибка добавления элемента -> $error"
                ));
            }
        }
    }
}

/* Функция получения ID формы по ее символьному коду */
function getFormIDBySID($formSID)
{
    $formID = false;

    if (CModule::IncludeModule("form") && strlen($formSID) > 0) {
        $rsForm = CForm::GetBySID($formSID);
        $arForm = $rsForm->Fetch();
        $formID = $arForm["ID"];
    }

    return $formID;
}