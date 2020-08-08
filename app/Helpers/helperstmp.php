<?php

if (!function_exists('du')) {
	/**
	 * Dump the passed variables and end the script.
	 *
	 * @param  mixed $args
	 *
	 * @return void
	 */
	function du(...$args) {
		try {
			$debug = @debug_backtrace();
			if (!empty($debug) AND is_array($debug)) {
				$_nl = $debugFiles = "<br>\r\n";
				$debugFiles = "";
				
				$call = @current($debug);
				foreach ($debug as $issues) {
					$_line = isset($issues['line']) ? $issues['line'] : 0;
//                    $debugFiles .= (!empty($debugFiles)?$_nl:"");
//                    $debugFiles .= "[" . @basename($issues['file']) . "] Line ({$_line})";// . $_nl;
				}
			} else throw new \Exception(__LINE__ . " function debug_backtrace() returned: {$debug}");
			$line = (isset($call['line']) ? $call['line'] : __LINE__);
			$file = (isset($call['file']) ? $call['file'] : __FILE__);
			$file = @basename($file);

//            dump( $debugFiles );
			if (App::runningInConsole()) {
				echo(
				"\n\n[{$file}] Line ({$line}): \n"
				);
			} else  $args = \Illuminate\Support\Arr::prepend($args, "{$file}:{$line}");
			
			collect($args)->each(function ($e) {
				dump($e);
			});
			
			if (App::runningInConsole()) {
				echo(
					"\n\n\n :" . __LINE__ . ""
				);
			} else echo(
				"<small>By: \n" . __FILE__ . ":" . __LINE__ . "  \n" . __CLASS__ . "@" . __METHOD__ . "</small>"
			);
//            exit;
		} catch (\Exception $e) {
			if (App::runningInConsole()) {
				echo $msg = (__LINE__ . " ERROR: Function (" . __FUNCTION__ . "), File (" . __FILE__ . "), Line (" . __LINE__ . "): " . $e->getMessage());
			} else
				echo $msg = (__LINE__ . " ERROR: Function (" . __FUNCTION__ . "), File (" . __FILE__ . "), Line (" . __LINE__ . "): " . $e->getMessage());
			dump(
				$e->getMessage(),
				$msg,
				debug_backtrace()
			);
//            exit;
		}

//        die(1);
	}
}
if (!function_exists('d')) {
	/**
	 * Dump the passed variables and end the script.
	 *
	 * @param  mixed $args
	 *
	 * @return void
	 */
	function d(...$args) {
		try {
			$debug = @debug_backtrace();
			if (!empty($debug) AND is_array($debug)) {
				$_nl = $debugFiles = "<br>\r\n";
				$debugFiles = "";
				
				$call = @current($debug);
				foreach ($debug as $issues) {
					$_line = isset($issues['line']) ? $issues['line'] : 0;
//                    $debugFiles .= (!empty($debugFiles)?$_nl:"");
//                    $debugFiles .= "[" . @basename($issues['file']) . "] Line ({$_line})";// . $_nl;
				}
			} else throw new \Exception(__LINE__ . " function debug_backtrace() returned: {$debug}");
			$line = (isset($call['line']) ? $call['line'] : __LINE__);
			$file = (isset($call['file']) ? $call['file'] : __FILE__);
			$file = @basename($file);

//            dump( $debugFiles );
			if (App::runningInConsole()) {
				echo(
				"\n\n[{$file}] Line ({$line}): \n"
				);
			} else echo("[{$file}] Line ({$line}): <br>");
			
			collect($args)->each(function ($e) {
				dump($e);
			});
			
			if (App::runningInConsole()) {
				echo(
					"\n\n\n :" . __LINE__ . ""
				);
			} else echo(
				"<small>By: \n" . __FILE__ . ":" . __LINE__ . "  \n" . __CLASS__ . "@" . __METHOD__ . "</small>"
			);
			exit;
		} catch (\Exception $e) {
			if (App::runningInConsole()) {
				echo $msg = (__LINE__ . " ERROR: Function (" . __FUNCTION__ . "), File (" . __FILE__ . "), Line (" . __LINE__ . "): " . $e->getMessage());
			} else
				echo $msg = (__LINE__ . " ERROR: Function (" . __FUNCTION__ . "), File (" . __FILE__ . "), Line (" . __LINE__ . "): " . $e->getMessage());
			dd(
				$e->getMessage(),
				$msg,
				debug_backtrace()
			);
			exit;
		}
		
		die(1);
	}
}

if (!function_exists('dx')) {
	/**
	 * Dump the passed variables and end the script.
	 *
	 * @param  mixed $args
	 *
	 * @return void
	 */
	function dx(...$args) {
		try {
			$debug = @debug_backtrace();
			if (!empty($debug) AND is_array($debug)) {
				$_nl = $debugFiles = "<br>\r\n";
				$debugFiles = "";
				
				$call = @current($debug);
				foreach ($debug as $issues) {
					$_line = isset($issues['line']) ? $issues['line'] : 0;
//                    $debugFiles .= (!empty($debugFiles)?$_nl:"");
//                    $debugFiles .= "[" . @basename($issues['file']) . "] Line ({$_line})";// . $_nl;
				}
			} else throw new \Exception(__LINE__ . " function debug_backtrace() returned: {$debug}");
			$line = (isset($call['line']) ? $call['line'] : __LINE__);
			$file = (isset($call['file']) ? $call['file'] : __FILE__);
			$file = @basename($file);

//            dump( $debugFiles );
			echo("[{$file}] Line ({$line}): <br>");
			
			collect($args)->each(function ($e) {
				dump($e);
			});
			
			echo(
				"<small>By: \n" . __FILE__ . ":" . __LINE__ . "  \n" . __CLASS__ . "@" . __METHOD__ . "</small>"
			);
		} catch (\Exception $e) {
			echo $msg = (__LINE__ . " ERROR: Function (" . __FUNCTION__ . "), File (" . __FILE__ . "), Line (" . __LINE__ . "): " . $e->getMessage());
			dd(
				$e->getMessage(),
				$msg,
				debug_backtrace()
			);
			exit;
		}
		
	}
}

if (!function_exists('dxx')) {
	/**
	 * Dump the passed variables and end the script.
	 *
	 * @param  mixed $args
	 *
	 * @return void
	 */
	function dxx(...$args) { }
}
