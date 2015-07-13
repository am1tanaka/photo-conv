<?php
function disp() {
  print_r($_FILES);
  readfile($_FILES['input_file']['tmp_name'][0]);
}
 ?>
