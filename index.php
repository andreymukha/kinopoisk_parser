<?php
require_once 'class/kinopoisk.php';


//$kris = new KP('Джерард Батлер');
//$kris = new KP('Эбигейл Спенсер');
//$kris = new KP('Анна Кендрик');
//$kris = new KP('Чарли Шин');
//$kris = new KP('Шрек 2');
//$kris = new KP('Натали Портман');
$kris = new KP('сверхъестественное ');



echo '<pre>';
print_r($kris->getFilmTitleEng());
echo '</pre>';