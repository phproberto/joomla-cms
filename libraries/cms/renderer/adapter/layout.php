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
 * JLayout based renderer
 *
 * @since  3.5
 */
class JRendererAdapterLayout extends JRendererAdapter implements JRendererInterface
{
	/**
	 * @var    string  Full path to actual layout files, after possible template override check
	 * @since  3.5
	 */
	protected $fullPath = null;

	/**
	 * Paths to search for layouts
	 *
	 * @var    array
	 * @since  3.5
	 */
	protected $searchPaths = array();

	/**
	 * Have includePaths been already prefixed?
	 *
	 * @var    boolean
	 * @since  3.5
	 */
	protected $isPrefixed = false;

	/**
	 * Cache to avoid duplicated file searches
	 *
	 * @var    array
	 * @since  3.5
	 */
	protected static $cache = array();

	/**
	 * Prefixes to be used in layout search
	 *
	 * @var    array
	 * @since  3.5
	 */
	protected $prefixes = array();

	/**
	 * Suffixes to be used in layout search
	 *
	 * @var    array
	 * @since  3.5
	 */
	protected $suffixes = array();

	/**
	 * Clear any suffix set
	 *
	 * @return  JRendererAdapterLayout
	 *
	 * @since   3.5
	 */
	public function clearPrefixes()
	{
		$this->prefixes = array();

		return $this;
	}

	/**
	 * Empties the search paths array
	 *
	 * @return  JRendererAdapterLayout
	 *
	 * @since   3.5
	 */
	public function clearSearchPaths()
	{
		$this->includePaths = array();

		// Reset options to re-calculate new path
		$this->isPrefixed = false;
		$this->fullPath = null;

		return $this;
	}

	/**
	 * Clear any suffix set
	 *
	 * @return  JRendererAdapterLayout
	 *
	 * @since   3.5
	 */
	public function clearSuffixes()
	{
		$this->suffixes = array();

		return $this;
	}

	/**
	 * Render the layout.
	 *
	 * @param   object  $data  Array which properties are used inside the layout file to build displayed output
	 *
	 * @return  string  Layout HTML code
	 *
	 * @since   3.5
	 */
	public function render($data = array())
	{
		$layoutOutput = parent::render($data);

		$displayData = $this->data;

		// Check possible overrides, and build the full path to layout file
		if ($path = $this->getPath())
		{
			JRendererStats::startTimer('fileRendering');
			JRendererStats::startTimer($path);

			// If there exists such a layout file, include it and collect its output
			if (file_exists($path))
			{
				// Initialise counter for this layout
				if (!array_key_exists($path, JRendererStats::$stats['layoutsRendered']))
				{
					JRendererStats::$stats['layoutsRendered'][$path] = 0;
				}

				ob_start();
				include $path;
				$layoutOutput = ob_get_contents();
				ob_end_clean();
				JRendererStats::stopTimer($path);
				++JRendererStats::$stats['layoutsRendered'][$path];
			}

			JRendererStats::stopTimer('fileRendering');
		}

		return $layoutOutput;
	}

