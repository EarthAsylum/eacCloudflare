<?php
namespace EarthAsylumConsulting;

/**
 * Add {eac}Cloudflare extension to {eac}Doojigger
 *
 * @category	WordPress Plugin
 * @package 	{eac}Cloudflare\{eac}Doojigger Extensions
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2026 EarthAsylum Consulting <www.earthasylum.com>
 * @link		https://eacdoojigger.earthasylum.com/eacCloudflare/
 * @link 		https://github.com/EarthAsylum/eacCloudflare
 *
 * @wordpress-plugin
 * Plugin Name:			{eac}Cloudflare
 * Description:			{eac}Cloudflare Provides limited control of Cloudflare settings, caching, blacklisting, and rate-limiting from WordPress.
 * Version:				2.0.0
 * Requires at least:	5.8
 * Tested up to:		7.1
 * Requires PHP:		8.1
 * Plugin URI:          https://eacdoojigger.earthasylum.com/eacCloudflare/
 * Update URI: 			https://eacdoojigger.earthasylum.com/software-updates/eaccloudflare.json
 * Author:				EarthAsylum Consulting
 * Author URI:			http://www.earthasylum.com
 * License: 			GPLv3 or later
 * License URI: 		https://www.gnu.org/licenses/gpl.html
 */

if (!defined( 'EACDOOJIGGER_VERSION' ) )
{
	require_once 'dependency.php';
	return dependency::notice([
		'plugin'	=>	[ '{eac}Cloudflare' 	=> plugin_basename( __FILE__ ) ],
		'requires'	=> 	[ '{eac}Doojigger' 		=> 'eacDoojigger/eacDoojigger.php' ],
		'manifest'	=> 'https://eacdoojigger.earthasylum.com/software-updates/eacdoojigger.json',
		'after'		=> self_admin_url('/admin.php?page=eacdoojigger-settings&tab=registration')
	]);
}

class eacCloudflare
{
	/**
	 * constructor method
	 *
	 * @return	void
	 */
	public function __construct()
	{
		/**
		 * {pluginname}_load_extensions - get the extensions directory to load
		 *
		 * @param 	array	$extensionDirectories - array of [plugin_slug => plugin_directory]
		 * @return	array	updated $extensionDirectories
		 */
		add_filter( 'eacDoojigger_load_extensions',	function($extensionDirectories)
			{
				if (is_admin())
				{
					/*
					 * Enable update notice (self hosted or wp hosted)
					 */
					eacDoojigger::loadPluginUpdater(__FILE__,'self');

					/*
					 * Add links on plugins page
					 */
					add_filter( (is_network_admin() ? 'network_admin_' : '').'plugin_action_links_' . plugin_basename( __FILE__ ),
						function($pluginLinks, $pluginFile, $pluginData) {
							return array_merge(
								[
									'settings'		=> eacDoojigger::getSettingsLink($pluginData,'Cloudflare'),
									'documentation'	=> eacDoojigger::getDocumentationLink($pluginData),
									'support'		=> eacDoojigger::getSupportLink($pluginData,plugin_basename( __FILE__ )),
								],
								$pluginLinks
							);
						},20,3
					);
				}

				/*
    			 * Add our extension to load
    			 */
				$extensionDirectories[ plugin_basename( __FILE__ ) ] = [plugin_dir_path( __FILE__ )];
				return $extensionDirectories;
			}
		);
	}
}
new \EarthAsylumConsulting\eacCloudflare();
?>
