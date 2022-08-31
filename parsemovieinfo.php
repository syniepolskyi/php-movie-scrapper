<?php

$movie_section = '_2unOE _1zCx8';
if (!isset($_POST['_'])){
  //echo json_encode(['error' => 'It must be POST query width "_" param ']);
  //exit();
}

if (isset($_POST['class'])){
  $movie_section = $_POST['class'];
}


$id=1;

if (isset($_POST['id'])){
  $id = intval($_POST['id']);
}

require "db.php";

$res = $conn->query("SELECT * FROM movies WHERE id=" . $id . "");
$row = $res->fetch_assoc();
$url = $row['afisha_link'];



$html = file_get_contents($url);
if(!$html){
  echo json_encode(['error' => 'Cannot read '. $url]);
  $conn->close();
  exit();
}
$entities = (new DOMXPath ( (@DOMDocument::loadHTML ( $html )) ))
->query ( 
      '//section[contains(@class , "' . $movie_section . '")]' 
);


if (!$entities->length){
  echo json_encode(['error' => 'There is no section.' . $movie_section]);
  $conn->close();
  exit();
}




$entityInfo = false;


if($entities->item(1)->childNodes->length >= 2) {
$entityInfo = $entities->item(1)->childNodes->item(1)->childNodes->item(0);
} else{
$entityInfo = $entities->item(1)->childNodes->item(0)->childNodes->item(0);
}

if (!$entityInfo){
  echo json_encode(['error' => 'There is no section.' . $movie_section]);
  $conn->close();
  exit();
}




$countriesNodes = $entityInfo->childNodes->item(0)->childNodes->item(0)->childNodes;
$countries = "";
for($i = 1; $i < $countriesNodes->length; $i++){
  if(trim($countriesNodes->item($i)->textContent) !==","){
  $countries .= $countriesNodes->item($i)->textContent . " ";
  }
}

$countries = str_replace("Южная Корея", "Южная_Корея", $countries);
$countries = str_replace("Новая Зеландия", "Новая_Зеландия", $countries);
$countries = str_replace("Саудовская Аравия", "Саудовская_Аравия", $countries);
$countries = str_replace("Босния и Герцеговина", "Босния_и_Герцеговина", $countries);
$countries = str_replace("  ", " ", $countries);
$countries = trim($countries);
$countries = str_replace(" ", ", ", $countries);
$countries = str_replace("Южная_Корея", "Южная Корея", $countries);
$countries = str_replace("Новая_Зеландия", "Новая Зеландия", $countries);
$countries = str_replace("Саудовская_Аравия", "Саудовская Аравия", $countries);
$countries = str_replace("Босния_и_Герцеговина", "Босния и Герцеговина", $countries);

$countries = str_replace(",,", ",", $countries);


$genresNodes = $entityInfo->childNodes->item(1)->childNodes->item(1)->childNodes;
$genres = [];
for($i = 0; $i < $genresNodes->length; $i++){
  if(mb_stripos($genresNodes->item($i)->textContent, "еще") === false && trim(str_replace(",","",$genresNodes->item($i)->textContent))!==""){
  $genres []= str_replace(",","",$genresNodes->item($i)->textContent);
  }
}

$duration_txt = $entityInfo->childNodes->item(3)->childNodes->item(0)->childNodes->item(1)->textContent;
$duration_matches = [];
preg_match_all('/[\\d]+/', $duration_txt, $duration_matches);
$duration =  0;
if(count($duration_matches) == 1 && count($duration_matches[0]) == 2){
  $duration = intval($duration_matches[0][0])*60 + intval($duration_matches[0][1]);
}
if(count($duration_matches) == 1 && count($duration_matches[0]) == 1){
  $duration = intval($duration_matches[0][0]);
}

if($duration < 5 && mb_strpos($duration_txt,"мин") !== false){
  $duration = 60 * $duration;
}

$released = $entityInfo->childNodes->item(4)->childNodes->item(0)->childNodes->item(1)->textContent;
$months = ['января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'];
$month = '01';
for ($i = 0; $i < count($months); $i++){
  if(mb_stripos($released, $months[$i])){
    $month = ($i < 9? '0':'') . ($i + 1);
    break;
  }
}
$released_matches = [];
preg_match_all('/[\\d]+/', $released, $released_matches);
if(count($released_matches) == 1 && count($released_matches[0]) == 2){
  $day = intval($released_matches[0][0]);
  $day = ($day < 10? '0':'') . ($day);
  $released = $released_matches[0][1] . '-' . $month . '-' . $day;
} else {
  $released = '0000-00-00';
}

