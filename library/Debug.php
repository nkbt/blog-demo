<?php
//set_error_handler(array('qDebug', 'errorHandler'));
//error_reporting(-1);

defined('DEBUG') || define('DEBUG', true);
defined('QDEBUG_FULL_TRACE') || define('QDEBUG_FULL_TRACE', true);
defined('QDEBUG_DB_PROFILER') || define('QDEBUG_DB_PROFILER', true);
defined('QDEBUG_LOGDIR') || define('QDEBUG_LOGDIR', rtrim(
	dirname(__FILE__) . '\\/'
) . '/../application/logs/application');
class qDebug {


	public static function errorHandler($errno, $errstr, $errfile, $errline) {
		if (($errno != E_ERROR || $errno != E_USER_ERROR)
			&& (strstr($errfile, DIRECTORY_SEPARATOR . 'Zend') !== false
				|| strstr($errfile, 'Autoloader.php') !== false)
		) {
			return true;
		}

		$replace = array(
			'%errno%' => $errno,
			'%errstr%' => $errstr,
			'%errline%' => $errline,
			'%errfile%' => $errfile,
		);

		switch ($errno) {
			case E_ERROR:
			case E_USER_ERROR:
				$replace['%errtype%'] = 'fatal error';
				break;

			case E_WARNING:
			case E_USER_WARNING:
				$replace['%errtype%'] = 'warning';
				break;

			case E_NOTICE:
			case E_USER_NOTICE:
				$replace['%errtype%'] = 'notice';
				break;

			default:
				$replace['%errtype%'] = 'unknown error';
				break;
		}

		$message = '<span class="message">%errtype%</span>: [%errno%] %errstr%<br /><span class="errorType">%errtype%</span> on line <span class="errorLine">%errline%</span> in file %errfile%<br />';

		$message = strtr($message, $replace);
		$tooltip = $message;
		$string = strip_tags(
			str_replace('<', ' <', sprintf('<span class="messageTemplate" title="%s">%s</span>', $tooltip, $message))
		);
        echo $string;
		qDebug::varDumpAndDie($string, $string, true, false);

		return false;
	}


	public static function getDbQueries() {
		$result = '';
		if (!Zend_Registry::isRegistered('dbAdapter')) {
			return $result;
		}
		/** @var Custom_Db_Adapter_Pdo_Mysql $adapter */
		$adapter = Zend_Registry::get('dbAdapter');
		if ($adapter instanceof Zend_Db_Adapter_Abstract) {
			$profiler = $adapter->getProfiler();
			if ($profiler instanceof Zend_Db_Profiler && $profiler->getTotalNumQueries()) {
				$result .= '<h4>';
				$result .= sprintf(
					'Total %d queries executed in %4.4f ms.',
					$profiler->getTotalNumQueries(),
					$profiler->getTotalElapsedSecs() * 1000
				);
				$result .= '</h4>';
				$result .= '<table cellpadding="0" cellspacing="5">';
				/** @var qDebug_Profiler_Query $query */
				foreach ($profiler->getQueryProfiles() as $queryNumber => $query) {
					$result .= '<tr>';
					$result .= '<td class="number">' . sprintf('%03d', $queryNumber) . '</td>';
					$result .= '<td class="time">'
						. sprintf('%02.4f', $query->getElapsedSecs() * 1000)
						. '</td>';
					$result .= '<td class="code">';
					if (isset($_SERVER['HTTP_HOST'])) {
						$result .= qDebug_Highlighter::highlight($query->getQuery());
					} else {
						$result .= $query->getQuery();
					}
					if (method_exists($query, 'getTraceRendered')) {
						$result .= "<br />" . $query->getTraceRendered();
					}
					$result .= '</td>';
					$result .= '<td class="bind">';
					if (isset($_SERVER['HTTP_HOST'])) {
						$result .= $query->getQueryParams()
							? highlight_string(var_export($query->getQueryParams(), true), true)
							: '&nbsp;';
					} else {
						$result .= $query->getQueryParams()
							? var_export($query->getQueryParams(), true)
							: '';
					}
					$result .= '</td>';
					$result .= '</tr>';
				}
				$result .= '</table>';
			}
		}
		return $result;
	}


