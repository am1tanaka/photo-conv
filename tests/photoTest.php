<?php

require_once "web/photo-conv-proc.php";
require_once "web/CAm1Imagick.php";
require_once "web/CPel.php";

define('ORIG_DATE', '2015:07:11 09:21:46');
define('ORIG_WIDTH', 3264);
define('ORIG_HEIGHT', 2448);
define('FILE_WRITE', '/Users/yutanaka/git/photo-conv/tests/files/out.jpg');

class photoTest extends PHPUnit_Framework_TestCase
{
  private $TEST_FILES = array(
    'input_file' => array(
      'tmp_name' => array(
        '/Users/yutanaka/git/photo-conv/tests/files/IMG_1538.jpg',
        '/Users/yutanaka/git/photo-conv/tests/files/132200.jpg',
        '/Users/yutanaka/git/photo-conv/tests/files/132400.jpg'
      ),
      'name' => array(
        'IMG_1538.jpg',
        '132200.jpg',
        '132400.jpg'
      ),
      'type' => array(
        'image/jpeg',
        'image/jpeg',
        'image/jpeg'
      ),
      'size' => array(
        2306428,
        419461,
        328690
      )
    )
  );

  private $imagick=null;

  /**
   * @group CAm1Imagick
   * イメージの読み込みテスト
   */
  public function testLoadImage() {
    //$_FILES['input_file']['tmp_name'][0] = '~/git/photo-conv/tests/files/JPG/132200.jpg';
    $this->imagick = new CAm1Imagick($this->TEST_FILES['input_file']['tmp_name'][0]);
    $this->assertInstanceOf("CAm1Imagick", $this->imagick);
  }

  /**
   * @group CAm1Imagick
   * 読み込んだイメージの拡大縮小
   */
  public function testResize() {
    $this->imagick = new CAm1Imagick($this->TEST_FILES['input_file']['tmp_name'][0]);
    $geo = $this->imagick->getGeometry();
    $this->assertEquals(ORIG_WIDTH, $geo['width']);
    $this->assertEquals(ORIG_HEIGHT, $geo['height']);

    // 変更なし
    $this->imagick->resize(5000,5000);
    $geo = $this->imagick->getGeometry();
    $this->assertEquals(ORIG_WIDTH, $geo['width']);
    $this->assertEquals(ORIG_HEIGHT, $geo['height']);

    // 幅
    $this->imagick->resize(1366,5000);
    $geo = $this->imagick->getGeometry();
    $this->assertEquals(1366, $geo['width']);
    $this->assertEquals((int)(ORIG_HEIGHT*1366/ORIG_WIDTH), $geo['height']);

    // 高さ
    $this->imagick->resize(1366,768);
    $geo = $this->imagick->getGeometry();
    $this->assertEquals((int)(ORIG_WIDTH*768/ORIG_HEIGHT), $geo['width']);
    $this->assertEquals(768, $geo['height']);

    // 同じ比率
    $w = (int)(ORIG_WIDTH*480/ORIG_HEIGHT);
    $this->imagick->resize($w,480);
    $geo = $this->imagick->getGeometry();
    $this->assertEquals($w, $geo['width']);
    $this->assertEquals(480, $geo['height']);
  }

  public function testWriteImage() {
    // 画像をリサイズ
    $this->imagick = new CAm1Imagick($this->TEST_FILES['input_file']['tmp_name'][0]);
    $sz = $this->imagick->resize(1366,768);
    $this->assertEquals(true, $this->imagick->writeFile(FILE_WRITE));

    // EXIFを調整する
    $pel = new CPel(FILE_WRITE);
    $pel->setSize($sz['width'], $sz['height']);
    $pel->setDateTime("2015/7/14 23:43");
    $pel->saveFile(FILE_WRITE);
  }

  public function testWriteImage2() {
    // EXIFを調整する
    $pel = new CPel($this->TEST_FILES['input_file']['tmp_name'][1]);
    $pel->setDateTime("2015/7/14 23:43");
    $pel->saveFile(FILE_WRITE."2.jpg");
  }

  public function testPelSize()
  {
    $pel = new CPel($this->TEST_FILES['input_file']['tmp_name'][0]);
    $this->assertEquals(ORIG_WIDTH, $pel->getWidth());
    $this->assertEquals(ORIG_HEIGHT, $pel->getHeight());
    $this->assertEquals(ORIG_DATE, $pel->getDateTime());
  }

  /** POSTデータを送信して、縮小とEXIFの設定を処理を行わせる
  * @group post
  */
  public function testPostAll() {
    $_POST['input_width'] = 1024;
    $_POST['input_height'] = 1024;
    $_POST['check_filetime'] = "on";
    $_POST['text_filedate'] = "2015/7/15";
    // ファイル
    $_FILES = $this->TEST_FILES;

    // ここで処理を止め、テストが未完成であるという印をつけます。
    CPhotoConvProc::doConv();

    $this->assertTrue(TRUE, '変換テスト');

    $this->markTestIncomplete('このテストは、まだ実装されていません。');
  }

  /**
   * サイズのみの修正。チェックを外した状態での処理をチェック
   */
  public function testPostSize() {
    $this->assertTrue(TRUE, 'サイズのみ');

    // ここで処理を止め、テストが未完成であるという印をつけます。
    $this->markTestIncomplete('このテストは、まだ実装されていません。');
  }

  /**
   * 日付が入力されていない時の処理をテスト。ファイルの撮影日をそのまま利用して、
   * 時間だけ修正する。
   */
  public function testPostNoDate() {
    $this->assertTrue(TRUE, '日付省略テスト');

    // ここで処理を止め、テストが未完成であるという印をつけます。
    $this->markTestIncomplete('このテストは、まだ実装されていません。');
  }


/*
  public function testAppend()
	{
    $_FILES['input_file']['tmp_name'][0] = '~/git/photo-conv/tests/files/JPG/132200.jpg';
    disp();

    $this->assertEquals(1,1);
  }
*/
}
?>
