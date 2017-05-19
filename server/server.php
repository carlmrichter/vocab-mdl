<?php

function getFileContent($id) {
    $directory = '../training/';
    $files = scandir($directory);

    // open file represented by id
    $file = fopen($directory.$files[$id + 2], 'r');

    // build return array
    $return = array();

    $return['content'] = array();
    for($i = 0, $count = 0; !feof($file); $i++, $count++) {
        $line = explode(';', trim(preg_replace('/\s+/', ' ', fgetss($file))), 2);
        if($line[0] == '\n' || $line[0] == ''){
            $count--;
            continue;
        }
        $return['content'][$i] = array();
        $return['content'][$i][0] = $line[0];
        $return['content'][$i][1] = $line[1];
    }
    $return['count'] = $count;
    fclose($file);
    //var_dump($return);

    return json_encode($return);
}

function setStat($id) {
    $dir_training = '../training/';
    if (!file_exists($dir_training)) {
        mkdir($dir_training);
    }
    $filename = scandir($dir_training)[$id + 2];
    $dir_stats = '../stats/';
    if (!file_exists($dir_stats)) {
        mkdir($dir_stats);
    }
    $stats = getStatsFilename();
    //$exist = file_exists($stats);

    $content = json_decode(file_get_contents($stats));
    //if (!$exist) chmod($stats, 0777);
    if (!isset($content)) $content = new stdClass();
    if (!isset($content->$filename)) $content->$filename = new stdClass();
    $content->$filename->answered++;
    if ($_POST['answer']) $content->$filename->correct++;
    $file = fopen($stats, 'w');
    fwrite($file, json_encode($content));
    fclose($file);
}

function getList() {
    $directory = '../training/';
    if (!file_exists($directory)) {
        mkdir($directory);
    }
    $dir_stats = '../stats/';
    if (!file_exists($dir_stats)) {
        mkdir($dir_stats);
    }
    $lang = new stdClass();
    if (file_exists('../stats/lang.json')) {
        $lang = json_decode(file_get_contents('../stats/lang.json'));
    }
    // filenames of directory
    $files = scandir($directory);
    $count = count($files);
    $return = array();
    // scandir() returns array with first elements: '.' and '..' --> $i = 2
    for ($i = 2; $i < $count; $i++) {
        $line_cnt = 0;
        $file = fopen($directory.$files[$i], 'r');
        // count lines
        for (; !feof($file); $line_cnt++) {
            $line = explode(';', trim(preg_replace('/\s+/', ' ', fgets($file))), 2);
            if ($line[0] === '\n' || $line[0] === '' || $line[1] === '\n' || $line[1] === '') {
                $line_cnt--;
            }
        }
        fclose($file);
        // get rid of filename extension
        $filename_no_ext = explode('.', $files[$i]);
        // get languages

        //build answer array
        $return[$i - 2] = array();
        $return[$i - 2]['name'] = $filename_no_ext[0];
        $return[$i - 2]['ext'] = $filename_no_ext[1];
        $return[$i - 2]['line_cnt'] = $line_cnt;
        $tmp = $files[$i];
        $return[$i - 2]['lang1'] = $lang->$tmp->lang1;
        $return[$i - 2]['lang2'] = $lang->$tmp->lang2;
    }
    // return answer array in json format
    return json_encode($return);
}

function deleteStat($file, $stats) {
    $content = json_decode(file_get_contents($stats));
    $content_array = (array)$content;

    if (count($content_array) < 2 || $file === "all") {
        unlink($stats);
        return json_encode(NULL);
    }
    unset($content->$file);
    file_put_contents($stats, json_encode($content));

    return json_encode($content);
}

function getStatsFilename() {
    //$filename = "";
    if (!isset($_COOKIE['id'])) {
        // cookie is valid for 365 days
        $time = time();
        $nextYear = $time + (365 * 86400);
        // get random file name out of timestamp and connected ip
        $filename = $time . "-" . str_replace(':','',$_SERVER['REMOTE_ADDR']);
        // set cookie to identify client
        setcookie('id', $filename, $nextYear, "/");
    }
    else {
        $filename = $_COOKIE['id'];
    }
    return '../stats/'.$filename.'.json';
    //return '../stats/'.str_replace(':','',$_SERVER['REMOTE_ADDR']).'.json';
}

if (isset($_POST['mode'])) {

    switch ($_POST['mode']) {
        case 'get_file_content':
            if (isset($_POST['id'])) {
                echo getFileContent($_POST['id']);
            }
            break;

        case 'set_stat':
            $id = $_POST['id'];
            if (isset($_POST['id'])) {
                setStat($_POST['id']);
            }
            break;


        case 'get_stats':
            $dir = '../training/';
            $dir_stats = '../stats/';
            if (!file_exists($dir)) {
                mkdir($dir);
            }
            if (!file_exists($dir_stats)) {
                mkdir($dir_stats);
            }

            $stats = new stdClass();
            $lang = new stdClass();
            $statsFile = getStatsFilename();
            if (file_exists($statsFile)) {
                $stats = json_decode(file_get_contents($statsFile));
            }
            if (file_exists('../stats/lang.json')) {
                $lang = json_decode(file_get_contents('../stats/lang.json'));
            }

            $return = array();
            //var_dump($lang);
            //var_dump($stats);
            $stats = (array)$stats;
            //var_dump($stats);
            $count = count($stats);
            //var_dump($count);
            $files = array_keys($stats);
            //var_dump($files);
            $order = scandir($dir);
            $file_count = count($order);
            $files_ordered = array();
            // order files array like files are stored in file system
            for ($j = 2, $index = 0; $j < $file_count; $j++) {
                for ($k = 0; $k < $count; $k++) {
                    if ($order[$j] === $files[$k]) {
                        $files_ordered[$index] = $files[$k];
                        $index++;
                    }
                }
            }
            $files = $files_ordered;
            //var_dump($files);
            for ($i = 0; $i < $count; $i++) {
                $tmp = $files[$i];
                $return[$i] = array();
                $return[$i]['filename'] = $tmp;
                if (isset($stats[$tmp])) {
                    $return[$i]['stats'] = $stats[$tmp];
                }
                if (isset($lang->$tmp)) {
                    $return[$i]['lang'] = $lang->$tmp;
                }
            }
            //var_dump($return);
            echo json_encode($return);
            break;


        case 'get_list':
            echo getList();
            break;


        case 'delete_stat':
            if (isset($_POST['file'])) {
                $stats = getStatsFilename();
                echo deleteStat($_POST['file'], $stats);
            }
            break;


        case 'delete_file':
            // TODO delete file physically
            $id = $_POST['id'];
            $files = scandir('../training');
            $file = '../training/'.$files[$id + 2];
            unlink($file);

            // TODO delete stats for that lesson
            $stats = getStatsFilename();
            deleteStat($id,$stats);

            // TODO rearrange ids in stats file (they will get messed up)
//            $content_read = json_decode(file_get_contents($stats));
//            $content_write = array();
//            $difference = 0;
//            foreach ($content_read as $key => $value) {
//                if ($key == $id) $difference--;
//                $key2 = $ke
//                $content_write->($key-$difference)
//
//            }
            break;

        default:
            break;
    }
}
?>