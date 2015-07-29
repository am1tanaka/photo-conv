<?php

define('PROC_START', 0);
define('PROC_UP', 1);
define('PROC_DL', 2);
define('PROC_DONE', 3);
define('PROC_ABORT', 4);

/** セッションの維持時間*/
define('SESSION_LIFETIME', 5*60);
define('TEMP_PHOTOCONV', '/photoconv_temp');
/** 作業フォルダーの有効秒数*/
define('TEMP_LIFETIME', 10*60);

require_once "CAm1Imagick.php";
require_once "CPel.php";

class CPhotoConvProc {
  private $proc = null;
  private $isTest = false;
  private $response = ["result" => "ok"];
  private $sess = null;

  function __construct($test=false) {
    $this->isTest = $test;
  }

  function __destruct() {
    if ($this->sess != null) {
      $this->removeTempFolder();
    }
  }

  // コンバートを実行する
  public function procConv() {
    // 処理を判定
    $this->detectProc();

    // 戻り値を返す
    echo json_encode($this->response, JSON_FORCE_OBJECT);
  }

  /**
   * 渡されたファイルを全て処理して、tempfolderに入れる
   */
  private function procAll() {
    // データを開始して、テンポラリを得る
    $this->procStart();

    // 処理開始
    $num = count($_FILES['input_file']['name']);
    for ($i=0 ; $i<$num ; $i++) {
      // 保存先ファイル名
      $dest = $_SESSION['tempfolder']."/".$_FILES['input_file']['name'][$i];
      $imagick = new CAm1Imagick($_FILES['input_file']['tmp_name'][$i]);

      if ($_SESSION['isresize'] == 'on') {
        $imagick->resize($_SESSION['width']-0, $_SESSION['height']-0);
      }
      $geo = $imagick->getGeometry();

      $imagick->writeFile($dest);
      $imagick->clear();

      // EXIF調整
      $pel = new CPel($dest);
      //// サイズ書き込み
      $pel->setSize($geo['width'],$geo['height']);
      //// 日時
      if ($_SESSION['filetime'] == 'on') {
        // ファイル名が時間を表しているか確認
        if (preg_match('/^\d{5,6}\./', $_FILES['input_file']['name'][$i])) {
          $dt = $_SESSION['filedate'];
          // 日付が指定されているか
          if (strlen($dt) < 4) {
            $dt = "".preg_split("/ /", $pel->getDateTime())[0];
            if (!$dt) {
              $this->response = array(
                "result" => "error",
                "message" => "[".$_FILES['input_file']['name'][$i]."]に撮影時間を追加するには、日付を指定してください。"
              );
              http_response_code(500);
              return;
            }
          }
          // :を-に変換
          $dt = preg_replace("/:/", "-", $dt);

          $tm = preg_split("/\./", $_FILES['input_file']['name'][$i]);
          $datetime = $dt." ".$tm[0];
          $pel->setDateTime($datetime);
        }
      }

      // 時間調整
      if ($_SESSION['addsecond'] != 0) {
        $now = (new DateTime($pel->getDateTime()))->getTimestamp()+$_SESSION['addsecond'];
        $newdt = (new DateTime())->setTimestamp($now);
        $pel->setDateTime($newdt->format('c'));
      }

      //// 保存
      $pel->saveFile($dest);
    }

    // フォルダーを圧縮して返す
    $this->downloadZip();
  }

  function downloadZip() {
    // 圧縮
    $zipname = $this->zip();
    if (!$zipname) {
      return;
    }

    // 圧縮したzipを読み込み
    $handle = fopen($zipname, "r");
    $file_size = filesize($zipname);
    $zipdata = fread($handle, $file_size);
    fclose($handle);

    // zipファイルを削除
    unlink($zipname);

    // 実際の稼動時の処理
    if (!$this->isTest) {
      // 作業フォルダーを削除する
      $this->removeTempFolder();

      // zipをダウンロード
      header("Content-Disposition: attachment; filename=photos.zip");
      header("Content-Length: $file_size");
      header("Content-Type: application/octet-stream");
      header("Connection: close");
      ob_end_clean();
      echo $zipdata;
      exit();
    }
    else {
      echo "this is test.\n";
      echo "zip file size=$file_size\n";
    }
  }

