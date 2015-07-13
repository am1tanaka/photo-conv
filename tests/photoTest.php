<?php

require_once "web/photo-conv-proc.php";

class photoTest extends PHPUnit_Framework_TestCase
{
  public function testAppend()
	{
    $_FILES['input_file']['tmp_name'][0] = '~/git/photo-conv/tests/files/JPG/132200.jpg';
    disp();

    $this->assertEquals(1,1);
  }

}
?>
