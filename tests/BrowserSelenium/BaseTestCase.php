<?php

namespace Tests\BrowserSelenium;

use Lmc\Steward\ConfigProvider;
use Facebook\WebDriver\WebDriverBy;
use Lmc\Steward\Test\AbstractTestCase;

/**
 * Abstract class for custom tests, could eg. define some properties or instantiate some common components in setUp().
 */
abstract class BaseTestCase extends AbstractTestCase
{
    /** @var int Default width of browser window (Steward's default is 1280) */
    public static $browserWidth = 1280;
    /** @var int Default height of browser window (Steward's default is 1024) */
    public static $browserHeight = 1024;
    /** @var string */
    public static $baseUrl;

    public function setUp()
    {
        parent::setUp();

        // Set base url according to environment
        switch (ConfigProvider::getInstance()->env) {
            case 'dev':
                self::$baseUrl = 'http://monica.test/';
                break;
            case 'laravel':
                self::$baseUrl = 'http://127.0.0.1:8000/';
                break;
            case 'local':
                self::$baseUrl = 'http://127.0.0.1/';
                break;
            default:
                throw new \RuntimeException(sprintf('Unknown environment "%s"', ConfigProvider::getInstance()->env));
        }

        $this->debug('Base URL set to "%s"', self::$baseUrl);

        if (ConfigProvider::getInstance()->env == 'production') {
            $this->warn('The tests are run against production, so be careful!');
        }
    }

    /**
     * Init the WebDriver.
     * (init should be run with "before" phpunit annotation, but it doesn't work !).
     */
    public function init()
    {
        $this->wd->get(self::$baseUrl);
    }

    /**
     * Init WebDriver and pass the login form.
     */
    public function initAndLogin()
    {
        $this->init();

        if ($this->getCurrentPath() == '/') {
            $emailElement = $this->wd->findElement(WebDriverBy::id('email'));
            $emailElement->sendKeys('admin@admin.com');

            $passwordElement = $this->wd->findElement(WebDriverBy::id('password'));
            $passwordElement->sendKeys('admin');

            $this->wd->findElement(WebDriverBy::tagName('button'))->click();
            
            $this->waitForLinkText('Logout');
        }
    }

    /**
     * Get the current url path.
     */
    public function getCurrentPath()
    {
        $url = $this->wd->getCurrentURL();

        return parse_url($url)['path'];
    }
}
