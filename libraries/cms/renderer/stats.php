<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Renderer statistics class
 *
 * @since  3.5
 */
abstract class JRendererStats
{
	/**
	 * Profiler
	 *
	 * @var  JProfiler
	 */
	static $profiler;

	/**
	 * Statistics
	 *
	 * @var    array
	 * @since  3.5
	 */
	public static $stats = array(
		'fileSearches'        => 0,
		'fileSearchesCached'  => 0,
		'fileSearchesSkipped' => 0,
		'times'               => array(
			'fileSearching'   => 0,
			'fileRendering'   => 0
		),
		'layoutsRendered'     => array()
	);

	/**
	 * Timers for stats/debug
	 *
	 * @var    array
	 * @since  3.5
	 */
	protected static $timers = array();

	/**
	 * [getProfiler description]
	 *
	 * @return  [type]  [description]
	 */
	protected static function getProfiler()
	{
		if (null === static::$profiler)
		{
			static::$profiler = JProfiler::getInstance('JRenderer');
		}

		return static::$profiler;
	}

	/**
	 * [getStats description]
	 *
	 * @return  [type]  [description]
	 */
	public static function getStats()
	{
		return static::$stats;
	}

	/**
	 * Start a timer to track time spent on different tasks
	 *
	 * @param   string  $type  Name of the timer
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public static function startTimer($type)
	{
		if (isset(static::$timers['times'][$type]))
		{
			static::stopTimer($type);
		}

		return static::$timers['times'][$type] = microtime(true);
	}

	/**
	 * Stop an active timer
	 *
	 * @param   string  $type  Name of the timer
	 *
	 * @return  float          Time elapsed with the timer active
	 *
	 * @since   3.5
	 */
	public static function stopTimer($type)
	{
		$timeElapsed = 0;

		// Initialise stats tracker if needed
		if (!isset(static::$stats['times'][$type]))
		{
			static::$stats['times'][$type] = 0;
		}

		if (!empty(static::$timers['times'][$type]))
		{
			static::$stats['times'][$type] += microtime(true) - static::$timers['times'][$type];
			unset(static::$timers['times'][$type]);
		}

		return $timeElapsed;
	}
}