	/**
	 * Get the list of include paths
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	public function getSearchPaths()
	{
		return $this->searchPaths;
	}

	/**
	 * Method to finds the full real file path, checking possible overrides
	 *
	 * @return  string  The full path to the layout file
	 *
	 * @since   3.5
	 */
	protected function getPath()
	{
		if (empty($this->searchPaths))
		{
			return null;
		}

		JLoader::import('joomla.filesystem.path');

		++JRendererStats::$stats['fileSearches'];

		if ($this->fullPath || empty($this->layoutId))
		{
			++static::$stats['fileSearchesSkipped'];

			return $this->fullPath;
		}

		JRendererStats::startTimer('fileSearching');

		$hash = md5(
			json_encode(
				array(
					'paths' => $this->searchPaths,
					'prefixes' => $this->prefixes,
					'suffixes' => $this->suffixes
				)
			)
		);

		if (!empty(static::$cache[$this->layoutId][$hash]))
		{
			++JRendererStats::$stats['fileSearchesCached'];
			JRendererStats::stopTimer('fileSearching');
			$this->addDebugMessage('<strong>Cached path:</strong> ' . static::$cache[$this->layoutId][$hash]);

			return static::$cache[$this->layoutId][$hash];
		}

		$this->addDebugMessage('<strong>Layout:</strong> ' . $this->layoutId);

		// Refresh prefixes
		if ($this->prefixes && !$this->isPrefixed)
		{
			$this->addDebugMessage('<strong>Prefixes:</strong> ' . print_r($this->prefixes, true));
			$this->prefixSearchPaths();
		}

		$this->addDebugMessage('<strong>Include Paths:</strong> ' . print_r($this->searchPaths, true));

		// Search for suffixed versions. Example: tags.j31.php
		if ($this->suffixes)
		{
			$this->addDebugMessage('<strong>Suffixes:</strong> ' . print_r($this->suffixes, true));

			foreach ($this->suffixes as $suffix)
			{
				$rawPath  = str_replace('.', '/', $this->layoutId) . '.' . $suffix . '.php';
				$this->addDebugMessage('<strong>Searching layout for:</strong> ' . $rawPath);

				if ($this->fullPath = JPath::find($this->searchPaths, $rawPath))
				{
					$this->addDebugMessage('<strong>Found layout:</strong> ' . $this->fullPath);
					JRendererStats::stopTimer('fileSearching');

					return static::$cache[$this->layoutId][$hash] = $this->fullPath;
				}
			}
		}

		// Standard version
		$rawPath  = str_replace('.', '/', $this->layoutId) . '.php';
		$this->addDebugMessage('<strong>Searching layout for:</strong> ' . $rawPath);
		$this->fullPath = JPath::find($this->searchPaths, $rawPath);

		if ($this->fullPath = JPath::find($this->searchPaths, $rawPath))
		{
			$this->addDebugMessage('<strong>Found layout:</strong> ' . $this->fullPath);
		}
		else
		{
			$this->addDebugMessage('<strong>Unable to find layout: </strong> ' . $this->layoutId);
		}

		JRendererStats::stopTimer('fileSearching');

		return static::$cache[$this->layoutId][$hash] = $this->fullPath;
	}

	/**
	 * Add one path to include in layout search. Proxy of addSearchPaths()
	 *
	 * @param   string  $path  The path to search for layouts
	 *
	 * @return  JRendererAdapterLayout
	 *
	 * @since   3.5
	 */
	public function addSearchPath($path)
	{
		$this->addSearchPaths($path);

		return $this;
	}

	/**
	 * Add one or more paths to include in layout search
	 *
	 * @param   string  $paths  The path or array of paths to search for layouts
	 *
	 * @return  JRendererAdapterLayout
	 *
	 * @since   3.5
	 */
	public function addSearchPaths($paths)
	{
		if (!empty($paths))
		{
			if (is_array($paths))
			{
				$this->searchPaths = array_unique(array_merge($paths, $this->searchPaths));
			}
			else
			{
				array_unshift($this->searchPaths, $paths);
			}
		}

		return $this;
	}

	/**
	 * Load the automatically generated language suffixes. Example: array('es-ES', 'es', 'ltr')
	 *
	 * @return  JRendererAdapterLayout
	 */
	public function loadLanguageSuffixes()
	{
		$lang = JFactory::getLanguage();
		$langTag = $lang->getTag();
		$langParts = explode('-', $langTag);
		$suffixes = array($langTag, $langParts[0]);
		$suffixes[] = $lang->isRTL() ? 'rtl' : 'ltr';

		$this->suffixes = $suffixes;

		return $this;
	}

	/**
	 * Load the automatically generated version suffixes. Example: array('j311', 'j31', 'j3')
	 *
	 * @return  JRendererAdapterLayout
	 */
	public function loadVersionSuffixes()
	{
		$cmsVersion = new JVersion;

		// Example j311
		$fullVersion = 'j' . str_replace('.', '', $cmsVersion->getShortVersion());

		// Create suffixes like array('j311', 'j31', 'j3')
		$suffixes = array(
			$fullVersion,
			substr($fullVersion, 0, 3),
			substr($fullVersion, 0, 2),
		);

		$this->suffixes = array_unique($suffixes);

		return $this;
	}

