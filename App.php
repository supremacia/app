<?php

/**
 * Limp - less is more in PHP
 * @copyright   Bill Rocha - http://plus.google.com/+BillRocha
 * @license     MIT
 * @author      Bill Rocha - prbr@ymail.com
 * @version     0.0.1
 * @package     Limp
 * @access      public
 * @since       0.3.0
 *
 */

namespace Limp\App;

use Limp\Data;

class App 
{

    static $config = [];
    private static $dock = [];
    private static $router = null;
    private static $root = null;
    private static $php = null;
    private static $ctrl = null;
    private static $html = null;
    private static $models = [];
    private static $style = null;
    private static $script = null;
    private static $files = null;
    private static $upload = null;
    private static $url = null;
    private static $rqst = [];
    private static $defaultController = 'home';
    private static $defaultAction = 'main';
    private static $vars = [];
    private static $db = [];

    static function url() 
    {
        return static::$url;
    }

    static function root() 
    {
        return static::$root;
    }

    static function php() 
    {
        return static::$php;
    }

    static function inc($file) 
    {
        return include static::$php . $file . '.php';
    }

    static function ctrl($file) 
    {
        return include static::$ctrl . $file . '.php';
    }

    static function html()
    {
        return static::$html;
    }

    static function upload() 
    {
        return static::$upload;
    }

    static function style() 
    {
        return static::$style;
    }

    static function script() 
    {
        return static::$script;
    }

    /** 
     * Database access point
     *
     *  ex.: App::db('name', new Lib\Name); -> instancia o objeto com o nome "name"
     *  ex.: App::db('name'); -> recupera o objeto "name"
     */
    static function db($alias = null, $node = null) 
    {
        if ($alias === null && $node === null && count(static::$config['db']) > 0)
            $alias = array_keys(static::$config['db'])[0]; //pega o primeiro "alias" em config (default)

        if (isset(static::$db[$alias]))
            return static::$db[$alias];
        if (!isset(static::$config['db'][$alias]))
            return false; //caso $alias não existir em "config"
        return static::$db[$alias] = new Db(static::$config['db'][$alias]); //instancia a classe e retorna
    }

    /** 
     * Models root
     *
     *  ex.: App::model('name', new Model\Name); -> instancia o objeto com o nome "name"
     *  ex.: App::model('name'); -> recupera o objeto "name"
     */
    static function model($alias, $node = null) 
    {
        if (isset(static::$models[$alias]))
            return static::$models[$alias];
        if ($node === null || !is_object($node))
            return false;
        return static::$models[$alias] = $node;
    }

    static function rqst($i = null) 
    {
        if ($i === null)
            return static::$rqst;
        return isset(static::$rqst[$i]) ? static::$rqst[$i] : null;
    }

    static function val($n, $v = null) 
    {
        if ($v === null)
            return static::$vars[$n];
        static::$vars[$n] = $v;
    }

    static function getConfig($item = null) 
    {
        if ($item !== null && isset(static::$config[$item]))
            return static::$config[$item];
        else
            return static::$config;
    }

    static function mount(Router $router) 
    {
        static::$router = $router;

        static::$root = ¢WWW;
        static::$php = ¢APP . '';
        static::$ctrl = ¢APP . 'controller/';
        static::$html = ¢HTML . '';
        static::$upload = ¢APP . 'upload/';
        static::$style = ¢CSS;
        static::$script = ¢JS;

        static::$url = ¢URL;
        static::$rqst = explode('/', ¢RQST);
        static::$files = ¢URL . 'files/';
    }

    /**
     * Run application controller
     * @return object Controller
     */
    static function run() 
    {
        return static::runController(static::$router);
    }

    /* Parking Object
     * 
     */
    static function push($name, $object) 
    {
        static::$dock[$name] = $object;
    }

    static function pull($name) 
    {
        return isset(static::$dock[$name]) ? static::$dock[$name] : false;
    }

    /**
     * Configure DataBase connections
     *
     */
    static function dbConnection($alias, $dsn, $user, $passw) 
    {
        static::$config['db'][$alias] = ['dsn' => $dsn, 'user' => $user, 'passw' => $passw];
    }

    /**
     * Set default DataBase connection
     *
     */
    static function dbDefault($alias = null) 
    {
        if ($alias !== null && is_string($alias))
            static::$config['default_db'] = $alias;
        return static::$config['default_db'];
    }

    /**
     * Run Controller
     *
     */
    static private function runController(Router $router) 
    {
        $res = $router->resolve();

        $ctrl = ucfirst($res['controller'] !== null ? $res['controller'] : static::$defaultController);
        $action = $res['action'] !== null ? $res['action'] : static::$defaultAction;

        //instantiate the controller
        $ctrl = '\\Controller\\' . $ctrl;
        $controller = new $ctrl(['params' => $res['params'], 'request' => static::rqst()]);

        if (method_exists($controller, $action))
            return $controller->$action();
        else
            return $controller->{static::$defaultAction}();
    }

    /* Jump to...
     *
     *
     */
    static function go($url = '', $type = 'refresh', $cod = 302) 
    {
        //se tiver 'http' na uri então será externo.
        if (strpos($url, 'http://') === false || strpos($url, 'https://') === false)
            $url = ¢URL . $url;

        //send header
        if (strtolower($type) == 'refresh')
            header('Refresh:0;url=' . $url);
        else
            header('Location: ' . $url, TRUE, $cod);

        //... and stop
        exit;
    }

}
