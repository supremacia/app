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
    private static $router = null;
    private static $www = null;
    private static $app = null;
    private static $phar = null;
    private static $html = null;
    private static $config = null;
    private static $log = null;
    private static $style = null;
    private static $script = null;
    private static $url = null;
    private static $rqst = [];
    private static $params = [];

    private static $defaultController = 'home';
    private static $defaultAction = 'main';

    private static $dock = [];
    private static $vars = [];

    static function url() 
    {
        return static::$url;
    }

    static function www() 
    {
        return static::$www;
    }

    static function app() 
    {
        return static::$app;
    }

    static function config() 
    {
        return static::$config;
    }

    static function log() 
    {
        return static::$log;
    }

    static function phar() 
    {
        return static::$phar;
    }

    static function html()
    {
        return static::$html;
    }

    static function style() 
    {
        return static::$style;
    }

    static function script() 
    {
        return static::$script;
    }
   
    static function rqst($i = null) 
    {
        if ($i === null)
            return static::$rqst;
        return isset(static::$rqst[$i]) ? static::$rqst[$i] : null;
    }

    static function params() 
    {
        return static::$params;
    }

    /* Set/Get variables
     * name = value par
     *
     */
    static function val($name, $value = null) 
    {
        if ($value === null)
            return static::$vars[$name];
        static::$vars[$name] = $value;
    }

    /* Parking Objects
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

    /* Mount static data object
     *
     */
    static function mount(Router $router = null, $cfg = null) 
    {
        if(!is_object($router)) $router = include _CONFIG.'router.php';
        static::$router = $router;

        static::$www = isset($cfg['www']) ? $cfg['www'] : _WWW;
        static::$app = isset($cfg['app']) ? $cfg['app'] : _APP;
        static::$phar = isset($cfg['phar']) ? $cfg['phar'] : _PHAR;
        static::$html = isset($cfg['html']) ? $cfg['html'] : _HTML;
        static::$style = isset($cfg['css']) ? $cfg['css'] : _CSS;
        static::$script = isset($cfg['js']) ? $cfg['js'] : _JS;
        static::$config = isset($cfg['config']) ? $cfg['config'] : _CONFIG;
        static::$log = isset($cfg['log']) ? $cfg['log'] : _LOG;
        static::$url = isset($cfg['url']) ? $cfg['url'] : _URL;
        static::$rqst = explode('/', (isset($cfg['rqst']) ? $cfg['rqst'] : _RQST));
    }

    /**
     * Run application controller
     * @return object Controller
     */
    static function run() 
    {
        return static::runController(static::$router);
    }

    /**
     * Run Controller
     *
     */
    static private function runController(Router $router) 
    {
        $res = $router->resolve();

        $ctrl = $res['controller'] !== null ? $res['controller'] : static::$defaultController;
        $action = $res['action'] !== null ? $res['action'] : static::$defaultAction;

        //Name format to Controller namespace
        $tmp = explode('\\', str_replace('/', '\\', $ctrl));
        $ctrl = '\\Controller';
        foreach($tmp as $tmp1){
            $ctrl .= '\\'.ucfirst($tmp1);
        }

        //instantiate the controller
        $controller = new $ctrl(['params' => $res['params'], 'request' => static::rqst()]);

        static::$params = $res['params'];

        if (method_exists($controller, $action))
            return $controller->$action();
        else
            return $controller->{static::$defaultAction}();
    }

    /* Jump to...
     *
     */
    static function go($url = '', $type = 'location', $cod = 302) 
    {
        //se tiver 'http' na uri então será externo.
        if (strpos($url, 'http://') === false || strpos($url, 'https://') === false)
            $url = static::$url.$url;

        //send header
        if (strtolower($type) == 'refresh')
            header('Refresh:0;url=' . $url);
        else
            header('Location: ' . $url, TRUE, $cod);

        //... and stop
        exit;
    }

    //Download de arquivo em modo PHAR (interno)
    static function download($reqst = '') {

        //checando a existencia do arquivo solicitado
        $reqst = static::fileExists($reqst);
        if($reqst == false) return false;

        //gerando header apropriado
        include static::$config.'mimetypes.php';
        $ext = end((explode('.', $reqst)));
        if (!isset($_mimes[$ext])) $mime = 'text/plain';
        else $mime = (is_array($_mimes[$ext])) ? $_mimes[$ext][0] : $_mimes[$ext];

        //get file
        $dt = file_get_contents($reqst);

        //download
        ob_end_clean();
        ob_start('ob_gzhandler');

        header('Vary: Accept-Language, Accept-Encoding');
        header('Content-Type: ' . $mime);
        header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', filemtime($reqst)) . ' GMT');
        header('Cache-Control: must_revalidate, public, max-age=31536000');
        header('Content-Length: ' . strlen($dt));
        header('Content-Disposition: attachment; filename='.basename($reqst)); 
        header('ETAG: '.md5($reqst));
        exit($dt);
    }

    //Check if file exists - return real path of file or false
    static function fileExists($file){
        if(file_exists($file)) return $file;
        if(file_exists(static::$www.$file)) return static::$www.$file;
        if(file_exists(static::$phar.$file)) return static::$phar.$file;
        $xfile = str_replace(static::$www, static::$phar, $file);
        if(file_exists($xfile)) return $xfile;
        return false;
    }

    //Print mixed data and exit
    static function e($v) { exit(static::p($v)); }
    static function p($v, $echo = false) {
        $tmp = '<pre>' . print_r($v, true) . '</pre>';
        if ($echo) echo $tmp;
        else return $tmp;
    }

}