    public static function getDbQueriesForCli()
    {
        $result = '';
        /** @var Bob_Db_Adapter_Replication $adapter */
        $adapter = Zend_Db_Table::getDefaultAdapter();
        if ($adapter instanceof Zend_Db_Adapter_Abstract) {
            $profiler = $adapter->getWriteAdapter()->getProfiler();
            if ($profiler instanceof Zend_Db_Profiler && $profiler->getTotalNumQueries()) {
                $result .= sprintf(
                    "\nTotal %d queries executed in %4.4f ms.\n",
                    $profiler->getTotalNumQueries(),
                    $profiler->getTotalElapsedSecs() * 1000
                );
                /** @var qDebug_Profiler_Query $query */
                foreach ($profiler->getQueryProfiles() as $queryNumber => $query) {
                    $result .= "\n\n";
                    $result .= sprintf('#%03d) ', $queryNumber);
                    $result .= sprintf('%06.4fms ', $query->getElapsedSecs() * 1000);
                    $result .= $query->getQuery();
                    $result .= "\n";
                    if (method_exists($query, 'getTraceRendered')) {
                        $result .= "\n" . $query->getTraceRendered();
                    }
                    $result .= "\n" . $query->getQueryParams()
                        ? var_export($query->getQueryParams(), true)
                        : '';
                }
            }
        }
		return $result;
	}


	public static function getDebugInfo($fullTrace = QDEBUG_FULL_TRACE, $dbProfiler = QDEBUG_DB_PROFILER) {
		try {
			throw new Exception('Fake!');
		} catch (Exception $exc) {
			$trace = $exc->getTrace();
			$file = isset($trace[1]['file']) ? $trace[1]['file'] : 'unknown';
			$line = isset($trace[1]['line']) ? $trace[1]['line'] : 'unknown';
			$class = isset($trace[2]['class']) ? $trace[2]['class'] : 'unknown';
			$function = isset($trace[2]['function']) ? $trace[2]['function'] : 'unknown';

			if (isset($_SERVER['HTTP_HOST'])) {
				$string = " in file $file:$line, class $class, function $function";
				$fullTraceId = uniqid('qDebug');
				$dbProfilerId = uniqid('qDebug');
				if ($fullTrace) {
					$element = 'document.getElementById(\'' . $fullTraceId . '\')';
					$string .= "\n<a href=\"javascript:void(0)\" onclick=\"$element.style.display = ($element.style.display == 'none') ? 'block' : 'none'; return false;\">Trace</a>";
				}
				if ($dbProfiler) {
					$element = 'document.getElementById(\'' . $dbProfilerId . '\')';
					$string .= "\n<a href=\"javascript:void(0)\" onclick=\"$element.style.display = ($element.style.display == 'none') ? 'block' : 'none'; return false;\">DbQueries</a>";
				}
				if ($fullTrace) {

//                    $trace = $exc->getTraceAsString();
					$trace = '';

					$i = 0;
					foreach ($exc->getTrace() as $traceItem) {
						$args = array();
						foreach ($traceItem['args'] as $arg) {
							if (!empty($arg)) {
								set_error_handler(function() use($arg, &$args) {
									$args[] = mb_strcut(serialize($arg), 0, 500, 'UTF-8');
								});
								$args[] = mb_strcut(json_encode($arg), 0, 500, 'UTF-8');
								restore_error_handler();
							} else {
								$args[] = 'NULL';
							}
						}
						$args = implode(',', $args);

						$type = isset($traceItem['type']) ? $traceItem['type'] : '';
						$function = isset($traceItem['function']) ? $traceItem['function'] : '';
						$class = isset($traceItem['class']) ? $traceItem['class'] : '';
						$line = isset($traceItem['line']) ? $traceItem['line'] : '';
						$file = isset($traceItem['file']) ? $traceItem['file'] : '';

                        $trace .= sprintf("%3d", $i) . " {$file}:{$line} {$class}{$type}{$function}($args)\n ";
						$i++;
					}

					$string .= "\n" . '<pre id="' . $fullTraceId . '" class="trace" style="display: none;">' . 'Full trace:' . "\n" . htmlspecialchars(
						$trace
					) . '</pre>';
				}
				if ($dbProfiler) {
					$string .= "\n" . '<pre id="' . $dbProfilerId . '" class="trace" style="display: none;">' . 'DB Queries:' . "\n" . self::getDbQueries(
					) . '</pre>';
				}
			} else {
				$string = " in file $file:$line, class $class, function $function";
				if ($fullTrace) {
					$string .= "\nTrace\n";


				}
				if ($dbProfiler) {
					$string .= "\nDbQueries\n";
					$string .= self::getDbQueriesForCli();
				}
			}
			return $string;
		}
	}


