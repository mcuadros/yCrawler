<?php 
namespace yCrawler;
/**
 * Ejemplo:
 * <code>
 * class CLI extends Misc_CLI {
 *     protected $_appName = 'Test';
 *     protected $_appVersion = '1.0';
 *     protected $_appDesc = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore';
 *     protected $_appAuthor = 'Yunait Unidos para Ahorrar S.L. <contacto@yunait.com>';
 * 
 *    protected $_appArgs  = Array(
 *         Array('arg_obligatorio', 'o', null, 'Argumento de ejemplo obligatorio'),
 *         Array('arg_opcional', null, 'Valor por defecto', 'Argumento de ejemplo opcional') 
 *     );
 *       
 *     protected $_appFlags  = Array(
 *         Array('flag', 'f', null, 'Flag de ejemplo'),
 *         Array('flag2', null, false, 'Flag de ejemplo 2')
 *     );
 * }
 * </code>
 * @package yCrawler
 */
abstract class Misc_CLI { 
    /**
     * Nombre de la aplicacion de linea de comando
     */
    protected $_appName = false;
    
    /**
     * Descripcion de la aplicacion de linea de comando
     */
    protected $_appDesc = false;
    
    /**
     * Version de la aplicacion de linea de comando
     */
    protected $_appVersion = false;

    /**
     * Version de la aplicacion de linea de comando
     */
    protected $_appAuthor = false;

    /**
     * Array con cada uno de los argumentos posibles, en formato array, el formato es:
     * 0 => Nombre largo del argumento
     * 1 => Nombre corto (una letra)
     * 2 => Valor opcional si lo hay, sino null
     * 3 => Texto de Ayuda
     * @var array
     */
    protected $_appArgs  = Array();

    /**
     * Array con cada uno de las flags posibles, en formato array, el formato es:
     * 0 => Nombre largo del argumento
     * 1 => Nombre corto (una letra)
     * 2 => Valor opcional si lo hay, sino null
     * 3 => Texto de Ayuda
     * @var array
     */
    protected $_appFlags  = Array();

    protected $_codes = Array( 
        'reset' => 0, 'bold'  => 1, 'underline' => 4,  'nounderline' => 24, 'blink' => 5, 'reverse' => 7,
        'normal' => 22, 'blinkoff' => 25, 'reverse' => 7, 'reverseoff' => 27, 'black' => 30,  'red' => 31,
        'green' => 32, 'brown' => 33, 'blue' => 34, 'magenta' => 35, 'cyan' => 36, 'grey' => 37, 
        'bg_black' => 40, 'bg_red' => 41, 'bg_green' => 42, 'bg_brown' => 43, 'bg_blue' => 44, 
        'bg_magenta' => 45, 'bg_cyan' => 46, 'bg_white'   => 47,
    );

    private $_config = Array();

    /**
     * Contructor que instancia la clase console_app
     */
    public function __construct() {      
        if ( !$this->appName ) $this->appName = $_SERVER['SCRIPT_NAME'];
        $this->parseArgs();
    }

    /**
     * Devuelve el valor de un argumento u opcion, estos valores son definidos en los atributos appArgs y appFlags
     * @param string $arg
     */
    public function getValue($arg) { 
        if ( array_key_exists($arg, $this->_config) ) return $this->_config[$arg];
        return null;
    }
    
    
    /**
     * Imprime un error, si $fatal es true, ademas hace un exit -1
     * @param string $msg
     * @param boolean $fatal opcional
     */   
    public function error($msg, $fatal=false) { 
        $this->printString('::ERROR::' . $msg, 'red|bold');
        if ( $fatal ) exit($fatal);
    }

    /**
     * Imprime un mensaje informativo
     * @param string $msg
     */
    public function info($msg) { return $this->printString('::INFO::' . $msg, 'red|bold'); }

