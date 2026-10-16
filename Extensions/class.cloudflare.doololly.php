<?php
namespace EarthAsylumConsulting\Extensions;

/**
 * Extension: cloudflare_extension - enable Cloudflare API
 *
 * Provides limited control of Cloudflare settings, caching, blacklisting, and rate-limiting from WordPress.
 * 1. Pause/Resume Cloudflare service.
 * 2. Override the browser cache time-to-live.
 * 3. Set 'cf-edge-cache' header to trigger APO or custom rules.
 *		custom rules can look for this header set to 'cache,platform=wordpress' or 'no-cache' or not set.
 * 4. Add 'Cache-Tag' header with mime type that can be used for selective cache purge.
 * 5. Set common Cloudflare options for speed & security directly from WordPress.
 * 6. Automatically purge cloudflare cache (by host name).
 * 7. Add a rate limiting rule to temporarily block IP addresses after 100 requests within 10 seconds.
 * 8. Create and update Cloudflare IP blacklist (account) and blocking rule (zone) using risk assessment results.
 *
 * Filters:
 * 1. 'cloudflare_purge_everything_actions' (array) - filter actions that trigger a cache purge.
 * 2. 'cloudflare_use_cache' (bool) - override `cf-edge-cache` setting (cache/no-cache).
 *
 * Actions:
 * 1. 'eacDoojigger_cloudflare_pause'	- pause the cloudflare service
 * 2. 'eacDoojigger_cloudflare_resume'	- resume the cloudflare service
 * 3. 'eacDoojigger_cloudflare_purge'	- purge the cloudflare cache
 *
 * Recommended for free plans. Paid plans should use the official Cloudflare plugin with
 * Automatic Platform Optimization (APO).
 *
 * @see https://wordpress.org/plugins/cloudflare/
 * @see https://developers.cloudflare.com/automatic-platform-optimization/
 * @see https://blog.cloudflare.com/building-automatic-platform-optimization-for-wordpress-using-cloudflare-workers/
 *
 * @category	WordPress Plugin
 * @package		{eac}Doojigger\Extensions
 * @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
 * @copyright	Copyright (c) 2026 EarthAsylum Consulting <www.EarthAsylum.com>
 */

class cloudflare_extension extends \EarthAsylumConsulting\abstract_extension
{
	/**
	 * @var string extension version
	 */
	const VERSION			= '26.1015.1';

	/**
	 * @var string to set default tab name
	 */
	const TAB_NAME			= 'Cloudflare';

	/**
	 * @var string|array|bool to set (or disable) default group display/switch
	 *		false		disable the 'Enabled'' option for this group
	 *		string		the label for the 'Enabled' option
	 *		array		override options for the 'Enabled' option (label,help,title,info, etc.)
	 */
	const ENABLE_OPTION		= [
		'label'		=>	"<abbr title='With your Cloudflare account information, ".
						"we can set certain common Cloudflare options as well as automatically purge the Cloudflare ".
						"cache when purging the local WordPress caches, ".
						"add a rate-limiting rule to mitigate brute-force and DDoS attacks, ".
						"and blacklist IP addresses identified by Risk Assessment.'>Cloudflare</abbr>",
	];


	/**
	 * @var array Cloudflare on/off settings
	 * @see https://developers.cloudflare.com/api/
	 * @see https://developers.cloudflare.com/api/resources/zones/subresources/settings/
	 */
	const CLOUDFLARE_OPTIONS = [
	/*
		"<abbr title=''>".
			"</abbr>"							=> '',
	*/
		"<abbr title='When the client requesting an asset supports the Brotli compression algorithm, Cloudflare will serve a Brotli compressed version of the asset.'>".
			"Brotli Compression</abbr>"			=> 'brotli',

		"<abbr title='Speed Brain speeds up page load times by leveraging the Speculation Rules API. This instructs browsers to make speculative prefetch requests as a way to speed up next page navigation loading time.'>".
			"Speed Brain</abbr>"				=> 'speed_brain',

		"<abbr title='Prioritizes your website content (text, images, fonts, and more) by deferring the loading of all of your JavaScript until after rendering.'>".
			"Rocket Loader</abbr>"				=> 'rocket_loader',

		"<abbr title='Enhance your website’s font delivery with Cloudflare Fonts. Deliver Google Hosted fonts from your own domain, boost performance, and enhance user privacy.'>".
			"Enhanced Font Delivery</abbr>"		=> 'fonts',

		"<abbr title='Cloudflare will attempt to speed up overall page loads by serving 103 responses with Link headers from the final response.'>".
			"Early Hints</abbr>"				=> 'early_hints',

		"<abbr title='Cloudflare will prefetch any URLs that are included in the response headers. (Enterprise)'>".
			"Prefetch Preload</abbr>"			=> 'prefetch_preload',

		"<abbr title='Reply to all requests for URLs that use http with a 301 redirect to the equivalent https URL.'>".
			"Always Use Https</abbr>"			=> 'always_use_https',

		"<abbr title='Automatic HTTPS Rewrites helps fix mixed content by changing “http” to “https” for all resources or links on your web site that can be served with HTTPS.'>".
			"Automatic Https Rewrites</abbr>"	=> 'automatic_https_rewrites',

		"<abbr title='Browser Integrity Check looks for common HTTP headers abused most commonly by spammers and denies access to your page. It will also challenge visitors that do not have a user agent or a non standard user agent.'>".
			"Browser Integrity Check</abbr>"	=> 'browser_check',

		"<abbr title='Advanced protection from Distributed Denial of Service (DDoS) attacks on your website. This is an uneditable value that is ‘on’ in the case of Business and Enterprise zones.'>".
			"Advanced DDoS</abbr>"				=> 'advanced_ddos',

		"<abbr title='Reordering of query strings. When query strings have the same structure, caching improves.'>".
			"Sort Query String For Cache</abbr>"=> 'sort_query_string_for_cache',

		"<abbr title='Provides on-demand resizing, conversion and optimisation for images served through Cloudflare.'>".
			"Image Resizing</abbr>"				=> 'image_resizing',

		"<abbr title='When the client requesting the image supports the WebP image codec, and WebP offers a performance advantage over the original image format, Cloudflare will serve a WebP version of the original image.'>".
			"WebP Image Codec</abbr>"			=> 'webp',

		"<abbr title='Accelerates your website with HTTP/2.'>".
			"Enable HTTP/2</abbr>"				=> 'http2',

		"<abbr title='Optimizes the order of resource delivery, independent of the browser. Greatest improvements will be experienced by visitors using Safari and Edge browsers.'>".
			"Enhanced HTTP/2 Prioritization</abbr>" => 'h2_prioritization',

		"<abbr title='Accelerates HTTP requests by using QUIC, which provides encryption and performance improvements compared to TCP and TLS.'>".
			"Enable HTTP/3 (with QUIC)</abbr>"	=> 'http3',

		"<abbr title='Improves performance for clients who have previously connected to your website.'>".
			"0-RTT Connection Resumption</abbr>"=> '0rtt',

		"<abbr title='Encrypt email adresses on your web page from bots, while keeping them visible to humans.'>".
			"Email Obfuscation</abbr>"			=> 'email_obfuscation',

		"<abbr title='Ensures that other sites cannot suck up your bandwidth by building pages that use images hosted on your site.'>".
			"Hotlink Protection</abbr>"			=> 'hotlink_protection',

		"<abbr title='Enable IP Geolocation to have Cloudflare geolocate visitors to your website and pass the country code to you.'>".
			"IP Geolocation</abbr>"				=> 'ip_geolocation',

		"<abbr title='Bypass the Cloudflare accelerated cache. Development mode will last for 3 hours and then automatically toggle off.'>".
			"Development Mode</abbr>"			=> 'development_mode',
	];

