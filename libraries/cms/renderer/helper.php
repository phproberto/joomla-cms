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
 * Helper to fastly render a layout using the application renderer
 *
 * @since  3.5
 */
class JRendererHelper
{
	/**
	 * [getRenderer description]
	 *
	 * @param   string  $layoutId  Dot separated path to the layout file, relative to base path
	 * @param   mixed   $config    Optional custom options to load. Registry or array format
	 *
	 * @return  JRendererInterface
	 */
	protected static function getRenderer($layoutId, $config = array())
	{
		/** @var JRendererInterface */
		$renderer = JFactory::getApplication()->getRenderer();
		$renderer->setLayout($layoutId);

		if ($config)
		{
			$renderer->setConfig($config);
		}

		return $renderer;
	}

	/**
	 * Method to render the layout.
	 *
	 * @param   string  $layoutId  Dot separated path to the layout file, relative to base path
	 * @param   object  $data      Array of data for the renderer
	 * @param   mixed   $config    Optional custom options to load. Registry or array format
	 *
	 * @return  string
	 *
	 * @since   3.5
	 */
	public static function render($layoutId, $data = array(), $config = array())
	{
		/** @var JRenderer */
		$renderer = static::getRenderer($layoutId, $config);

		return $renderer->render($data);
	}

	/**
	 * Fast function to debug a layout
	 *
	 * @param   string  $layoutId  Dot separated path to the layout file, relative to base path
	 * @param   object  $data      Array of data for the renderer
	 * @param   mixed   $config    Optional custom options to load. Registry or array format
	 *
	 * @return  sring
	 */
	public static function debug($layoutId, $data = array(), $config = array())
	{
		$renderer = static::getRenderer($layoutId, $config);

		return $renderer->debug($data);
	}
}
