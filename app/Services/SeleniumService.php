<?php

namespace App\Services;

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

class SeleniumService
{
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
                '--user-agent=Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.5790.110 Safari/537.36'
            ]);
        $this->desiredCapabilities = DesiredCapabilities::chrome();
        $this->desiredCapabilities->setCapability(ChromeOptions::CAPABILITY, $chromeOptions);

        $this->_driver = RemoteWebDriver::create($this->host, $this->desiredCapabilities);  // connect to Selenium Grid
    }

    public function parsePrice(array $urls = []): array
    {
        $results = [];
        $cssSelector = SelectorHelper::getPriceSelector('css');  // price element selector

        foreach ($urls as $ind => $url) {
            if (!UrlHelper::isValid($url)) {
                $results[$url] = null;
                continue;
            }
            try {
                // if ($ind > 0) sleep(2);  // pause between concurrent requests
                $this->_driver->get($url);
                $wait = new WebDriverWait($this->_driver, 7, 200); // waiting for page load
                try {
                    $wait->until(
                        WebDriverExpectedCondition::presenceOfElementLocated(
                            WebDriverBy::cssSelector($cssSelector)
                        )
                    );
                } catch (\Exception $e) {
                    logger()->info('WebDriver timed out on element presence location: ' . $e->getMessage());
                }
                // find price html element
                $priceElement = $this->_driver->findElement(WebDriverBy::cssSelector($cssSelector));
                $priceText = $priceElement->getText();
                $results[$url] = PriceHelper::convertToFloat($priceText);
            } catch (\Exception $e) {
                logger()->error('Selenium error: ' . $e->getMessage() . ' Code: ' . $e->getCode(), ['url' => $url]);
                $results[$url] = null;
            }
        }
        $this->_driver->quit();  // close browser sessions

        return $results;
    }

    public function __destruct()
    {
        $this->_driver?->quit();
    }
}
