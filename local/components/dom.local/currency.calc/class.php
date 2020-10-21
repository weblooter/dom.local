<?

use Bitrix\Currency\CurrencyRateTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Web\HttpClient;

class CurrencyCalcComponent extends CBitrixComponent
{
    /**
     * @return mixed|void|null
     */
    public function executeComponent()
    {
        try {
            if (!Loader::includeModule('currency')) {
                throw new Exception('Модуль валют не доступен.', 10);
            }

            $this->checkValidRates();
            if ($this->startResultCache(60 * 60)) {
                $this->fillResult();
                if (empty($this->arResult)) {
                    $this->abortResultCache();
                    throw new Exception('Не удалось построить матрицу валют.', 20);
                }
                $this->endResultCache();
            }

            $this->includeComponentTemplate();
        } catch (Throwable $e) {
            switch ((int)$e->getCode()) {
                case 10;
                case 20;
                    ?><p style="color: #d12"><?=$e->getMessage()?></p><?php
                    break;
            }
        }
    }

    /**
     * Заполняем матрицу валют
     */
    protected function fillResult()
    {
        $arResult = [];
        try {
            $rsCurrencyRates = CurrencyRateTable::getList(
                [
                    'filter' => [
                        '>=DATE_RATE' => new Bitrix\Main\Type\Date(),
                    ],
                    'group' => [
                        'CURRENCY'
                    ],
                    'select' => [
                        'CURRENCY',
                        'RATE_CNT',
                        'RATE',
                        'BASE_CURRENCY',
                    ]
                ]
            );
            $arResult['CURRENCY_MATRIX'] = [];
            while ($a = $rsCurrencyRates->fetch()) {
                $arResult['CURRENCY_MATRIX'][$a['BASE_CURRENCY']][$a['CURRENCY']] = round($a['RATE_CNT'] / $a['RATE'], 6);
                $arResult['CURRENCY_MATRIX'][$a['CURRENCY']][$a['BASE_CURRENCY']] = round($a['RATE'] / $a['RATE_CNT'], 6);
            }
            if (empty($arResult['CURRENCY_MATRIX'])) {
                throw new Exception();
            }

            ksort($arResult['CURRENCY_MATRIX']);
            array_walk(
                $arResult['CURRENCY_MATRIX'],
                function (&$v)
                    {
                        ksort($v);
                    }
            );
        } catch (Throwable $e) {
            $arResult = [];
            $this->abortResultCache();
        }
        $this->arResult = $arResult;
    }

    /**
     * Проверяет курсы валют на текущий день
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function checkValidRates()
    {
        if (empty($this->getSiteCurrenciesByCurrentDay())) {
            $this->fillCurrencyRateByCurrentDay();
        }
    }

    /**
     * Производит заполнение валют на текущий день
     */
    protected function fillCurrencyRateByCurrentDay()
    {
        try {
            $oHttpClient = new HttpClient();
            $oHttpClient->get('https://www.cbr-xml-daily.ru/daily_json.js');
            if ((int)$oHttpClient->getStatus() !== 200) {
                throw new Exception();
            }
            $aCurrencies = json_decode($oHttpClient->getResult(), true);
            if (
                !is_null($aCurrencies)
                && !empty($aCurrencies['Valute'])
                && is_array($aCurrencies['Valute'])
            ) {
                $this->actualizeCurrenciesRate($aCurrencies['Valute']);
            }
        } catch (Throwable $e) {
        }
    }

    /**
     * Актуализирует курсы валют за текущий день
     *
     * @param  array  $aCBData
     *
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function actualizeCurrenciesRate(array $aCBData)
    {
        $aSiteCurrenciesCurrentDay = $this->getSiteCurrenciesByCurrentDay();
        array_walk(
            $aCBData,
            function ($aData, $sCurrency) use ($aSiteCurrenciesCurrentDay)
                {
                    if (!in_array($sCurrency, $aSiteCurrenciesCurrentDay)) {
                        CurrencyRateTable::add(
                            [
                                'CURRENCY' => $sCurrency,
                                'BASE_CURRENCY' => 'RUB',
                                'DATE_RATE' => new Bitrix\Main\Type\DateTime(),
                                'RATE_CNT' => 1,
                                'RATE' => (float)$aData['Value']
                            ]
                        );
                    }
                }
        );
    }

    /**
     * Получить список валют из курсов за сегодняшний день
     *
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getSiteCurrenciesByCurrentDay(): array
    {
        $a = [];
        $rs = CurrencyRateTable::getList(
            [
                'filter' => [
                    '>=DATE_RATE' => new Bitrix\Main\Type\Date(),
                ],
                'group' => [
                    'CURRENCY'
                ],
                'select' => [
                    'CURRENCY'
                ]
            ]
        );
        if ($rs->getSelectedRowsCount() > 0) {
            $a = array_column($rs->fetchAll(), 'CURRENCY');
        }
        return $a;
    }
}