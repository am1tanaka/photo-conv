<?php
/**
 * 処理専用のフォルダーを用意 TEMP_PHOTOCONV
 * 期限+セッションIDのフォルダーを作成して、そのフォルダーに変換データを保存
 * フォルダーの有効期限は10分
 * 次回処理の時に、時間切れのフォルダーを削除する
 *
 * 写真の一括縮小とEXIFの受付php
 * 開始:$_POST['cmd']=start
 * 変換パラメータをセッションに記録
 * セッションに保存先フォルダー名を記録
 *
 * 写真受け取り:$_POST['cmd']=up
 * 写真データのアップロードを受けて、処理する
 * 完了したらOK(200)を返す
 *
 * 中断:$_POST['cmd']=abort
 * 作業フォルダーを削除
 * セッションを削除
 *
 * ダウンロード：$_POST['cmd']=dl
 * 保存先フォルダーを圧縮して、ダウンロード
 *
 * ダウンロード完了:$_POST['cmd']=done
 * 作業フォルダーとセッション情報を削除
 *
 * @copyright YuTanaka@AmuseOne 2015
 * @license MIT
 */

require_once "photo-conv-proc.php";

(new CPhotoConvProc())->procConv();

 ?>