	/**
	 * @var string blacklist name & description
	 */
	const BLACK_LIST_NAME		= 'risk_assessment_blacklist';
	const BLACK_LIST_DESC		= '{eac}Cloudflare - Block IP addresses identified by Risk Assessment';
	private $black_list_name	= self::BLACK_LIST_NAME;

	/**
	 * @var string ratelimit rule description
	 */
	const RATE_LIMIT_DESC 		= '{eac}Cloudflare - Default Rate Limiting Rule';

	/**
	 * @var string cloudflare api url
	 */
	private $cloudflare_url;

	/**
	 * @var array cloudflare account
	 *	'id' => '...',
	 *	'name' => '...',
	 *	'type' => 'standard',
	 *	'settings' => []
	 */
	private $cloudflare_account;

	/**
	 * @var string cloudflare zone
	 */
	private $cloudflare_zone;

	/**
	 * @var string cloudflare api key
	 */
	private $cloudflare_key;

	/**
	 * @var string cloudflare account email
	 */
	private $cloudflare_email;

	/**
	 * @var array cloudflare settings
	 */
	private $cloudflare_settings;

	/**
	 * @var array cloudflare zone status
	 */
	private $cloudflare_status;


	/**
	 * constructor method
	 *
	 * @param	object	$plugin main plugin object
	 */
	public function __construct($plugin)
	{
		parent::__construct($plugin, self::ALLOW_ALL|self::ALLOW_NON_PHP|self::DEFAULT_DISABLED);

		add_action('admin_init', function()
		{
			$this->registerExtension( $this->className );
			// Register plugin options when needed
			$this->add_action( "options_settings_page", array($this, 'admin_options_settings') );
			// Add contextual help
			$this->add_action( 'options_settings_help', array($this, 'admin_options_help') );
		},50);	// <- tab at the right end
	}


	/**
	 * initialize method - called from main plugin
	 *
	 */
	public function initialize()
	{
		if ( ! parent::initialize() ) return; // disabled

		// check for required settings
		if (!$this->get_cf_auth()) {
			return $this->isEnabled(false);
		}
		$this->cloudflare_url = sprintf("https://api.cloudflare.com/client/v4/zones/%s/",$this->cloudflare_zone);
	}


