<?php

    /////////////////////////////////////////////////////////////////////////////
    // Synkee Node
    // Author: Tihomir Piha (tpiha@naklikaj.com)
    // URL: www.synkee.com
    /////////////////////////////////////////////////////////////////////////////

    error_reporting(E_ALL);
    // error_reporting(0);
    ini_set('max_execution_time', 600);

    if (isset($_SERVER['HTTPS']))  {
        $proto = 'https';
    }
    else {
        $proto = 'http';
    }

    $project_url = "$proto://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $project_name = str_replace('https://', '', $project_url);
    $project_name = str_replace('http://', '', $project_name);
    $project_name = str_replace('/synkee.php', '', $project_name);
    $project_name = str_replace('/', '-', $project_name);

    $user_id = 106;
    $user_sid = "47694e29-7eda-4f56-9307-a6fd307e92bf";
    $project_sid = "c4885900-e8be-4b0e-9b2f-940c388e1702";
    $files_base = "http://deploy.synkee.com/";

    // list dirs recursively
    function ls($dir, $prefix = '', $show_dirs = false) {
        $dir = rtrim($dir, '\\/');
        $result = array();
        $h = opendir($dir);
        while (($f = readdir($h)) !== false) {
            if ($f !== '.' and $f !== '..') {
                if (is_dir("$dir/$f")) {
                    if ($show_dirs) {
                        $result[] = "$dir/$f";
                    }
                    $result = array_merge($result, ls("$dir/$f", "$prefix$f/", $show_dirs));
                } else if ($prefix.$f != 'synkee.php') {
                    $result[] = $prefix.$f;
                }
            }
        }
        closedir($h);
        return $result;
    }

    // get file for download
    function download() {
        global $project_sid;
        // get file if existing
        if (empty($_GET['file'])) {
            $file = False;
        }
        else {
            $file = $_GET['file'];
        }

        if (empty($_GET['psid'])) {
            $file = False;
        }
        else {
            $psid = $_GET['psid'];
            if ($psid != $project_sid) {
                $file = False;
            }
        }
        
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            readfile($file);
            exit;
        }
    }

    // send list of all files to the Synkee server
    function list_files() {
        global $project_url, $user_sid, $project_sid;
        $list = ls('./');
        $upload_url = 'https://www.synkee.com/app/download';
        $data = array(
            'project_url' => $project_url,
            'user_sid' => $user_sid,
            'project_sid' => $project_sid
        );
        foreach ($list as $path) {
            $data['files'][] = $path;
        }
        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ),
        );
        $context  = stream_context_create($options);
        $result = file_get_contents($upload_url, false, $context);
    }

    function deploy() {
        global $data, $user_id, $project_name, $files_base;
        foreach ($data['entries'] as $entry) {
            if ($entry[1]) {
                if ($entry[1]['is_dir']) {
                    $dir_name = update_path_root($entry[1]['path']);
                    mkdir($dir_name, $mode=0755, $recursive=true);
                }
                else if ($entry[1]['path'] != '/synkee.php') {
                    $file_url = $files_base . $data['prefix'] . "/" . $project_name . '/' . $entry[1]['path'];
                    $file_url = str_replace(' ', '%20', $file_url);
                    $file_name = update_path_root($entry[1]['path']);
                    if (!file_exists(dirname($file_name))) {
                        mkdir(dirname($file_name), $mode=0755, $recursive=true);
                    }
                    file_put_contents($file_name, download_file($file_url));
                }
                echo "new: " . (string) $entry[1]['path'] . "\n";
            }
            else if ($entry[0] != '/synkee.php') {
                $path = get_filesystem_path($entry[0]);
                delete($path);
                echo 'deleting: ' . $entry[0] . "\n";
            }
        }
    }

    function get_filesystem_path($entry) {
        $low_entry = strtolower($entry);
        $lsa = ls('./', '', true);
        foreach ($lsa as $fsentry) {
            $low_fsentry = strtolower($fsentry);
            $low_fsentry = str_replace('./', '', $low_fsentry);
            if ($low_fsentry == $low_entry) {
                return $fsentry = str_replace('./', '', $fsentry);;
            }
        }
        return false;
    }

    function update_path_root($entry) {
        $low_entry = strtolower($entry);
        $lsa = ls('./', '', true);
        $base = '';
        foreach ($lsa as $fsentry) {
            $low_fsentry = strtolower($fsentry);
            $low_fsentry = str_replace('./', '', $low_fsentry);
            $fsentry = str_replace('./', '', $fsentry);
            if (strpos($low_entry, $low_fsentry) === 0) {
                if (strlen($fsentry) > strlen($base)) {
                    $base = $fsentry;
                }
            }
        }
        if (strlen($base)) {
            return substr_replace($entry, $base, 0, strlen($base));
        }
        return $entry;
    }

    function delete($path) {
       if (is_dir($path)) {
            $objects = scandir($path);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($path . "/" . $object) == "dir") {
                        delete($path . "/" . $object); 
                    }
                    else {
                        if (file_exists($path . "/" . $object)) {
                            unlink($path . "/" . $object);
                        }
                    }
                }
            }
            reset($objects);
            if (file_exists($path)) {
                rmdir($path);
            }
        }
        else {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    function download_file($url) {
        $ch = curl_init();
        $timeout = 1800;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    $data = json_decode(file_get_contents('php://input'), true);

    // get action
    if ($data && $data['action'] == 'deploy') {
        $action = 'deploy';
    }
    else if (!empty($_GET['action'])) {
        $action = $_GET['action'];
    }
    else if (!empty($_POST['action'])) {
        $action = $_POST['action'];
    }
    else {
        $action = 'index';
    }

    // do something according to the action
    switch ($action) {
        case 'download':
            download();
            break;

        case 'deploy':
            deploy();
            break;
        
        default:
            list_files();
            break;
    }

    echo $action;
?>