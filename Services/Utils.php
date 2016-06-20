<?php
/**
 * @author: Open
 */
namespace Open\GherkinatorBundle\Services;

use Symfony\Component\DomCrawler\Crawler;

class Utils {
    /**
     * file_get_html
     * syntax reading html tag from the file
     */
    static function file_get_html($url, $use_include_path = false, $context = null, $offset = -1) {
        //}, $lowercase = true, $forceTagsClosed = true, $target_charset = DEFAULT_TARGET_CHARSET, $stripRN = true, $defaultBRText = DEFAULT_BR_TEXT, $defaultSpanText = DEFAULT_SPAN_TEXT) {
        if($html = file_get_contents($url, $use_include_path, $context, $offset)){
            $dom = new Crawler($html);
            return $dom;
        }
        return false;
    }

    /**
     * convertfile2feature
     * syntax conversion of an entire file
     */
    static function convertfile2feature($file_path) {
        $feature = null;
        $testsuitehtml = self::file_get_html($file_path); // Retrieve tests cases from ".test" files
        $nb_ligne = 0;
        $nb_ligne_convert = 1;
        foreach ($testsuitehtml->filter('tr') as $tr) { //Run the file line by line in order to convert them
            $feature .= self::convert2step($tr); //Find the tests cases and convert them
            if (self::convert2step($tr) != $result = "        "."\n") { //empty line
                $nb_ligne_convert++;
            }
            $nb_ligne++;
        }
        return array($nb_ligne, $nb_ligne_convert, $feature);
    }

    /**
     * convert2step
     * syntax copying file
     */
    static function copyFile($file, $source_path, $destination_folder) {
        $destination_path = addslashes($source_path.$destination_folder);
        if(!file_exists($destination_path)){
            mkdir($destination_path);
            chmod($destination_path, 0766);  // Everything for the owner, read and write for the others
        }
        $contents = file_get_contents(addslashes($source_path.$file));
        file_put_contents($destination_path.$file, $contents); //Write in the file destination;
    }

    /**
     * convert2step
     * syntax archiving
     */
    static function archivate($archive_path, $feature_path){
        if(!file_exists($archive_path)){
            mkdir($archive_path);
            chmod($archive_path, 0766);  // Everything for the owner, read and write for the others
        }
        if(sizeof(glob($feature_path."*")) >1){
            $archive = new \ZipArchive();
            $archive_name = 'Archives-'.date('Ymd_His').".zip";
            $archive->open($archive_path.$archive_name,  \ZipArchive::CREATE);
            $dir_features = opendir($feature_path);
            while(false !== ($feature_archive_file = readdir($dir_features))) {
                if(preg_match('/\.feature$/', $feature_archive_file)){
                    $feature_archive_file = $feature_path.$feature_archive_file;
                    $archive->addFromString(basename($feature_archive_file),  file_get_contents($feature_archive_file));
                }
            }
            closedir($dir_features);
            $archive->close();
        }
    }

