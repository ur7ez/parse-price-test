<?php

namespace App\Services;

use App\Services\Contracts\ParserServiceInterface;
use App\Helpers\PriceHelper;
use App\Helpers\SelectorHelper;
use App\Helpers\UrlHelper;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverCapabilities;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverWait;

/**
 * Use if the ld+json script is dynamically added by JavaScript after the page loads.
 */
class SeleniumService implements ParserServiceInterface
{
    private array $_adData = [];  // ad data from ld+json script
    protected string $priceSelector;
    protected string $dataSelector;

    // Selenium server host address (localhost or IP, if Selenium works on another server)
    // protected string $host = 'http://localhost:4444';
    protected string $host = 'http://host.docker.internal:4444';
    protected RemoteWebDriver $_driver;
    protected WebDriverCapabilities $desiredCapabilities;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        $chromeOptions = (new ChromeOptions())
            ->addArguments([
                '--headless',
//                '--disable-gpu',
//                '--no-sandbox',
//                '--disable-dev-shm-usage',
//                '--disable-extensions',
                '--disable-images',
                '--blink-settings=imagesEnabled=false',
                '--user-agent=' . config('parser.http.user_agent'),
            ]);
        $this->desiredCapabilities = DesiredCapabilities::chrome();
        $this->desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        $this->_driver = RemoteWebDriver::create($this->host, $this->desiredCapabilities);  // connect to Selenium Grid

        $this->priceSelector = SelectorHelper::getPriceSelector();  // price element selector
        $this->dataSelector = SelectorHelper::getAdDataSelector();
    }

    public function parsePrice(array $urls): array
    {
        $results = [];

        foreach ($urls as $ind => $url) {
            $this->_adData[$url] = null;
            if (!UrlHelper::isValid($url)) {
                $results[] = [$url, config('parser.placeholders.invalid_url')];
                continue;
            }
            try {
                // if ($ind > 0) sleep(2);  // pause between concurrent requests
                $this->_driver->get($url);
                $wait = new WebDriverWait(
                    $this->_driver,
                    config('parser.selenium.timeout'),
                    config('parser.selenium.driver_pollibng_interval')
                ); // waiting for page load
                try {
                    $wait->until(
                        WebDriverExpectedCondition::presenceOfElementLocated(
                            WebDriverBy::cssSelector($this->dataSelector)
                        )
                    );
                } catch (\Exception $e) {
                    $results[] = [$url, config('parser.placeholders.invalid_url')];
                    logger()->warning('WebDriver timed out on element presence location: ' . $e->getMessage(), ['url' => $url]);
                    continue;
                }

                // Find ad data script element

                // This way does not work with large / complex content like json:
//                $ldJsonContent = $this->_driver
//                    ->findElement(WebDriverBy::cssSelector($this->dataSelector))
//                    ->getAttribute('innerHTML');

                // Use plain JS to get json content for script tag:
                $ldJsonContent = $this->_driver->executeScript(
                    'return document.querySelector(arguments[0]).textContent;',
                    [$this->dataSelector]
                );
                try {
                    $adData = json_decode($ldJsonContent, true, 512, JSON_THROW_ON_ERROR);
                    $this->_adData[$url] = $adData;
                    $price = $adData['offers']['price']
                        ?? config('parser.placeholders.price_not_found');
                    $results[] = [$url, $price];
                } catch (\JsonException $ex) {
                    logger()->error("Error getting ad data from `$url`: " . $ex->getMessage() . ' Code: ' . $ex->getCode() . "\n Data content: $ldJsonContent");
                }
            } catch (\Exception $e) {
                $results[] = [$url, config('parser.placeholders.invalid_url')];
                logger()->error('Selenium error: ' . $e->getMessage() . ' Code: ' . $e->getCode(), ['url' => $url]);
            }
        }
        $this->_driver->quit();  // close browser sessions

        return $results;
    }

    /**
     * get ad page ld+json script data
     * @return array
     */
    public function getAdData(): array
    {
        return $this->_adData;
    }

    public function __destruct()
    {
        $this->_driver->quit();
    }
}
