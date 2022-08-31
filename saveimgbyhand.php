<?php

if(!isset($_REQUEST['url'])){
  echo json_encode(['error' => 'url not received']); die();
}

if(!isset($_REQUEST['id'])){
  echo json_encode(['error' => 'id not received']); die();
}

$url = $_REQUEST['url'];
$id = intval($_REQUEST['id']);

$content = file_get_contents($url);
if (strlen($content)){
require "db.php";
$name = "images/" . $id . ".jpg";
file_put_contents(dirname(__FILE__).'/'.$name, $content);
if (strlen($content) < 10000){
  $name = './'.$name;
}
$conn->query("UPDATE movies SET poster_img='".$name."' WHERE id=".$id);
$conn->close();
echo json_encode(['id' => $id, 'url' => $url, 'size' => strlen($content)]);
} else {
echo json_encode(['error' => 'For ' . $id . ' no content from ' . $url]);
}
