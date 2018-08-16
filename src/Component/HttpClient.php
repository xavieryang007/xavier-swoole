<?php

namespace xavier\swoole\Component;

/**
 * Created by PhpStorm.
 * User: xavier
 * Date: 2018/8/15
 * Time: 上午9:09
 */
use GuzzleHttp\Client;

class HttpClient
{
    private $client;
    private $url = '';
    private static $instance = [];
    private $type = 'post';
    private $many = 'single';
    private $html='json';

    public function __construct($url, $timeout = 2)
    {
        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => $url,
            // You can set any number of default request options.
            'timeout'  => $timeout,
        ]);
    }

    public static function instance($url = 'default', $timeout = 2)
    {
        if (strpos($url, 'http') !== 0) {
            return false;
        }
        $key = md5($url);
        if (isset(self::$instance[$key])) {
            return self::$instance[$key];
        }
        self::$instance[$key] = new static($url, $timeout);
        return self::$instance[$key];
    }

    public function url($url = '')
    {
        $this->url = $url;
        return $this;
    }

    public function type($type = 'post')
    {
        $this->type = $type;
        return $this;
    }

    public function many($many='single')
    {
        $this->many=$many;
        return $this;
    }

    public function get($name, $arguments)
    {
        $url      = empty($this->url) ? '/' . $name : '/' . $this->url . '/' . $name;
        $response = $this->client->request('POST', $url, [
            'query' => empty($arguments) ? [] : $arguments,
        ]);
        $body     = $response->getBody();
        return ((string)$body);
    }

    public function post($name, $arguments)
    {

        $url      = empty($this->url) ? '/' . $name : '/' . $this->url . '/' . $name;
        $response = $this->client->request('POST', $url, [
            'form_params' => empty($arguments) ? [] : $arguments,
        ]);
        $body     = $response->getBody();
        return ((string)$body);
    }

    public function setcontenttype($htmltype='json')
    {
        $this->html=$htmltype;
    }



    public function __call($name, $arguments)
    {
        // TODO: Implement __call() method.
        if ($this->many!="single"){
            $arg=isset($arguments)?$arguments:[];
        }else{
            $arg=isset($arguments[0])?$arguments[0]:[];
        }
        $data='';
        switch ($this->type) {
            case 'post':
                $data= $this->post($name, $arg);
            default:
                $data= $this->get($name, $arg);
        }
        if (!empty($data)&&$this->html=="json"){
            return \GuzzleHttp\json_decode($data,true);
        }
        return $data;
    }
}