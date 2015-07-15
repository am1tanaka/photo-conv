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

class CPhotoConvProc {
  private $proc = null;
  private $isTest = false;
  private $response = ["result" => "ok"];

  function __construct($test) {
    $this->isTest = $test;
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

      // サイズ調整
      $imagick = new CAm1Imagick($_FILES['input_file']['tmp_name'][$i]);
      $imagick->resize($_SESSION['width']-0, $_SESSION['height']-0);
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
            $dt = preg_split("/ /", $pel->getDateTime())[0];
          }
          // :を-に変換
          $dt = preg_replace("/:/", "-", $dt);

          $tm = preg_split("/\./", $_FILES['input_file']['name'][$i]);
          $datetime = $dt." ".$tm[0];
          $pel->setDateTime($datetime);
        }
        //// 保存
        $pel->saveFile($dest);
      }
    }
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
    if (!mkdir($tmpfolder, 0777, true)) {
      // 失敗したらエラー
      http_response_code(500);
      $this->response = array(
        "result" => "error",
        "message" => "サーバーエラーが発生しました(1)."
      );
    }

    // パラメータを受け取る
    $_SESSION['width'] = $_POST['input_width']-0;
    $_SESSION['height'] = $_POST['input_height']-0;
    $_SESSION['filetime'] = $_POST['check_filetime'];
    $_SESSION['filedate'] = (new DateTime($_POST['text_filedate']))->format("Y:m:d");
    $_SESSION['filenum'] = $_POST['file_count'];
    $_SESSION['filecount'] = 0;
    $_SESSION['tempfolder'] = $tmpfolder;
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
