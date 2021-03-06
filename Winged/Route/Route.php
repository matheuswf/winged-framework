<?php

namespace Winged\Route;

use Winged\Date\Date;
use Winged\Http\Session;
use Winged\Utils\RandomName;
use Winged\Utils\WingedLib;
use Winged\Winged;

/**
 * Class Route
 * @package Winged\Route
 */
class Route
{

    /**
     * @var $routes array
     */
    protected static $routes = [];

    /**
     * @var $routesPart array
     */
    protected static $part = [];

    /**
     * @var $response array
     */
    protected static $response = [];

    /**
     * @var $name string
     */
    protected $name = '';

    /**
     * Route constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }


    /**
     * @param string $search
     * @param string $new_name
     * @return mixed|Route
     */
    public static function duplicate($search = '', $new_name = '')
    {
        if (array_key_exists($search, Route::$routes) && $new_name != $search && !array_key_exists($new_name, Route::$routes)) {
            Route::$routes[$new_name] = new Route($new_name);
            Route::$part[$new_name] = clone Route::$part[$search];
            return Route::$routes[$new_name];
        }
        //silence errors case duplicate fails
        return new Route(RandomName::generate('sisisisi'));
    }

    /**
     * @return $this
     */
    public function changeToGet()
    {
        Route::$part[$this->name]->http = 'get';
        return $this;
    }

    /**
     * @return $this
     */
    public function changeToPost()
    {
        Route::$part[$this->name]->http = 'post';
        return $this;
    }

    /**
     * @return $this
     */
    public function changeToPut()
    {
        Route::$part[$this->name]->http = 'put';
        return $this;
    }

    /**
     * @return $this
     */
    public function changeToDelete()
    {
        Route::$part[$this->name]->http = 'delete';
        return $this;
    }

    /**
     * @param array $origins
     * @return $this
     */
    public function origins($origins = []){
        if(is_array($origins)){
            Route::$part[$this->name]->origins = $origins;
        }
        return $this;
    }

    /**
     * @param $name
     */
    public function name($name)
    {
        Route::$routes[$name] = Route::$routes[$this->name];
        Route::$part[$name] = Route::$part[$this->name];
        unset(Route::$routes[$this->name]);
        unset(Route::$part[$this->name]);
        $this->name = $name;
    }

    /**
     * if use this method, basci auth is required in request for this route
     * @param string | callable $user
     * @param string $password
     * @param bool $require_password
     * @return $this
     */
    public function credentials($user = 'root', $password = '', $require_password = false)
    {
        $current = false;
        if (server('php_auth_user') && server('php_auth_pw') || (!$require_password && server('php_auth_user'))) {
            if (is_string($user)) {
                if (!$require_password) {
                    if (!server('php_auth_user') === $user) {
                        $current = true;
                    }
                } else if (!server('php_auth_user') === $user || !server('php_auth_pw') === $password) {
                    $current = true;
                }
            } else if (is_callable($user)) {
                $current = call_user_func_array($user, [server('php_auth_user'), server('php_auth_pw')]);
                if ($current) {
                    if (is_array($current)) {
                        Route::$part[$this->name]->vars = array_merge(Route::$part[$this->name]->vars, $current);
                    }
                    $current = false;
                } else {
                    $current = true;
                }
            } else {
                $current = true;
            }
        } else {
            $current = true;
        }
        if ($current) {
            Route::$part[$this->name]->_401 = true;
            Route::$part[$this->name]->errors['unauthorized'] = 'This request was not authorized by the server. Credentials available in the header are incorrect or not found.';
        }
        return $this;
    }

    /**
     * access this method and this route got required a token for send a 200 OK response
     * @return $this
     */
    public function session()
    {
        $current = false;
        $header = getallheaders();
        if (array_key_exists('X-Auth-Token', $header)) {
            $token = $header['X-Auth-Token'];
            $session = Session::get($token);
            if ($session) {
                $date = new Date($session['create_time']);
                $now = new Date();
                $dif = $date->diff($now, ['s']);
                if ($dif->seconds > $session['expires']) {
                    Session::remove($token);
                    $current = true;
                }
            } else {
                $current = true;
            }
        } else {
            $current = true;
        }
        if ($current) {
            Route::$part[$this->name]->_401 = true;
            Route::$part[$this->name]->errors['unauthorized'] = 'Token invalid or expired, generate a new token to continue with the requisitions.';
        }
        return $this;
    }

