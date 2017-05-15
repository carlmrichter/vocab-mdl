<?php
function getFileContent($id, $only_first_line) {
    $directory = '../training/';
    $files = scandir($directory);

    // open file represented by id
    $file = fopen($directory.$files[$id + 2], 'r');

    // get both language names out of first line of file
    $languages = explode(';', trim(preg_replace('/\s+/', ' ', fgetss($file))), 2);

    if ($only_first_line) {
        fclose($file);
        return $languages;
    }

    // build return array
    $return = array();
    $return['lang1'] = $languages[0];
    $return['lang2'] = $languages[1];


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
    return json_encode($return);
}

function deleteStat($id, $file) {
    $content = json_decode(file_get_contents($file));
    $content_array = (array)$content;
    $return['ids'] = array();
    if (count($content_array) < 2 || $id == -1) {
        unlink($file);
        $return['ids'][0] = -1;
        $return['ids'] = array_merge($return['ids'], array_keys($content_array));
        return json_encode($return);
    }
    unset($content->$id);
    file_put_contents($file, json_encode($content));
    $return['ids'][0] = $id;
    $return['content'] = $content;
    return json_encode($return);
}

function getStatsFilename() {
    return '../stats/'.str_replace(':','',$_SERVER['REMOTE_ADDR']).'.json';
}

if (isset($_POST['mode'])) {

    switch ($_POST['mode']) {
        case 'id':
            if (isset($_POST['id'])) {
                echo getFileContent($_POST['id'], false);
            }
            break;


        case 'stat':
            $id = $_POST['id'];
            $filenames = scandir('../training/');
            if (!file_exists('../stats/')) {
                mkdir('../stats/');
            }
            $stats = getStatsFilename();
            $exist = file_exists($stats);

            $filename = explode('.', $filenames[$id + 2])[0];
            $content = json_decode(file_get_contents($stats));
            //if (!$exist) chmod($stats, 0777);
            $content->$id->filename = $filename;
            if (!isset($content->$id->lang1) && !isset($content->$id->lang2)) {
                $lang = getFileContent($id, true);
                $content->$id->lang1 = $lang[0];
                $content->$id->lang2 = $lang[1];
            }
            $content->$id->answered++;
            if ($_POST['answer']) $content->$id->correct++;
            $file = fopen($stats, 'w');
            fwrite($file, json_encode($content));
            fclose($file);
            //echo print_r($content);
            break;


        case 'get_stats':
            echo file_get_contents(getStatsFilename());
            break;


        case 'list':
            $directory = '../training/';
            // filenames of directory
            $files = scandir($directory);
            $count = count($files);
            $return = array();
            // scandir() returns array with first elements: '.' and '..' --> $i = 2
            for ($i = 2; $i < $count; $i++) {
                $line_cnt = 0;
                $file = fopen($directory.$files[$i], 'r');
                // get languages from first line of .csv
                $lang = fgetss($file);
                $lang = trim(preg_replace('/\s+/', ' ', $lang));
                // count lines
                for ($line_cnt = 0; !feof($file); $line_cnt++) {
                    $line = explode(';', trim(preg_replace('/\s+/', ' ', fgets($file))), 2);
                    if ($line[0] == '\n' || $line[0] == '') {
                        $line_cnt--;
                    }
                }
                fclose($file);
                // get rid of filename extension
                $filename_no_ext = explode('.', $files[$i]);
                //build answer array
                $return[$i - 2] = array();
                $return[$i - 2]['name'] = str_replace(';', ' - ', $lang) . ': ' . $filename_no_ext[0];
                //$return[$i-2]['lang1'] = $languages[0];
                //$return[$i-2]['lang2'] = $languages[1];
                $return[$i - 2]['line_cnt'] = $line_cnt;
                $return[$i - 2]['ext'] = $filename_no_ext[1];
            }
            // submit answer array in json format
            echo json_encode($return);
            break;


        case 'delete_stat':
            $id = $_POST['id'] - 1;
            $stats = getStatsFilename();
            echo deleteStat($id, $stats);
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