	/**
	 * register options on options_settings_page
	 *
	 * @access public
	 */
	public function admin_options_settings()
	{
		if (!$this->plugin->isSettingsPage(self::TAB_NAME)) return;

		$this->registerExtensionOptions( $this->className,
			[
				'cloudflare_email'	=> array(
						'type'		=>	'text',
						'label'		=>	'Account Email',
						'info'		=>	'Your Cloudflare account email address.',
						'default'	=>	get_bloginfo('admin_email'),
				),
				'cloudflare_key'	=> array(
						'type'		=>	'text',
						'label'		=>	'Global API Key',
						'info'		=>	'Your Cloudflare global API key.'.
										'<br><small>From your <em>Account Profile</em> &rarr; API Tokens &rarr; API Keys &rarr; Global API Key</small>',
				),
			]
		);

		if ($this->get_cf_auth())
		{
			$this->get_cf_options();
			if (isset($this->cloudflare_settings['browser_cache_ttl'])) {
				$this->update_option('cloudflare_cachettl',(string)$this->cloudflare_settings['browser_cache_ttl']);
			}

			$this->registerExtensionOptions( $this->className,
			[
				'cloudflare_zone'	=> array(
						'type'		=>	'select',
						'label'		=>	'Domain Zone',
						'options'	=>	$this->get_zone_list(),
						'info'		=>	'Your Cloudflare zone for this domain.',
						'style'		=> 	'min-width:22ch;',
				),
			],
			);
		}
		if ($this->is_option('cloudflare_zone'))
		{
			$this->cloudflare_status = $this->get_zone_status();
			$isPaused = ($this->cloudflare_status['paused']) ? 'Resume' : 'Pause';
			$blacklist_id = $this->get_blacklist_id(false,false) ?: 'blacklist';

			$isLimit = ($this->get_ratelimit_rule()) ? 'Delete' : 'Add';

			$this->registerExtensionOptions( $this->className,
			[
				'_cloudflare_pause'=> array(
						'type'		=>	'button',
						'label'		=>	"{$isPaused} This Zone",
						'default'	=>	$isPaused,
						'info'		=>	"Pausing stops traffic from passing through the Cloudflare network, ".
										"making your origin server IP address visible. ".
										"Also, Cloudflare’s security and protection features become disabled.",
						'validate'	=>	function($value) {return $this->cloudflare_pause($value);},
				),
				'cloudflare_cachettl'=> array(
						'type'		=>	'select',
						'label'		=>	'Browser Cache Time-To-Live',
						'options'	=> [
							'Respect Existing Headers'	=> 0,
							'2 minutes'					=> 2*MINUTE_IN_SECONDS,
							'5 minutes'					=> 5*MINUTE_IN_SECONDS,
							'20 minutes'				=> 20*MINUTE_IN_SECONDS,
							'30 minutes'				=> 30*MINUTE_IN_SECONDS,
							'1 hour'					=> 1*HOUR_IN_SECONDS,
							'2 hours'					=> 2*HOUR_IN_SECONDS,
							'3 hours'					=> 3*HOUR_IN_SECONDS,
							'4 hours'					=> 4*HOUR_IN_SECONDS,
							'5 hours'					=> 5*HOUR_IN_SECONDS,
							'8 hours'					=> 8*HOUR_IN_SECONDS,
							'12 hours'					=> 12*HOUR_IN_SECONDS,
							'16 hours'					=> 16*HOUR_IN_SECONDS,
							'20 hours'					=> 20*HOUR_IN_SECONDS,
							'1 day'						=> 1*DAY_IN_SECONDS,
							'2 days'					=> 2*DAY_IN_SECONDS,
							'3 days'					=> 3*DAY_IN_SECONDS,
							'4 days'					=> 4*DAY_IN_SECONDS,
							'5 days'					=> 5*DAY_IN_SECONDS,
							'8 days'					=> 8*DAY_IN_SECONDS,
							'16 days'					=> 16*DAY_IN_SECONDS,
							'24 days'					=> 24*DAY_IN_SECONDS,
							'1 month'					=> 1*MONTH_IN_SECONDS,
							'2 months'					=> 2*MONTH_IN_SECONDS,
							'6 months'					=> 6*MONTH_IN_SECONDS,
							'1 year'					=> 1*YEAR_IN_SECONDS,
						],
						'info'		=>	'Controls how long resources cached by client browsers remain valid.',
						'help'		=>	'[info]<br><small>Zone &rarr; Caching &rarr; Configuration</small>',
						'validate'	=>	[$this,'set_cache_ttl'],
						'style'		=> 	'min-width:22ch;',
				),
				'cloudflare_options'=> array(
						'type'		=>	'switch',
						'label'		=>	'Cloudflare Options',
						'options'	=>	array_merge(
							["<abbr title='Set cf-edge-cache header for use with Automatic Platform Optimization or custom rules.'>Caching for WP APO</abbr>" => 'wp_edge_cache'],
							array_filter(self::CLOUDFLARE_OPTIONS, function($v) {return isset($this->cloudflare_settings[$v]);})
						),
						'info'		=>	'Enable/disable common Cloudflare options for speed &amp; security.',
						'help'		=>	'[info]'.
									'<br><small>Zone &rarr; Speed &rarr; Settings</small>'.
									'<br><small>Zone &rarr; SSL/TLS &rarr; Edge Certificates</small>'.
									'<br><small>Zone &rarr; Security &rarr; Settings</small>',

						'validate'	=>	[$this,'set_cf_settings'],
				),
				'_cloudflare_ratelimit'=> array(
				//		'when'		=> 	!$this->get_ratelimit_rule(),
						'type'		=>	'button',
						'label'		=>	"Rate Limit Rule",
						'default'	=>	$isLimit.' Rule',
						'info'		=>	"Adds a rate limiting rule to temporarily block IP addresses after 100 requests within 10 seconds. ".
										"<br><small><strong><em>Set to work with free plans but may overwrite existing rule.</em></strong></small>",
						'help'		=>	'[info]<br><small>Zone &rarr; Security &rarr; Security rules</small>',
						'validate'	=>	[$this,'set_ratelimit_rule'],
				//		'attributes'=> 	($this->get_ratelimit_rule()) ? ['disabled'=>'disabled'] : '',
				),
				'cloudflare_blacklist'=> array(
						'type'		=>	'switch',
						'label'		=>	'Risk Assessment Blacklist',
						'options'	=>	["Blacklisting Enabled" => $blacklist_id],
						'info'		=>	'When a request is blocked by the {eac}Doojigger Risk Assessment process, '.
										'add the remote IP address to the Cloudflare blacklist to prevent further access to the site.',
						'help'		=>	"[info] This creates or updates a Cloudflare IP list ".
										"<small>(Account &rarr; Configurations &rarr; Lists)</small> ".
										"and adds a security rule ".
										"<small>(Zone &rarr; Security &rarr; Security Rules)</small> ".
										"to block the list.<br>".
										"<small>Note: To use an existing IP list, add <code>[{eac}Cloudflare]</code> to its description.</small>",
						'validate'	=>	[$this,'set_cloudflare_blacklist'],
				),
				'cloudflare_blacklist_purge'=> array(
						'when'		=> 	!empty($this->is_option('cloudflare_blacklist')),
						'type'		=>	'select',
						'label'		=>	'Blacklist Expiration',
						'options'	=> [
							'Indefinite'			=> 0,
							'1 Hour'				=> HOUR_IN_SECONDS,
							'4 Hours'				=> 4*HOUR_IN_SECONDS,
							'8 Hours'				=> 8*HOUR_IN_SECONDS,
							'12 Hours'				=> 12*HOUR_IN_SECONDS,
							'1 Day'					=> DAY_IN_SECONDS,
							'2 Days'				=> 2*DAY_IN_SECONDS,
							'3 Days'				=> 3*DAY_IN_SECONDS,
							'1 Week'				=> 7*DAY_IN_SECONDS,
							'2 Weeks'				=> 14*DAY_IN_SECONDS,
							'1 Month'				=> MONTH_IN_SECONDS,
							'3 Months'				=> 3*MONTH_IN_SECONDS,
						],
						'info'		=>	'Controls how long an IP address is blacklisted due to Risk Assessment.',
						'help'		=> 	'[info] The Cloudflare blacklist is purged hourly.',
						'style'		=> 	'min-width:22ch;',
				),
			]);
		}
	}


