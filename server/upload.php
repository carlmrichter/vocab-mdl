<?php
require_once("lib/detectlanguage.php");
$dir = '../training/';

use \DetectLanguage\DetectLanguage;
//DetectLanguage::setApiKey("47e945b68f0d6f3e717e8a8bdb58c707");
DetectLanguage::setApiKey("demo");

function isHtml($string) {
    return ($string != strip_tags($string)) ? true : false;
}

function endsWith($string, $end) {
    $str = $string;
    $length = strlen($end);
    if ($length == 0) {
        return true;
    }
    return (substr($str, -$length) === $end);
}

function getFullName($code) {
    $file = fopen('languages.csv', 'r');
    $full = 'Unknown';
    while(!feof($file)) {
        $line = explode(',', trim(preg_replace('/\s+/', ' ', fgetss($file))), 2);
        if ($line[0] === $code) {
            $full = $line[1];
            break;
        }
    }
    fclose($file);
    return $full;
}


$data = $_POST['data'];
$filename = $_POST['name'];
$return = array();
$file = null;
if (!file_exists($dir)) {
    mkdir($dir);
}
$files = scandir($dir);
$return['success'] = false;

// check if file already exists
foreach ($files as $f) {
    if ($f == $filename) {
        $return['message'] = 'error_file_exists';
        echo json_encode($return);
        return;
    }
}
// check if file is not a .txt or .csv file
if (!(endsWith($filename, '.csv')) and !(endsWith($filename, '.txt'))) {
    $return['message'] = 'error_filetype';
    echo json_encode($return);
    return;
}
// check if file is empty
if ($data === '') {
    $return['message'] = 'error_empty_file';
    echo json_encode($return);
    return;
}

// check if filename contains html or php
if (isHtml($filename)) {
    $return['message'] = 'error_html';
    echo json_encode($return);
    return;
}

// check if line count < 5
$line_count = count(preg_split('/\n/',$data));
if ($line_count < 5) {
    $return['message'] = 'error_line_count';
    echo json_encode($return);
    return;
}

// check if opening file was successful
if (!($file = fopen($dir.$filename, 'w'))) {
    $return['message'] = 'error_open';
    echo json_encode($return);
    return;
}

// check if writing file was successful
if (!(fwrite($file, $data))) {
    $return['message'] = 'error_write';
    fclose($file);
    echo json_encode($return);
    return;
}
else {
    $return['success'] = true;

    fclose($file);
    $file = fopen($dir.$filename, 'r');
    // create entry in lang file
    $query = array("","");
    for($i = 0; (($i < 10) && (!feof($file))); $i++) {
        $line = explode(';', trim(preg_replace('/\s+/', ' ', fgetss($file))), 2);
        $query[0] = $query[0].$line[0]." ";
        $query[1] = $query[1].$line[1]." ";
    }
    fclose($file);
    // detect both languages in file
    $results = DetectLanguage::detect($query);

    $langFile = '../stats/lang.json';
    if (!file_exists('../stats/')) {
        mkdir('../stats/');
    }
    $content = new stdClass();
    if (file_exists($langFile)) {
        $content = json_decode(file_get_contents($langFile));
    }

//    $file2 = fopen($langFile, 'w');
    if (!isset($content->$filename)) $content->$filename = new stdClass();
    $content->$filename->lang1 = getFullName($results[0][0]->language);
    $content->$filename->lang2 = getFullName($results[1][0]->language);
    file_put_contents($langFile, json_encode($content));
//    fwrite($file2, json_encode($content));
//    fclose($file2);
}

echo json_encode($return);