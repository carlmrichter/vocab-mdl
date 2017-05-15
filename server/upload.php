<?php
function isHtml($string) {
    return ($string != strip_tags($string)) ? true : false;
}

function endsWith($string, $end) {
    $length = strlen($end);
    if ($length == 0) {
        return true;
    }
    return (substr($string, -$length) === $end);
}

$data = $_POST['data'];
$filename = $_POST['name'];
$return = array();
if (!file_exists('../training/')) {
    mkdir('../training/');
}
$files = scandir('../training/');

// check if file already exists
foreach ($files as $f) {
    if ($f == $filename) {
        $return['success'] = false;
        $return['message'] = 'error_file_exists';
        echo json_encode($return);
        return;
    }
}
// check if file is a .txt or .csv file
if (endsWith($filename, '.csv') || endsWith($filename, '.txt')) {

    // check if file is empty
        if ($data != '') {

        // check if filename contains html or php
        if (!isHtml($filename)) {

            // check if opening file was successful
            if ($file = fopen('../training/'.$filename, 'w')){

                // check if writing file was successful
                if (fwrite($file, $data)) {
                    $return['success'] = true;
                }
                else {
                    $return['success'] = false;
                    $return['message'] = 'error_write';
                }
                fclose($file);
            }
            else {
                $return['success'] = false;
                $return['message'] = 'error_open';
            }
        }
        else {
            $return['success'] = false;
            $return['message'] = 'error_html';
        }
    }
    else {
        $return['success'] = false;
        $return['message'] = 'error_empty_file';
    }
}
else {
    $return['success'] = false;
    $return['message'] = 'error_filetype';
}

echo json_encode($return);