  /**
   * 作業フォルダーを削除する
   */
  public function removeTempFolder() {
    // ファイルを追加
    if (is_dir($this->sess['tempfolder']))
    {
      if ($dh = opendir($this->sess['tempfolder'])) {
        while (($file=readdir($dh))!== FALSE) {
          $datafile = $this->sess['tempfolder']."/".$file;
          // ファイルで、拡張子がjpgの時、アーカイブに追加
          if (is_file($datafile))
          {
            unlink($datafile);
          }
        }
      }
    }

    // 作業フォルダーを削除する
    rmdir($this->sess['tempfolder']);
  }

  /**
   * 返還後のフォルダーを圧縮。圧縮ファイルのパスを返す
   * 圧縮したファイルは消す
   * @return {string} $fname zipファイル名。失敗時はFALSE
   */
  function zip()
  {
    $zip = new ZipArchive();
    $fname = sys_get_temp_dir()."/am1-photoconv".$this->makeRandStr(4).".zip";
    if ($zip->open($fname, ZipArchive::CREATE) !== TRUE) {
      $this->response = array("result" => "error", "message" => "Create Zip Error.");
      return FALSE;
    }
    // ファイルを追加
    if (is_dir($_SESSION['tempfolder']))
    {
      if ($dh = opendir($_SESSION['tempfolder'])) {
        while (($file=readdir($dh))!== FALSE) {
          $datafile = $_SESSION['tempfolder']."/".$file;
          // ファイルで、拡張子がjpgの時、アーカイブに追加
          if (  is_file($datafile)
          &&  (preg_match("/\.jpg$/i", $datafile)))
          {
            $zip->addFile($datafile, $file);
          }
        }
      }
    }
    $zip->close();

    return $fname;
  }

  /**
   * ランダム文字列生成 (英数字)
   * $length: 生成する文字数
   */
  function makeRandStr($length = 8) {
      static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
      $str = '';
      for ($i = 0; $i < $length; ++$i) {
          $str .= $chars[mt_rand(0, 61)];
      }
      return $str;
  }

  /**
   * 開始
   */
  private function procStart() {
    if (!$this->isTest) {
      session_set_cookie_params(SESSION_LIFETIME);
      session_start();
    }

    date_default_timezone_set("GMT");

    // 作業フォルダーの作成
    $limit = (new DateTime())->getTimestamp()+TEMP_LIFETIME;
    $tmpfolder = dirname(__FILE__).TEMP_PHOTOCONV."/".$limit."_".$this->makeRandStr(3);
    if (!mkdir($tmpfolder, 0700, true)) {
      // 失敗したらエラー
      http_response_code(500);
      $this->response = array(
        "result" => "error",
        "message" => "サーバーエラーが発生しました(1)."
      );
      return;
    }

    // パラメータを受け取る
    $_SESSION['isresize'] = "";
    if ($_POST['check_resize']) {
      $_SESSION['isresize'] = $_POST['check_resize']-0;
    }
    $_SESSION['width'] = $_POST['input_width']-0;
    $_SESSION['height'] = $_POST['input_height']-0;
    $_SESSION['filetime'] = $_POST['check_filetime'];
    if ($_POST['text_filedate']) {
      $_SESSION['filedate'] = (new DateTime($_POST['text_filedate']))->format("Y:m:d");
    }
    else {
      $_SESSION['filedate'] = "";
    }
    $_SESSION['addsecond'] = 0;
    if (isset($_POST['text_addsecond'])) {
      $_SESSION['addsecond'] = $_POST['text_addsecond']-0;
    }
    /*
    $_SESSION['filenum'] = $_POST['file_count'];
    $_SESSION['filecount'] = 0;
    */
    $_SESSION['tempfolder'] = $tmpfolder;

    // セッションを記録
    $this->sess = $_SESSION;
  }

  private function procUpload() {

  }

  private function procDownload() {

  }

  private function procDone() {

  }

  private function procAbort() {

  }

  /**
   * 受け取り値から処理を決める
   */
  private function detectProc() {
    switch ($_POST['cmd']) {
      case 'start':
        $this->procStart();
        break;
      case 'up':
        $this->procUpload();
        break;
      case 'dl':
        $this->procDownload();
        break;
      case 'done':
        $this->procDone();
        break;
      case 'abort':
        $this->procAbort();
        break;
      case 'all':
        $this->procAll();
        break;
    }

  }


  public static function printFormData() {
    // フォームの表示
    print_r($_POST);

    // ファイルの表示
    print_r($_FILES);
  }

  function disp() {
    print_r($_FILES);
    readfile($_FILES['input_file']['tmp_name'][0]);
  }
}

 ?>
