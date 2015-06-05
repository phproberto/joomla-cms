<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Base class for rendering a layout
 *
 * @since  3.5
 */
abstract class JRendererAdapter implements JRendererInterface
{
	/**
	 * Data for the layout
	 *
	 * @var    array
	 * @since  3.5
	 */
	protected $data = array();

	/**
	 * Debug mode enabled?
	 *
	 * @var    boolean
	 * @since  3.5
	 */
	protected $debug = false;

	/**
	 * Layout identifier
	 *
	 * @var  null
	 */
	protected $layoutId = null;

	/**
	 * Configuration array
	 *
	 * @var    \Joomla\Registry\Registry
	 * @since  3.5
	 */
	protected $config;

	/**
	 * Debug information messages
	 *
	 * @var    array
	 * @since  3.5
	 */
	protected $debugMessages = array();

	/**
	 * Constructor.
	 *
	 * @param   mixed  $config  Optional configuration array.
	 *
	 * @since   3.5
	 */
	public function __construct($config = array())
	{
		$this->setConfig($config);
	}

	/**
	 * Add a debug message to the debug messages array
	 *
	 * @param   string  $message  Message to save
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	protected function addDebugMessage($message)
	{
		if (!$this->debug)
		{
			return $this;
		}

		$this->debugMessages[] = $message;
	}

	/**
	 * Function to empty all the options
	 *
	 * @return  JLayoutBase  Instance of $this to allow chaining.
	 *
	 * @since   3.5
	 */
	public function clearConfig()
	{
		$this->config = new Registry;
	}

	/**
	 * Get debug information about a layout
	 *
	 * @param   object  $data  Array with data that will be used into the layout
	 *
	 * @return  string
	 *
	 * @since   3.5
	 */
	public function debug($data = array())
	{
		// Enable debug mode
		$this->debug = true;

		$layoutOutput = $this->render($data);
		$debugMessages = "<pre>" . $this->renderDebugMessages() . "</pre>";

		// Disable debug mode
		$this->debug = false;

		return $debugMessages . $layoutOutput;
	}

	/**
	 * Method to escape output.
	 *
	 * @param   string  $output  The output to escape.
	 *
	 * @return  string  The escaped output.
	 *
	 * @since   3.5
	 */
	public function escape($output)
	{
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}

	/**
	 * Get the renderer configuration
	 *
	 * @return  \Joomla\Registry\Registry
	 *
	 * @since   3.5
	 */
	public function getConfig()
	{
		return $this->config;
	}

	/**
	 * Data getter
	 *
	 * @return  array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Get the debug messages array
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	public function getDebugMessages()
	{
		return $this->debugMessages;
	}

	/**
	 * Get use statistics
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	public static function getStats()
	{
		return JRendererStats::getStats();
	}

	/**
	 * Method to render the layout.
	 *
	 * @param   object  $data  Array with data that will be used into the layout
	 *
	 * @return  string  The necessary HTML to display the layout
	 *
	 * @since   3.5
	 */
	public function render($data)
	{
		$this->data = array_merge($this->data, $data);

		return '';
	}

	/**
	 * Render the list of debug messages
	 *
	 * @return  string  Output text/HTML code
	 *
	 * @since   3.5
	 */
	protected function renderDebugMessages()
	{
		return implode($this->debugMessages, "\n");
	}

	/**
	 * Reset / Clean the debug messages list
	 *
	 * @return  JRendererAdapter
	 *
	 * @since   3.5
	 */
	protected function resetDebugMessages()
	{
		$this->debugMessages = array();

		return $this;
	}

	/**
	 * Method to assign a data property
	 *
	 * @param   string  $property  Property to set
	 * @param   mixed   $value     Value to assign to the property
	 *
	 * @return  JLayoutFile        Return self instance for chaining
	 *
	 * @since   3.5
	 */
	public function set($property, $value)
	{
		$this->data[(string) $property] = $value;

		return $this;
	}

	/**
	 * Set the configuration
	 *
	 * @param   mixed  $config  Array / Registry object with the options to load
	 *
	 * @return  JRendererInterface
	 *
	 * @since   3.5
	 */
	public function setConfig($config = array())
	{
		if ($config instanceof Registry)
		{
			$this->config = $config;

			return $this;
		}

		if (is_array($config))
		{
			$this->config = new Registry($config);

			return $this;
		}

		$this->config = new Registry;

		return $this;
	}

	/**
	 * Data setter
	 *
	 * @param   array  $data  Data that will be used in the layout
	 *
	 * @return  JRendererAdapter
	 *
	 * @since   3.5
	 */
	public function setData(array $data)
	{
		$this->data = $data;
	}

	/**
	 * Change the layout
	 *
	 * @param   string  $layoutId  Layout to render
	 *
	 * @return  JRendererAdapter
	 *
	 * @since   3.5
	 */
	public function setLayout($layoutId)
	{
		$this->layoutId = $layoutId;

		return $this;
	}
}
