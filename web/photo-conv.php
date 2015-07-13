<?php
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
