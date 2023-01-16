<?php
declare(strict_types=1);

/**
 * Class to calculate the commission of transactions
 */
class CalculateCommissions
{
    /**
     * URL of the binlist service
     *
     * @var string
     */
    private string $binListUrl = "https://lookup.binlist.net/";

    /**
     * URL of the exchange rates service
     *
     * @var string
     */
    private string $exchangeRatesUrl = "https://api.exchangerate.host/latest";

    /**
     * Array to hold the exchange rates data
     *
     * @var array
     */
    private array $rates = [];

    /**
     * File name which contains the transactions data
     *
     * @var string
     */
    private string $fileName;

    /**
     * Array of two-letter country codes of EU countries
     *
     * @var array
     */
    private array $EUCountries = [
        'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU',
        'LV', 'MT', 'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'
    ];

    /**
     * Class constructor
     *
     * @param string $fileName
     * @throws InvalidArgumentException if file is not valid
     */
    public function __construct(string $fileName)
    {
        if (!is_file($fileName)) {
            throw new InvalidArgumentException(
                sprintf(
                    '"%s" is not a valid file',
                    $fileName
                )
            );
        }
        $this->fileName = $fileName;
    }

    /**
     * Retrieves the exchange rates from the service
     *
     * @return array
     */
    final public function getRates(): array
    {
        if (!empty($this->rates)) {
            return $this->rates;
        }

        $ratesData = file_get_contents($this->exchangeRatesUrl);
        $decoded = json_decode($ratesData, true, 512, JSON_THROW_ON_ERROR);
        $this->rates = (array) $decoded['rates'];

        return $this->rates;
    }

    /**
     * Calculates the commission of transactions
     *
     * @return string
     */

    public function calculate(): string
    {
        $output = '';
        $ratesArray = $this->getRates();

        if (!is_file($this->fileName)) {
            trigger_error('File ' . $this->fileName . ' cannot be found', E_USER_ERROR);
        }

        $contents = file_get_contents($this->fileName);
        $rowsArray = explode(PHP_EOL, $contents);
        foreach ($rowsArray as $dataJson) {
            $data = json_decode($dataJson, true, 512, JSON_THROW_ON_ERROR);
            $binData = file_get_contents($this->binListUrl . $data['bin']);
            $binDataArr = json_decode($binData, true, 512, JSON_THROW_ON_ERROR);
            $binCountry = $binDataArr['country']['alpha2'];
            $amountEUR = 0;
            if ($data['currency'] === 'EUR' || in_array($binCountry, $this->EUCountries, true)) {
                $amountEUR = $data['amount'] * 0.01;
            } elseif ($data['currency'] !== 'EUR'
                && null !== $ratesArray[$data['currency']]
                && !in_array($binCountry, $this->EUCountries, true)
            ) {
                $amountEUR = $data['amount'] / $ratesArray[$data['currency']] * 0.02;
            } else {
                trigger_error('Error with data: ' . implode(PHP_EOL, $data), E_USER_NOTICE);
            }
            $output .= round($amountEUR, 2) . PHP_EOL;
        }
        return $output;
    }
}