	/**
	 * Add help tab on admin page
	 *
	 */
	public function admin_options_help()
	{
		if (!$this->plugin->isSettingsPage(self::TAB_NAME)) return;
	}


	/**
	 * Add filters and actions - called from main plugin
	 *
	 */
	public function addActionsAndFilters()
	{
		if ($this->is_option('cloudflare_options','wp_edge_cache'))
		{
			// do this late
			add_filter('wp_headers',			array($this, 'cloudflare_wp_edge_cache'),PHP_INT_MAX,1);
			add_filter('nocache_headers',		function($headers)
			{
				$headers['cf-edge-cache'] = 'no-cache';
				return $headers;
			});
		}

		$this->add_filter('after_flush_caches', array($this,'cloudflare_purge'),100,1);
		$this->add_action('cloudflare_purge',	array($this,'cloudflare_purge'),100,1);
		$this->add_action('cloudflare_pause',	function(){$this->cloudflare_pause('Pause');});
		$this->add_action('cloudflare_resume',	function(){$this->cloudflare_pause('Resume');});

		$purge_actions = array(
			'switch_theme',						// Switch theme
			'customize_save_after',				// Customizer
		//	  'edit_post',						  // Edit a post
		//	  'deleted_post',					  // Delete a post
		//	  'delete_attachment',				  // Delete/replace an attachment
		//	  'transition_post_status',			  // When published
			'clean_post_cache',					// When a post changes
			'clean_page_cache',					// When a page changes
			'transition_comment_status',		// When approved
			'comment_post',						// When added
		/*
			'autoptimize_action_cachepurged'	// Autoptimize plugin
			'cache_enabler_clear_site_cache'	// Cache Enabler plugin
			'w3tc_flush_all'					// W3 Total Cache plugin
			'wpo_cache_flush'					// WP Optimize plugin
			'after_rocket_clean_domain'			// WP Rocket plugin
			'wp_cache_cleared'					// WP Super Cache plugin
		*/
		);

		/**
		 * filter 'cloudflare_purge_everything_actions' (same as cloudflare plugin)
		 * @param array actions to trigger cache purge
		 */
		$purge_actions = apply_filters('cloudflare_purge_everything_actions', $purge_actions);

		foreach ($purge_actions as $action) {	// no arguments
			add_action($action,					array($this,'cloudflare_purge'),100,0);
		}

		if ($this->is_option('cloudflare_blacklist'))
		{
			$this->add_action('risk_assessment_blocked',		array($this, 'add_ip_to_blacklist'),10,2 );
			if ($this->is_option('cloudflare_blacklist_purge')) {
				$this->do_action( 'add_event_task', 'hourly',	array($this, 'blacklist_purge'));
			}
		}
	}


	/**
	 * get/set cloudflare auth
	 *
	 */
	public function get_cf_auth(): bool
	{
		// check for required settings
		if ( (!$this->cloudflare_key   && !($this->cloudflare_key	= $this->get_option('cloudflare_key'))) ) {
			return false;
		}
		if ( (!$this->cloudflare_email && !($this->cloudflare_email = $this->get_option('cloudflare_email'))) ) {
			return false;
		}
		$this->cloudflare_zone	= $this->get_option('cloudflare_zone');
		return true;
	}


	/**
	 * Get the Cloudflare account
	 *
	 */
	private function get_cloudflare_account()
	{
		if (empty($this->cloudflare_account))
		{
			$this->cloudflare_account = $this->get_transient('cloudflare_account', function()
				{
					$result = wp_remote_get("https://api.cloudflare.com/client/v4/accounts",
						[
							'headers'	=> $this->cloudflare_headers(),
						]
					);
					$result = json_decode( wp_remote_retrieve_body($result), true );
					return ($result['success']) ? $result['result'][0] : null;
				},
				DAY_IN_SECONDS
			);
		}
		return $this->cloudflare_account['id'];
	}


	/**
	 * Get the cloudflare zone list
	 *
	 */
	public function get_zone_list(): array
	{
		$zones = [];
		if ($this->get_cf_auth())
		{
			$cloudflare_url = sprintf( 'https://api.cloudflare.com/client/v4/zones?page=%s&per_page=%s', 1, 50 );
			$result = wp_remote_get($cloudflare_url,
				[
					'headers'	=> $this->cloudflare_headers(),
				]
			);
			$result = json_decode( wp_remote_retrieve_body($result), true );
			if ($result['success']) {
				foreach ( $result['result'] as $list ) {
					if ( isset( $list['name'],$list['id'] ) ) {
						$zones[ $list['name'] ] = $list['id'];
					}
				}
			}
		}
		return $zones;
	}


