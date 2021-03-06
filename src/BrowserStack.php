<?php

namespace HTML5test\Automate;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Exception\WebDriverCurlException;


class BrowserStack {

    private $project = 'HTML5test';
    private $build;

    public function __construct($config) {
        $this->config = $config;

        $this->client = new Client([
            'base_uri'  => 'https://www.browserstack.com/automate/',
            'timeout'   => 5.0,
            'auth'      => [ $this->config['username'], $this->config['accesskey'] ]
        ]);

        try {
            $response = $this->client->get('plan.json');
        } catch(ClientException $e) {
            die ("Error: Please make sure your Browserstack username and accesskey are correct!\n");
        }

        $this->build = 'Update reports @ ' . date("H:i");
    }

    public function getBrowsers() {
        $response = $this->client->get('browsers.json');
        return json_decode((string) $response->getBody());
    }

    public function getPlan() {
        $response = $this->client->get('plan.json');
        return json_decode((string) $response->getBody());
    }

    public function waitForAvailableSession() {
        $timeout = 60 * 5;
        $success = false;

        while ($timeout > 0 && !$success) {
            $plan = $this->getPlan();

            if ($plan->parallel_sessions_running < $plan->parallel_sessions_max_allowed) {
                $success = true;
                continue;
            }

            sleep(5);
            $timeout -= 5;
        }

        if (!$success) {
            die ("Error: No sessions available... please try again later!\n");
        }
    }

    public function openUrl($browser, $url) {
        $session = null;

        $browser->project = $this->project;
        $browser->build = $this->build;
        $browser->realMobile = true;

        try {
            echo "  Connecting to remote session\n";
            $web_driver = RemoteWebDriver::create(
                "https://" . $this->config['username'] . ":" . $this->config['accesskey'] . "@hub-cloud.browserstack.com/wd/hub",
                (array) $browser,
                1200000
            );

            $session = $web_driver->getSessionID();

            echo "  Loading URL " . $url . "\n";
            $web_driver->get($url);
        } catch (WebDriverException $e) {
        } catch (WebDriverCurlException $e) {
        } catch (Exception $e) {
        }

        return $session;
    }

    public function close($session) {
        try {
            $web_driver = RemoteWebDriver::createBySessionID(
                $session,
                "https://" . $this->config['username'] . ":" . $this->config['accesskey'] . "@hub-cloud.browserstack.com/wd/hub"
            );

            echo "  Closing remote session...\n";
            $web_driver->quit();
        } catch (WebDriverException $e) {
        } catch (WebDriverCurlException $e) {
        } catch (Exception $e) {
        }
    }
}
