<?php

/**
 * Created by PhpStorm.
 * User: Andrey Mukha
 * Date: 01.05.2016
 * Time: 23:41
 *
 * Обёртка для Curl
 *
 * Использование:
 *	require_once 'curl.php';
 *
 * Инициализация нового экземпляра класса
 * 	$curl = curl::app('http://site.ru/');
 */
class curl {
  private $host; //Адрес сайта
  private $ch; //Объект curl
  private $options; //Установленные настройки CURL

  /**
   * Инициализация объекта для хоста
   *
   * @param $host
   * @return curl
   */
  public static function app($host){
    return new self($host);
  }

  /**
   * Инициализируем CURL
   *
   * @param $host
   */
  private function __construct($host){
    $this->ch = curl_init();
    $this->host = $host;
    $this->set(CURLOPT_RETURNTRANSFER, true);
  }

  /**
   * Закрывает сессию CURL
   */
  public function __destruct(){
    curl_close($this->ch);
  }

  /**
   * Объединение хоста и адреса страницы,
   * формирование полного url адреса
   *
   * @param $url
   * 	Адрес страницы без адреса домена
   *
   * @return string
   * 	Полный url
   */
  private function _joinURL($url){
    if($url[0] != '/')
      $url = '/' . $url;

    if($this->host[strlen($this->host)-1] === '/')
      $this->host = substr($this->host, 0, -1);

    return $this->host . $url;
  }

  /**
   * Формирует полученную страницу, разбивает на заголовок и контент
   *
   * @param $data
   * 	Полученная страница с заголовками или без
   *
   * @return array
   * 	Заголовки и контент
   */
  private function _processing($data){
    if(($this->get(CURLOPT_HEADER) === NULL) || ($this->get(CURLOPT_HEADER) === FALSE)){
      return array(
        'errno' => curl_errno($this->ch),
        'header' => '',
        'content' => $data,
      );
    }

    $header_size = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
    $header = substr($data, 0, $header_size);

    $headers = array();

    // Разделяем контент по двум переносам.
    $arrRequests = explode("\r\n\r\n", $header);

    // Разбираем заголовки
    for ($index = 0; $index < count($arrRequests) - 1; $index++) {
      foreach (explode("\r\n", $arrRequests[$index]) as $i => $line) {
        if ($i === 0){
          $headers[$index]['http_code'] = $line;
        } else {
          list ($key, $value) = explode(': ', $line);
          $headers[$index][$key] = $value;
        }
      }
    }

    return array(
      'errno' => curl_errno($this->ch),
      'header' => $headers,
      'content' => substr($data, $header_size),
    );
  }

  /**
   * Осуществляет запрос по указанному адресу и возвращает страницу с заголовками
   *
   * @param $url
   *
   * @param null $in
   * 	Входящая кодировка
   *
   * @param null $out
   * 	Исходящая кодировка
   *
   * @return array
   * 	Контент и заголовок
   */
  public function request($url, $in = NULL, $out = NULL){
    curl_setopt($this->ch, CURLOPT_URL, $this->_joinURL($url));
    $data = curl_exec($this->ch);
    if($in !== NULL){
      $data = iconv($in, $out, $data);
    }
    return $this->_processing($data);
  }

  /**
   * Установить значение CURL
   *
   * @param $name
   * 	Имя опции CURL
   *
   * @param $value
   * 	Значение опции CURL
   *
   * @return $this
   */
  public function set($name, $value){
    $this->options[$name] = $value;
    curl_setopt($this->ch, $name, $value);
    return $this;
  }

  /**
   * Получение установленного значения CURL
   *
   * @param $name
   * 	Имя опции CURL
   *
   * @return mixed
   */
  public function get($name) {
    return $this->options[$name];
  }

  /**
   * Показать/скрыть заголовки
   *
   * @param bool|true $header
   *
   * @return $this
   */
  public function setHeader($header = true){
    if(!$header){
      $header = false;
    }
    $this->set(CURLOPT_HEADER, $header);
    return $this;
  }

  /**
   * Следование за перенаправлениями
   *
   * @param bool|true $follow
   *
   * @return $this
   */
  public function setFollow($follow = true) {
    if(!$follow){
      $follow = false;
    }
    $this->set(CURLOPT_FOLLOWLOCATION, $follow);
    return $this;
  }

  /**
   * Установить страницу, с которой был переход
   *
   * @param $url
   * @return $this
   */
  public function setReferer($url) {
    $this->set(CURLOPT_REFERER, $url);
    return $this;
  }

  /**
   * Установить юзерагент
   *
   * @param string $agent
   * @return $this
   */
  public function setUagent($agent = 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:45.0) Gecko/20100101 Firefox/45.0') {
    $this->set(CURLOPT_USERAGENT, $agent);
    return $this;
  }

  /**
   * Выполнить запрос
   *
   * @param $type
   * 	GET - сделать GET запрос
   * 	PUT - сделать PUT запрос
   * 	POST - сделать POST запрос, нужен массив $post_data
   *
   * @param null $post_data
   * 	Массив данных для POST запроса
   *
   * @return $this
   */
  public function setQuery($type, $post_data = NULL){
    switch($type){
      case 'GET':
        $this->set(CURLOPT_HTTPGET, true);
        break;
      case 'PUT':
        $this->set(CURLOPT_PUT, true);
        break;
      case 'POST':
        $this->set(CURLOPT_POST, true);
        $this->set(CURLOPT_POSTFIELDS, http_build_query($post_data));
        break;
      default:
        $this->set(CURLOPT_HTTPGET, true);
    }
    return $this;
  }

  /**
   * Отправить Cookie
   *
   * @param $cookie
   * 	Строка ccookie
   *
   * @return $this
   */
  public function setCookie($cookie){
    $this->set(CURLOPT_COOKIE, $cookie);
    return $this;
  }

  /**
   * Отправить кастомные заголовки
   *
   * @param $headers
   * 	Массив с заголовками
   *
   * @return $this|bool
   */
  public function setHeaders($headers){
    if(!is_array($headers)){
      return false;
    }

    $header = array();
    foreach ($headers as $k => $v){
      $header[] = $k.': '.$v."\r\n";
    }
    $this->set(CURLOPT_HTTPHEADER, $header);
    return $this;
  }

  /**
   * Установить прокси
   *
   * @param $proxy
   * @return $this
   */
  public function setProxy($proxy){
    $this->set(CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
    if('auto' === $proxy){
      $proxy_list_json = file_get_contents('http://api.foxtools.ru/v2/Proxy?cp=UTF-8&lang=Auto&available=Yes&free=Yes&country=RU&formatting=1');
      $proxy_list = json_decode($proxy_list_json)->response->items;
      $rand = array_rand($proxy_list);
      $proxy_string = $proxy_list[$rand];
      $this->set(CURLOPT_PROXY, $proxy_string->ip.':'.$proxy_string->port);
    }else{
      $this->set(CURLOPT_PROXY, $proxy);
    }
    return $this;
  }

  public function setDisableSSL(){
    $this->set(CURLOPT_SSL_VERIFYPEER, FALSE);
    $this->set(CURLOPT_SSL_VERIFYHOST, FALSE);
  }
}