for($i = 0; $i < $entityInfo->childNodes->length; $i++){
if(mb_strpos($entityInfo->childNodes->item($i)->textContent,"Возрастное ограничение") !== false){
  $agelimit = $entityInfo->childNodes->item($i)->childNodes->item(0)->childNodes->item(1)->textContent;
  break;
}
}


$moviecast = [];
for($i = 0; $i < $entityInfo->childNodes->length; $i++){
if(mb_strpos($entityInfo->childNodes->item($i)->textContent,"Актеры") !== false){
  $moviecastNodes = $entityInfo->childNodes->item($i)->childNodes->item(1)->childNodes;
  for($j = 0; $j < $moviecastNodes->length; $j++){
    $val = $moviecastNodes->item($j)->textContent;
    if(trim($val) !=="," && mb_stripos($val, "еще") === false && strlen(trim($val)) > 0){
    $moviecast []= $val;
    }
  }
  break;
}
}

if(count($moviecast) ==0){
  $entities = (new DOMXPath ( (@DOMDocument::loadHTML ( $html )) ))
  ->query ( 
      '//div[@class = "_33kkU eJx-j"]' 
  );
  for($i = 0; $i < $entities->length; $i++){
    $moviecast []= $entities->item($i)->textContent;
  }
}


//
//



$imdbEntities = (new DOMXPath ( (@DOMDocument::loadHTML ( $html )) ))
->query ( 
      '//a[contains(@href , "https://www.imdb.com/title")]' 
);

$imgEntities = (new DOMXPath ( (@DOMDocument::loadHTML ( $html )) ))
->query ( 
      '//img[contains(@src , ".afisha.ru/mediastorage")]' 
);

$img_path = '';
for ($i = 0; $i < $imgEntities->length; $i++){
  if($imgEntities->item($i)->parentNode->attributes->getNamedItem("class")->value === "_3bgWH _3OYT2 _1R076"){
    $img_path = preg_replace('/^(.+?)\/afisha\/(.+?)\/s(.+?)\.afisha\.ru\/(.+)$/', 
    '$1/afisha/e920x1380q65i/s$3.afisha.ru/$4',
     $imgEntities->item($i)->attributes->getNamedItem("src")->value);
  }
}

$db_img = '';
if($img_path){
  $res = $conn->query("SELECT * FROM movies WHERE afisha_link='" . $url . "'");
  $row = $res->fetch_assoc();
  $content = file_get_contents($img_path);
  file_put_contents(dirname(__FILE__) . '/images/' . $row['id'] . '.jpg', $content);
  $db_img = 'images/' . $row['id'] . '.jpg';
  if(strlen($content) < 10000){
    $db_img = './' . $db_img;
  }
}



$imdb_id = '';
if($imdbEntities && $imdbEntities->length > 0 && $imdbEntities->item(0)->attributes){
$imdb_id = str_replace("https://www.imdb.com/title/","",$imdbEntities->item(0)->attributes->getNamedItem("href")->value);
}

     $stmt = $conn->prepare("UPDATE movies SET genres=?,countries=?,duration=?,"
     ."released=?,moviecast=?,agelimit=?,imdb_id=?,poster_img=?,movieyear=year(released) WHERE afisha_link=?");
     $stmt->bind_param("ssissssss", 
       implode(', ',$genres), 
       $countries,
       $duration,
       $released,
       implode(', ',$moviecast),
       $agelimit,
       $imdb_id,
       $db_img,
       $url);
     $result = ['msg' => 'UPDATED url : ' . $url];
     if(!$stmt->execute()){
       $result = ['error' => $stmt->error];
     }

     $stmt->close();

$res = $conn->query("SELECT id FROM movies WHERE poster_img IS NULL AND duration IS NULL AND countries IS NULL ORDER BY id");
$row = $res->fetch_assoc();
$next_id = $row['id'];
$result['next'] = $next_id;


$conn->close();
echo json_encode($result);
		
