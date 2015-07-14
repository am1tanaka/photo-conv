<?php

/**
 * Imagickとpelを利用するための命令を集めたクラス
 * http://lsolesen.github.io/pel/
 */
class CAm1Imagick {
  private $insImagick = null;

  public function getGeometry() {
    return $this->insImagick->getImageGeometry();
  }

  /**
   * 指定のファイルをImagickに読み込む
   * @param {string} ファイルパス
   */
  function __construct($fname) {
    $this->insImagick = new Imagick($fname);
  }

  /**
   * 幅と高さの上限値を渡して、いずれかに抵触する場合はそれに収まるように縮小する
   * @param {number} $wmax 幅の最大値
   * @param {number} $hmax 高さの最大値
   * @return {array} widthに幅、heightに変換後の高さを入れて返す
   */
  function resize($wmax, $hmax) {
    $sz = $this->insImagick->getImageGeometry();
    // 画像サイズが指定サイズを下回る場合は何もしない
    if (  ($sz['width'] <= $wmax)
      &&  ($sz['height'] <= $hmax))
    return $sz;

    $this->insImagick->resizeImage($wmax, $hmax, Imagick::FILTER_LANCZOS, 1, TRUE);
    return $this->insImagick->getImageGeometry();
  }

  /**
   * 現在保持している画像を指定のパスのファイルに保存する
   * http://lsolesen.github.io/pel/doc/PEL/PelTag.html
   * @param {string} $fname ファイル名
   */
  function writeFile($fname) {
    // 現在のファイルを書き出す
    return $this->insImagick->writeImage ($fname);
  }

  /**
   * 読み込んだimagickを解放
   */
   function clear() {
     if ($this->insImagick != null) {
       $this->insImagick->clear();
       $this->insImagick = null;
     }
   }

   /** 解放*/
   function __destruct()
   {
     $this->clear();
   }

   /**
    * exif情報を出力する
    */
   public function printExif() {
     print_r(
      $this->insImagick->getImageProperties("exif:*")
    );
   }
}
 ?>
