<?php

class CPhotoConvProc {
  // コンバートを実行する
  public static function doConv() {

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
