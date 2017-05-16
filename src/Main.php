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

        $skipIdentifiers = [
            "chrome-17.0|Windows-XP", "chrome-17.0|Windows-7", "opera-12.15|OS X-Lion", "opera-12.15|OS X-Mountain Lion", "opera-12.15|OS X-Mavericks",
            "ipad|ios-5.0|iPad 2 (5.0)", "ipad|ios-5.1|iPad 3rd", "iphone|ios-5.1|iPhone 4S", "ipad|ios-6.0|iPad 3rd (6.0)", "iphone|ios-6.0|iPhone 4S (6.0)",
            "iphone|ios-6.0|iPhone 5", "ipad|ios-7.0|iPad Mini", "ipad|ios-7.0|iPad 4th", "iphone|ios-7.0|iPhone 5S", "ipad|ios-8.3|iPad Mini 2",
            "ipad|ios-8.3|iPad Air", "iphone|ios-8.3|iPhone 6", "iphone|ios-8.3|iPhone 6 Plus", "ipad|ios-9.1|iPad Air 2", "ipad|ios-9.1|iPad Mini 4",
            "ipad|ios-9.1|iPad Pro", "iphone|ios-9.1|iPhone 6S", "android|android-4.0|Amazon Kindle Fire 2", "android|android-4.0|Amazon Kindle Fire HD 8.9",
            "android|android-4.0|Samsung Galaxy Note 10.1", "android|android-4.0|HTC One X", "android|android-4.0|Motorola Razr",
            "android|android-4.0|Sony Xperia Tipo", "android|android-4.0|Google Nexus", "android|android-4.3|Amazon Kindle Fire HDX 7",
            "android|android-4.3|Samsung Galaxy S4", "android|android-4.3|Samsung Galaxy Note 3", "android|android-4.1|Google Nexus 7",
            "android|android-4.1|Samsung Galaxy S3", "android|android-4.1|Samsung Galaxy Note 2", "android|android-4.1|Motorola Razr Maxx HD",
            "android|android-4.4|Samsung Galaxy Tab 4 10.1", "android|android-4.4|Samsung Galaxy S5", "android|android-4.4|Samsung Galaxy S5 Mini",
            "android|android-4.4|HTC One M8", "android|android-5.0|Google Nexus 5", "android|android-4.2|Google Nexus 4"
        ];

        foreach ($available as $key => $value) {
            if (!in_array($key, $finished) && !in_array($key, $skipIdentifiers)) {
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