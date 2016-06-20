Gherkinator Selenium IDE parser is a simple parser which allows you to convert Selenium IDE tests and
try your scenarios under a behat gherkin environment.



Install
===========================================

    This package can be found on packagist and is best loaded using composer

Usage
============================================
    1- In the "composer.json" add :
        "behat/behat" : "3.1.x-dev",
        "behat/mink" : "1.6.*",
        "behat/mink-extension" : "*",
        "behat/mink-goutte-driver" : "*",
        "behat/mink-selenium2-driver" : "*",
        "monolog/monolog": "*",
        "gherkinator/selenium-ide-parser" : "dev-master"

    2- In the "AppKernel.php" add :
        new Open\GherkinatorBundle\GherkinatorBundle(),

    3- In the "app/config/parameters.yml" add :
        #Gherkinator
            default_target_charser: "UTF-8"
            default_br_text: "\r\n"
            default_span_text: " "
            max_file_size: "600000"
            default_delay: "5000"
            features_path: "%kernel.root_dir%/../features/"
            screen_shots_path: "%kernel.root_dir%/../screen_shots/"
            gherkinator_path: "%kernel.root_dir%/../src/Open/GherkinatotBundle/"
            archive_path: "%kernel.root_dir%/../archives/"
            log_path: "%kernel.root_dir%/logs/"
            treated_file: "treated/"
            error_file: "error/"
            to_review_file: "toReview/"
    
    4- Then Init behat :
        sudo bin/behat --init
        sudo chmod -R 777 features
    5- Then you should copy from "vendor/gherkinator/selenium-ide-parser" :
        "behat.yml" in the project root
        "WebContext.php" under "features/bootstrap"