	/**
	 * Get the cloudflare zone status
	 *
	 *	  "success": true,
	 *	  "errors": [],
	 *	  "messages": [],
	 *	  "result": {
	 *		"id": "023e105f4ecef8ad9ca31a8372d0c353",
	 *		"name": "example.com",
	 *		"status": "active",
	 *		"paused": true,
	 *		"type": "full",
	 *		"created_on": "2026-01-01T00:00:00.000000Z",
	 *		"modified_on": "2026-09-14T00:00:00.000000Z"
	 */
	public function get_zone_status()
	{
		if ($this->get_cf_auth() && $this->cloudflare_url)
		{
			$result = wp_remote_get($this->cloudflare_url,
				[
					'headers'	=> $this->cloudflare_headers(),
				]
			);
			$result = json_decode( wp_remote_retrieve_body($result), true );
			if ($result['success']) {
				return $result['result'];
			} else {
				$this->add_admin_notice("Cloudflare: Status (".$result['errors'][0]['code'].") ".$result['errors'][0]['message'],'error');
			}
		}
		return [];
	}


	/**
	 * Set the cloudflare browser or edge cache time option
	 *
	 */
	public function set_cache_ttl($value, $fieldName, $metaData, $priorValue)
	{
		if ($value == $priorValue) return;

		if ($this->get_cf_auth() && $this->cloudflare_url)
		{
			$this->set_cf_setting('browser_cache_ttl', (int)$value);
		}

		return $value;
	}


	/**
	 * Get/set our cloudflare options
	 *
	 */
	public function get_cf_options(): array
	{
		$options	= (isset($_POST,$_POST['cloudflare_options']))
			? $_POST['cloudflare_options']
			: $this->get_option('cloudflare_options');

		if ($this->get_cf_auth() && $this->cloudflare_url)
		{
			$settings = $this->get_cf_settings();
			$options = (is_array($options) && in_array('wp_edge_cache',$options)) ? ['wp_edge_cache'] : [];
			foreach(self::CLOUDFLARE_OPTIONS as $name => $id) {
				if (isset($settings[$id]) && $settings[$id] == 'on') {
					$options[] = $id;
				}
			}
		}

		$this->update_option('cloudflare_options',$options);
		return (array)$options;
	}


	/**
	 * Get all settings from cloudflare
	 *
	 */
	private function get_cf_settings(): array
	{
		// get all zone settings (this api is deprecated)
		$result = wp_remote_get($this->cloudflare_url . "settings",
			[
				'headers'	=> $this->cloudflare_headers(),
			]
		);

		$result = json_decode( wp_remote_retrieve_body($result), true );
		if ($result['success']) {
			foreach ( $result['result'] as $setting ) {
				if (isset($setting['value'],$setting['id'])) {
					if ($setting['editable']) {
						$this->cloudflare_settings[ $setting['id'] ] = $setting['value'];
					}
				}
			}
		// if failure, get each setting individually (may produces different results)
		} else {
			foreach(self::CLOUDFLARE_OPTIONS as $name => $id) {
				$this->get_cf_setting($id);
			}
		}

		$this->cloudflare_settings[ 'browser_cache_ttl' ] = $this->get_cf_setting('browser_cache_ttl');
		return $this->cloudflare_settings ?? [];
	}


	/**
	 * Get a single cloudflare option
	 *
	 */
	private function get_cf_setting($option)
	{
		$result = wp_remote_get($this->cloudflare_url . "settings/{$option}",
			[
				'headers'	=> $this->cloudflare_headers(),
			]
		);

		$result = json_decode( wp_remote_retrieve_body($result), true );
		if ($result['success']) {
			$setting = $result['result'];
			if (isset($setting['value'],$setting['id'])) {
				if ($setting['editable']) {
					$this->cloudflare_settings[ $setting['id'] ] = $setting['value'];
				}
			}
			return $setting['value'];
		}

		return false;
	}


	/**
	 * Set the cloudflare options
	 *
	 */
	public function set_cf_settings($value, $fieldName, $metaData, $priorValue)
	{
		if ($value === $priorValue) return $value;

		if (!is_array($value)) $value = [];
		if (!is_array($priorValue)) $priorValue = [];

		if ($this->get_cf_auth() && $this->cloudflare_url)
		{
			foreach(self::CLOUDFLARE_OPTIONS as $name => $id) {
				if ( (in_array($id,$value) && !in_array($id,$priorValue))
				||	 (!in_array($id,$value) && in_array($id,$priorValue))
				) {
					$this->set_cf_setting($id, in_array($id,$value) ? 'on' : 'off');
				}
			}
		}

		return (array)$value;
	}


	/**
	 * Set a single cloudflare option
	 *
	 */
	private function set_cf_setting($option, $value)
	{
		$result = wp_remote_post($this->cloudflare_url . "settings/{$option}",
			[
				'method'	=> 'PATCH',
				'headers'	=> $this->cloudflare_headers(),
				'body'		=> wp_json_encode([ 'value' => $value ])
			]
		);

		$result = json_decode( wp_remote_retrieve_body($result), true );
		if (!$result['success']) {
			$this->add_admin_notice("Cloudflare {$option}: (".$result['errors'][0]['code'].") ".$result['errors'][0]['message'],'error');
			return false;
		}

		return true;
	}


	/**
	 * Eligible/Ineligible for cloudflare caching
	 *
	 * @param array $headers http headers
	 */
	public function cloudflare_wp_edge_cache($headers)
	{
		/**
		 * filter 'cloudflare_use_cache' - override cache setting (same as cloudflare plugin)
		 * @param bool enable caching
		 */
		if (apply_filters('cloudflare_use_cache', $this->is_cacheable($headers))) {
		//	if ($ttl = $this->get_option('cloudflare_cachettl')) {
		//		$headers['cache-control']	= "Cache-Control: public, max-age={$ttl}";
		//	}
			$headers['cf-edge-cache']		= 'cache, platform=wordpress';
			$headers['Cache-Tag']			= $this->get_mime_type($headers);
		} else {
			$headers['cf-edge-cache']		= 'no-cache';
		}
		return $headers;
	}


