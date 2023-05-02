<?php
// Чтобы CSaleBasket::GetList в свойстве DETAIL_PAGE_URL возвращал ссылку на карточку товара, а не предложения,
// необходимо подменить её в обработчике событий добавления товара в корзину

AddEventHandler("sale", "OnBeforeBasketAdd", "changeDetailPageUrl");
function changeDetailPageUrl(&$arFields)
{
    if(CModule::IncludeModule('catalog') && CModule::IncludeModule('iblock'))
    {
        if($arFields["PRODUCT_PROVIDER_CLASS"] === "CCatalogProductProvider" && $arFields["MODULE"] === "catalog" && $arFields["PRODUCT_ID"] > 0) {
            $offer = CCatalogSku::GetProductInfo($arFields["PRODUCT_ID"]);
            if($offer)
            {
                $arResult = CIBlockElement::GetByID($offer["ID"])->GetNext();
                $arFields["DETAIL_PAGE_URL"] = $arResult["DETAIL_PAGE_URL"];
            }
        }
    }
}
