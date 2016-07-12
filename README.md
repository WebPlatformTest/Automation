<img src="https://html5te.st/assets/html5test.svg" width="250">

Use BrowserStack and Selenium Webdriver to automatically generate test reports for HTML5test.com. This is a command line tool that is responsible for new browser reports ending up on HTML5test.com. 

###Instructions:
In order to run this tool you need a BrowserStack account that allows automated testing. Rename the `config.php.dist` file to `config.php` and enter your BrowserStack API username and access key.

To install all the dependancies run the following command:
````
composer install
````

Start the tool by running the follow command:
````
php bin/automate.php
````

###Thanks to:
<a href="https://www.browserstack.com"><img src="https://html5te.st/assets/browserstack.svg" width="180"></a>
