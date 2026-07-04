<?php
// error_reporting(0);

$op         = isset($_REQUEST['op']) ? $_REQUEST['op'] : "";
$use_ssh    = isset($_REQUEST['ssh_id']) ? 1 : 0;
$url        = isset($_POST['url']) ? $_POST['url'] : "";
$path       = isset($_POST['path']) ? $_POST['path'] : "";
$xoops_lib  = isset($_POST['xoops_lib']) ? $_POST['xoops_lib'] : "";
$xoops_data = isset($_POST['xoops_data']) ? $_POST['xoops_data'] : "";
$home       = isset($_POST['home']) ? $_POST['home'] : "";
$trust_path = isset($_POST['trust_path']) ? $_POST['trust_path'] : "";

switch ($op) {
    case "install_xoops":
        install_xoops($use_ssh, $home, $trust_path, $xoops_lib, $xoops_data);
        break;

    default:
        install_setup();
        break;
}

function get_xoops_path()
{

    $path = dirname($_SERVER['SCRIPT_FILENAME']) . "/";
    $path = str_replace('\\', '/', $path);
    if (substr($path, -1) == '/') {
        $path = substr($path, 0, -1);
    }
    $xoops_path['path'] = $path;
    $url                = str_replace('install.php', '', 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI']);
    if (substr($url, -1) == '/') {
        $url = substr($url, 0, -1);
    }
    $xoops_path['url'] = $url;

    if (strpos($path, '/home') !== false) {
        $dir_arr = explode("/", $path);
        $home    = "{$dir_arr[0]}/{$dir_arr[1]}/{$dir_arr[2]}/{$dir_arr[3]}/";
    } else {
        $home = str_replace('\\', '/', $_SERVER["DOCUMENT_ROOT"]);
    }
    $trust_path               = dirname($home);
    $xoops_path['home']       = $home;
    $xoops_path['trust_path'] = $trust_path;

    $www_dir = substr($path, strlen("{$home}"));
    if (substr($www_dir, 0, 1) == '/') {
        $www_dir = substr($www_dir, 1);
    }

    $subdir = str_replace('/', '_', $www_dir);
    if (empty($subdir)) {
        $subdir = "xoops";
    }

    $xoops_path['subdir']     = $subdir;
    $xoops_path['xoops_lib']  = "{$trust_path}/{$subdir}_lib";
    $xoops_path['xoops_data'] = "{$trust_path}/{$subdir}_data";
    return $xoops_path;
}

function install_setup()
{

    $xoops_path = get_xoops_path();
    // die(var_dump($xoops_path));
    foreach ($xoops_path as $key => $value) {
        $$key = $value;
    }

    $is_writable            = is_writable($path);
    $is_trust_path_writable = is_writable($trust_path);

    $ssh_input = "";
    if (!$is_writable) {

        $_COOKIE['ssh_id']   = isset($_COOKIE['ssh_id']) ? $_COOKIE['ssh_id'] : "";
        $_COOKIE['ssh_pass'] = isset($_COOKIE['ssh_pass']) ? $_COOKIE['ssh_pass'] : "";
        $_COOKIE['ssh_port'] = isset($_COOKIE['ssh_port']) ? $_COOKIE['ssh_port'] : "22";

        $ssh_input = "
        <div class='alert alert-info'>
            <div class='form-group form-group-lg'>
                <label class='col-sm-2 control-label'>ssh 帳號：</label>
                <div class='col-sm-2'>
                <input type='text' name='ssh_id' value='{$_COOKIE['ssh_id']}' placeholder='ssh 帳號' class='form-control input-lg'>
                </div>
                <label class='col-sm-2 control-label'>ssh 密碼：</label>
                <div class='col-sm-3'>
                <input type='password' name='ssh_pass' value='{$_COOKIE['ssh_pass']}' placeholder='請輸入 ssh 密碼' class='form-control input-lg'>
                </div>
                <label class='col-sm-1 control-label'>port：</label>
                <div class='col-sm-2'>
                <input type='text' name='ssh_port' value='{$_COOKIE['ssh_port']}' placeholder='輸入 port' class='form-control input-lg'>
                </div>
            </div>
            請確定輸入的ssh帳號是否有存取 $trust_path 及 $path 目錄權限，否則建議用 root 身份來安裝。
        </div>
        ";
    }

    $phpversion = phpversion();
    $php_note   = "（<span class='text-success'>PHP > 5.6.0，可以安裝。</span>）";
    if (version_compare(phpversion(), '5.6.0', '<')) {
        $php_note = "（<span class='text-danger'>PHP 版本太舊，必須 5.6.0 以上才能安裝。</span>）";
    }

    $form = "
      <form action='{$_SERVER['PHP_SELF']}' method='post' class='form-horizontal' role='form'>
        <div class='form-group form-group-lg'>
          <label class='col-sm-4 control-label'>PHP版本</label>
          <div class='col-sm-8'>
            <p class=\"form-control-static\">{$phpversion}{$php_note}</p>
          </div>
        </div>
        <div class='form-group form-group-lg'>
          <label class='col-sm-4 control-label'>XOOPS_URL（網站網址）</label>
          <div class='col-sm-8'>
              <p class=\"form-control-static\">{$url}</p>
          </div>
        </div>
        <div class='form-group form-group-lg'>
          <label class='col-sm-4 control-label'>XOOPS_ROOT_PATH（安裝路徑）</label>
          <div class='col-sm-8'>
              <p class=\"form-control-static\">{$path}</p>
          </div>
        </div>


        <div class='alert alert-warning'>

            <div class='form-group form-group-lg'>
              <label class='col-sm-4 control-label'>網頁目錄為</label>
              <div class='col-sm-8'>
                <input type='text' name='home' value='{$home}' placeholder='網頁目錄' class='form-control input-lg'>
              </div>
            </div>
            <div class='form-group form-group-lg'>
              <label class='col-sm-4 control-label'>網頁目錄外（安全目錄）為</label>
              <div class='col-sm-8'>
                <input type='text' name='trust_path' value='{$trust_path}' placeholder='安全目錄' class='form-control input-lg'>
              </div>
            </div>
            <div class='form-group form-group-lg'>
              <label class='col-sm-4 control-label'>xoops_lib目錄（須在網頁目錄外）</label>
              <div class='col-sm-8'>
                  <input type='text' name='xoops_lib' value='{$xoops_lib}' placeholder='xoops_lib目錄（須在網頁目錄外）' class='form-control input-lg'>
              </div>
            </div>
            <div class='form-group form-group-lg'>
              <label class='col-sm-4 control-label'>xoops_data目錄（須在網頁目錄外）</label>
              <div class='col-sm-8'>
                  <input type='text' name='xoops_data' value='{$xoops_data}' placeholder='xoops_data目錄（須在網頁目錄外）' class='form-control input-lg'>
              </div>
            </div>
        </div>

        {$ssh_input}

        <div class='text-center'>
          <input type='hidden' name='op' value='install_xoops'>
          <button type='submit' class='btn btn-primary'>送出</button>
        </div>
      </form>
      ";

    $main = "
    <!DOCTYPE html>
    <html>
    <head>
    <title>XOOPS快速安裝</title>
    <meta http-equiv='content-type' content='text/html; charset=UTF-8' />
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <!-- Bootstrap -->
    <!-- Latest compiled and minified CSS -->
    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css' integrity='sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7' crossorigin='anonymous'>

    <!-- Optional theme -->
    <link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css' integrity='sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r' crossorigin='anonymous'>

    <!-- Latest compiled and minified JavaScript -->
    <script src='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js' integrity='sha384-0mSbJDEHialfmuBBQP6A4Qrprq5OVfW37PRR3j5ELqxss1yVqOtnepnHVP9aJ7xS' crossorigin='anonymous'></script>
    <style>
      body{
        font-size:1.5em;
        font-family: 微軟正黑體;
      }
    </style>
    </head>
    <body>
    <div class='row'>
      <div class='col-sm-2'></div>
      <div class='col-sm-8'>
        <h1 class='text-center text-info'>XOOPS 2.5.11 輕鬆架快速安裝</h1>
        <hr>
        $form
      </div>
      <div class='col-sm-2'></div>
    </div>

    </body>
    </html>
    ";

    die($main);
}

function install_xoops($use_ssh = 0, $home = "", $trust_path = "", $xoops_lib = "", $xoops_data = "")
{
    if ($use_ssh != 0) {
        if (!empty($_POST['ssh_id'])) {
            setcookie("ssh_id", $_POST['ssh_id']);
        }
        if (!empty($_POST['ssh_pass'])) {
            setcookie("ssh_pass", $_POST['ssh_pass']);
        }
        if (!empty($_POST['ssh_port'])) {
            setcookie("ssh_port", $_POST['ssh_port']);
        } else {
            $_POST['ssh_port'] = 22;
        }

        // set_include_path(get_include_path() . PATH_SEPARATOR . 'phpseclib');

        include 'vendor/autoload.php';
        $ssh = new \phpseclib3\Net\SSH2('127.0.0.1', $_POST['ssh_port']);

        if ($ssh->login($_POST['ssh_id'], $_POST['ssh_pass'])) {
            // die('ssh_install');
            ssh_install($ssh, $home, $trust_path, $xoops_lib, $xoops_data);
        } else {
            // die('php_install');
            php_install($home, $trust_path, $xoops_lib, $xoops_data);
        }

    } else {
        php_install();
    }

    $xoops_url = str_replace('install.php', 'index.php', 'http://' . $_SERVER["HTTP_HOST"] . $_SERVER['SCRIPT_NAME']);

    header("location: {$xoops_url}");
    exit;
}

function ssh_install($ssh, $home = "", $trust_path = "", $xoops_lib = "", $xoops_data = "")
{
    // error_reporting(-1);
    $xoops_path = get_xoops_path();
    foreach ($xoops_path as $key => $value) {
        if (empty($$key)) {
            $$key = $value;
        }
    }

    $ssh->exec("chmod -R 755 {$path}");
    if (!file_exists("{$path}/my_xoops2511.zip")) {
        $ssh->exec("cd {$path}\nwget https:\/\/campus-xoops.tn.edu.tw\/uploads\/my_xoops2511.zip");
    }

    // $ssh->exec("unzip -o {$path}/my_xoops2511.zip -d {$home}");
    $ssh->exec("unzip my_xoops2511.zip");

    if (!file_exists("{$path}/xoops.css")) {
        $ssh->exec("chmod 777 {$path}");
        $zip = new ZipArchive;
        if ($zip->open('my_xoops2511.zip') === true) {
            $zip->extractTo($path);
            $zip->close();
        } else {
            die("解壓縮 {$path}/my_xoops2511.zip 至 {$path}/ 失敗");
        }
    }

    if (file_exists("{$path}/xoops.css")) {
        $ssh->exec("chmod -R 755 {$path}");
        $ssh->exec("chmod -R 777 {$path}/xoops_data");
        $ssh->exec("chmod -R 777 {$path}/uploads");
        $ssh->exec("chmod 777 {$path}/mainfile.php");
        $ssh->exec("chmod 777 {$path}/include/license.php");
        $ssh->exec("chmod -R 777 {$path}/themes/default/modules");
        $ssh->exec("chmod -R 777 {$path}/themes/school2022/modules");

        if ($subdir != 'xoops') {
            $ssh->exec("sed -i 's/xx_/{$subdir}_/g' {$path}/xoops_data/data/xoops.sql");
            $ssh->exec("sed -i 's/xx/{$subdir}/g' {$path}/xoops_data/data/secure.php");
        }

        $path       = str_replace('/', '\/', $path);
        $xoops_lib  = str_replace('/', '\/', $xoops_lib);
        $xoops_data = str_replace('/', '\/', $xoops_data);

        $ssh->exec("sed -i 's/xoops_root_path/{$path}/g' {$path}/mainfile.php");
        $ssh->exec("sed -i 's/xoops_lib_path/{$xoops_lib}/g' {$path}/mainfile.php");
        $ssh->exec("sed -i 's/xoops_data_path/{$xoops_data}/g' {$path}/mainfile.php");

        $ssh->exec("mv {$path}/xoops_data {$xoops_data}");
        $ssh->exec("mv {$path}/xoops_lib {$xoops_lib}");

        $ssh->exec("chmod -R 444 {$path}/mainfile.php");

        $ssh->exec("chown -R {$_COOKIE['ssh_id']}:{$_COOKIE['ssh_id']} {$path}");
        $ssh->exec("chown -R {$_COOKIE['ssh_id']}:{$_COOKIE['ssh_id']} {$xoops_lib}");
        $ssh->exec("chown -R {$_COOKIE['ssh_id']}:{$_COOKIE['ssh_id']} {$xoops_data}");

        $ssh->exec("rm -Rf {$path}/vendor");
        $ssh->exec("rm {$path}/install.php");
        $ssh->exec("rm -f {$path}/my_xoops2511.zip");
        $ssh->exec("rm {$path}/index.html");
        $ssh->exec("rm {$path}/composer.json");
        $ssh->exec("rm {$path}/composer.lock");
    } else {
        die("解壓縮 {$path}/my_xoops2511.zip 至 {$path}/ 失敗");
    }

}

function php_install($home = "", $trust_path = "", $xoops_lib = "", $xoops_data = "")
{
    $xoops_path = get_xoops_path();

    foreach ($xoops_path as $key => $value) {
        if (empty($$key)) {
            $$key = $value;
        }
    }

    if (!get_my_xoops($path)) {
        die("無法下載");
    }

    $ssh->exec("chmod 777 {$path}");
    $zip = new ZipArchive;
    if ($zip->open('my_xoops2511.zip') === true) {
        $zip->extractTo($path);
        $zip->close();
    } else {
        die("解壓縮 {$path}/my_xoops2511.zip 至 {$path}/ 失敗");
    }

    // exec("unzip.exe {$path}/my_xoops2511.zip");

    delete_directory("{$path}/phpseclib");
    unlink("{$path}/my_xoops2511.zip");

    chmod_R("{$path}", 0644, 0755);
    chmod_R("{$path}/xoops_data", 0777, 0777);
    chmod_R("{$path}/uploads", 0777, 0777);
    chmod_R("{$path}/mainfile.php", 0777, 0777);
    chmod_R("{$path}/themes/school2019/modules", 0777, 0777);

    if ($subdir != 'xoops') {
        php_sed('xx_', "{$subdir}_", "{$path}/xoops_data/data/xoops.sql");
        php_sed('xx', "{$subdir}", "{$path}/xoops_data/data/secure.php");
    }

    php_sed('xoops_root_path', "{$path}", "{$path}/mainfile.php");
    php_sed('xoops_lib_path', "{$xoops_lib}", "{$path}/mainfile.php");
    php_sed('xoops_data_path', "{$xoops_data}", "{$path}/mainfile.php");

    move_dir("{$path}/xoops_data", $xoops_data);
    move_dir("{$path}/xoops_lib", $xoops_lib);

    chmod_R("{$path}/mainfile.php", 0444, 0444);
    unlink("{$path}/install.php");
    unlink("{$path}/index.html");
}

//取得輕鬆架
function get_my_xoops($path)
{
    if (file_exists("{$path}/my_xoops2511.zip")) {
        return true;
    }

    $url = "https://campus-xoops.tn.edu.tw/uploads/my_xoops2511.zip";
    if (function_exists('curl_init')) {
        $ch      = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
    } elseif (function_exists('file_get_contents')) {
        $data = file_get_contents($url);
    } else {
        $handle = fopen($url, "rb");
        $data   = stream_get_contents($handle);
        fclose($handle);
    }

    if (file_exists("{$path}/my_xoops2511.zip")) {
        return false;
    }
    $openedfile = fopen("{$path}/my_xoops2511.zip", "w");
    fwrite($openedfile, $data);
    fclose($openedfile);
    if ($data === false) {
        $status = false;
    } else {
        $status = true;
    }
    return $status;
}

function move_dir($old_dir, $new_dir)
{
    mk_dir($new_dir);
    recurse_copy($old_dir, $new_dir);

}

function mk_dir($dir = "")
{
    //若無目錄名稱秀出警告訊息
    if (empty($dir)) {
        redirect_header("index.php", 3, _TAD_NO_DIRNAME);
    }

    //若目錄不存在的話建立目錄
    if (!is_dir($dir)) {
        umask(000);
        //若建立失敗秀出警告訊息
        if (!mkdir($dir, 0777)) {
            redirect_header("index.php", 3, sprintf(_TAD_MKDIR_ERROR, $dir));
        }
    }
}

function chmod_R($path, $filemode, $dirmode)
{

    if (is_dir($path)) {
        if (!chmod($path, $dirmode)) {
            $dirmode_str = decoct($dirmode);
            // print "chmod -R $dirmode_str $path \n";
            return false;
        }
        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            if ($file != '.' && $file != '..') {
                // skip self and parent pointing directories
                $fullpath = $path . '/' . $file;
                chmod_R($fullpath, $filemode, $dirmode);
            }
        }
        closedir($dh);
    } else {
        if (is_link($path)) {
            // print "link '$path' is skipped\n";
            return;
        }
        if (!chmod($path, $filemode)) {
            $filemode_str = decoct($filemode);
            // print "Failed applying filemode '$filemode_str' on file '$path'\n";
            return false;
        }
    }
}

function delete_directory($dirname)
{
    if (is_dir($dirname)) {
        $dir_handle = opendir($dirname);
    }

    if (!$dir_handle) {
        return false;
    }

    while ($file = readdir($dir_handle)) {
        if ($file != "." && $file != "..") {
            if (!is_dir($dirname . "/" . $file)) {
                unlink($dirname . "/" . $file);
            } else {
                delete_directory($dirname . '/' . $file);
            }

        }
    }
    closedir($dir_handle);
    rmdir($dirname);
    return true;
}

function php_sed($search, $replace, $file)
{
    $content = file_get_contents($file);
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
}

function recurse_copy($src, $dst)
{
    $dir = opendir($src);
    mk_dir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}
