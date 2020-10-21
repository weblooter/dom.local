<?

/**
 * @var array $arParams
 * @var array $arResult
 */

use Bitrix\Main\Page\Asset;

Asset::getInstance()
    ->addString('<script type="text/javascript" src="/assets/js/app.js?id='.microtime(true).'"></script>');

$sJsonResultMatrix = json_encode($arResult['CURRENCY_MATRIX'], JSON_UNESCAPED_UNICODE);
Asset::getInstance()
    ->addString(
        <<<DOCHERE
<link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">
<script type="text/template" id="currencyCalcData">$sJsonResultMatrix</script>
DOCHERE
    );