<?php
// 1.1 В файл init.php (либо в отдельный файл, в котором хранятся обработчики событий битрикса) необходимо добавить следующий код
// Также необходимо создать путь каталогов (при условии, если их ещё нет) /local/logs/ для хранения лога

use Bitrix\Main\EventManager;

// Регистрация обработчика события
EventManager::getInstance()->addEventHandler(
    "iblock",
    "OnAfterIBlockElementUpdate",
    "onAfterIBlockElementUpdateLog"
);

// Реализация функции обработчика события
function onAfterIBlockElementUpdateLog(&$fields)
{
    global $USER;

    $userId = $USER->GetID();
    $userName = $USER->GetFullName();
    $iblockId = $fields['IBLOCK_ID'];
    $elementId = $fields['ID'];
    $timeStamp = date('Y-m-d H:i:s');

    // Формируем строку с информацией о пользователе и изменении элемента инфоблока
    $logString = "[{$timeStamp}] Пользователь ID: {$userId}, ФИО: {$userName}, изменение элемента инфоблока {$iblockId}, ID элемента: {$elementId}\n";

    // Записываем информацию в лог-файл
    file_put_contents($_SERVER["DOCUMENT_ROOT"].'/local/logs/iblock_changes.log', $logString, FILE_APPEND);
}

// 1.2 За отправку лога руководителю через форму будет отвечать следущий код:

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$logFileName = $_SERVER["DOCUMENT_ROOT"] . '/local/logs/iblock_changes.log';
$logContents = file_get_contents($logFileName);
if (!$logContents) {
    die('No new log entries to send.');
}

// Отправляем письмо через веб-форму
CModule::IncludeModule("form");

$arValues = array(
    "TITLE" => "Новые логи",
    "AUTHOR" => "REST API Битрикс24",
    "STATUS_ID" => "DEFAULT",
    "FORM_ID" => 3, // Замените 3 на id нужной вам веб-формы
    "mail_message" => 'Логи: ' . $logContents
);

CFormResult::Add($arValues);

// Очищаем лог
file_put_contents($logFileName, '');

// Так как запрещено использовать крон для отправки лога, то есть 2 варианта решения проблемы:
// 1) Реализовать отправку через агентов битрикса, с заданным интервалом в 30 минут,
// но в данной ситуации должна быть гарантия частого посещения сайта, так как стандартно агенты работают на хитах к сайту,
// что означает, что код будет выполняться на хите раз в 30 минут, но если пользователи будут редко посещать сайт, то есть вероятность,
// что отправка может происходить с большим интервалом времени, нежели 30 минут
// 2) поместить данную конструкцию в бесконечный цикл, который запустить в фоновом процессе, и настроить кастомный таймер, чтобы отправка просиходила раз в 30 минут
// Так этот не будет на прямую влиять на работу сайта, но будет отъедать ресурсы сервера из-за того, что постоянно будет находится в работе,
// что может привести к полному занятию ресурсов сервера на его обработку
// но за-то, будет гарантировано, что отправка лога будет происходить ровно раз в 30 минут
