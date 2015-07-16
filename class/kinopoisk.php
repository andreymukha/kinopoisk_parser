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
  public $search_name;
  public $rus_name;
  public $eng_name;
  public $career;
  public $height;
  public $birthday;
  public $place_of_birth;
  public $genres;
  public $family;

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
    preg_match('!name\/([0-9]+)\/!', $search_page, $artist_id);
    $artist_page = self::getContent('http://www.kinopoisk.ru/name/'.$artist_id[1]);
    return str_replace("charset=windows-1251", "charset=utf-8", $artist_page);
  }

   public function __construct($name){
    $this->search_name = $name;
    $this->artist_page = phpQuery::newDocument(self::getArtistPage($this->search_name), "text/html; charset=windows-1251");
    $this->rus_name = self::_rusName();
    $this->eng_name = self::_engName();
    $this->career = self::_career();
    $this->height = self::_height();
    $this->birthday = self::_birthday();
    $this->place_of_birth = self::_placeOfBirth();
    $this->genres = self::_genres();
    $this->family = self::_family();
  }

  private function _rusName (){
    $rus_name = $this->artist_page->find('div#headerPeople h1')->text();
    return $rus_name;
  }

  private function  _engName(){
    $eng_name = trim($this->artist_page->find('div#headerPeople span')->text());
    return $eng_name;
  }

  private function _career(){
    $careers = $this->artist_page->find('.info tr:contains(карьера) a');
    $career = array();
    foreach($careers as $part){
      $career[] = pq($part)->text();
    }
    return $career;
  }

  private function _height(){
    $height = $this->artist_page->find('.info tr:contains(рост) span')->text();
    return $height;
  }

  private function _birthday(){
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
    $birthday = $timestamp[0].' '.$timestamp[1].' ('.$year[0].') - '.$zodiac;
    return $birthday;
  }

  private function _placeOfBirth(){
    $places_of_birth = $this->artist_page->find('.info tr:contains(место рождения) a');
    $place_of_birth = array();
    foreach($places_of_birth as $part){
      $place_of_birth[] = pq($part)->text();
    }
    return $place_of_birth;
  }

  private function _genres(){
    $genres = $this->artist_page->find('.info tr:contains(жанры) a');
    $genre = array();
    foreach($genres as $part){
      $genre[] = pq($part)->text();
    }
    return $genre;
  }

  private function _family(){
    //todo поместить в многомерный массив
    $family = $this->artist_page->find('.info tr:contains(супруг) td:last-child')->text();
    return $family;
  }


  public function test(){
    echo self::getArtistPage($this->search_name);
  }
}