    /**
     * add a pattern for validate params in url
     * @param $property
     * @param bool $rule
     * @return $this
     */
    public function where($property, $rule = false)
    {
        if (is_array($property)) {
            Route::$part[$this->name]->rules = $property;
        } else {
            Route::$part[$this->name]->rules[$property] = $rule;
        }
        return $this;
    }

    /**
     * @param $uri
     * @param $callback
     * @return Route
     */
    public static function get($uri, $callback)
    {
        return self::parseRegister('get', $uri, $callback);
    }

    /**
     * @param $uri
     * @param $callback
     * @return Route
     */
    public static function post($uri, $callback)
    {
        return self::parseRegister('post', $uri, $callback);
    }

    /**
     * @param $uri
     * @param $callback
     * @return Route
     */
    public static function put($uri, $callback)
    {
        return self::parseRegister('put', $uri, $callback);
    }

    /**
     * @param $uri
     * @param $callback
     * @return Route
     */
    public static function delete($uri, $callback)
    {
        return self::parseRegister('delete', $uri, $callback);
    }

    /**
     * @param $array
     * @param $xml
     */
    public static function arrayToXml($array, &$xml)
    {
        /**
         * @var $xml \SimpleXMLElement
         */
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (is_int($key)) {
                    $key = "e";
                }
                $label = $xml->addChild($key);
                self::arrayToXml($value, $label);
            } else {
                $xml->addChild($key, $value);
            }
        }
    }

    /**
     * set response
     * @param $response
     */
    protected static function registerErrorResponse($response)
    {
        self::$response = $response;
    }

    /**
     * @param $method
     * @param $uri
     * @param $callback
     * @return Route
     */
    protected static function parseRegister($method, $uri, $callback)
    {
        $construct = [
            'http' => $method,
            'callable' => false,
            'class' => false,
            'method' => false,
            'vars' => [],
            'uri' => false,
            '_404' => false,
            '_401' => false,
            '_502' => false,
            'valid' => false,
            'rules' => [],
            'origins' => [],
            'createSessionOptions' => [],
            'errors' => [
                'rule' => []
            ]
        ];
        if (is_string($callback)) {
            //test if callback is string configuration for model@method
            $exp = explode('@', $callback);
            if (count7($exp) === 2) {
                $className = explode('\\', $exp[0]);
                $className = end($className);
                if (file_exists("./models/" . $className . ".php")) {
                    $obj = new $exp[0]();
                } else {
                    $obj = false;
                }
                if (method_exists($obj, $exp[1])) {
                    $construct['class'] = $obj;
                    $construct['method'] = $exp[1];
                } else {
                    $construct['_502'] = true;
                }
            }
            if ($construct['_502']) {
                $construct['_502'] = 'Callback malformed or not configured, response from this URI ever is 502. Contact admin server or programmer of this system.';
            }
        } else if (is_array($callback)) {
            //util to create a token for future requests
            $construct['createSessionOptions'] = $callback;
        } else if (is_callable($callback) || function_exists($callback)) {
            //test if callback is a function or name of a existent function
            $construct['callable'] = $callback;
        } else {
            $construct['_502'] = 'Callback malformed or not configured, response from this URI ever is 502. Contact admin server or programmer of this system.';
        }
        //in any case of not configured callback or malformed callback throw 502 bad request
        $parsed = [];
        $exp = WingedLib::explodePath($uri);
        $uri = WingedLib::explodePath(Winged::$uri);
        if (!$uri) {
            $uri = [];
        }
        /*
         * parse uri
         * determine what is a value and what is a keyword
         */
        if ($exp) {
            foreach ($exp as $index => $value) {
                $current = [];
                $_value = $value;
                if (begstr($value) === '{' && endstr($value) === '}') {
                    $current['type'] = 'arg';
                    $current['required'] = true;
                    begstr_replace($value);
                    endstr_replace($value, 1);
                    $_value = str_replace('?', '', $value);
                    if ($_value !== $value) {
                        $current['required'] = false;
                    }
                } else {
                    $current['type'] = 'name';
                    $current['required'] = true;
                }
                $current['name'] = $_value;
                $current['value'] = null;
                if (array_key_exists($index, $uri)) {
                    $current['value'] = $uri[$index];
                }
                $parsed[$_value] = $current;
            }
        }
        $construct['uri'] = $parsed;
        $name = RandomName::generate('sisisi', false, false);
        $route = new Route($name);
        Route::$routes[$name] = $route;
        Route::$part[$name] = (object)$construct;
        return $route;
    }
}