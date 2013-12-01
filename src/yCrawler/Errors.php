<?php

namespace yCrawler;

class Errors
{
    /**
     * Contructor
     */
    public static function init()
    {
        ini_set('display_errors',0);

        set_exception_handler(array('yCrawler\Errors', 'exceptionHandler'));
        set_error_handler(array('yCrawler\Errors', 'errorHandler'));
        register_shutdown_function(array('yCrawler\Errors', 'shutdownHandler'));
    }

    /**
     * Funcion para register_shutdown_function
     * @link http://php.net/manual/en/function.register-shutdown-function.php
     */
    public static function shutdownHandler()
    {
        $err = error_get_last();
        if ( in_array($err['type'], array(E_PARSE, E_USER_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_ERROR)) ) {
            Errors::showError(
                Errors::convertCode($err['type']),
                $err['message'],
                $err['file'],
                $err['line'],
                null
            );
        }

        //Output::log('Memory Usage -> ' . number_format (memory_get_usage())  . ' bytes / ' . number_format (memory_get_usage(true)) .'  bytes', Output::DEBUG);
        //Output::log('Memory Usage Peak -> ' . number_format(memory_get_peak_usage())  . ' bytes / ' .number_format ( memory_get_peak_usage(true)) .'  bytes', Output::DEBUG);
        //Output::log('Execution Time -> ' .  (microtime(true) - $this->ini) . ' secs', Output::DEBUG);
    }

    /**
     * Funcion para set_error_handler
     * @link http://www.php.net/manual/en/function.set-error-handler.php
     * @param int    $severity
     * @param string $message
     * @param string $filepath
     * @param int    $line
     * @param string $context
     */
    public static function errorHandler($severity, $message, $filepath, $line, $context)
    {
        Errors::showError(
            Errors::convertCode($severity),
            $message,
            $filepath,
            $line,
            isset($context['trace']) ? $context['trace'] : null
        );
    }

    /**
     * Funcion para exception_handler
     * @link http://bd.php.net/manual/en/function.set-exception-handler.php
     * @param Exception $exception
     */
    public static function exceptionHandler($exception)
    {
        Errors::showError(
            Output::EXCEPTION,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );
    }

    private static function convertCode($severity)
    {
        switch ($severity) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                return Output::ERROR;

            case E_WARNING:
            case E_CORE_WARNING:
            case E_COMPILE_WARNING:
            case E_USER_WARNING:
                return Output::WARNING;

            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
                return Output::NOTICE;

            default:
                return Output::WARNING;
        }
    }

    private static function showError($level, $message, $filepath, $line, $trace)
    {
        $filepath = str_replace("\\", "/", $filepath);

        if (FALSE !== strpos($filepath, '/')) {
            $x = explode('/', $filepath);
            $filepath = $x[count($x)-2].'/'.end($x);
        }

        Output::log($message . "\n" .' at line ' . $line  . ' file ' . $filepath, $level);

    }

}
