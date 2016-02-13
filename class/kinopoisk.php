<?php
/**
 * Created by PhpStorm.
 * User: Fly
 * Date: 22.05.2015
 * Time: 8:43
 */

//Подключаем необходимые файлы: библиотеку phpQuery и класс с системными методами
$dir = dirname(__FILE__).'/../';
require_once $dir.'class/system.class.php';
require_once $dir.'lib/phpQuery/phpQuery.php';

class KP extends system {
  private $page;
  private $page_id;
  private $search_name;
  private $url;

  private $img;

  private $artist_id;
  private $artist_rus_name;
  private $artist_eng_name;
  private $artist_career;
  private $artist_height;
  private $artist_birthday;
  private $artist_place_of_birth;
  private $artist_genres;
  private $artist_family;
  private $artist_film_list;

  private $film_id;
  private $film_title_rus;
  private $film_title_eng;
  private $film_year;
  private $film_country;
  private $film_tagline;
  private $film_director;
  private $film_screenplay;
  private $film_producer;
  private $film_composer;
  private $film_painter;
  private $film_mounting;
  private $film_genre;
  private $film_budget;
  private $film_dues_usa;
  private $film_dues_world;
  private $film_dues_russia;
  private $film_dvd_usa;
  private $film_audience;
  private $film_premiere_world;
  private $film_premiere_russian;
  private $film_release_blueray;
  private $film_release_dvd;
  private $film_age;
  private $film_rating_mpaa;
  private $film_runtime;
  private $film_rating;
  private $film_duration;
  private $film_description;
  private $film_actors;


  protected function getMultipleField($field_name){
    $fields = $this->page->find(".info tr:contains($field_name) a");
    $field = array();
    foreach($fields as $part){
      $field[] = pq($part)->text();
    }
    return $field;
  }

  private function getPage($name) {
    $search_page = self::getContent('http://www.kinopoisk.ru/index.php?first=no&what=&kp_query=' . urlencode($name));
    $this->url = 'http://www.kinopoisk.ru' . phpQuery::newDocument($search_page)->find('.most_wanted .name a')->attr('href');
    preg_match('!(name|film)/([0-9]+)!', $this->url, $this->page_id);
    $artist_page = self::getContent('http://www.kinopoisk.ru/' . $this->page_id[0]);
    return str_replace("charset=windows-1251", "charset=utf-8", $artist_page);
  }

  private function getAllActors($film_id){
    $actors_page = str_replace("charset=windows-1251", "charset=utf-8", self::getContent('http://www.kinopoisk.ru/film/'.$film_id.'/cast/'));
    $actors_doc = phpQuery::newDocument($actors_page, "text/html; charset=windows-1251");
    $actors = $actors_doc->find('div.block_left > div');
    $actors_list = array();
    foreach($actors as $actor){
      if(pq($actor)->attr('style') == 'padding-left: 20px; border-bottom: 2px solid #f60; font-size: 16px'){
        $title = pq($actor)->text();
      }
      $actors_list[$title][] = array(
              'rus_name' => pq($actor)->find('div.info div.name a')->text(),
              'eng_name' => pq($actor)->find('div.info div.name span')->text(),
              'pic' => 'http://st.kp.yandex.net'.pq($actor)->find('.actorInfo .photo img')->attr('title'),
              'role' => pq($actor)->find('div.info div.role')->text(),
      );
    }

    foreach($actors_list as $k=>$v){
      foreach($v as $v2){
        if(empty($v2['eng_name'])) continue;
        $tmp[$k][] = $v2;
      }
    }
    return $tmp;
  }

  public function __construct($name) {
    $this->search_name = $name;
    $this->page = phpQuery::newDocument(self::getPage($this->search_name), "text/html; charset=windows-1251");
    $this->img = $this->page->find('.film-img-box a img')->attr('src') ? base64_encode(file_get_contents($this->page->find('.film-img-box a img')->attr('src'))) : base64_encode(file_get_contents("http://st.kp.yandex.net/images/persons/photo_none.png"));
    if ($this->page_id[1] == 'name') {
      $this->GetArtistInit();
    }elseif($this->page_id[1] == 'film'){
      $this->GetFilmInit();
    }else{
      echo "Ничего не найдено";
    }
  }