	/**
	 * Eligible/Ineligible for cloudflare caching
	 *
	 * @todo - check cookies and query parameters ?
	 *
	 * @param array $headers http headers
	 */
	protected function is_cacheable($headers)
	{
		if ($_SERVER['REQUEST_METHOD'] != 'GET') {
		   return false;
		}
		if (http_response_code() != 200) {
		   return false;
		}
		if ($this->doing_ajax() || is_user_logged_in()) {
		   return false;
		}
		if (class_exists('woocommerce') &&
			(is_cart() || is_checkout() || is_order_received_page() || is_account_page())) {
			return false;
		}
		$checkHeaders = array_merge(
			$this->explode_with_keys(':',headers_list()),
			(array)$headers
		);
		if (isset($checkHeaders['Cache-Control'])) {
			if (str_contains($checkHeaders['Cache-Control'],'no-cache')) {
				return false;
			}
			if (str_contains($checkHeaders['Cache-Control'],'private')) {
				return false;
			}
		}
		if (isset($checkHeaders['Pragma'])) {
			if (str_contains($checkHeaders['Pragma'],'no-cache')) {
				return false;
			}
		}
		return true;
	}


	/**
	 * Get the mime type for the current request
	 *
	 */
	protected function get_mime_type($headers,$extension='')
	{
		// get mime type from content-type header
		if (isset($headers['Content-Type']))
		{
			list($contentType,) = explode(';',$headers['Content-Type']);
			return trim($contentType);
		}

		// get mime type from request extension
		if (empty($extension) && array_key_exists('REQUEST_URI', $_SERVER))
		{
			$extension = explode('?',$_SERVER['REQUEST_URI']);
			$extension = pathinfo(trim($extension[0],'/'),PATHINFO_EXTENSION);
		}

		if ($extension)
		{
			$mime_types = array_merge(wp_get_mime_types(),[
				'json'		=> 'application/json',
				'xml'		=> 'application/xml',
				'rss'		=> 'application/rss+xml',
			]);
			$extensions = array_keys( $mime_types );

			foreach ( $extensions as $_extension ) {
				if ( preg_match( "/{$extension}/i", $_extension ) ) {
					return $mime_types[ $_extension ];
				}
			}
		}

		// get mime type from WP default
		return get_option( 'html_type' );
	}


	/**
	 * Purge the cloudflare cache
	 *
	 * @param array caches purged - only with 'after_flush_caches'
	 */
	public function cloudflare_purge($caches = [])
	{
		static $onlyOnce = 0;
		if ($onlyOnce++) return $caches;

		if ($this->get_cf_auth() && $this->cloudflare_url)
		{
			$site_url = parse_url( get_site_url() );
			$site_url = trailingslashit($site_url['host'] . ($site_url['path'] ?? '')).'*';
			$result = wp_remote_post($this->cloudflare_url . "purge_cache",
				[
					'headers'	=> $this->cloudflare_headers(),
					'body'		=> wp_json_encode([ "prefixes" => [$site_url] ])
				]
			);
			$result = json_decode( wp_remote_retrieve_body($result), true );
			if ($result['success']) {
				$this->add_admin_notice('The Cloudflare cache for '.$site_url.' has been purged','success');
				$caches[] = 'Cloudflare Cache';
			} else {
				$this->add_admin_notice("Cloudflare Cache Purge: (".$result['errors'][0]['code'].") ".$result['errors'][0]['message'],'error');
			}
		}
		return $caches;
	}


	/**
	 * Pause the cloudflare dns service
	 *
	 * @param array $option pause/resume
	 */
	public function cloudflare_pause(string $option)
	{
		$pause = ($option == 'Pause');

		$site_id = $this->cloudflare_status['name'];

		if ($this->get_cf_auth() && $this->cloudflare_url)
		{
			$result = wp_remote_post($this->cloudflare_url,
				[
					'method'	=> 'PATCH',
					'headers'	=> $this->cloudflare_headers(),
					'body'		=> wp_json_encode([ "paused" => $pause ])
				]
			);
			$result = json_decode( wp_remote_retrieve_body($result), true );
			if ($result['success']) {
				$this->add_admin_notice("The Cloudflare service for {$site_id} has been {$option}d",'success');
				return ($result['result']['paused'] == true) ? 'Pause' : 'Resume';
			} else {
				$this->add_admin_notice("Cloudflare Service {$option}: (".$result['errors'][0]['code'].") ".$result['errors'][0]['message'],'error');
			}
		}
		return $option;
	}


	/**
	 * Get rate limit rule
	 *
	 */
	public function get_ratelimit_rule()
	{
		$result = wp_remote_get($this->cloudflare_url.'rulesets/phases/http_ratelimit/entrypoint',
			[
				'headers'	=> $this->cloudflare_headers(),
			]
		);
		$result = json_decode( wp_remote_retrieve_body($result), true );

		if ($result['success'] && isset($result['result']['rules']))
		{
			foreach ($result['result']['rules'] as $rule) {
				if ($rule['description'] == self::RATE_LIMIT_DESC) {
					return $rule;
				}
			}
			return true;
		}
		return false;
	}


