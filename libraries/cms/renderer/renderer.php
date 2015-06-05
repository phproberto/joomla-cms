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
 * Proxy class to ease renderer
 *
 * @since  3.5
 */
class JRenderer
{
	/**
	 * Returns an instance of a renderer
	 *
	 * @param   string  $name    Name of the renderer to use
	 * @param   array   $config  Optional configuration array
	 *
	 * @return  JRendererInterface
	 *
	 * @since   3.5
	 */
	public static function get($name = 'layout', $config = array())
	{
		// Class name directly received?
		if (class_exists($name))
		{
			return new $name($config);
		}

		$adapterName = 'JRendererAdapter' . ucfirst(strtolower($name));

		if (class_exists($adapterName))
		{
			return new $adapterName($config);
		}

		throw new RuntimeException(sprintf('Renderer not found: ', $name, $adapterName), 500);
	}
}
