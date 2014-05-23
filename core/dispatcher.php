<?php
require_once(dirname(__FILE__) . '/utils.php');
require_once(dirname(__FILE__) . '/feed.php');
require_once(dirname(__FILE__) . '/system.php');
require_once(dirname(__FILE__) . '/transmission.php');

define('FEED_INFO', dirname(__FILE__) . '/../files/_' . FEED_CLASS . '.json');

function __autoload( $class ){
    $class = strtolower($class);
    $path = dirname(__FILE__) . '/../feeds/' . $class . '.php';
    if( is_file($path) ) require_once($path);
}

class Dispatcher{

    public static function dispatch(){
        session_start();

        if( isset($_GET['a']) ){
            $_SESSION['result'] = '';

            if( method_exists('Dispatcher', $_GET['a']) ){
                if( !isset($_GET['param']) ){
                    $to_echo = call_user_func('self::' . $_GET['a']);
                }else{
                    $to_echo = call_user_func('self::' . $_GET['a'], $_GET['param']);
                }
            }else{
                $to_echo = 'Action not Found';
            }

            if( !empty($to_echo) ) $_SESSION['result'] = $to_echo;

            // Avoid unwanted call of previous action
            header('Location: ./');
        }
    }

    /* Functions that can be called by dispatcher */

    private static function done(){
        $to_echo = '';
        foreach( Utils::getDoneList() as $done ){
            $to_echo .= Utils::printLink($done) . '<br>';
        }
        return $to_echo;
    }

    private static function shows(){
        $to_echo = '';
        foreach( Utils::getShowList() as $show ){
            $to_echo .= '<a target="_blank" href="' . Utils::getWebsiteLinkToShow($show) . '">' . $show . '</a> ';
            $to_echo .= '( <a title="Preview the show: ' . $show . '" href="./?a=preview&param=' . bin2hex($show) . '">?</a>';
            $to_echo .= ' | <a title="Download the show: ' . $show . '" onclick="return confirm(\'Are you sure?\')" href="./?a=launch&param=' . bin2hex($show) . '">&#9660;</a>';
            $to_echo .= ' | <a title="Delete ' . $show . '" onclick="return confirm(\'Are you sure?\')" href="./?a=remove_show&param=' . bin2hex($show) . '">&#10007;</a> )<br>';
        }
        return $to_echo;
    }

    private static function add_show(){
        if( isset($_POST['name_of_show']) ){
            Utils::addShow($_POST['name_of_show']);
        }
        return self::shows();
    }

    private static function remove_show( $name ){
        if( isset($name) && !empty($name) ){
            Utils::removeShow(hex2bin($name));
        }
        return self::shows();
    }

    private static function preview( $name = null ){
        $links = Utils::launchDownloads(true, hex2bin($name));
        return Utils::printLinks($links);
    }

    private static function launch( $name = null ){
        $links = Utils::launchDownloads(false, hex2bin($name));
        return Utils::printLinks($links);
    }

    private static function update_date(){
        Utils::updateDate();
    }

    private static function empty_done(){
        Utils::emptyDoneList();
    }

    private static function start_xbmc(){
        return System::startXBMC();
    }

    private static function status_xbmc(){
        return System::getStatusOfXBMC();
    }

    private static function kill_xbmc(){
        System::killXBMC();
    }

    private static function reboot(){
        System::reboot();
    }

    private static function shutdown(){
        System::shutdown();
    }

    private static function transmission( $function ){
        Transmission::call($function);
    }

}

?>