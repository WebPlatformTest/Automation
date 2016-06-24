<?php

namespace HTML5test\Automate;

use HTML5test\Automate\BrowserStack;
use HTML5test\Automate\HTML5test;

class Main {

    public function __construct($config) {
        $this->config = $config;
    }

    public function run() {
        // Retrieve the already finished identifiers from HTML5test
        $html5test = new HTML5test($this->config['html5test']);
        $finishedIdentifiers = $html5test->getIdentifiers('browserstack');

        // Retrieve the available identifiers from BrowserStack
        $browserstack = new BrowserStack($this->config['browserstack']);
        $availableIdentifiers = $this->getIdentifiersFromBrowsers($browserstack->getBrowsers());

        // Determine which identifiers we still need to process
        $unknownIdentifiers = $this->findUnknownIdentifiers($availableIdentifiers, $finishedIdentifiers);

        echo "Found " . count($unknownIdentifiers) . " unknown identifiers\n\n";

        $i = 1;

        foreach ($unknownIdentifiers as $identifier => $browser) {
            $browserstack->waitForAvailableSession();

            echo "- [" . $i . "/" . count($unknownIdentifiers) . "] " . $identifier . "\n\n";

            $result = false;
            $task = $html5test->getTask('browserstack', $identifier);

            $session = $browserstack->openUrl($browser, $task->url);

            echo "  Waiting for result...";

            $timeout = 30;
            $finished = false;

            while (!$finished && $timeout > 0) {
                $result = $html5test->hasTask($task->task);

                if ($result !== false) {
                    break;
                }

                echo ".";
                sleep(1);
                $timeout--;
            }

            echo "\n";

            if ($session) {
                $browserstack->close($session);
            }

            echo "\n";

            if ($result) {
                echo "  Score: " . $result->score . "  => [ " . $result->url . " ]" . "\n";
            } else {
                echo "  Failed!!!";
            }

            echo "\n\n";
            $i++;
        }
    }

    private function findUnknownIdentifiers($available, $finished) {
        $identifiers = [];

        foreach ($available as $key => $value) {
            if (!in_array($key, $finished)) {
                $identifiers[$key] = $value;
            }
        }

        return $identifiers;
    }

    private function getIdentifiersFromBrowsers($browsers) {
        $identifiers = [];

        foreach ($browsers as $key => $value) {
            $identifiers[$this->browserToIdentifier($value)] = $value;
        }

        return $identifiers;
    }

    private function browserToIdentifier($browser) {
        $tokens = [];

        if (!is_null($browser->browser)) {
            $token = $browser->browser;

            if (!is_null($browser->browser_version)) {
                $token .= '-' . $browser->browser_version;
            }

            $tokens[] = $token;
        }

        if (!is_null($browser->os)) {
            $token = $browser->os;

            if (!is_null($browser->os_version)) {
                $token .= '-' . $browser->os_version;
            }

            $tokens[] = $token;
        }

        if (!is_null($browser->device)) {
            $tokens[] = $browser->device;
        }

        return implode('|', $tokens);
    }
}