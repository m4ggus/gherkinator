Gherkinator Selenium IDE parser is a simple parser which allows you to convert Selenium IDE tests and
try your scenarios under a behat gherkin environment.

This package can be found on packagist and is best loaded using composer

=======
Install
=======
    1- In the "composer.json" add :
        "behat/behat" : "3.1.x-dev",
        "behat/mink" : "1.6.*",
        "behat/mink-extension" : "*",
        "behat/mink-goutte-driver" : "*",
        "behat/mink-selenium2-driver" : "*",
        "monolog/monolog": "*",
        "gherkinator/selenium-ide-parser" : "dev-master"
    
    2- Update the composer
        sudo composer update

    3- In the "AppKernel.php" add :
        new Open\GherkinatorBundle\GherkinatorBundle(),

    4- In the "app/config/config.yml" add :
        imports:
        - { resource: @GherkinatorBundle/Resources/config/parameters.yml }
    
    5- Then Init behat :
        sudo bin/behat --init
        sudo chmod -R 777 features
    6- Then you should copy from "vendor/gherkinator/selenium-ide-parser" :
        "behat.yml" in the project root
        "WebContext.php" under "features/bootstrap"

Usage
=====
    php app/console gherkinator:feature:generate