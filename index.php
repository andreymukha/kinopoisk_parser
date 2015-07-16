<?php
require_once 'class/kinopoisk.php';


//$kris = new KP('Джерард Батлер');
$kris = new KP('Эбигейл Спенсер');
//$kris = new KP('Чарли Шин');


echo '<pre>';
print_r($kris->getImg());
echo '<br>';
print_r($kris->getRusName());
echo '<br>';
print_r($kris->getEngName());
echo '<br>';
print_r($kris->getCareer());
echo '<br>';
print_r($kris->getHeight());
echo '<br>';
print_r($kris->getBirthday());
echo '<br>';
print_r($kris->getPlaceOfBirth());
echo '<br>';
print_r($kris->getGenres());
echo '<br>';
print_r($kris->getFamily());
echo '<br>';
print_r($kris->getFilmList());
echo '</pre>';