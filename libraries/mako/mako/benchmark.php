<?php

namespace mako
{
	use \mako\benchmark\Exception as BenchmarkException;
	
	/**
	* Simple benchmarking/timer class.
	*
	* @author     Frederic G. Østby
	* @copyright  (c) 2008-2011 Frederic G. Østby
	* @license    http://www.makoframework.com/license
	*/

	class Benchmark
	{
		//---------------------------------------------
		// Class variables
		//---------------------------------------------

		/**
		* Array of benchmarks.
		*/

		protected static $benchmarks = array();

		//---------------------------------------------
		// Class constructor, destructor etc ...
		//---------------------------------------------

		/**
		* Protected constructor since this is a static class.
		*/

		protected function __construct()
		{
			// Nothing here
		}

		//---------------------------------------------
		// Class methods
		//---------------------------------------------

		/**
		* Start a benchmark.
		*
		* @access  public
		* @param   string  Benchmark name
		*/

		public static function start($name)
		{
			static::$benchmarks[$name] = array
			(
				'start'        => microtime(true),
				'stop'         => false
			);
		}

		/**
		* Stop a benchmark.
		*
		* @access  public
		*/

		public static function stop($name)
		{
			static::$benchmarks[$name]['stop'] = microtime(true);
		}

		/**
		* Get the elapsed time in seconds.
		*
		* @access  public
		* @param   string  Benchmark name
		* @param   int     (optional) Benchmark precision
		* @return  int
		*/

		public static function get($name, $precision = 4)
		{
			if(isset(static::$benchmarks[$name]['start']) === false)
			{
				return false;
			}

			if(static::$benchmarks[$name]['stop'] === false)
			{
				throw new BenchmarkException(__CLASS__.": The '{$name}' benchmark has not been stopped.");
			}

			return round(static::$benchmarks[$name]['stop'] - static::$benchmarks[$name]['start'], $precision);
		}

		/**
		* Returns an array containing all the benchmarks.
		*
		* @access  public
		* @param   int     (optional) Benchmark precision
		* @return  array
		*/

		public static function getAll($precision = 4)
		{
			$benchmarks = array();

			foreach(static::$benchmarks as $k => $v)
			{
				$benchmarks[$k] = static::get($k, $precision);
			}

			return $benchmarks;
		}

		/**
		* Returns the sum of all the benchmark times.
		*
		* @access  public
		* @param   int     (optional) Benchmark precision
		* @return  int
		*/

		public static function totalTime($precision = 4)
		{
			return array_sum(static::getAll($precision));
		}
	}	
}

/** -------------------- End of file --------------------**/