<?php
/**
 * @package Swiftlet
 * @copyright 2009 ElbertF http://elbertf.com
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU Public License
 */

if ( !isset($swiftlet) ) die('Direct access to this file is not allowed');

/**
 * Plugin
 * @abstract
 */
class Plugin
{
	public
		$name,
		$description,
		$version,
		$compatible   = array('from' => '', 'to' => ''),
		$dependencies = array(),
		$hooks        = array(),
		$ready        = FALSE
		;

	protected
		$app
		;

	/**
	 * Initialize
	 * @param object $app
	 * @param string $name
	 */
	function __construct($app, $name)
	{
		$this->app  = $app;
		$this->name = $name;

		if ( !$this->version )
		{
			$app->error(FALSE, 'No version number provided for plugin `' . $name . '`.', __FILE__, __LINE__);
		}

		/**
		 * Check if the plugin is compatible with this version of Swiftlet
		 */
		if ( !$this->compatible['from'] || !$this->compatible['to'] )
		{
			$app->error(FALSE, 'No compatibility information provided for plugin `' . $name . '`.', __FILE__, __LINE__);
		}

		if ( version_compare(Application::VERSION, str_replace('*', '99999', $this->compatible['from']), '<') || version_compare(Application::VERSION, str_replace('*', '99999', $this->compatible['to']), '>') )
		{
			$app->error(FALSE, 'Plugin `' . $name . '` is designed for ' . ( $this->compatible['from'] == $this->compatible['to'] ? 'version ' . $this->info['compatible']['from'] : 'versions ' . $compatible['from'] . ' to ' . $compatible['to'] ) . ' of Swiftlet (running version ' . Model::VERSION . ')', __FILE__, __LINE__);
		}

		if ( $this->hooks )
		{
			$app->hook_register($name, $this->hooks);
		}
	}

	/**
	 * Hook the plugin
	 * @param string hook
	 */
	function hook($hook, $order, &$params = array())
	{
		$app = $this->app;

		$timerStart = $app->timer_start();

		require($this->info['file']);

		$app->pluginsHooked[$this->info['name']][$hook] = TRUE;

		$app->debugOutput['plugins hooked']['hook: ' . $hook][] = array(
			'order'          => $order,
			'plugin'         => $this->info['name'] . ' (' . $this->info['file'] . ')',
			'execution time' => $app->timer_end($timerStart)
			);
	}

	/**
	 * Get version number of an installed plugin
	 */
	function get_version()
	{
		if ( !empty($this->app->db->ready) && in_array($this->app->db->prefix . 'versions', $this->app->db->tables) )
		{
			$this->app->db->sql('
				SELECT
					`version`
				FROM `' . $this->app->db->prefix . 'versions`
				WHERE
					`plugin` = "' . $this->name . '"
				LIMIT 1
				;');

			if ( isset($this->app->db->result[0]) && $r = $this->app->db->result[0] )
			{
				return $r['version'];
			}
		}
	}
}