	/**
	 * Set rate limit rule option
	 *
	 */
	public function set_ratelimit_rule($value, $fieldName, $metaData, $priorValue)
	{
		if ($value == 'Add Rule')
		{
			if ($this->get_ratelimit_rule()) {
				$this->add_admin_notice("Cloudflare Ratelimit: existing ratelimit rule found.",'success');
				return;
			}

			$rule = [
				[
					'ratelimit' => [
						'characteristics' => [
							'ip.src',
							'cf.colo.id'
						],
						'period' => 10, // required, free plan
						'requests_per_period' => 100,
						'requests_to_origin' => false,
						'mitigation_timeout' => 10 // required, free plan
					],
					'description' => self::RATE_LIMIT_DESC,
					'expression' => '(http.request.uri.path wildcard r"*")',
					'action' => 'block', // required, free plan
					'enabled' => true
				]
			];

			$result = wp_remote_post($this->cloudflare_url.'rulesets/phases/http_ratelimit/entrypoint',
				[
					'method'	=> 'PUT',
					'headers'	=> $this->cloudflare_headers(),
					'body'		=> wp_json_encode(['rules' => $rule]),
				]
			);
			$result = json_decode( wp_remote_retrieve_body($result), true );
			if ($result['success']) {
				$this->add_admin_notice("Cloudflare Ratelimit: <em>".self::RATE_LIMIT_DESC.'</em> created.','success');
			} else {
				$this->add_admin_notice("Cloudflare Ratelimit: (".$result['errors'][0]['code'].") ".$result['errors'][0]['message'],'error');
			}
			return $value;
		}
		else if ($value == 'Delete Rule')
		{
			$result = wp_remote_get($this->cloudflare_url.'rulesets/phases/http_ratelimit/entrypoint',
				[
					'headers'	=> $this->cloudflare_headers(),
				]
			);
			$result = json_decode( wp_remote_retrieve_body($result), true );

			if ($result['success'] && isset($result['result']['rules']))
			{
				foreach ($result['result']['rules'] as $ix => $rule) {
					if ($rule['description'] == self::RATE_LIMIT_DESC) {
						unset($result['result']['rules'][$ix]);
						$result = wp_remote_post($this->cloudflare_url.'rulesets/phases/http_ratelimit/entrypoint',
							[
								'method'	=> 'PUT',
								'headers'	=> $this->cloudflare_headers(),
								'body'		=> wp_json_encode(['rules'=>$result['result']['rules']]),
							]
						);
						$result = json_decode( wp_remote_retrieve_body($result), true );
						if ($result['success']) {
							$this->add_admin_notice("Cloudflare Ratelimit: <em>".self::RATE_LIMIT_DESC.'</em> removed.','success');
						} else {
							$this->add_admin_notice("Cloudflare Ratelimit: (".$result['errors'][0]['code'].") ".$result['errors'][0]['message'],'error');
						}
						return $value;
					}
				}
			}
			$this->add_admin_notice("Cloudflare Ratelimit: <em>".self::RATE_LIMIT_DESC.'</em> not found.','warning');
		}
	}


	/**
	 * Add IP to Cloudflare blacklist (IP access rules)
	 *
	 * @param string	$ipAddress
	 * @param array		$data
	 */
	public function add_ip_to_blacklist(string $ipAddress, array $data)
	{
		if ($list_id = $this->get_blacklist_id(false))
		{
			$account_id = $this->get_cloudflare_account();
			$list_url = "https://api.cloudflare.com/client/v4/accounts/{$account_id}/rules/lists";

			// add IP addres to blacklist
			$comment = sprintf(
				"%s score: %d/%d",
				ucwords($data['RiskAssessmentType']),$data['RiskAssessmentScore'],$data['RiskAssessmentLimit']
			);
			// show provider scores
			foreach ($data['RiskAssessmentScores'] as $provider => $score) {
				if (current($score) > 0) {
					$comment .= "; {$provider} ".key($score)."=".current($score);
				}
			}
			// show internal (registered) messages
			if (isset($data['RiskAssessmentData']['registered'])) {
				$comment .= "; ".implode("; ",array_unique($data['RiskAssessmentData']['registered']['message']));
			}
			// show location
			$location = $data['Region'] ?: $data['City'];
			$comment .= "; [".trim($location.' '.$data['CountryCode'])."]";

			$result = wp_remote_post($list_url."/{$list_id}/items",
				[
					'headers'	=> $this->cloudflare_headers(),
					'body'		=> wp_json_encode(array([
						'ip'		=> $ipAddress,
						'comment'	=> trim($comment,' ;'),
					]))
				]
			);
			$result = json_decode( wp_remote_retrieve_body($result), true );
			if (!$result['success']) {
				$this->logError($result['errors'],__METHOD__);
			} else {
				$this->logNotice(trim($comment,' ;'),'Cloudflare blacklist');
			}
		}
	}


	/**
	 * Set Cloudflare blacklist option
	 *
	 */
	public function set_cloudflare_blacklist($value, $fieldName, $metaData, $priorValue)
	{
		if ($value && $value != $priorValue)
		{
			$this->get_blacklist_id();
			$this->set_blacklist_rule();
		}
		return $value;
	}


	/**
	 * get or create blacklist list (at the account level)
	 *
	 */
	public function get_blacklist_id($verbose = true, $create = true)
	{
		$blacklist = $this->get_transient('cloudflare_blacklist_list', function() use($verbose,$create)
			{
				return $this->get_blacklist_list($verbose,$create);
			},
			HOUR_IN_SECONDS // could/should be longer, but what if the CF list gets deleted?
		);

		if (!$blacklist) {
			$this->delete_option('cloudflare_blacklist');
			return false;
		}

		$this->black_list_name = $blacklist['name'];
		return $blacklist['id'];
	}


	/**
	 * get or create blacklist list (at the account level)
	 *
	 */
	private function get_blacklist_list($verbose = true, $create = true)
	{
		$account_id = $this->get_cloudflare_account();
		$list_url	= "https://api.cloudflare.com/client/v4/accounts/{$account_id}/rules/lists";
		$blacklist	= false;

		// get existing black list
		$result = wp_remote_get($list_url,
			[
				'headers'	=> $this->cloudflare_headers(),
			]
		);
		$result = json_decode( wp_remote_retrieve_body($result), true );

		if ($result['success'])
		{
			foreach ( $result['result'] as $list ) {
				if ($list['name'] == self::BLACK_LIST_NAME || str_contains($list['description'],'{eac}Cloudflare')) {
					$blacklist = $list;
					if ($verbose) $this->add_admin_notice("Cloudflare: List '<em>".$blacklist['name']."</em>' found",'success');
					break;
				}
			}
		}

		// create new blacklist list
		if (!$blacklist && $create) {
			$result = wp_remote_post($list_url,
				[
					'headers'	=> $this->cloudflare_headers(),
					'body'		=> wp_json_encode([
						'kind'			=> 'ip',
						'name'			=> self::BLACK_LIST_NAME,
						'description'	=> self::BLACK_LIST_DESC,
					])
				]
			);
			$result = json_decode( wp_remote_retrieve_body($result), true );

			if ($result['success']) {
				$blacklist = $result['result'];
				if ($verbose) $this->add_admin_notice("Cloudflare: List '<em>".$blacklist['name']."</em>' created",'success');
			} else {
				if ($verbose) $this->add_admin_notice('Cloudflare List: '.$result['errors'][0]['message'],'error');
			}
		}

		return $blacklist;
	}


