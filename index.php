<?php
require_once 'class/kinopoisk.php';


//$kris = new KP('Джерард Батлер');
//$kris = new KP('Эбигейл Спенсер');
//$kris = new KP('Анна Кендрик');
//$kris = new KP('Чарли Шин');
//$kris = new KP('Шрек 2');
//$kris = new KP('Бионсе');
//$kris = new KP('Натали Портман');
$kris = new KP('Мстители');



//echo '<pre>';
//print_r($kris->getUrl());
//echo '</pre>';
//
//echo '<pre>';
//print_r($kris->getImg());
//echo '</pre>';

echo '<pre>';
print_r($kris->getFilmActors());
echo '</pre>';