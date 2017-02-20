<?php

$filename = iconv('UTF-8', 'GBK', $_FILES['file']['name']); 
if ($filename) {

$fileArr = explode('.',$filename);

$filename = md5(uniqid(microtime())).'.'.$fileArr[count($fileArr)-1];

    move_uploaded_file($_FILES["file"]["tmp_name"],
      "uploads/" . $filename);
}
echo $filename;
?>