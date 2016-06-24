<?php

namespace HTML5test\Automate;

use GuzzleHttp\Client;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Exception\WebDriverCurlException;


class BrowserStack {

    private $project = 'HTML5test';
    private $build;

    public function __construct($config) {
        $this->config = $config;

        $this->build = 'Update reports @ ' . date("H:i");

        $this->client = new Client([
            'base_uri'  => 'https://www.browserstack.com/automate/',
            'timeout'   => 5.0,
            'auth'      => [ $this->config['username'], $this->config['accesskey'] ]
        ]);
    }

    public function getBrowsers() {
        $response = $this->client->get('browsers.json');
        return json_decode((string) $response->getBody());
    }

    private function getRunningSessions() {
        $response = $this->client->get('builds.json?status=running');
        $builds = json_decode((string) $response->getBody());

        $sessions = [];

        foreach ($builds as $key => $value) {
            if (isset($value->automation_build)) {
                $id = $value->automation_build->hashed_id;

                $response = $this->client->get('builds/' . $id . '/sessions.json?status=running');
                $data = json_decode((string) $response->getBody());

                foreach ($data as $key => $session) {
                    if (isset($session->automation_session)) {
                        $sessions[] = $session->automation_session;
                    }
                }
            }
        }

        return $sessions;
    }

    public function waitForAvailableSession() {
        $timeout = 60 * 5;
        $success = false;

        while ($timeout > 0 && !$success) {
            $sessions = $this->getRunningSessions();

            if (count($sessions) < $this->config['sessions']) {
                $success = true;
                continue;
            }

            sleep(5);
            $timeout -= 5;
        }

        if (!$success) {
            echo "ERROR: no sessions available... timeout!";
        }
    }

    public function openUrl($browser, $url) {
        $session = null;

        $browser->project = $this->project;
        $browser->build = $this->build;

        try {
            echo "  Connecting to remote session\n";
            $web_driver = RemoteWebDriver::create(
                "https://" . $this->config['username'] . ":" . $this->config['accesskey'] . "@hub-cloud.browserstack.com/wd/hub",
                $browser,
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
