<?php
namespace EarthAsylumConsulting\Extensions;

/**
 * Extension: cloudflare_extension - enable Cloudflare API
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
	const VERSION			= '26.1017.1';

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
	 * @var array Cloudflare toggle (on/off) settings
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
	 * @var array Cloudflare unique/independent settings
	 * option_name => setting_name
	 */
	const CLOUDFLARE_UNIQUE = [
		'cloudflare_cachettl'		=> 'browser_cache_ttl',
		'cloudflare_cache_level'	=> 'cache_level',
		'cloudflare_security'		=> 'security_level',
	];

	/**
	 * @var string transient names
	 */
	const TRANSIENT_ACCOUNT			= 'cloudflare_account';
	const TRANSIENT_ZONE_LIST		= 'cloudflare_zone_list';
	const TRANSIENT_ZONE_STATUS 	= 'cloudflare_zone_status';
	const TRANSIENT_ZONE_SETTINGS 	= 'cloudflare_zone_settings';
	const TRANSIENT_BLACKLIST_LIST 	= 'cloudflare_blacklist_list';

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
	 * @var string Cloudflare api url
	 * sprintf(self::CLOUDFLARE_URL,'accounts'||'zones',accountId||zoneId);
	 */
	const CLOUDFLARE_URL 		= "https://api.cloudflare.com/client/v4/%s/%s";

	/**
	 * @var string cloudflare api url (account)
	 */
	private $cf_account_url;

	/**
	 * @var string cloudflare api url (zone)
	 */
	private $cf_zone_url;

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
		if (!$this->get_cloudflare_auth())
		{
			return $this->isEnabled(false);
		}
	}


	/**
	 * register options on options_settings_page
	 *
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

		if ($this->get_cloudflare_auth())
		{
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
		$this->registerExtensionOptions( $this->className,
			[
				'_break1'	=> array(
						'type'		=>	'display',
						'label'		=>	' ',
						'default'	=>	' ',
				),
			]
		);
		if ($this->is_option('cloudflare_zone'))
		{
			$this->get_cf_options();

			if (isset($_POST['_cloudflare_pause'])) {
				$isPaused = ($_POST['_cloudflare_pause'] == 'Pause') ? 'Resume' : 'Pause';
			} else {
				$this->cloudflare_status = $this->get_zone_status();
				$isPaused = ($this->cloudflare_status['paused']) ? 'Resume' : 'Pause';
			}

			if (isset($_POST['_cloudflare_ratelimit'])) {
				$isLimit = ($_POST['_cloudflare_ratelimit'] == 'Add Rule') ? 'Delete' : 'Add';
			} else {
				$isLimit = ($this->get_ratelimit_rule()) ? 'Delete' : 'Add';
			}

			$optionNames = array_filter(
				self::CLOUDFLARE_OPTIONS,
				function($v) {return isset($this->cloudflare_settings[$v]);}
			);
			$optionValues = array_keys(
				array_filter($this->cloudflare_settings,
				function($v) {return $v == 'on';}
			));


			$blacklist_id = $this->get_blacklist_id(false,false) ?: 'blacklist';

			// cleanup prior version option names or options to reset
			$this->delete_option('cloudflare_options');
			$this->delete_option('cloudflare_cache_level');
			$this->delete_option('cloudflare_security');

			$this->registerExtensionOptions( $this->className,
			[
				'_cloudflare_pause'=> array(
						'type'		=>	'button',
						'label'		=>	"{$isPaused} This Zone",
						'default'	=>	$isPaused,
						'after'		=> 	($isPaused == 'Resume')
											? "<span style='color:var(--eac-wp-medium-red)' class='dashicons dashicons-warning'></span>" : "",
						'info'		=>	"Pausing stops traffic from passing through the Cloudflare network, ".
										"making your origin server IP address visible. ".
										"Also, Cloudflare’s security and protection features become disabled.",
						'validate'	=>	[$this,'pause_cloudflare_zone'],
				),
				'_cloudflare_security'=> array(
						'type'		=>	'select',
						'label'		=>	"Security Level",
						'default'	=> 	$this->cloudflare_settings['security_level'],
						'options'	=> 	[
							'Off'			=> 'essentially_off',
							'Low'			=> 'low',
							'Medium'		=> 'medium',
							'High'			=> 'high',
							'*Under Attack*'=> 'under_attack',
						],
						'after'		=> 	($this->is_option('cloudflare_security','under_attack'))
											? "<span style='color:var(--eac-wp-medium-red)' class='dashicons dashicons-warning'></span>" : "",
						'info'		=> 	"Cloudflare's security automatically protects your domain from malicious traffic.".
										"Off: Applies a very low threshold, challenging only the most extreme threats.<br>".
										"Low: Challenges visitors who have a high threat score.<br>".
										"Medium: Challenges visitors with a moderate or high threat score.<br>".
										"High: Challenges any visitor with even a low threat score, plus additional suspicious profiles..<br>".
										"Under Attack: Performs additional security checks to help mitigate layer 7 DDoS attacks.",
						'help'		=>	"[info] Validated users access your website and suspicious traffic is blocked. ".
										"It is designed to be used as one of the last resorts when a zone is under attack.",
						'style'		=> 	'min-width:22ch;',
						'validate'	=>	function($value){return $this->set_cf_setting('security_level',$value);},
				),
				'cloudflare_cachettl'=> array(
						'type'		=>	'select',
						'label'		=>	'Browser Cache Time-To-Live',
						'default'	=> 	$this->cloudflare_settings['browser_cache_ttl'],
						'options'	=> [
							'Respect Existing Headers'	=> 0,
							'2 minutes'			=> 2*MINUTE_IN_SECONDS,
							'5 minutes'			=> 5*MINUTE_IN_SECONDS,
							'20 minutes'		=> 20*MINUTE_IN_SECONDS,
							'30 minutes'		=> 30*MINUTE_IN_SECONDS,
							'1 hour'			=> 1*HOUR_IN_SECONDS,
							'2 hours'			=> 2*HOUR_IN_SECONDS,
							'3 hours'			=> 3*HOUR_IN_SECONDS,
							'4 hours'			=> 4*HOUR_IN_SECONDS,
							'5 hours'			=> 5*HOUR_IN_SECONDS,
							'8 hours'			=> 8*HOUR_IN_SECONDS,
							'12 hours'			=> 12*HOUR_IN_SECONDS,
							'16 hours'			=> 16*HOUR_IN_SECONDS,
							'20 hours'			=> 20*HOUR_IN_SECONDS,
							'1 day'				=> 1*DAY_IN_SECONDS,
							'2 days'			=> 2*DAY_IN_SECONDS,
							'3 days'			=> 3*DAY_IN_SECONDS,
							'4 days'			=> 4*DAY_IN_SECONDS,
							'5 days'			=> 5*DAY_IN_SECONDS,
							'8 days'			=> 8*DAY_IN_SECONDS,
							'16 days'			=> 16*DAY_IN_SECONDS,
							'24 days'			=> 24*DAY_IN_SECONDS,
							'1 month'			=> 1*MONTH_IN_SECONDS,
							'2 months'			=> 2*MONTH_IN_SECONDS,
							'6 months'			=> 6*MONTH_IN_SECONDS,
							'1 year'			=> 1*YEAR_IN_SECONDS,
						],
						'info'		=>	'Controls how long resources cached by client browsers remain valid.',
						'help'		=>	'[info]<br><small>Zone &rarr; Caching &rarr; Configuration</small>',
						'validate'	=>	function($value){return $this->set_cf_setting('browser_cache_ttl',(int)$value);},
						'style'		=> 	'min-width:22ch;',
				),
				'_cloudflare_cache_level'=> array(
						'type'		=>	'select',
						'label'		=>	'Caching Level',
						'default'	=> 	$this->cloudflare_settings['cache_level'],
						'options'	=> [
							'Bypass'			=> 'bypass',
							'Basic'				=> 'basic',
							'Simplified'		=> 'simplified',
							'Aggressive'		=> 'aggressive',
							'Everything'		=> 'cache_everything',
						],
						'info'		=>	"Caching levels determine how much of your website’s static content Cloudflare should cache.<br>".
										"Bypass: Cloudflare does not cache.<br>".
										"Basic: Delivers resources from cache when there is no query string<br>".
										"Simplified: Delivers the same resource to everyone independent of the query string.<br>".
										"Aggressive: Caches all static content that has a query string.<br>".
										"Everything: Treats all content as static and caches all file types beyond the Cloudflare default cached content.",
						'help'		=>	'[info]<br><small>Zone &rarr; Caching &rarr; Configuration</small>',
						'validate'	=>	function($value){return $this->set_cf_setting('cache_level',$value);},
						'style'		=> 	'min-width:22ch;',
				),
				'cloudflare_edge_cache'=> array(
						'type'		=>	'switch',
						'label'		=>	'Edge Caching',
						'default'	=> 	$optionValues,
						'options'	=>	['Support Edge Caching' => 'wp_edge_cache'],
						'info'		=>	'Set cf-edge-cache &amp; x-html-edge-cache http headers to support custom rules or Automatic Platform Optimization. '.
										'Additionally, adds cache-control with appropriate values for optimal caching, '.
										'and edge-cache-tag indicating the requested document type (based on WordPress conditionals or mime type).',
						'help'		=>	'[info] Custom rules can look for these headers (if set)...<br><small>'.
										'cf-edge-cache: "cache,platform=wordpress" or "no-cache".<br>'.
										'x-html-edge-cache: "cache,bypass-cookies=wp-|wordpress|woocommerce" or "no-cache" or "purgeall".<br>'.
										'edge-cache-tag: index,text/html (home page example)'.
										'</small>',
				),
				'_cloudflare_options'=> array(
						'type'		=>	'switch',
						'label'		=>	'Cloudflare Options',
						'default'	=> 	$optionValues,
						'options'	=>	$optionNames,
						'info'		=>	'Enable/disable common Cloudflare options for speed &amp; security.',
						'help'		=>	'[info] '.
									'<br><small>Zone &rarr; Speed &rarr; Settings</small>'.
									'<br><small>Zone &rarr; SSL/TLS &rarr; Edge Certificates</small>'.
									'<br><small>Zone &rarr; Security &rarr; Settings</small>',
						'validate'	=>	[$this,'set_cf_options'],
				),
				'_cloudflare_ratelimit'=> array(
						'type'		=>	'button',
						'label'		=>	"Rate Limit Rule",
						'default'	=>	$isLimit.' Rule',
						'info'		=>	"Adds a rate limiting rule to temporarily block IP addresses after 100 requests within 10 seconds. ".
										"<br><small><strong><em>Set to work with free plans but may overwrite existing rule.</em></strong></small>",
						'help'		=>	'[info]<br><small>Zone &rarr; Security &rarr; Security rules</small>',
						'validate'	=>	[$this,'set_ratelimit_rule'],
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
										"<small>Note: To use an existing Cloudflare IP list, add <code>{eac}Cloudflare</code> to its description.</small>",
						'validate'	=>	[$this,'set_cloudflare_blacklist'],
				),
				'cloudflare_blacklist_purge'=> array(
						'when'		=> 	!empty($this->is_option('cloudflare_blacklist')),
						'type'		=>	'select',
						'label'		=>	'Blacklist Expiration',
						'options'	=> [
							'Indefinite'	=> 0,
							'1 Hour'		=> HOUR_IN_SECONDS,
							'4 Hours'		=> 4*HOUR_IN_SECONDS,
							'8 Hours'		=> 8*HOUR_IN_SECONDS,
							'12 Hours'		=> 12*HOUR_IN_SECONDS,
							'1 Day'			=> DAY_IN_SECONDS,
							'2 Days'		=> 2*DAY_IN_SECONDS,
							'3 Days'		=> 3*DAY_IN_SECONDS,
							'1 Week'		=> 7*DAY_IN_SECONDS,
							'2 Weeks'		=> 14*DAY_IN_SECONDS,
							'1 Month'		=> MONTH_IN_SECONDS,
							'3 Months'		=> 3*MONTH_IN_SECONDS,
						],
						'info'		=>	'Controls how long an IP address is blacklisted due to Risk Assessment.',
						'help'		=> 	'[info] The Cloudflare blacklist is purged hourly.',
						'style'		=> 	'min-width:22ch;',
				),
			]);
		}
		$this->registerExtensionOptions( $this->className,
			[
				'_cloudflare_link'	=> array(
						'type'		=>	'display',
						'label'		=>	'Cloudflare Dashboard',
						'info'		=>	'For full access to and management of your domain(s).',
						'default'	=>	"&rarr; <a href='https://dash.cloudflare.com/' target='_blank'>Log in to your Cloudflare Dashboard</a><br>".
										"&rarr; <a href='https://www.support.cloudflare.com/' target='_blank'>Visit the Cloudflare Help Center</a>",
				),
			]
		);
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
		if ($this->is_option('cloudflare_edge_cache','wp_edge_cache'))
		{
			// do this late
			add_filter('wp_headers',			array($this, 'set_cf_edge_cache'),PHP_INT_MAX,1);
			add_filter('nocache_headers',		function($headers)
			{
				$headers['cf-edge-cache'] = 'no-cache';
				return $headers;
			},PHP_INT_MAX);
		}

		$this->add_filter('after_flush_caches', array($this,'purge_cloudflare_cache'),100,1);
		$this->add_action('cloudflare_purge',	array($this,'purge_cloudflare_cache'),100,1);
		$this->add_action('cloudflare_pause',	function(){$this->pause_cloudflare_zone('Pause');});
		$this->add_action('cloudflare_resume',	function(){$this->pause_cloudflare_zone('Resume');});

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
			add_action($action,					array($this,'purge_cloudflare_cache'),100,0);
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
	 * get/set cloudflare auth parameters
	 *
	 */
	public function get_cloudflare_auth(): bool
	{
		// check for required settings
		if ( (!$this->cloudflare_key   && !($this->cloudflare_key	= $this->get_option('cloudflare_key'))) ) {
			return false;
		}
		if ( (!$this->cloudflare_email && !($this->cloudflare_email = $this->get_option('cloudflare_email'))) ) {
			return false;
		}
		if ($this->cloudflare_zone	= $this->get_option('cloudflare_zone'))
		{
			$this->cf_zone_url 		= sprintf(self::CLOUDFLARE_URL,'zones',$this->cloudflare_zone);
		}
		return true;
	}


	/**
	 * Get the Cloudflare account
	 * we only need the id and may have gotten it from zone status
	 *
	 */
	private function get_cloudflare_account()
	{
		if (empty($this->cloudflare_account))
		{
			$this->cloudflare_account = $this->get_transient(self::TRANSIENT_ACCOUNT, function()
				{
					$result = $this->cf_api_request('get',sprintf(SELF::CLOUDFLARE_URL,'accounts',''));
					return ($result['success']) ? $result['result'][0] : null;
				},
				DAY_IN_SECONDS/2
			);
		}
		$this->cf_account_url 	= sprintf(self::CLOUDFLARE_URL,'accounts',$this->cloudflare_account['id']);
		return $this->cloudflare_account['id'];
	}


	/**
	 * Get the cloudflare zone list
	 *
	 */
	public function get_zone_list(): array
	{
		if ($this->get_cloudflare_auth())
		{
			$zones = $this->get_transient(self::TRANSIENT_ZONE_LIST, function()
				{
					$result = $this->cf_api_request('get',sprintf(self::CLOUDFLARE_URL,'zones',"?page=1&per_page=100"));
					$zones = [];
					if ($result['success']) {
						foreach ( $result['result'] as $list ) {
							if ( isset( $list['name'],$list['id'] ) ) {
								$zones[ $list['name'] ] = $list['id'];
							}
						}
					}
					return $zones;
				},
				DAY_IN_SECONDS/2
			);
			return $zones;
		}
		return [];
	}


	/**
	 * Get the cloudflare zone status
	 *
	 *	  "success": true,
	 *	  "errors": [],
	 *	  "messages": [],
	 *	  "result": {
	 *		"id": "...",
	 *		"name": "example.com",
	 *		"status": "active",
	 *		"paused": true,
	 *		"type": "full",
	 *		"created_on": "2026-01-01T00:00:00.000000Z",
	 *		"modified_on": "2026-09-14T00:00:00.000000Z"
	 *		"account": {
	 *			"id": "..."
	 *		}
	 */
	public function get_zone_status(): array
	{
		if ($this->get_cloudflare_auth() && $this->cf_zone_url)
		{
			$status = $this->get_transient(self::TRANSIENT_ZONE_STATUS, function()
				{
					$result = $this->cf_api_request('get',$this->cf_zone_url);
					if ($result['success']) {
						return $result['result'];
					} else {
						$this->add_admin_notice("Cloudflare: Status (".$result['errors'][0]['code'].") ".$result['errors'][0]['message'],'error');
					}
				},
				MINUTE_IN_SECONDS*15
			);
			$this->cloudflare_account = ['id' => $status['account']['id'] ];
			return $status;
		}
		return [];
	}


	/**
	 * Get our cloudflare options
	 * either posted or from cloudflare
	 *
	 */
	public function get_cf_options(): array
	{
		if ($this->get_cloudflare_auth() && $this->cf_zone_url)
		{
			$settings 	= $this->get_cf_settings();
			$options 	= [];

			foreach(self::CLOUDFLARE_OPTIONS as $name => $id)
			{
				$value = (isset($_POST['_cloudflare_options'][$id])) ? 'on' : $settings[$id] ?? 'off';
				if ($value == 'on') $options[] = $id;
			}

			return $options;
		}
		return [];
	}


	/**
	 * Get all settings from cloudflare
	 *
	 */
	private function get_cf_settings(): array
	{
		$settings = $this->get_transient(self::TRANSIENT_ZONE_SETTINGS, function()
			{
				$settings = [];
				// get all zone settings (this api is deprecated and may be revoked)
				$result = $this->cf_api_request('get',$this->cf_zone_url . "/settings");
				if ($result['success']) {
					foreach ( $result['result'] as $setting ) {
						if (isset($setting['value'],$setting['id'])) {
							if ($setting['editable']) {
								$settings[ $setting['id'] ] = $setting['value'];
							}
						}
					}
				// if failure, get each setting individually (may produces different results)
				} else {
					foreach(self::CLOUDFLARE_OPTIONS as $name => $id) {
						if ($setting = $this->get_cf_setting($id)) {
							if ($setting['editable']) {
								$settings[ $setting['id'] ] = $setting['value'];
							}
						}
					}
				}
				// get individual settings
				foreach(self::CLOUDFLARE_UNIQUE as $name => $id) {
					if ($setting = $this->get_cf_setting($id)) {
						if ($setting['editable']) {
							$settings[ $setting['id'] ] = $setting['value'];
						}
					}
				}
				return $settings;
			},
			MINUTE_IN_SECONDS*15
		);
		foreach ($settings as $id => $value) {
			$this->cloudflare_settings[ $id ] = $value;
		}
		return $settings;
	}


	/**
	 * Get a single cloudflare setting
	 *
	 * @param string $option the name of the setting to get
	 */
	private function get_cf_setting(string $id)
	{
		$result = $this->cf_api_request('get',$this->cf_zone_url . "/settings/{$id}");

		return ($result['success']) ? $result['result'] : false;
	}


	/**
	 * Set the cloudflare toggle settings
	 *
	 */
	public function set_cf_options($value, $fieldName, $metaData, $priorValue)
	{
		if (!is_array($value)) $value = [];
		if (!is_array($priorValue)) $priorValue = [];

		if ($this->get_cloudflare_auth() && $this->cf_zone_url)
		{
			$settings = $this->get_transient(self::TRANSIENT_ZONE_SETTINGS,[]);
			foreach(self::CLOUDFLARE_OPTIONS as $name => $id)
			{
				if ( (in_array($id,$value) && !in_array($id,$priorValue))
				||	 (!in_array($id,$value) && in_array($id,$priorValue))
				) {
					$settings[$id] = $this->set_cf_setting($id, in_array($id,$value) ? 'on' : 'off');
				}
			}

			$this->set_transient(self::TRANSIENT_ZONE_SETTINGS,$settings,MINUTE_IN_SECONDS*15);
		}
		return (array)$value;
	}


	/**
	 * Set a single cloudflare setting
	 *
	 */
	private function set_cf_setting(string $id, $value)
	{
		$result = $this->cf_api_request('patch',$this->cf_zone_url . "/settings/{$id}",
			[
				'body' => [ 'value' => $value ]
			]
		);

		if (!$result['success']) {
			$this->add_admin_notice("Cloudflare (".$result['errors'][0]['code'].") ".$result['errors'][0]['message']." [{$id}:{$value}]",'error');
			return false;
		}

		if (!in_array($id,self::CLOUDFLARE_OPTIONS)) {
			if ($settings = $this->get_transient(self::TRANSIENT_ZONE_SETTINGS)) {
				if (isset($settings[$id])) {
					$settings[$id] = $value;
					$this->set_transient(self::TRANSIENT_ZONE_SETTINGS,$settings,MINUTE_IN_SECONDS*15);
				}
			}
		}
		return $value;
	}


	/**
	 * Eligible/Ineligible for cloudflare edge caching
	 *
	 * @param array $headers http headers
	 */
	public function set_cf_edge_cache(?array $headers)
	{
		/**
		 * filter 'cloudflare_use_cache' - override cache setting (same as cloudflare plugin)
		 * @param bool enable caching
		 */
		if (apply_filters('cloudflare_use_cache', $this->is_cacheable($headers))) {
			$headers = is_array($headers) ? array_change_key_case($headers, CASE_LOWER) : [];
			if ($ttl = $this->get_option('cloudflare_cachettl')) {
				header_remove('cache-control');
				$headers['cache-control']	= "public,max-age={$ttl}";
			}
			$headers['x-html-edge-cache'] 	= "cache,bypass-cookies=wp-|wordpress|woocommerce";
			$headers['cf-edge-cache']		= 'cache,platform=wordpress';
			$headers['edge-cache-tag']		= $this->get_cache_tag_type($headers);
		} else {
			$headers['x-html-edge-cache'] 	= "no-cache";
			$headers['cf-edge-cache']		= 'no-cache';
		}
		return $headers;
	}


	/**
	 * Eligible/Ineligible for cloudflare caching
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
	/*
		if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
			return false;
		}
		if (defined('REST_REQUEST') && REST_REQUEST) {
			return false;
		}
		if (wp_is_xml_request()) {
			return false;
		}
	*/
		if ($this->doing_ajax() || is_user_logged_in()) {
			return false;
		}
		if (class_exists('woocommerce') &&
			(is_cart() || is_checkout() || is_order_received_page() || is_account_page())) {
			return false;
		}
		$checkHeaders = array_change_key_case( array_merge(
			$this->explode_with_keys(':',headers_list()),
			$headers
		), CASE_LOWER);
		if (isset($checkHeaders['cache-control'])) {
			if (str_contains($checkHeaders['cache-control'],'no-cache')) {
				return false;
			}
			if (str_contains($checkHeaders['cache-control'],'no-store')) {
				return false;
			}
			if (str_contains($checkHeaders['cache-control'],'private')) {
				return false;
			}
			if (str_contains($checkHeaders['cache-control'],'must-revalidate')) {
				return false;
			}
		}
		if (isset($checkHeaders['pragma'])) {
			if (str_contains($checkHeaders['pragma'],'no-cache')) {
				return false;
			}
		}
		foreach ($_COOKIE as $cookie => $value) {
			if (str_starts_with('wp-',$cookie)) return false;
			if (str_starts_with('wordpress_',$cookie) && $cookie != 'wordpress_test_cookie') return false;
			if (str_starts_with('woocommerce_',$cookie)) return false;
			if (str_starts_with('comment_',$cookie)) return false;
		}

		return true;
	}


	/**
	 * Get the mime type for the current request
	 *
	 */
	protected function get_cache_tag_type($headers,$extension='')
	{
		global $wp_query;

		$cacheTag = [];

		if ( isset( $wp_query ) ) {
			if (is_front_page() ||
				is_home()) $cacheTag[] = 'index';
			if (is_page()) $cacheTag[] = 'page';
			if (is_single()) $cacheTag[] = 'post';
			if (is_search()) $cacheTag[] = 'search';
			if (is_category()) $cacheTag[] = 'category';
			if (is_tag()) $cacheTag[] = 'tag';
			if (is_tax()) $cacheTag[] = 'taxonomy';
			if (is_author()) $cacheTag[] = 'author';
			if (is_date()) $cacheTag[] = 'date';
			if (is_archive()) $cacheTag[] = 'archive';
			if (function_exists('is_product') && is_product()) $cacheTag[] = 'product_item';
			if (function_exists('is_product_category') && is_product_category()) $cacheTag[] = 'product_category';
			if (function_exists('is_product_tag') && is_product_tag()) $cacheTag[] = 'product_tag';
			if (function_exists('is_shop') && is_shop()) $cacheTag[] = 'shop';
		}

		if (empty($extension) && array_key_exists('REQUEST_URI', $_SERVER))
		{
			$extension = explode('?',$_SERVER['REQUEST_URI']);
			$extension = pathinfo(trim($extension[0],'/'),PATHINFO_EXTENSION);
		}

		if (isset($headers['content-type'])) {
			list($contentType,) = explode(';',$headers['content-type']);
			$type = explode($contentType,';');
			$cacheTag[] = trim($type[0]);
		} else
		if (defined('REST_REQUEST') && REST_REQUEST) {
			$cacheTag[] = 'application/json';
		} else
		if (wp_is_json_request()) {
			$cacheTag[] = 'application/json';
		} else
		if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
			$cacheTag[] = 'text/xml';
		} else
		if (wp_is_xml_request()) {
			$cacheTag[] = 'text/xml';
		} else
		if ($extension) {
			$mime_types = array_merge(wp_get_mime_types(),[
				'json'		=> 'application/json',
				'xml'		=> 'text/xml',
				'rss'		=> 'application/rss+xml',
			]);
			if (isset($mime_types[$extension])) {
				$cacheTag[] = $mime_types[$extension];
			}
		} else $cacheTag[] = get_option( 'html_type' );

		return implode(',',$cacheTag);
	}


	/**
	 * Purge the cloudflare cache
	 *
	 * @param array caches purged - only with 'after_flush_caches'
	 */
	public function purge_cloudflare_cache($caches = [])
	{
		static $onlyOnce = 0;
		if ($onlyOnce++) return $caches;

		if ($this->get_cloudflare_auth() && $this->cf_zone_url)
		{
			$site_url = parse_url( get_site_url() );
			$site_url = trailingslashit($site_url['host'] . ($site_url['path'] ?? '')).'*';

			$result = $this->cf_api_request('post',$this->cf_zone_url . "/purge_cache",
				[
					'body' => [ "prefixes" => [$site_url] ]
				]
			);

			if ($result['success']) {
				$this->add_admin_notice('The Cloudflare cache for '.$site_url.' has been purged','success');
				$caches[] = 'Cloudflare Cache';
			} else {
				$this->add_admin_notice("Cloudflare Cache Purge: (".$result['errors'][0]['code'].") ".$result['errors'][0]['message'],'error');
			}
		}
		header("x-html-edge-cache: purgeall");
		return $caches;
	}


	/**
	 * Pause the cloudflare dns service
	 *
	 * @param array $option pause/resume
	 */
	public function pause_cloudflare_zone(string $option)
	{
		$paused = ($option == 'Pause');

		if ($this->get_cloudflare_auth() && $this->cf_zone_url)
		{
			$this->delete_transient(self::TRANSIENT_ZONE_STATUS);
			$result = $this->cf_api_request('patch',$this->cf_zone_url,
				[
					'body' => [ "paused" => $paused ]
				]
			);

			if ($result['success']) {
				$site_id = $result['result']['name'];
				$this->add_admin_notice("The Cloudflare service for {$site_id} has been {$option}d",'success');
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
		$result = $this->cf_api_request('get',$this->cf_zone_url . "/rulesets/phases/http_ratelimit/entrypoint");

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

			$result = $this->cf_api_request('put',$this->cf_zone_url . "/rulesets/phases/http_ratelimit/entrypoint",
				[
					'body' => ['rules' => $rule],
				]
			);

			if ($result['success']) {
				$this->add_admin_notice("Cloudflare Ratelimit: <em>".self::RATE_LIMIT_DESC.'</em> created.','success');
			} else {
				$this->add_admin_notice("Cloudflare Ratelimit: (".$result['errors'][0]['code'].") ".$result['errors'][0]['message'],'error');
			}
			return $value;
		}
		else if ($value == 'Delete Rule')
		{
			$result = $this->cf_api_request('get',$this->cf_zone_url . "/rulesets/phases/http_ratelimit/entrypoint");

			if ($result['success'] && isset($result['result']['rules']))
			{
				foreach ($result['result']['rules'] as $ix => $rule) {
					if ($rule['description'] == self::RATE_LIMIT_DESC) {
						unset($result['result']['rules'][$ix]);
						$result = $this->cf_api_request('put',$this->cf_zone_url . "/rulesets/phases/http_ratelimit/entrypoint",
							[
								'body' => ['rules'=>$result['result']['rules']],
							]
						);
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
			$list_url = $this->cf_account_url . "/rules/lists";

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

			$result = $this->cf_api_request('post',$list_url."/{$list_id}/items",
				[
					'body' => array([
						'ip'		=> $ipAddress,
						'comment'	=> trim($comment,' ;'),
					])
				]
			);

			if (!$result['success']) {
				$this->cloudflare_api_error(
					$result['errors'][0]['code'],
					$result['errors'][0]['message'],
					__FUNCTION__,
					$list_url
				);
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
			if ($this->get_blacklist_id()) $this->set_blacklist_rule();
		}
		return $value;
	}


	/**
	 * get or create blacklist list (at the account level)
	 *
	 */
	public function get_blacklist_id($verbose = true, $create = true)
	{
		if ($this->get_cloudflare_account())
		{
			$blacklist = $this->get_transient(self::TRANSIENT_BLACKLIST_LIST, function() use($verbose,$create)
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
		return false;
	}


	/**
	 * get or create blacklist list (at the account level)
	 *
	 */
	private function get_blacklist_list($verbose = true, $create = true)
	{
		$list_url	= $this->cf_account_url . "/rules/lists";
		$blacklist	= false;

		// get existing black list
		$result = $this->cf_api_request('get',$list_url);

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
			$result = $this->cf_api_request('post',$list_url,
				[
					'body' => [
						'kind'			=> 'ip',
						'name'			=> self::BLACK_LIST_NAME,
						'description'	=> self::BLACK_LIST_DESC,
					]
				]
			);

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
		$rule_url	= $this->cf_zone_url . "/rulesets";
		$rule_id	= false;
		$rule_list	= false;

		// get existing rulesets
		$result = $this->cf_api_request('get',$rule_url);

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
			$result = $this->cf_api_request('post',$rule_url,
				[
					'body' => [
						'kind'			=> 'custom',
						'name'			=> self::BLACK_LIST_NAME,
						'description'	=> self::BLACK_LIST_DESC,
						'phase'			=> 'http_request_firewall_custom',
					]
				]
			);

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
		$result = $this->cf_api_request('get',$rule_url."/{$rule_id}");

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
		$result = $this->cf_api_request('post',$rule_url."/{$rule_id}/rules",
			[
				'body' => [
					'description'	=> self::BLACK_LIST_DESC,
					'expression'	=> '(ip.src in $'.$this->black_list_name.')',
					'action'		=> 'block',
					'enabled'		=> true,
				]
			]
		);

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
			$list_url	= $this->cf_account_url . "/rules/lists";

			// get existing blacklist items
			$result = $this->cf_api_request('get',$list_url."/{$list_id}/items?per_page=500");
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
			$result = $this->cf_api_request('delete',$list_url."/{$list_id}/items",
				[
					'body' => ['items' => $itemsToDelete],
				]
			);
			if ($result['success']) {
				$this->logNotice("Cloudflare Blacklist removed {$count} of {$total} IP addresses");
			} else {
				$this->logDebug($result,__METHOD__);
			}
		}
	}


	/**
	 * Cloudflare API request
	 *
	 */
	private function cf_api_request( string $method, string $url, array $args = [] )
	{
		$args['method'] 	= strtoupper($method);
		$args['headers']	= [
			'Accept'		=> 'application/json',
			'X-Auth-Key'	=> $this->cloudflare_key,
			'X-Auth-Email'	=> $this->cloudflare_email,
		];

		if ($args['method'] != 'GET') {
			$args['headers']['Content-Type'] = 'application/json';
			if (isset($args['body']) && is_array($args['body'])) {
				$args['body'] = wp_json_encode($args['body']);
			}
		}

		$result = wp_remote_request($url, $args);

		if (is_wp_error($result))
		{
			return $this->cloudflare_api_error(
					$result->get_error_code(),
					$result->get_error_message(),
					$args['method'],
					$url
			);
		}

        $status = (int)wp_remote_retrieve_response_code($result);
        if ($status >= 300)
        {
            return $this->cloudflare_api_error(
            		'http_status_'.$status,
            		wp_remote_retrieve_response_message($result),
            		$args['method'],
            		$url
            );
        }

		$result = json_decode( wp_remote_retrieve_body($result), true );

        if (json_last_error() !== JSON_ERROR_NONE)
        {
			return $this->cloudflare_api_error(
					'json_decode',
					'Error decoding Cloudflare response',
					$args['method'],
					$url
			);
        }

		return $result;
	}


	/**
	 * Cloudflare API error
	 *
	 */
	private function cloudflare_api_error( string $code, string $message, string $method, string $url ): array
	{
			$url = str_replace(
				[
					$this->cloudflare_account['id'] ?? '{account}' ,
					$this->cloudflare_zone ?: '{zone}'
				],[
					'{account}',
					'{zone}'
				],
				$url
			);
			$this->logError(["({$code}) {$message}","{$method} {$url}"],__FUNCTION__);

			return [
				'success' 		=> false,
				'errors'		=> [
					'code'		=> $code,
					'message'	=> $message,
				]
			];
	}
}
/**
* return a new instance of this class
*/
if (isset($this)) return new cloudflare_extension($this);
?>
