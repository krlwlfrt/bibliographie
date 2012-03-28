<?php
class bibliographie_error_handler {
	/**
	 * Handles runtime errors.
	 * @param string $number
	 * @param string $string
	 * @param string $file
	 * @param string $line
	 */
	public static function errors ($number, $string, $file, $line) {
		if(!in_array($number, array(E_STRICT, E_NOTICE))){
			bibliographie_error_handler::log('Code: '.$number.PHP_EOL.'Message: '.$string.PHP_EOL.'File: '.$file.':'.$line);
			bibliographie_error_handler::stop();
		}
	}

	public static function fatal_errors () {
		$e = error_get_last();
		self::errors($e['type'], $e['message'], $e['file'], $e['line']);
	}

	/**
	 * Handles uncaught runtime exceptions.
	 * @param \Exception $exception
	 */
	public static function exceptions ($exception) {
		bibliographie_error_handler::log('Code: '.$exception->getCode().PHP_EOL.'Message: '.$exception->getMessage().PHP_EOL.'File: '.$exception->getFile().':'.$exception->getFile().PHP_EOL.$exception->getTraceAsString());

		bibliographie_error_handler::stop();
	}

	/**
	 * Log something in the error log.
	 * @param string $message
	 */
	public static function log ($message) {
		$file = fopen(BIBLIOGRAPHIE_ROOT_PATH.'/logs/errors/'.date('Y.W').'.log', 'a');
		fwrite($file, $message.PHP_EOL.PHP_EOL);
		fclose($file);
	}

	/**
	 * Stop execution and show error page.
	 */
	private static function stop () {
		ob_end_clean();
		exit('<!DOCTYPE html><html><head><title>Error</title></head><body><h1>Error</h1><p>An error occured!</p></body></html>');
	}
}