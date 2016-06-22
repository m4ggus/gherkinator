<?php
/**
 * @author: Open
 */
namespace Open\GherkinatorBundle\Services;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Converter {
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function execute($folder) {
        //If you want to generate *.feature: php convert.php <Selenium_IDE.test Folder> <Destination>
        /*$console = new Console();
        $console->activate($argv);*/

        $dir = opendir($folder);

        // -----------------------------------------------------------------------------
        // Logging
        // -----------------------------------------------------------------------------
        $date_debut = date("Y-m-d H:i:s");
        $name_log_file = 'gherkinator.log';
        $log = new Logger($name_log_file);
        $log->pushHandler(new StreamHandler($this->container->getParameter('log_path').$name_log_file, Logger::DEBUG)); //log channel
        $log->pushHandler(new FirePHPHandler());
        $log->addInfo("----- HEURE DEBUT " . $date_debut . "\n\n"); //Write in the log

        // -----------------------------------------------------------------------------
        //Archivate old features
        // -----------------------------------------------------------------------------
        Utils::archivate($this->container->getParameter('archive_path'), $this->container->getParameter('features_path'));
        $log->addInfo("\t ARCHIVAGE OK\n\n");

        // -----------------------------------------------------------------------------
        //Create new features
        // -----------------------------------------------------------------------------
        while (false !== ($fichier = readdir($dir))) { //Read files from the source folder
            $file_path = $folder.$fichier;
            $feature = null;
            if (($fichier != '.') && ($fichier != '..') && (!preg_match('/\.tar/', $fichier)) && is_file($file_path)) {
                $testsuitehtml = Utils::file_get_html($file_path);
                if ($testsuitehtml && count($testsuitehtml->filter('td'))) { //There is a table
                    $feature_name = $testsuitehtml->filter('td')->eq(0); //Retieve the feature's name
                    if (count($feature_name->filter('b'))) { //Title in bold
                        $feature_name = $testsuitehtml->filter('b')->eq(0); //Retieve the feature's name
                    }
                    $feature_name = $feature_name->html();
                    $header = "@ui\nFeature: " . $feature_name . "\n\n\n" .
                        "@javascript \n" .
                        "    Scenario:"; //Feature Gherkin's hearder
                    $body = null;
                    if (count($testsuitehtml->filter('a'))) { //It's a test case suite
                        $total_line_converted = 0;
                        $total_line = 0;

                        foreach ($testsuitehtml->filter('a') as $tr) { //Run the file line by line in order to convert them
                            $file_path = $folder.$tr->attributes->item(0)->nodeValue;
                            list($nb_line, $nb_converted_line, $part_feature) = Utils::convertfile2feature($file_path);
                            $body .= $part_feature;
                            $total_line_converted += $nb_converted_line;
                            $total_line += $nb_line;
                        }
                    } else { //It's a simple test case
                        list($total_line, $total_line_converted, $body) = Utils::convertfile2feature($file_path);
                    }
                    if ($body) {
                        $feature = $header . $body;
                        $file = $feature_name . '.feature';
                        if(is_dir($this->container->getParameter('features_path'))) {
                            file_put_contents($this->container->getParameter('features_path') . $file, $feature);
                        } //Write in the file destination;
                        else{
                            $message = "First you have to init behat\n";
                            $message.= "You have to execute the next lines\n";
                            $message.= "\tsudo bin/behat --init\n";
                            $message.= "\tsudo chmod -R 777 features/\n";
                            throw  new \Exception($message);
                        }
                        $log->addInfo("\t CONVERT \"" . $fichier . "\" TO \t \"" . $file . "\"\t" . 'OK' . "\n");
                        if ($total_line != $total_line_converted) {
                            Utils::copyFile($fichier, $folder, $this->container->getParameter('to_review_file')); //Copy the file in the toReview file
                        } else {
                            Utils::copyFile($fichier, $folder, $this->container->getParameter('treated_file')); //Copy the file in the treated file
                        }
                    }
                } else {
                    $log->addInfo("\t FILE: " . $fichier . "\t IS NOT SUPPORTED \tCONVERSION KO \n");
                    Utils::copyFile($fichier, $folder, $this->container->getParameter('error_file')); //Copy the file in the error file
                }
            }
        }
        closedir($dir);
        $date_fin = date("Y-m-d H:i:s");
        $log->addInfo("----- HEURE FIN " . $date_fin);
        $interval = (strtotime($date_fin) - strtotime($date_debut));
        $log->addInfo("(TIME : " . $interval . " SEC)");
        $log->addInfo("OK");
        return $name_log_file;
    }
}
?>