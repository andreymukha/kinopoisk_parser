<?php
require_once 'class/kinopoisk.php';


//$kris = new KP('Джерард Батлер');
$kris = new KP('Эбигейл Спенсер');
//$kris = new KP('Чарли Шин');


echo '<pre>';
print_r($kris->rus_name);
echo '<br>';
print_r($kris->eng_name);
echo '<br>';
print_r($kris->career);
echo '<br>';
print_r($kris->height);
echo '<br>';
print_r($kris->birthday);
echo '<br>';
print_r($kris->place_of_birth);
echo '<br>';
print_r($kris->genres);
echo '<br>';
print_r($kris->family);
echo '</pre>';