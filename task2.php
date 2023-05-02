<?php
require_once $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php";

use \Bitrix\Iblock\Elements\ElementCoursesTable as CoursesTable;
use \Bitrix\Iblock\Elements\ElementScheduleTable as ScheduleTable;
use Bitrix\Main\ORM\Fields\Relations\Reference;

$cityName = 'some name';

$courses = CoursesTable::getList([
    'select' => [
        'ID',
        'CITY.ELEMENT',
        'SCHEDULE_TIME'
    ],
    'filter' => [
        'CITY.ELEMENT.NAME' => $cityName,
        '<=SCHEDULE_TIME.ACTIVE_FROM' => ConvertDateTime(date('d-m-Y H:i:s')),
        '>=SCHEDULE_TIME.ACTIVE_TO' => ConvertDateTime(date('d-m-Y H:i:s'))
    ],
    'runtime' => [
        new Reference(
            'SCHEDULE_TIME',
            ScheduleTable::class,
            ['this.ID' => 'ref.COURSE_ID'],
            ['join_type' => 'inner']
        ),
    ],
])->fetchAll();
