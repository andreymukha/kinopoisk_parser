<?php
/**
 * Created by PhpStorm.
 * User: Fly
 * Date: 22.05.2015
 * Time: 8:40
 */
class system {
  const FILENAME = 'INFORMATION.txt';

  protected function myMbUcfirst($str) {
    $fc = mb_strtoupper(mb_substr($str, 0, 1));
    return $fc.mb_substr($str, 1);
  }

  //обёртка для CURL, для более удобного использования
  public static function getUrlContent($param = NULL) {
    if (is_array($param)) {
      $ch = curl_init();
      if ($param['type'] == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
      }

      if ($param['type'] == 'GET') {
        curl_setopt($ch, CURLOPT_HTTPGET, 1);
      }

      curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0');

      if (isset($param['header'])) {
        curl_setopt($ch, CURLOPT_HEADER, 1);
      }

      if (isset($param['location'])) {
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $param['location']);
      }

      curl_setopt($ch, CURLOPT_TIMEOUT, 120);

      if (isset($param['returntransfer'])) {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      }

      curl_setopt($ch, CURLOPT_URL, $param['url']);

      if (isset($param['postfields'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param['postfields']);
      }

      if (isset($param['cookie'])) {
        curl_setopt($ch, CURLOPT_COOKIE, $param['cookie']);
      }

      if (isset($param['sendHeader'])) {
        $header = array();
        foreach ($param['sendHeader'] as $k => $v) {
          $header[] = $k . ': ' . $v . "\r\n";
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      }

      if (isset($param['referer'])) {
        curl_setopt($ch, CURLOPT_REFERER, $param['referer']);
      }

      if (isset($param['userpwd'])) {
        curl_setopt($ch, CURLOPT_USERPWD, $param['userpwd']);
      }

      $result = curl_exec($ch);
      curl_close($ch);

      if (isset($param['convert'])) {
        $result = iconv($param['convert'][0], $param['convert'][1], $result);
      }

      return $result;
    }
  }
}