	/**
	 * Refresh the list of include paths
	 *
	 * @return  JRendererAdapterLayout
	 *
	 * @since   3.5
	 */
	protected function prefixSearchPaths()
	{
		$prefixedPaths = array();

		foreach ($this->searchPaths as $searchPath)
		{
			$prefixedPaths = array_merge($prefixedPaths, $this->prefixPath($searchPath));
		}

		$this->searchPaths = array_merge($prefixedPaths, $this->searchPaths);
		$this->isPrefixed = true;

		return $this;
	}

	/**
	 * Method to prefix a path
	 *
	 * @param   string  $path  Path to prefix
	 *
	 * @return  array          Prefixed routes
	 *
	 * @since   3.5
	 */
	protected function prefixPath($path)
	{
		$paths = array();

		if ($path && $this->prefixes && !in_array('none', $this->prefixes))
		{
			foreach ($this->prefixes as $prefix)
			{
				$paths[] = $path . '/' . $prefix;
			}
		}

		return $paths;
	}

	/**
	 * Remove one path from the layout search
	 *
	 * @param   string  $path  The path to remove from the layout search
	 *
	 * @return  JRendererAdapterLayout
	 *
	 * @since   3.5
	 */
	public function removeSearchPath($path)
	{
		$this->removeSearchPaths($path);

		return $this;
	}

	/**
	 * Remove one or more paths to exclude in layout search
	 *
	 * @param   string  $paths  The path or array of paths to remove for the layout search
	 *
	 * @return  JRendererAdapterLayout
	 *
	 * @since   3.5
	 */
	public function removeSearchPaths($paths)
	{
		if (!empty($paths))
		{
			$paths = (array) $paths;

			$this->searchPaths = array_diff($this->searchPaths, $paths);
		}

		return $this;
	}

	/**
	 * Method to set the include paths
	 *
	 * @param   mixed  $paths  Single path or array of paths
	 *
	 * @return  JRendererAdapterLayout
	 *
	 * @since   3.5
	 */
	public function setSearchPaths($paths)
	{
		$this->searchPaths = (array) $paths;

		// Refresh prefixes for the new include paths
		$this->isPrefixed = false;

		return $this;
	}

	/**
	 * Use prefixes to search layouts in different main folders
	 *
	 * @param   mixed  $prefixes  String with a single prefix or array or prefixes
	 *
	 * @return  JRendererAdapterLayout
	 *
	 * @since   3.5
	 */
	public function setPrefixes($prefixes)
	{
		if (empty($prefixes))
		{
			return $this;
		}

		$this->prefixes = (array) $prefixes;

		// Refresh prefixes on render
		$this->isPrefixed = false;
		$this->fullPath = null;

		return $this;
	}

	/**
	 * Set suffixes to search layouts
	 *
	 * @param   mixed  $suffixes  String with a single suffix or 'auto' | 'none' or array of suffixes
	 *
	 * @return  JRendererAdapterLayout
	 *
	 * @since   3.5
	 */
	public function setSuffixes($suffixes)
	{
		if (empty($suffixes))
		{
			return $this;
		}

		// Force path recalculation
		$this->fullPath = null;

		$this->suffixes = (array) $suffixes;

		return $this;
	}

	/**
	 * Change the layout
	 *
	 * @param   string  $layoutId  Layout to render
	 *
	 * @return  JRendererAdapterLayout
	 *
	 * @since   3.5
	 */
	public function setLayout($layoutId)
	{
		parent::setLayout($layoutId);

		$this->fullPath = null;

		return $this;
	}

	/**
	 * Render a layout with the same include paths & options
	 *
	 * @param   object  $layoutId  Object which properties are used inside the layout file to build displayed output
	 * @param   mixed   $data      Data to be rendered
	 *
	 * @return  string  The necessary HTML to display the layout
	 *
	 * @since   3.5
	 */
	public function sublayout($layoutId, $data)
	{
		// Sublayouts are searched in a subfolder with the name of the current layout
		if (!empty($this->layoutId))
		{
			$layoutId = $this->layoutId . '.' . (string) $layoutId;
		}

		$sublayout = new static;
		$sublayout->setLayout($layoutId)
			->setOptions($this->options)
			->setSearchPaths($this->searchPaths);

		return $sublayout->render($data);
	}
}