	/**
	 * get/create blacklist rule (at the zone level)
	 *
	 */
	public function set_blacklist_rule()
	{
		$rule_url	= $this->cloudflare_url.'/rulesets';
		$rule_id	= false;
		$rule_list	= false;

		// get existing rulesets
		$result = wp_remote_get($rule_url,
			[
				'headers'	=> $this->cloudflare_headers(),
			]
		);
		$result = json_decode( wp_remote_retrieve_body($result), true );

		if ($result['success']) {
			$rule_list = $result['result'];
			foreach ( $rule_list as $rule ) {
				if ($rule['name'] == $this->black_list_name) {
					$rule_id = $rule['id'];
					break;
				}
			}
		}

		// if not found, try to create new ruleset (paid account)
		if (!$rule_id) {
			$result = wp_remote_post($rule_url,
				[
					'headers'	=> $this->cloudflare_headers(),
					'body'		=> wp_json_encode([
						'kind'			=> 'custom',
						'name'			=> self::BLACK_LIST_NAME,
						'description'	=> self::BLACK_LIST_DESC,
						'phase'			=> 'http_request_firewall_custom',
					])
				]
			);
			$result = json_decode( wp_remote_retrieve_body($result), true );

			if ($result['success']) {
				$rule_id = $result['result']['id'];
			} else if ($rule_list) {
			// if not created, try to find the right 'phase' ruleset
				foreach ( $rule_list as $rule ) {
					if ($rule['phase'] == 'http_request_firewall_custom') {
						$rule_id = $rule['id'];
						break;
					}
				}
			}
		}

		// not found, not created, we're done
		if (!$rule_id) {
			$this->add_admin_notice('Cloudflare Rule: '.$result['errors'][0]['message'].'error');
			return;
		}

		// get list of rules from the ruleset
		$result = wp_remote_get($rule_url."/{$rule_id}",
			[
				'headers'	=> $this->cloudflare_headers(),
			]
		);
		$result = json_decode( wp_remote_retrieve_body($result), true );
		// find existing rule in ruleset
		if ($result['success']) {
			foreach ( $result['result']['rules'] as $rule ) {
				if ($rule['description'] == self::BLACK_LIST_DESC) {
					$this->add_admin_notice("Cloudflare: Rule '<em>".self::BLACK_LIST_DESC."</em>' found",'success');
					return $rule['id'];
				}
			}
		}

		// create a new rule
		$result = wp_remote_post($rule_url."/{$rule_id}/rules",
			[
				'headers'	=> $this->cloudflare_headers(),
				'body'		=> wp_json_encode([
					'description'	=> self::BLACK_LIST_DESC,
					'expression'	=> '(ip.src in $'.$this->black_list_name.')',
					'action'		=> 'block',
					'enabled'		=> true,
				])
			]
		);
		$result = json_decode( wp_remote_retrieve_body($result), true );
		if ($result['success']) {
			$this->add_admin_notice("Cloudflare: Rule '<em>".self::BLACK_LIST_DESC."</em>' added",'success');
			return $result['result']['id'];
		} else {
			$this->add_admin_notice('Cloudflare Rule: '.$result['errors'][0]['message'],'error');
			return false;
		}
	}


	/**
	 * purge old blacklisted IPs
	 *
	 */
	public function blacklist_purge()
	{
		if (! ($time = (int)$this->get_option('cloudflare_blacklist_purge')) ) return;
		$cutoffTimestamp = time() - $time;

		if ($list_id = $this->get_blacklist_id(false))
		{
			$account_id = $this->get_cloudflare_account();
			$list_url	= "https://api.cloudflare.com/client/v4/accounts/{$account_id}/rules/lists";

			// get existing blacklist items
			$result = wp_remote_get($list_url."/{$list_id}/items?per_page=500",
				[
					'headers'	=> $this->cloudflare_headers(),
				]
			);
			$result = json_decode( wp_remote_retrieve_body($result), true );
			if (!$result['success']) return;

			$items = $result['result'];
			$total = count($items);
			if ($total == 0) return;

			$itemsToDelete = [];
			foreach ($items as $item) {
				if (isset($item['created_on'])) {
					$created = strtotime($item['created_on']);
					if ($created < $cutoffTimestamp) {
						$itemsToDelete[] = ['id' => $item['id']];
					}
				}
			}
			$count = count($itemsToDelete);
			if ($count == 0) return;

			// delete existing blacklist items
			$result = wp_remote_post($list_url."/{$list_id}/items",
				[
					'method'	=> 'DELETE',
					'headers'	=> $this->cloudflare_headers(),
					'body'		=> wp_json_encode(['items' => $itemsToDelete]),
				]
			);
			$result = json_decode( wp_remote_retrieve_body($result), true );
			if ($result['success']) {
				$this->logNotice("Cloudflare Blacklist removed {$count} of {$total} IP addresses");
			} else {
				$this->logDebug($result,__METHOD__);
			}
		}
	}


	/**
	 * Set the cloudflare header array
	 *
	 */
	public function cloudflare_headers(): array
	{
		return [
			'Accept'		=> 'application/json',
			'Content-Type'	=> 'application/json',
			'X-Auth-Key'	=> $this->cloudflare_key,
			'X-Auth-Email'	=> $this->cloudflare_email,
		];
	}
}
/**
* return a new instance of this class
*/
if (isset($this)) return new cloudflare_extension($this);
?>
