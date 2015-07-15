<?php

//set_include_path(get_include_path() . PATH_SEPARATOR . "/Users/yutanaka/git/photo-conv/web/plugins/pel");

require_once "web/plugins/pel/autoload.php";
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelTag;
use lsolesen\pel\PelIfd;
use lsolesen\pel\PelEntryShort;
use lsolesen\pel\PelEntryTime;

class CPel {
  private $insPel = null;
  private $exif = null;

  function __construct($fname) {
    $this->insPel = new PelJpeg($fname);
    $app1 = $this->insPel->getExif();
    if ($app1) {
      $tiff = $app1->getTiff();
      $ifd0 = $tiff->getIfd();
      if ($ifd0) {
        $this->exif = $ifd0->getSubIfd(PelIfd::EXIF);
      }
    }
  }

  /**
   * Exifの幅を返す。Exifがないときはfalseを返す
   */
  public function getWidth() {
    if ($this->exif) {
      return $this->exif->getEntry(PelTag::PIXEL_X_DIMENSION)->getValue();
    }
    else {
      return false;
    }
  }

  public function getHeight() {
    if ($this->exif) {
      return $this->exif->getEntry(PelTag::PIXEL_Y_DIMENSION)->getValue();
    }
    else {
      return false;
    }
  }

  public function getDateTime() {
    if ($this->exif) {
      return $this->exif->getEntry(PelTag::DATE_TIME_ORIGINAL)->getText();
    }
    else {
      return false;
    }
  }

  private function setSizeExif($id, $data) {
    $entry = $this->exif->getEntry($id);
    if ($entry){
      $entry->setValue($data);
    }
    else {
      $new = new PelEntryShort($id, $data);
      $this->addEntry($new);
    }
  }

  /**
   * EXIFのサイズを設定する
   * @param {number} $w 幅
   * @param {number} $h 高さ
   */
  public function setSize($w, $h) {
    $this->setSizeExif(PelTag::PIXEL_X_DIMENSION, $w);
    $this->setSizeExif(PelTag::PIXEL_Y_DIMENSION, $h);
  }

  /**
   * 指定の日時を撮影日時としてEXIFに設定する
   * @param {string} $datetime 設定する日時。西暦-月-日 時間:分:秒形式
   */
  function setDateTime($datetime) {
    // EXIFは標準時間を基準とするので、グリニッジ標準時を設定
    date_default_timezone_set("GMT");

    // 設定する日時を作成する
    $dttm = (new DateTime($datetime))->getTimestamp();
    $entrydt = $this->exif->getEntry(PelTag::DATE_TIME_ORIGINAL);
    if ($entrydt) {
      $entrydt->setValue($dttm);
    }
    else {
      $newdt = new PelEntryTime(PelTag::DATE_TIME_ORIGINAL, $dttm);
      $this->exif->addEntry($newdt);
    }
  }

  /**
   * 指定のファイル名でJPEG画像を保存する
   * @param {string} $fname ファイル名
   */
  public function saveFile($fname) {
    $this->insPel->saveFile($fname);
  }

}

 ?>
