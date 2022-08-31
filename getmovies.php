<?php
require "db.php";

$page = 1;
if (isset($_POST['page'])){
  $page = intval($_POST['page']);
}

$id = 0;
if (isset($_POST['id'])){
  $id = intval($_POST['id']);
}

$res = $conn->query(

"SELECT * FROM movies " 
. " WHERE 1 " . (($id > 0)?  " AND id = " . $id . " " : "" ) 
. " ORDER BY id "
. ( ( ! $id )? " LIMIT 24 OFFSET " . ( "" . ($page-1)*24 ) : "" )

);
$data = [];
while($row = $res->fetch_assoc()){
  $data []= $row;
}

$conn->close();

echo json_encode($data);
