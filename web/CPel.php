<?php

//set_include_path(get_include_path() . PATH_SEPARATOR . "/Users/yutanaka/git/photo-conv/web/plugins/pel");

require_once "plugins/pel/autoload.php";
use lsolesen\pel\PelExif;
use lsolesen\pel\PelJpeg;
use lsolesen\pel\PelTag;
use lsolesen\pel\PelTiff;
use lsolesen\pel\PelIfd;
use lsolesen\pel\PelEntryShort;
use lsolesen\pel\PelEntryTime;
use lsolesen\pel\PelSubIfd;

class CPel {
  private $insPel = null;
  private $exif = null;

  function __construct($fname) {
    $this->insPel = new PelJpeg($fname);
    $app1 = $this->insPel->getExif();
    // exifがなかったら付加する
    if ($app1 == null) {
      $app1 = new PelExif();
      $this->insPel->setExif($app1);
    }

    $tiff = $app1->getTiff();
    if ($tiff == null) {
      $tiff = new PelTiff();
      $app1->setTiff($tiff);
    }

    $ifd0 = $tiff->getIfd();
    if ($ifd0 == null) {
      $ifd0 = new PelIfd(PelIfd::IFD0);
      $tiff->setIfd($ifd0);
    }

    $this->exif = $ifd0->getSubIfd(PelIfd::EXIF);
    if ($this->exif == null) {
      $this->exif = new PelIfd(PelIfd::EXIF);
      $ifd0->addSubIfd($this->exif);
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
      $orig = $this->exif->getEntry(PelTag::DATE_TIME_ORIGINAL);
      if ($orig) {
        return $orig->getText();
      }
    }
    return "";
  }

  /**
   * 写真の回転を返す
   * ない場合は変更なし(1)を返す
   */
  public function getOrientation() {
    if ($this->exif) {
      $ori = $this->exif->getEntry(PelTag::ORIENTATION);
      if ($ori) {
        return $ori->getValue();
      }
    }
    return 1;
  }

  private function setSizeExif($id, $data) {
    $entry = $this->exif->getEntry($id);
    if ($entry){
      $entry->setValue($data);
    }
    else {
      $new = new PelEntryShort($id, $data);
      $this->exif->addEntry($new);
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
