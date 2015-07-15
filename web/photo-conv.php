#!/usr/bin/php
<?php
/**
 * 写真の一括縮小とEXIF書き込みツール
 * @copyright YuTanaka@AmuseOne 2015
 * @license MIT
 */

require_once "CAm1Imagick.php";
require_once "CPel.php";

$prog = array_shift($argv);
$error = false;

// help
if (empty($argv)) {
  println('Usage: %s -w <width> -h <height> [-e <date>] -i <input_path> -o <output_path>', $prog);
  println(' Optional arguments:');
  println('  -w : 幅の上限');
  println('  -h : 高さの上限');
  println('  -e : EXIFの日時を書き換える。日付を 西暦/月/日 形式(2015/7/15など)で指定');
  println('  -i : 入力パスを指定。指定したフォルダー下の全ての画像を変換する');
  println('  -o : 出力パスを指定。変換した画像を指定のフォルダー下に出力する。同名ファイルは上書き');
}

// ファイルの表示
foreach($_FILES['input_file']['error'] as $key => $error) {
    if ($error == 0) {
      $tmp_name = $_FILES['input_file']['tmp_name'][$key];
      $name = $_FILES['input_file']['name'][$key];
      $type = $_FILES['input_file']['type'][$key];
      $size = $_FILES['input_file']['size'][$key];
      echo "FILES[tmp_name][$key]=".$tmp_name."<br/>";
      echo "FILES[name][$key]=".$name."<br/>";
      echo "FILES[type][$key]=".$type."<br/>";
      echo "FILES[size][$key]=".$size."<br/>";
    }
    else {
      echo "error:".$error."<br/>";
    }
}

phpinfo();

 ?>
