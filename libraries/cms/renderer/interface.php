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
 * Interface to handle display layout
 *
 * @see    https://docs.joomla.org/Sharing_layouts_across_views_or_extensions_with_JLayout
 * @since  3.0
 */
interface JRendererInterface
{
	/**
	 * Debug layout rendering information
	 *
	 * @param   object  $data  Array with the data to be rendered
	 *
	 * @return  string  The rendered layout.
	 *
	 * @since   3.5
	 */
	public function debug($data);

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @since   3.5
	 */
	public function escape($output);

	/**
	 * Method to render the layout.
	 *
	 * @param   object  $data  Array with the data to be rendered
	 *
	 * @return  string  The rendered layout.
	 *
	 * @since   3.5
	 */
	public function render($data);
}