    /**
     * convert2step
     * syntax conversion line by line
     */
    static function convert2step($command) {
        $action2step = array(
            'open'                 => 'Given I am on $target',
            'click'                => 'When I click on $target',
            'clickAndWait'         => 'When I click on $target'."\n".'        Then I wait for 1 seconds',
            'check'                => 'When I check $target',
            'type'                 => 'When I fill in $target with $value',
            //'select'               => 'When I select $target with $value',
            //'store'                => 'When I store',
            'verify'               => 'Then I should see',
            'assert'               => 'Then I should see',
            'waitFor'              => 'Then I should see',
            'not'                  => 'Then I should not see',
            //'gotoIf'               => 'Then I go to $value if $target',
            'label'                => 'Given I am on label $target',
        );
        $result = "        "; //indentation
        $td0 = $command->childNodes[1];
        if ($td0) $instruction = $td0->nodeValue;
        else $instruction = '';
        if ($td1 = $command->childNodes[3]) {
            $target = self::parse_target($td1->nodeValue);
            unset($value, $action, $postfix);
            list($action, $postfix) = self::parse_assert_command($instruction);
            if ($td2 = $command->childNodes[5]) {
                if (strpos($td2->nodeValue, "=")) {
                    list($m, $value) = explode("=", $td2->nodeValue);
                } else {
                    $value = $td2->nodeValue;
                }
                if ($action == 'type') $value = " ";
                $value = "\"$value\"";
            }
            if ($action === false) {
                if (!empty($target)) {
                    $target = "$target\"";
                }
                if (preg_match('/("id")/', $target) === 0) {
                    $target = "\"$target";
                }
                switch ($instruction) {
                    case 'open':
                        if (empty($target)) {
                            $target = 'homepage';
                        }
                        break;
                    case 'label':
                        if (!empty($label)) {
                            $target = $label;
                        }
                        break;
                    case 'setSpeed':
                        if (!empty($target)) {
                            $delay = intval($target);
                        }
                        /*else {
                            $container = new \Symfony\Component\DependencyInjection\Container();
                            $delay = $container->getParameter('default_delay');
                        }*/
                        return '';
                    default:
                        break;
                }

                if (isset($action2step[$instruction])  && $r = $action2step[$instruction]) {
                    eval("\$result .= \"$r\";");
                }
            } else {
                if (isset($action2step[$action]) && $r = $action2step[$action]) {
                    if (preg_match('/Not/', $postfix)) {
                        $result .= $action2step["not"];
                    } else {
                        eval("\$result .= \"$r\";");
                    }
                }
                if ($postfix !== 'TextPresent') {
                    if (preg_match('/"css"/', $target) === 0) {
                        $target = "$target";
                    }
                }
                switch ($postfix) {
                    case 'Title':
                        $result .= " \"$value\" in the title";
                        break;
                    case 'NotTitle':
                        $result .= " not \"$value\" in the title";
                        break;
                    case 'Table':
                        $result .= " \"$value\" in the table cell $target\"";
                        break;
                    case 'NotTable':
                        $result .= " not \"$value\" in the table cell $target\"";
                        break;
                    case 'NotText':
                    case 'NotValue':
                        $result .= " not \"$value\"";
                        if (!empty($target)) {
                            $result .= " in the element $target\"";
                        }
                        break;
                    case 'TextPresent':
                        $result .= " $target";
                        break;
                    case 'Visible':
                    case 'NotVisible':
                    case 'ElementPresent':
                        $result .= " \"$target\"";
                        if (!empty(str_replace('""', '', $value))) {
                            if ($action == 'store') $result .= " into ";
                            else $result .= " with ";
                            $result .= $value;
                        }
                        break;
                    case 'Eval':
                        $result .= " \"$target\"";
                        if (!empty(str_replace('""', '', $value))) {
                            if ($action == 'store') $result .= " into ";
                            else $result .= " with value ";
                            $result .= $value;
                        }
                        break;
                    case 'Value':
                        $result .= " $target\"";
                        if (!empty(str_replace('""', '', $value))) {
                            if ($action == 'store') $result .= " into ";
                            else $result .= " with value ";
                            $result .= $value;
                        }
                        break;
                    case 'Text':
                        $result .= " \"$target\"";
                        if (!empty(str_replace('""', '', $value))) {
                            if ($action == 'store') $result .= " into ";
                            else $result .= " with ";
                            $result .= $value;
                        }
                        break;
                    case 'ElementNotPresent':
                        $result .= " \"$target\"";
                        if (!empty(str_replace('""', '', $value))) $result .= " with value $value";
                        break;
                    default:
                        $result .= " $value ";
                        if (!empty($target)) {
                            $result .= "in the element \"$target\"";
                        }
                        break;
                }
            }
        }
        $result .= "\n";
        return $result;
    }

    /**
     * parse_assert_command
     * syntax parsing Selenium IDE steps which are mixed like storeEval, VerifyElementpresent ...
     */
    static function parse_assert_command($instruction) {
        //$assertactions = array('waitFor', 'verify', 'assert', 'store');
        $assertactions = array('waitFor', 'verify', 'assert');
        foreach ($assertactions as $a) {
            if (strpos($instruction, $a) !== false) {
                return (array($a, substr($instruction, strlen($a))));
            }
        }
        return array(false, false);
    }


    /**
     * parse_target
     * syntax parsing Selenium IDE target
     */
    static function parse_target($td) {
        $target = str_replace("\"", "'", trim($td));
        $target = str_replace("&gt;", ">", $target);
        $target = str_replace("&#152", "~", $target);
        $target = str_replace("&#134", "+", $target);

        if (empty($target)) {
            return "";
        }
        if (preg_match('/^xpath=(.+)$/', $target, $matches) === 1 || preg_match('/^\/\/(.+)$/', $target, $matches) === 1) {
            return "$matches[0]\" \"xpath";
        }
        if (preg_match('/^css=(.+)$/', $target, $matches) === 1) {
            return "$matches[1]\" \"css";
        }
        if (preg_match('/^id=(.+)$/', $target, $matches) === 1) {
            return "\"$matches[1]\" \"id";
        }
        if (preg_match('/^link=(.+)$/', $target, $matches) === 1) {
            return "$matches[1]\" \"link";
        }
        if (preg_match('/^(\w+)=(.+)$/', $target, $matches) === 1) {
            return "$matches[2]\" \"named";
        }
        return "$target";
    }
}
?>