    /**
     * Hace una pregunta yes/no, devolviendo un boolean
     * @param string $msg
     * @return boolean
     */
    public function confirm($msg) {
        if ( strtolower($this->read('::CONFIRM::' . $msg . "\nYes|No (no)", 'brown|bold')) == 'yes' ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Lee una cadena de texto, del stdin
     * @param string $msg
     * @return string
     */
    public function read($msg = false, $tags = '') { 
        $this->printString($msg, $tags);
        return $this->readString();
    }

    /**
     * Imprime una cadena
     * @param string $msg
     */
    public function msg($msg, $tags = '') { return $this->printString('::' . $msg, $tags);  }



    public function table($label, $array, $style = false) {
        $this->info($label);
        $array = $this->formatArray($array, $style);
        $this->printArray($array);
    }

    /**
     * Imprime la ayuda y hace un exit
     */
    public function help($exitcode = 0) { 
        $this->printString($this->_appName . ' ' . $this->_appVersion, 'bold');
       
        if( $this->_appAuthor ) $this->printString($this->_appAuthor . "\n");
        if( $this->_appDesc ) $this->printString(wordwrap($this->_appDesc, 80) . "\n");
        $this->printString('-h,--help display this help' . "\n");

        if( count($this->_appArgs) ){
            $lines[]['title'] = '### OPTIONS LIST FOR ' . $this->taggedString($this->_appName,'bold|blue');
            foreach($this->_appArgs as $arg){
                $line = Array();
                $line['attr'] = '[';
                if ( $arg[1] ) $line['attr'] .= '-' . $arg[1] . ', ';
                $line['attr'] .= '--' . $arg[0] . ']';


                if ( $arg[2] ) $line['help'] .= '(Default value: \'' . $arg[2] . '\') ';
                else if ( $arg[2] === null ) $line['help'] .= $this->taggedString('Required ','bold|red');
                $line['help'] .= $arg[3];

                $lines[] = $line;
            }

        }

        if( count($this->_appFlags) ){
            $lines[]['title'] = "\n" . '### FLAG / SWITCHES LIST FOR ' . $this->taggedString($this->_appName,'bold|blue') ;
            foreach($this->_appFlags as $arg){
                $line = Array();
                $line['attr'] = '[';
                if ( $arg[1] ) $line['attr'] .= '-' . $arg[1] . ', ';
                $line['attr'] .= '--' . $arg[0] . ']';

                if ( $arg[2] === null ) $line['help'] .= $this->taggedString('Required ','bold|red');
                $line['help'] .= $arg[3];

                $lines[] = $line;
            }
        }

        $this->printArray($lines);
        exit($exitcode);
    }

    /**
     * Hace un dump, si es un array imprime una tabla, si imprime la variable
     * @param mixed $data
     * @param boolean $table
     */
    public function dump($data, $table = true) {
        if ( is_array($data) && $table ) return $this->app->print_table($data);
        $this->app->show($data);
    }

    /**
     * Ejectua $cmd en la shell, si $nohup es true, lo ejecuta con nohup y devuelve su pid
     * @param string $cmd
     * @param boolean $nohup
     * @param mixed $log fichero de log donde se guardara el restultado
     */
    public function exec($cmd, $nohup = false, $log = false) {
        $this->debug('Ejecutando comando: "' . $cmd . '"', true);
        
        if ( $nohup ) {
            if ( !$log ) $log = '/tmp/yu'.md5($cmd);
        
            $cmd = 'nohup nice -n 10 '.$cmd.' &> '.$log.' & echo $!';       
            return (int)shell_exec($cmd);         
        }
        
        return shell_exec($cmd);    
    }
    

    private function searchArgv($value, Array $array) {
        foreach($array as $arg){ 
            if ( strlen($value) == 2 ) {
                if ( '-'.$arg[1] == $value ) return $arg;
            } else {
                if ( '--'.$arg[0] == $value ) return $arg;
            }
        }

        return false;
    }

    private function parseArgs(){
        $argv = $_SERVER['argv'];
        $argc = $_SERVER['argc'];
    
        if( $argc <= 1 ) return $this->help(0);
        foreach ($argv as $str) {
            if( in_array($str, Array('--help','-h')) ) return $this->help(0);

            if( $str[0] != '-' ) {
                if ( $config ) $data[$config[0]]['values'][] = $str;
            } else {
                if ( $config = $this->searchArgv($str, $this->_appFlags) ) $data[$config[0]] = Array('type' => 'flag');              
                else if ( $config = $this->searchArgv($str, $this->_appArgs) ) $data[$config[0]] = Array('type' => 'arg'); 
            }
        }

        foreach($this->_appFlags as $flag) {
            if ( array_key_exists($flag[0], $data) ) $this->_config[$flag[0]] = true;
            else if ( $flag[2] === null ) $this->error('** Missing required flag: '.$flag[0].' (' . ($flag[1]?'-'.$flag[1].', ':'') .'--'. $flag[0] . ') **',true);
            else $this->_config[$flag[0]] = false;
        }

        foreach($this->_appArgs as $arg) {
            if ( array_key_exists($arg[0], $data) ) $this->_config[$arg[0]] = implode(' ', $data[$arg[0]]['values']);
            else if ( $arg[2] ) $this->_config[$arg[0]] = $arg[2];
            else if ( $arg[2] === null ) $this->error('** Missing required parameter: '.$arg[0].' (' . ($arg[1]?'-'.$arg[1].', ':'') .'--'. $arg[0] . ') **',true);
            else $this->_config[$arg[0]] = false; 
        }

        return true;
    }

    private function formatArray($array, $style) {
        $print = Array();
        switch($style) {
            case false: return $array;
            case 1:
                foreach($array as $key => $value) {
                    if ( is_bool($value) ) if ( $value === true ) { $value = 'true'; } else { $value='false'; }
                    $print[] = Array('field' => $key, 'value' => $value);
                    $value = str_replace(Array("\n", "\r", "\t"), '', $value);
                }
                return $print;
            case 2:
                foreach($array as $key => $values) {
                    foreach($values as $value) {
                        if ( is_bool($value) ) if ( $value === true ) { $value = 'true'; } else { $value='false'; }
                        $value = str_replace(Array("\n", "\r", "\t"), '', $value);
                        $print[] = Array('field' => $key, 'value' => $value);
                    }
                }
                return $print;

        }
    }

    private function printArray($array) {
        foreach($array as $data) {
            $tmp = 0;
            foreach($data as $col => $value) {
                if ( !isset($sizes[$col]) || strlen($value) > $sizes[$col] ) $sizes[$col] = strlen($value);
                $tmp += strlen($value);
            }

            if ( !isset($sizes['max']) || $tmp > $sizes['max'] ) $sizes['max'] = $tmp;
        }

        foreach($array as $data) {
            $tags = 'cyan|bold';
            $colWidth = 0;
            foreach($data as $col => $value) {
                $w = $sizes[$col];
                if ( $w > 90 ) {
                    $w = 90; 
                    $value = wordwrap($value, 90, str_pad("\n", $colWidth + 1));
                }

                fwrite(STDOUT, $this->taggedString(str_pad($value, $w), $tags) . "   ");
                $colWidth += $w + 3;

                if ( $tags ) $tags = 'reset';
            }

            fwrite(STDOUT, "\n");
        }
    }

    private function printString($string, $tags = 'reset', $where = STDOUT, $eof = true) {
        fwrite($where, $this->taggedString($string, $tags) . "\n");
    }

    private function readString($string, $tags = 'reset', $where = STDOUT) {
        $string = '';
        if( function_exists('readline') ){
            $read = readline($string);
            if( $read === FALSE ) exit();
        } else {
            if($string) fwrite(STDOUT,$string);
            $read=fgets(STDIN,4096);
            if( !strlen($read) ) exit();
            $read = preg_replace('![\r\n]+$!','',$read);
        }

        if( $read == 'exit' ){
            $this->info('execution stopped by user.');
            exit(0);
        }

        return $read;
    }

    private function taggedString($string, $tag='reset'){
        if( substr_count($tag, '|') ) {
            foreach(explode('|', $tag) as $tag){
                $str[]= isset($this->_codes[$tag]) ? $this->_codes[$tag] : 30;
            }
            
            $str = "\033[" . implode(';',$str) . 'm' . $string . "\033[0m";
        } else {
            if( in_array($this->_codes[$tag], Array(4,5,7) ) ){
                $end = "\033[2" . $this->_codes[$tag] . 'm';
            } else {
                $end = "\033[0m";
            }
    
            $str = "\033[".( isset($this->_codes[$tag]) ? $this->_codes[$tag] : 30) . 'm' . $string . $end;
        }

        return $str;
    }
} 