  private function GetFilmInit(){
    $this->film_id = $this->page_id[2];
    $this->film_title_rus = $this->page->find('#headerFilm h1.moviename-big')->text();
    $this->film_title_eng = $this->page->find('#headerFilm > span')->text();
    $this->film_year = str_replace(array("\n","\r"), '', $this->page->find('.info tr:contains(год) a')->text());
    $this->film_country = $this->getMultipleField('страна');
    $this->film_tagline = $this->page->find('.info tr:contains(слоган) a')->text();
    $this->film_director = $this->getMultipleField('режиссер');
    $this->film_screenplay = $this->getMultipleField('сценарий');
    $this->film_producer = $this->getMultipleField('продюсер');
    $this->film_composer = $this->getMultipleField('композитор');
    $this->film_painter = $this->getMultipleField('художник');
    $this->film_mounting = $this->getMultipleField('монтаж');
    $this->film_genre = $this->getMultipleField('жанр');
    $this->film_budget = $this->page->find('.info tr:contains(бюджет) a')->text();
    $this->film_dues_usa = $this->page->find('.info tr:contains(сборы в США) a')->text();
    $this->film_dues_world = $this->page->find('.info tr:contains(сборы в мире) a:first-child')->text();
    $this->film_dues_russia = $this->page->find('.info tr:contains(сборы в России) a')->text();
    $this->film_dvd_usa = $this->page->find('.info tr:contains(DVD в США) a')->text();

    $audience = str_replace(', ...', '', $this->page->find('.info tr:contains(зрители) div div')->text());
    $audience = preg_replace('![^0-9а-я,. ]!u', '', trim($audience));
    $this->film_audience = explode(',', $audience);
    //todo Добавить остальные поля

    $film_premiere_world = $this->getMultipleField('премьера (мир)');
    $this->film_premiere_world = $film_premiere_world[0];

    $film_premiere_russian = $this->getMultipleField('премьера (РФ)');
    $this->film_premiere_russian = $film_premiere_russian[0];

    $film_release_dvd = $this->getMultipleField('релиз на DVD');
    $this->film_release_dvd = $film_release_dvd[0];

    $film_release_blueray = $this->getMultipleField('релиз на Blu-Ray');
    $this->film_release_blueray = $film_release_blueray[0];

    $this->film_age = $this->page->find('.info tr:contains(возраст) span')->text();

    $this->film_rating_mpaa = trim($this->page->find('.info tr:contains(рейтинг MPAA) span')->text());

    $this->film_runtime = trim($this->page->find('.info tr:contains(время) td:last-child')->text());

    $this->film_description = $this->page->find('._reachbanner_ .brand_words')->text();
    $this->film_duration = $this->page->find('.info tr:contains(время) #runtime')->text();
    $this->film_rating['digital'] = $this->page->find('.rating_ball')->text();
    $this->film_rating['picture'] = "<img src=\"http://rating.kinopoisk.ru/{$this->page_id[2]}.gif\">";
    $this->film_actors = $this->getAllActors($this->film_id);
  }

  private function GetArtistInit(){
    //ID сущности (артист/фильм)
    $this->artist_id = $this->page_id[2];

    //Имя артиста на русском
    $this->artist_rus_name = $this->page->find('div#headerPeople h1')->text();

    //Имя артиста на английском
    $this->artist_eng_name = trim($this->page->find('div#headerPeople span')->text());

    //Карьера артиста
    $this->artist_career = $this->getMultipleField('карьера');

    //Рост артиста
    $this->artist_height = $this->page->find('.info tr:contains(рост) span')->text();

    //Дата рождения артиста
    $birthday = $this->page->find('.info tr:contains(дата рождения)');
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
    $year = $this->page->find('.info tr:contains(дата рождения) td:not([class="type"])')->text();
    preg_match('![0-9]+ (года|лет)!', $year, $year);
    $day_birth = explode(' ', $timestamp[0]);
    $this->artist_birthday = array(
      'day_of_birth' => $day_birth[0],
      'month_of_birth' => $day_birth[1],
      'year_of_birth' => $timestamp[1],
      'years' => $year[0],
      'sign_of_the_zodiac' => $zodiac,
    );

    //Место рождения артиста
    $this->artist_place_of_birth = $this->getMultipleField('место рождения');

    //Жанры в которых снимается
    $this->artist_genres = $this->getMultipleField('жанры');

    //Семейное положение
    $fam_type = $this->page->find('.info tr:contains(супруг) td:first-child')->text();
    $fams = $this->page->find('.info tr:contains(супруг) td:last-child a');
    $family = array();
    foreach($fams as $fam){
      $family[] = array(
        'name' => pq($fam)->text(),
        'link' => 'http://www.kinopoisk.ru'.pq($fam)->attr('href')
      );
    }
    array_unshift($family, $fam_type);
    $this->artist_family = $family;

    //Список фильмов
    $films = $this->page->find('.specializationBox');
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
    $this->artist_film_list = $film_list;
  }