	public static function getDebugOutput($output, $name = null, $debugInfo) {
		$html = '
			<style type="text/css">
				.qDebug {  position: relative; background: #fff3f3; color: #333; font-family: Consolas !important; /*overflow: auto;*/ border: 2px solid red; margin: 5px 10px; padding: 5px; font-size: 11px; line-height: 110%; }
				.qDebug * { font-family: Consolas !important; font-size: 11px !important; line-height: 110% !important;}
				.qDebug a { color: #06c !important;}
				.qDebug td { vertical-align: top; padding-right: 3px;}
				.qDebug .closeButton { position: absolute; width: 16px; height: 16px; top: 0; right: 0; background-image: url("/img/controls/delete.png"); opacity: 0.9; }
				.qDebug .message { text-transform: uppercase; font-weight: bold; }
				.qDebug .messageTemplate { background: #f00; color: #fff; font-size: 16px; line-height: 18px; }
				.qDebug .lineNumber { color: #00f; font-weight: bold; font-size: 120%; }
				.qDebug .name { color: #f00; font-weight: bold; font-size: 120%; }
				.qDebug .output { font-size: 12px; line-height: 100%; padding-top: 10px;  white-space: pre-wrap; }
				.qDebug .output pre { white-space: pre-wrap; }
				.qDebug .trace { min-width: 2000px; white-space: pre-wrap; }
				.qDebug .errorType { text-transform: capitalize; }
				.qDebug .errorLine { color: #000; font-weight: bold; }
				.qDebug small { display: inline; color: inherit; font-size: inherit; }
				.qDebug small:before { content: inherit; }
			</style>
		';
		$html .= '<a href="javascript:void(0)" onclick="$(this).up(\'div\').hide(); return false;" class="closeButton"></a>';
		if (!is_null($name)) {
			$html .= sprintf('<span class="name">%s</span>', $name);
		}
		$html .= $debugInfo;
        if (!is_null($output)) {
            $html .= sprintf('<div class="output">%s</div>', $output);
        }
        if (!is_null($html)) {
            $html = sprintf('<div class="qDebug">%s</div>', $html);
        }
        return $html;
    }


	public static function varExportAndDie($result, $name = null, $fullTrace = QDEBUG_FULL_TRACE, $dbProfiler = QDEBUG_DB_PROFILER) {
		if (!DEBUG) {
			return;
		}
		echo "\n";
		echo self::getDebugOutput(
			highlight_string(var_export($result, true), true), $name, self::getDebugInfo($fullTrace, $dbProfiler));
		echo "\n";
		die();
	}


	public static function varExport($result, $name = null, $fullTrace = QDEBUG_FULL_TRACE, $dbProfiler = QDEBUG_DB_PROFILER) {
		if (!DEBUG) {
			return;
		}
		echo self::getDebugOutput(
			highlight_string(var_export($result, true), true), $name, self::getDebugInfo($fullTrace, $dbProfiler));
		echo "\n";
	}


	public static function varExportSimpleAndDie($result, $name = null, $fullTrace = QDEBUG_FULL_TRACE, $dbProfiler = QDEBUG_DB_PROFILER) {
		if (!DEBUG) {
			return;
		}
		echo "\n\n" . strip_tags($name), strip_tags(self::getDebugInfo($fullTrace, $dbProfiler)), "\n\n";
		var_export($result);
		echo "\n";
		die();
	}

	public static function varLog($result, $name = null,
		$fullTrace = QDEBUG_FULL_TRACE, $dbProfiler = QDEBUG_DB_PROFILER,
		$noMeta = false)
	{
		if (!DEBUG) {
			return;
		}
		$file = QDEBUG_LOGDIR . '/qDebug-' . date("Ymd") . '.log';
		$content = "\n";
		$content .= date("Ymd-His"). ": ";
		if ($name) {
			$content .= strip_tags($name);
		}
		if (!$noMeta) {
			$content .= strip_tags(self::getDebugInfo($fullTrace, $dbProfiler)) . "\n";
		}
		$content .= var_export($result, true) . "\n";
		file_put_contents($file, $content, FILE_APPEND);
	}

	public static function varLogAndDie($result, $name = null,
		$fullTrace = QDEBUG_FULL_TRACE, $dbProfiler = QDEBUG_DB_PROFILER,
		$noMeta = false)
	{
		self::varLog($result, $name, $fullTrace, $dbProfiler, $noMeta);
		die();
	}


	public static function varExportSimple($result, $name = null,
		$fullTrace = QDEBUG_FULL_TRACE, $dbProfiler = QDEBUG_DB_PROFILER)
	{
		if (!DEBUG) {
			return;
		}
		echo "\n\n" . strip_tags($name), strip_tags(self::getDebugInfo($fullTrace, $dbProfiler)), "\n\n";
		var_export($result);
		echo "\n";
	}


	public static function varDumpAndDie($result, $name = null, $fullTrace = QDEBUG_FULL_TRACE, $dbProfiler = QDEBUG_DB_PROFILER) {
		if (!DEBUG) {
			return;
		}
		ob_start();
		var_dump($result);
		$result = ob_get_clean();
		echo self::getDebugOutput($result, $name, self::getDebugInfo($fullTrace, $dbProfiler), $fullTrace);
		echo "<br />";
		die();
	}


	public static function varDump($result, $name = null, $fullTrace = QDEBUG_FULL_TRACE, $dbProfiler = QDEBUG_DB_PROFILER) {
		if (!DEBUG) {
			return;
		}
		ob_start();
		var_dump($result);
		$result = ob_get_clean();
		echo self::getDebugOutput($result, $name, self::getDebugInfo($fullTrace, $dbProfiler));
		echo "<br />";
	}

    const TIMER_TYPE_WEB = 'web';
    const TIMER_TYPE_CMD = 'cmd';
    protected  static $_timer = array();
    public static $timerType = self::TIMER_TYPE_WEB;
    public static function timerStart($ident = null, $name = null, $fullTrace = QDEBUG_FULL_TRACE, $dbProfiler = QDEBUG_DB_PROFILER) {
        self::$_timer[$ident] = array(microtime(1), $name);
        if (self::$timerType == self::TIMER_TYPE_WEB) {
            echo self::getDebugOutput("Start " . is_null($name) ? $ident : $name, null, self::getDebugInfo($fullTrace, $dbProfiler));
        } else {
            echo "\n\nStart: " , is_null($name) ? $ident : $name, "\n";
        }
    }


    public static function timerEnd($ident = null, $fullTrace = QDEBUG_FULL_TRACE, $dbProfiler = QDEBUG_DB_PROFILER) {
        $timerEnd = microtime(1);
        reset(self::$_timer);
        $firstTimer = current(self::$_timer);
        if (isset(self::$_timer[$ident])) {
            $result = sprintf(
                "Completed in: %4.4f s (cumulative: %4.4f s), Memory commit: %dK (peak: %dK) of %s",
                ($timerEnd - self::$_timer[$ident][0]),
                ($timerEnd - $firstTimer[0]),
                round(memory_get_usage() / 1024),
                round(memory_get_peak_usage() / 1024),
                ini_get("memory_limit")
            );
        } else {
            $result = 'Timer was not started';
        }
        if (self::$timerType == self::TIMER_TYPE_WEB) {
            echo self::getDebugOutput($result, self::$_timer[$ident][1], self::getDebugInfo($fullTrace, $dbProfiler));
        } else {
            echo self::$_timer[$ident][1], "\n", $result, "\n";
        }

    }

	public static function fbLog($result, $name = null, $fullTrace = true, $dbProfiler = false) {
		if (!DEBUG) {
			return;
		}

		if (Zend_Registry::isRegistered('FirePhpLogger')) {
			$logger = Zend_Registry::get('FirePhpLogger');
		} else {
			//			require_once 'Zend/Log/Writer/Firebug.php';
			//			require_once 'Zend/Log.php';
			$writer = new Zend_Log_Writer_Firebug();
			$logger = new Zend_Log($writer);
			Zend_Registry::set('FirePhpLogger', $logger);
		}

		if (is_null($name) && !$fullTrace) {
			$message = $result;
		} else {
			$message = array(
				'Var' => $name,
				'Value' => $result,
			);
			if ($fullTrace) {
				$message['Trace'] = strip_tags(self::getDebugInfo($fullTrace, $dbProfiler));
			}
		}

		$logger->debug($message, Zend_Log::DEBUG);
	}

}

require_once ZEND_ROOT . '/Zend/Db/Profiler.php';
class qDebug_Profiler extends Zend_Db_Profiler
{

    public function queryStart($queryText, $queryType = null)
    {
        if (!$this->_enabled) {
            return null;
        }

        // make sure we have a query type
        if (null === $queryType) {
            switch (strtolower(substr(ltrim($queryText), 0, 6))) {
                case 'insert':
                    $queryType = self::INSERT;
                    break;
                case 'update':
                    $queryType = self::UPDATE;
                    break;
                case 'delete':
                    $queryType = self::DELETE;
                    break;
                case 'select':
                    $queryType = self::SELECT;
                    break;
                default:
                    $queryType = self::QUERY;
                    break;
            }
        }

        /**
         * @see Zend_Db_Profiler_Query
         */
        require_once 'Zend/Db/Profiler/Query.php';
        $this->_queryProfiles[] = new qDebug_Profiler_Query($queryText, $queryType);

        end($this->_queryProfiles);

        return key($this->_queryProfiles);
    }

}

require_once 'Zend/Db/Profiler/Query.php';
class qDebug_Profiler_Query extends Zend_Db_Profiler_Query {

	protected $_traceAsString;
	protected $_trace;

	public function __construct($query, $queryType) {
		try {
			throw new Exception('Fake!');
		} catch (Exception $exc) {
			$this->_trace = $exc->getTrace();
			$this->_traceAsString = $exc->getTraceAsString();
		}
		parent::__construct($query, $queryType);
	}

	public function getTraceAsString() {
		return $this->_traceAsString;
	}


	public function getTrace() {
		return $this->_trace;
	}


	public function getTraceRendered() {
		$trace = $this->getTrace();
		$fullTraceId = uniqid('qDebug');
		$element = 'document.getElementById(\'' . $fullTraceId . '\')';
		$string = "\n<a style=\"color: #ccc; font-size: 80%; text-decoration: none; \" href=\"javascript:void(0)\" onclick=\"$element.style.display = ($element.style.display == 'none') ? 'block' : 'none'; return false;\">Trace</a>";
		$string .= "\n" . '<pre id="' . $fullTraceId . '" class="trace" style="display: none;">' . 'Full trace:' . "\n" . htmlspecialchars(
			$this->getTraceAsString()
		) . '</pre>';

		return $string;
	}


}


/*********************************************************************
 * Highlighter class - highlights SQL with preg and some compromises
 *
 * @Author    dzver <dzver@abv.bg>
 * @Copyright GNU v 3.0
 *********************************************************************/

class qDebug_Highlighter {


	/*
		protected $colors - key order is important because of highlighting < and >
		chars and not encoding them to &lt; and &gt;
	  */
	protected static $colors = array(
		//		'bind' => 'green; font-weight: bold;',
		'chars' => 'grey',
		'keywords' => 'blue',
		'joins' => 'gray',
		'functions' => 'violet',
		'constants' => 'red',
	);
	/*
		lists are not complete.
	  */
	protected static $words = array(
		'bind' => '/(:\\w+)/i',
		'keywords' => array(
			'SELECT', 'UPDATE', 'INSERT', 'DELETE', 'REPLACE', 'INTO', 'CREATE', 'ALTER', 'TABLE', 'DROP', 'TRUNCATE',
			'FROM',
			'ADD', 'CHANGE', 'COLUMN', 'KEY',
			'WHERE', 'ON', 'CASE', 'WHEN', 'THEN', 'END', 'ELSE', 'AS',
			'USING', 'USE', 'INDEX', 'CONSTRAINT', 'REFERENCES', 'DUPLICATE',
			'LIMIT', 'OFFSET', 'SET', 'SHOW', 'STATUS',
			'BETWEEN', 'AND', 'IS', 'NOT', 'OR', 'XOR', 'INTERVAL', 'TOP',
			'GROUP BY', 'ORDER BY', 'DESC', 'ASC', 'COLLATE', 'NAMES', 'UTF8', 'DISTINCT', 'DATABASE',
			'CALC_FOUND_ROWS', 'SQL_NO_CACHE', 'MATCH', 'AGAINST', 'LIKE', 'REGEXP', 'RLIKE',
			'PRIMARY', 'AUTO_INCREMENT', 'DEFAULT', 'IDENTITY', 'VALUES', 'PROCEDURE', 'FUNCTION',
			'TRAN', 'TRANSACTION', 'COMMIT', 'ROLLBACK', 'SAVEPOINT', 'TRIGGER', 'CASCADE',
			'DECLARE', 'CURSOR', 'FOR', 'DEALLOCATE'
		),
		'joins' => array('JOIN', 'INNER', 'OUTER', 'FULL', 'NATURAL', 'LEFT', 'RIGHT'),
		'chars' => '/([\\.,\\(\\)<>=`]+)/i',
		'functions' => array(
			'MIN', 'MAX', 'SUM', 'COUNT', 'AVG', 'CAST', 'COALESCE', 'CHAR_LENGTH', 'LENGTH', 'SUBSTRING',
			'DAY', 'MONTH', 'YEAR', 'DATE_FORMAT', 'CRC32', 'CURDATE', 'SYSDATE', 'NOW', 'GETDATE',
			'FROM_UNIXTIME', 'FROM_DAYS', 'TO_DAYS', 'HOUR', 'IFNULL', 'ISNULL', 'NVL', 'NVL2',
			'INET_ATON', 'INET_NTOA', 'INSTR', 'FOUND_ROWS',
			'LAST_INSERT_ID', 'LCASE', 'LOWER', 'UCASE', 'UPPER',
			'LPAD', 'RPAD', 'RTRIM', 'LTRIM',
			'MD5', 'MINUTE', 'ROUND',
			'SECOND', 'SHA1', 'STDDEV', 'STR_TO_DATE', 'WEEK'
		),
		'constants' => '/(\'[^\']*\'|[0-9]+)/i',
	);


	public static function highlight($sql) {
		$sql = str_replace('\\\'', '\\&#039;', $sql);

		foreach (self::$colors as $key => $color) {
			if (in_array($key, array('constants', 'chars', 'bind'))) {
				$regexp = self::$words[$key];
			} else {
				$regexp = '/\\b(' . join("|", self::$words[$key]) . ')\\b/i';
			}
			$sql = preg_replace($regexp, '<span style="color:' . $color . "\">$1</span>", $sql);
		}

		return $sql;
	}
}
