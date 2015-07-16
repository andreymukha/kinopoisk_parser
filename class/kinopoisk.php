<?php
/**
 * Created by PhpStorm.
 * User: Fly
 * Date: 22.05.2015
 * Time: 8:43
 */

$dir = dirname(__FILE__).'/../';
require_once $dir.'class/system.class.php';
require_once $dir.'lib/phpQuery/phpQuery.php';

class KP {
  private $artist_page;

  private $img;
  private $search_name;
  private $rus_name;
  private $eng_name;
  private $career;
  private $height;
  private $birthday;
  private $place_of_birth;
  private $genres;
  private $family;
  private $film_list;



  private function getContent($link){
    $result = system::getUrlContent(
      array(
        'url' => $link,
        'type' => 'GET',
        'returntransfer' => 1,
        'sendHeader' => array(
          'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
          'Accept-Language' => 'ru,en-us;q=0.7,en;q=0.3',
          'Accept-Charset' => 'windows-1251,utf-8;q=0.7,*;q=0.7',
          'Keep-Alive' => '300',
          'Connection' => 'keep-alive',
          'Referer' => 'http://www.kinopoisk.ru/',
        ),
        'convert' => array('Windows-1251', 'utf-8'),
      )
    );
    return $result;
  }

  private function getArtistPage($name){
    $search_page = self::getContent('http://www.kinopoisk.ru/index.php?first=no&what=&kp_query='.urlencode($name));
    $artist_page_url = substr('http://www.kinopoisk.ru' . phpQuery::newDocument($search_page)->find('.most_wanted .name a')->attr('href'), 0, -5);
    $artist_page = self::getContent($artist_page_url);
    return str_replace("charset=windows-1251", "charset=utf-8", $artist_page);
  }

   public function __construct($name){
    $this->search_name = $name;
    $this->artist_page = phpQuery::newDocument(self::getArtistPage($this->search_name), "text/html; charset=windows-1251");
    $this->GetArtistInit();
  }

  public function GetArtistInit(){
    $this->img = base64_encode(file_get_contents($this->artist_page->find('.film-img-box a img')->attr('src')));

    $this->rus_name = $this->artist_page->find('div#headerPeople h1')->text();

    $this->eng_name = trim($this->artist_page->find('div#headerPeople span')->text());

    $careers = $this->artist_page->find('.info tr:contains(карьера) a');
    $career = array();
    foreach($careers as $part){
      $career[] = pq($part)->text();
    }
    $this->career = $career;

    $this->height = $this->artist_page->find('.info tr:contains(рост) span')->text();

    //todo разбить на отдельные поля
    $birthday = $this->artist_page->find('.info tr:contains(дата рождения)');

    $timestamp = array();
    foreach(pq($birthday)->find('a') as $cnt=>$date) {
      if($cnt == 1 or $cnt == 0){
        $timestamp[] = pq($date)->text();
      }
    }
    $zodiac = array();
    foreach(pq($birthday)->find('span') as $cnt=>$other){
      if($cnt == 1){
        $zodiac = pq($other)->text();
      }
    }
    $year = $this->artist_page->find('.info tr:contains(дата рождения) td:not([class="type"])')->text();
    preg_match('![0-9]+ (года|лет)!', $year, $year);
    $this->birthday = $timestamp[0].' '.$timestamp[1].' ('.$year[0].') - '.$zodiac;

    $places_of_birth = $this->artist_page->find('.info tr:contains(место рождения) a');
    $place_of_birth = array();
    foreach($places_of_birth as $part){
      $place_of_birth[] = pq($part)->text();
    }
    $this->place_of_birth = $place_of_birth;

    $genres = $this->artist_page->find('.info tr:contains(жанры) a');
    $genre = array();
    foreach($genres as $part){
      $genre[] = pq($part)->text();
    }
    $this->genres = $genre;

    //todo поместить в многомерный массив
    $this->family = $this->artist_page->find('.info tr:contains(супруг) td:last-child')->text();

    $films = $this->artist_page->find('.specializationBox');
    $film_list = array();
    foreach($films as $film){
      $headersAmplua = pq($film)->find('.headersAmplua span.txtWorks')->text();
      foreach(pq($film)->find('.personPageItems .item') as $item){
        $film_list[$headersAmplua][pq($item)->find('.name a')->text()] = array(
          'url' => 'http://www.kinopoisk.ru' . pq($item)->find('.name a')->attr('href'),
          'rating' => pq($item)->find('.rating a')->text(),
          'role' => pq($item)->find('.role')->text(),
        );
      }
    }
    $this->film_list = $film_list;
  }

  /**
   * @return mixed
   */
  public function getSearchName() {
    return $this->search_name;
  }

  /**
   * @return mixed
   */
  public function getRusName() {
    return $this->rus_name;
  }

  /**
   * @return mixed
   */
  public function getEngName() {
    return $this->eng_name;
  }

  /**
   * @return mixed
   */
  public function getCareer() {
    return $this->career;
  }

  /**
   * @return mixed
   */
  public function getHeight() {
    return $this->height;
  }

  /**
   * @return mixed
   */
  public function getBirthday() {
    return $this->birthday;
  }

  /**
   * @return mixed
   */
  public function getPlaceOfBirth() {
    return $this->place_of_birth;
  }

  /**
   * @return mixed
   */
  public function getGenres() {
    return $this->genres;
  }

  /**
   * @return mixed
   */
  public function getFamily() {
    return $this->family;
  }

  /**
   * @param bool|TRUE $with_tag
   * @return string
   */
  public function getImg($with_tag = true) {
    if($with_tag){
      return '<img alt="" src="data:image/jpg;base64,'.$this->img.'" />';
    }else{
      return $this->img;
    }
  }

  /**
   * @return mixed
   */
  public function getFilmList() {
    return $this->film_list;
  }

  public function test(){
    echo self::getArtistPage($this->search_name);
  }
}