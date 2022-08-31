<?php

$movie_title_class = '_3Yfoo';
if (!isset($_POST['_'])){
  echo json_encode(['error' => 'It must be POST query width "_" param ']);
  exit();
}

if (isset($_POST['class'])){
  $movie_title_class = $_GET['class'];
}

$page = 1;
if (isset($_POST['page'])){
  $page = intval($_POST['page']);
}

$url = 'https://www.afisha.ru/movie/movies/?sort=rating';

if ($page > 1){
  $url = 'https://www.afisha.ru/movie/movies/page' . $page . '/?sort=rating';
}


$html = file_get_contents($url);
if(!$html){
  echo json_encode(['error' => 'Cannot read '. $url]);
  exit();
}
$entities = (new DOMXPath ( (@DOMDocument::loadHTML ( $html )) ))
->query ( 
      '//h2[contains(@class, ' . $movie_title_class . ')]' 
);

if (!$entities->length){
  echo json_encode(['error' => 'There is no h2.' . $movie_title_class]);
  exit();
}

require "db.php";

$result = [];
for ($i = 0; $i < $entities->length; $i++){
   $name = $entities->item($i)->nodeValue;
   $afisha_link = 'https://www.afisha.ru' . $entities->item($i)->parentNode->attributes->getNamedItem("href")->nodeValue;
   if(mb_strpos($afisha_link, "meeting") !== false){continue;}
   if ($name && $afisha_link){
     $stmt = $conn->prepare("INSERT INTO movies(name,afisha_link) VALUES(?,?)");
     $stmt->bind_param("ss", $name, $afisha_link);
     if(!$stmt->execute()){
       $result []= ['error' => $stmt->error];
       $stmt->close();
       continue;
     }
     if($conn->insert_id){
       $result []= [$conn->insert_id, $name, $afisha_link];
     }
     $stmt->close();
   }
}

$conn->close();

echo json_encode($result);	