  /**
   * @return mixed
   */
  public function getUrl() {
    return $this->url;
  }

  /**
   * @return mixed
   */
  public function getArtistId() {
    return $this->artist_id;
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
  public function getArtistRusName() {
    return $this->artist_rus_name;
  }

  /**
   * @return mixed
   */
  public function getArtistEngName() {
    return $this->artist_eng_name;
  }

  /**
   * @return mixed
   */
  public function getArtistCareer() {
    return $this->artist_career;
  }

  /**
   * @return mixed
   */
  public function getArtistHeight() {
    return $this->artist_height;
  }

  /**
   * @return mixed
   */
  public function getArtistBirthday() {
    return $this->artist_birthday;
  }

  /**
   * @return mixed
   */
  public function getArtistPlaceOfBirth() {
    return $this->artist_place_of_birth;
  }

  /**
   * @return mixed
   */
  public function getArtistGenres() {
    return $this->artist_genres;
  }

  /**
   * @return mixed
   */
  public function getArtistFamily() {
    return $this->artist_family;
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
  public function getFilmId() {
    return $this->film_id;
  }


  /**
   * @return mixed
   */
  public function getArtistFilmList() {
    return $this->artist_film_list;
  }

  /**
   * @return mixed
   */
  public function getFilmTitleRus() {
    return $this->film_title_rus;
  }

  /**
   * @return mixed
   */
  public function getFilmTitleEng() {
    return $this->film_title_eng;
  }

  /**
   * @return mixed
   */
  public function getFilmYear() {
    return $this->film_year;
  }

  /**
   * @return mixed
   */
  public function getFilmCountry() {
    return $this->film_country;
  }

  /**
   * @return mixed
   */
  public function getFilmTagline() {
    return $this->film_tagline;
  }

  /**
   * @return mixed
   */
  public function getFilmDirector() {
    return $this->film_director;
  }

  /**
   * @return mixed
   */
  public function getFilmScreenplay() {
    return $this->film_screenplay;
  }

  /**
   * @return mixed
   */
  public function getFilmProducer() {
    return $this->film_producer;
  }

  /**
   * @return mixed
   */
  public function getFilmComposer() {
    return $this->film_composer;
  }

  /**
   * @return mixed
   */
  public function getFilmPainter() {
    return $this->film_painter;
  }

  /**
   * @return mixed
   */
  public function getFilmMounting() {
    return $this->film_mounting;
  }

  /**
   * @return mixed
   */
  public function getFilmGenre() {
    return $this->film_genre;
  }

  /**
   * @return mixed
   */
  public function getFilmBudget() {
    return $this->film_budget;
  }

  /**
   * @return mixed
   */
  public function getFilmDuesUsa() {
    return $this->film_dues_usa;
  }

  /**
   * @return mixed
   */
  public function getFilmRating() {
    return $this->film_rating;
  }

  /**
   * @return mixed
   */
  public function getFilmDuration() {
    return $this->film_duration;
  }

  /**
   * @return mixed
   */
  public function getFilmDescription() {
    return $this->film_description;
  }

  /**
   * @return mixed
   */
  public function getFilmDuesWorld() {
    return $this->film_dues_world;
  }

  /**
   * @return mixed
   */
  public function getFilmDuesRussia() {
    return $this->film_dues_russia;
  }

  /**
   * @return mixed
   */
  public function getFilmAudience() {
    return $this->film_audience;
  }

  /**
   * @return mixed
   */
  public function getFilmDvdUsa() {
    return $this->film_dvd_usa;
  }

  /**
   * @return mixed
   */
  public function getFilmPremiereWorld() {
    return $this->film_premiere_world;
  }

  /**
   * @return mixed
   */
  public function getFilmPremiereRussian() {
    return $this->film_premiere_russian;
  }

  /**
   * @return mixed
   */
  public function getFilmReleaseBlueray() {
    return $this->film_release_blueray;
  }

  /**
   * @return mixed
   */
  public function getFilmReleaseDvd() {
    return $this->film_release_dvd;
  }

  /**
   * @return mixed
   */
  public function getFilmAge() {
    return $this->film_age;
  }

  /**
   * @return mixed
   */
  public function getFilmRatingMpaa() {
    return $this->film_rating_mpaa;
  }

  /**
   * @return mixed
   */
  public function getFilmRuntime() {
    return $this->film_runtime;
  }

  /**
   * @return mixed
   */
  public function getFilmActors() {
    return $this->film_actors;
  }

  public function test(){
    echo self::getPage($this->search_name);
  }
}