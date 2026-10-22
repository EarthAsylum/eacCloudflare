<?php
namespace EarthAsylumConsulting\Extensions;

/**
 * Extension: cloudflare_extension - enable Cloudflare API
 *
 * Recommended for and supports free plans.
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
	const VERSION			= '26.1022.1';

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
	 * @var array Cloudflare toggle (on/off) options
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
	 * @var array Cloudflare distinct/independent settings
	 * option_name => setting_name
	 */
	const CLOUDFLARE_SETTINGS = [
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
	const TRANSIENT_BLACKLIST 		= 'cloudflare_blacklist_list';

	/**
	 * @var int transient time-to-live
	 */
	const TRANSIENT_ACCOUNT_TTL		= DAY_IN_SECONDS;
	const TRANSIENT_ZONE_TTL		= HOUR_IN_SECONDS;
	const TRANSIENT_BLACKLIST_TTL 	= HOUR_IN_SECONDS;

	/**
	 * @var string blacklist name & description
	 */
	const BLACK_LIST_NAME			= 'risk_assessment_blacklist';
	const BLACK_LIST_DESC			= '{eac}Cloudflare - Block IP addresses identified by Risk Assessment';
	private $black_list_name		= self::BLACK_LIST_NAME;

	/**
	 * @var string ratelimit rule description
	 */
	const RATE_LIMIT_DESC 			= '{eac}Cloudflare - Default Rate Limiting Rule';

	/**
	 * @var string Cloudflare api url
	 * sprintf(self::CLOUDFLARE_URL,'accounts'||'zones',accountId||zoneId);
	 */
	const CLOUDFLARE_URL 			= "https://api.cloudflare.com/client/v4/%s/%s";

	/**
	 * @var string cloudflare api url (account)
	 */
	private $cf_account_url;

	/**
	 * @var string cloudflare api url (zone)
	 */
	private $cf_zone_url;

	/**
	 * @var string tag prefix for multi-site within zone
	 * multi-site = 'siteN', else l|d|s|p - wp environment
	 */
	private $cf_tag_prefix 			= '';

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
	 * @var string cloudflare account email
	 */
	private $cloudflare_email;

	/**
	 * @var string cloudflare api key
	 */
	private $cloudflare_key;

	/**
	 * @var string cloudflare api token
	 */
	private $cloudflare_token;

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

		$this->cloudflare_token = (defined('CLOUDFLARE_API_TOKEN'))
			? CLOUDFLARE_API_TOKEN
			: false;

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
		require_once 'cloudflare.options.php';
	}


	/**
	 * Add help tab on admin page
	 *
	 */
	public function admin_options_help()
	{
	//	if (!$this->plugin->isSettingsPage(self::TAB_NAME)) return;
	}


	/**
	 * Add filters and actions - called from main plugin
	 *
	 */
	public function addActionsAndFilters()
	{
		if ($this->is_option('cloudflare_edge_cache','wp_edge_cache'))
		{
			add_filter('nocache_headers', 		function($headers)
			{
				$headers['cf-edge-cache'] = 'no-cache';
				return $headers;
			});

			// doing this late/last
			add_filter('wp_headers', 			function($headers)
			{
				add_filter('wp_headers',		array($this, 'set_cf_edge_cache'),PHP_INT_MAX,1);
				return $headers;
			});
		}

		// blacklisting based on risk assessment
		if ($this->is_option('cloudflare_blacklist'))
		{
			if ($this->is_option('cloudflare_blacklist_purge')) {
				$this->do_action( 'add_event_task', 'hourly',	array($this, 'blacklist_purge'));
			}

			// Security -> Risk Assessment
			$this->add_action('risk_assessment_blocked',		array($this, 'add_ip_to_blacklist'),10,2 );
			$this->add_action('risk_assessment_unblocked',		array($this, 'remove_ip_from_blacklist'),10,1);
		}

		// actions available to outside actors
		add_action('cloudflare_pause',			function(){$this->pause_cloudflare_zone('Pause');});
		add_action('cloudflare_resume',			function(){$this->pause_cloudflare_zone('Resume');});
		add_action('cloudflare_purge',			array($this,'purge_cloudflare_cache'),10,0);
		add_action('cloudflare_purge_tags',		array($this,'purge_cloudflare_tags'),10,3);

		add_action('init',						array($this,'add_purge_actions'),100);
	}


	/**
	 * Add cloudflare cache purge actions on init
	 *
	 */
	public function add_purge_actions()
	{
		if (defined('CLOUDFLARE_CACHETAG_PREFIX') && is_scalar(CLOUDFLARE_CACHETAG_PREFIX)) {
			$this->cf_tag_prefix = rtrim((string)CLOUDFLARE_CACHETAG_PREFIX,'-').'-';
		} else if (is_multisite()) {
			$this->cf_tag_prefix = 'site'.(string)get_current_blog_id().'-';
		} else if (function_exists('\wp_get_environment_type')) {
			$this->cf_tag_prefix = substr(wp_get_environment_type(),0,1).'-';
		}

		$purge_actions = array(
			'switch_theme',						// Switch theme
			'customize_save_after',				// Customizer
		/*
			'eacDoojigger_after_flush_caches'	// after flushing WP caches
			'autoptimize_action_cachepurged'	// Autoptimize plugin
			'cache_enabler_clear_site_cache'	// Cache Enabler plugin
			'w3tc_flush_all'					// W3 Total Cache plugin
			'wpo_cache_flush'					// WP Optimize plugin
			'after_rocket_clean_domain'			// WP Rocket plugin
			'wp_cache_cleared'					// WP Super Cache plugin
		*/
		);

		if (defined('CLOUDFLARE_PURGE_ACTIONS') && is_array(CLOUDFLARE_PURGE_ACTIONS)) {
			$purge_actions = array_merge($purge_actions,CLOUDFLARE_PURGE_ACTIONS);
		}

		/**
		 * filter 'cloudflare_purge_everything_actions' (same as cloudflare plugin)
		 * @param array actions to trigger cache purge
		 */
		$purge_actions = apply_filters('cloudflare_purge_everything_actions', $purge_actions);

		// purge everything
		foreach ($purge_actions as $action) {
			add_filter($action,					array($this,'purge_cloudflare_cache'),10,1);
		}

		foreach (['post','term','user'] as $trigger) {
			$purge_actions = array(
				"clean_{$trigger}_cache"		// action triggered on update
			);
			/**
			 * filter 'cloudflare_purge_[post|term|user]_actions'
			 * @param array actions to trigger cache purge by tag
			 */
			$purge_actions = apply_filters("cloudflare_purge_{$trigger}_actions", $purge_actions);

			// purge by tag(s)
			foreach ($purge_actions as $action) {
				add_action($action,				function($id,$object) use($trigger) {
					$type = match($trigger) {
						'post'	=> is_object($object) ? $object->post_type : $trigger,
						'user' 	=> 'author',
						default	=> is_string($object) ? $object : $trigger,
					};
					$this->purge_cloudflare_tags($type,$id,$object);
				},10,2);
			}
		}

		// purge post after comment addition
		add_action('comment_post', 				function($id,$status,$comment) {
	        if ($comment_status == 1 && is_array($comment) && $comment['comment_post_ID']) {
				$this->purge_cloudflare_tags('post',$comment['comment_post_ID']);
			}
		},10,3);

		// purge post after comment status change
		add_action('transition_comment_status', function($old,$new,$comment) {
	        if ($old == 'approved' || $new == 'approved') {
				$this->purge_cloudflare_tags('post',$comment['comment_post_ID']);
			}
		},10,3);
	}


	/**
	 *
	 * Account/zone
	 *
	 */


	/**
	 * get/set cloudflare auth parameters
	 *
	 */
	public function get_cloudflare_auth(): bool
	{
		$authMethod = false;

		// check for required settings
		if ( ($this->cloudflare_token || ($this->cloudflare_token = $this->get_option_decrypt('cloudflare_token'))) ) {
			$authMethod = 'token';
		} else {
			if ( (!$this->cloudflare_key   && !($this->cloudflare_key	= $this->get_option('cloudflare_key'))) ) {
				return false;
			}
			if ( (!$this->cloudflare_email && !($this->cloudflare_email = $this->get_option('cloudflare_email'))) ) {
				return false;
			}
			$authMethod = 'emailkey';
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
				self::TRANSIENT_ACCOUNT_TTL
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
				self::TRANSIENT_ZONE_TTL
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
				self::TRANSIENT_ZONE_TTL
			);
			$this->cloudflare_account = ['id' => $status['account']['id'] ];
			return $status;
		}
		return [];
	}


	/**
	 *
	 * Options (local) and settings (cloudflare)
	 *
	 */


	/**
	 * Get our cloudflare options
	 * either posted or from cloudflare
	 *
	 */
	public function get_cf_options($withPost=true): array
	{
		if ($this->get_cloudflare_auth() && $this->cf_zone_url)
		{
			$settings 	= $this->get_cf_settings();
			$options 	= [];

			foreach(self::CLOUDFLARE_OPTIONS as $name => $id)
			{
				if ($withPost && isset($_POST['_cloudflare_options'])) {
					$value = (in_array($id,$_POST['_cloudflare_options']))
						? 'on' : ( (isset($settings[$id])) ? 'off' : false );
				} else {
					$value = $settings[$id] ?? false;
				}
				if ($value) $options[$id] = $value;
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
		$this->cloudflare_settings = $this->get_transient(self::TRANSIENT_ZONE_SETTINGS, function()
			{
				$settings = [];
				// get all editable zone settings (this api is deprecated and may be revoked)
				$result = $this->cf_api_request('get',$this->cf_zone_url . "/settings");
				if ($result['success']) {
					foreach ( $result['result'] as $setting ) {
						if ($setting['editable']) {
							$settings[ $setting['id'] ] = $setting['value'];
						}
					}
				// on failure, get each setting individually
				} else {
					foreach(array_merge(self::CLOUDFLARE_OPTIONS,self::CLOUDFLARE_SETTINGS) as $id) {
						if ($setting = $this->get_cf_setting($id)) {
							if ($setting['editable']) {
								$settings[ $setting['id'] ] = $setting['value'];
							}
						}
					}
				}
				return $settings;
			},
			self::TRANSIENT_ZONE_TTL
		);

		return $this->cloudflare_settings;
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
		//if (!is_array($priorValue))
		$priorValue = array_keys( array_filter($this->get_cf_options(false),fn($v) => $v == 'on') );

		if ($this->get_cloudflare_auth() && $this->cf_zone_url)
		{
			$settings = $this->get_transient(self::TRANSIENT_ZONE_SETTINGS,[]);
			foreach(self::CLOUDFLARE_OPTIONS as $id)
			{
				if ( (in_array($id,$value) && !in_array($id,$priorValue))
				||	 (!in_array($id,$value) && in_array($id,$priorValue))
				) {
					$settings[$id] = $this->set_cf_setting($id, in_array($id,$value) ? 'on' : 'off');
				}
			}

			$this->set_transient(self::TRANSIENT_ZONE_SETTINGS,$settings,self::TRANSIENT_ZONE_TTL);
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

		if (in_array($id,self::CLOUDFLARE_SETTINGS)) {
			$settings = $this->get_transient(self::TRANSIENT_ZONE_SETTINGS,[]);
			$settings[$id] = $value;
			$this->set_transient(self::TRANSIENT_ZONE_SETTINGS,$settings,self::TRANSIENT_ZONE_TTL);
		}
		return $value;
	}


	/**
	 *
	 * Caching Headers
	 *
	 */


	/**
	 * Eligible/Ineligible for cloudflare edge caching
	 *
	 * @param array $headers http headers
	 */
	public function set_cf_edge_cache(?array $headers)
	{
		/*
			HTTP/2 & HTTP/3: The specification (RFC 9113) requires that header names
			must be converted to lowercase before they are transmitted over the network.
		*/
		$headers = is_array($headers) ? array_change_key_case($headers, CASE_LOWER) : [];
		/**
		 * filter 'cloudflare_use_cache' - override cache setting (same as cloudflare plugin)
		 * @param bool enable caching
		 */
		if (apply_filters('cloudflare_use_cache', $this->is_cacheable($headers))) {
			if ($ttl = $this->get_option('cloudflare_cachettl')) {
				header_remove('cache-control');
				$headers['cache-control']	= "public,max-age={$ttl}";
			}
			$headers['x-html-edge-cache'] 	= "cache,bypass-cookies=wp-|wordpress|woocommerce";
			$headers['cf-edge-cache']		= 'cache,platform=wordpress';
			$headers['cache-tag']			= $this->get_cache_tag_type($headers);
		} else {
			$headers['x-html-edge-cache'] 	= "no-cache";
			$headers['cf-edge-cache']		= 'no-cache';
		}
		/*
		$result = array_reduce(array_keys($headers), function($carry, $key) use ($headers) {
			$new_key = ucwords($key,"-");
			$carry[$new_key] = $headers[$key];
			return $carry;
		}, []);
		*/
		return $headers;
	}


	/**
	 * Eligible/Ineligible for cloudflare caching
	 *
	 * @param array $headers http headers
	 */
	protected function is_cacheable($headers)
	{
		if (defined('CLOUDFLARE_USE_CACHE') && ! CLOUDFLARE_USE_CACHE) {
			return false;
		}
		if ($_SERVER['REQUEST_METHOD'] != 'GET') {
			return false;
		}
		if (http_response_code() != 200) {
			return false;
		}
		if ($this->doing_ajax() || is_user_logged_in()) {
			return false;
		}
		if (class_exists('woocommerce',false) &&
			(is_cart() || is_checkout() || is_order_received_page() || is_account_page())) {
			return false;
		}
		$checkHeaders = array_merge(
			array_change_key_case($this->explode_with_keys(':',(array)headers_list()),CASE_LOWER),
			(array)$headers
		);
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
		//	if (str_contains($checkHeaders['cache-control'],'must-revalidate')) {
		//		return false;
		//	}
		}
		if (isset($checkHeaders['pragma'])) {
			if (str_contains($checkHeaders['pragma'],'no-cache')) {
				return false;
			}
		}
		if (isset($checkHeaders['cf-edge-cache'])) {
			if (str_contains($checkHeaders['cf-edge-cache'],'no-cache')) {
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
	protected function get_cache_tag_terms($theID,$optTerm=null)
	{
		$cacheTags = [];
		if ($tags = get_the_category($theID)) {
			foreach ($tags as $tag) {
				$cacheTags[] = $this->cf_tag_prefix.'category-'.$tag->term_id;
			}
		}
		if ($tags = get_the_tags($theID)) {
			foreach ($tags as $tag) {
				$cacheTags[] = $this->cf_tag_prefix.'tag-'.$tag->term_id;
			}
		}

		if ($optTerm) {
			foreach ((array)$optTerm as $term) {
				if ($tags = get_the_terms($theID,$term)) {
					foreach ($tags as $tag) {
						$cacheTags[] = $this->cf_tag_prefix.$term.'-'.$tag->term_id;
					}
				}
			}
		}
		return $cacheTags;
	}


	/**
	 * Get the mime type for the current request
	 *
	 */
	protected function get_cache_tag_type($headers)
	{
		global $wp_query;

		$cacheTags = [];
		$theID = get_queried_object_id();

		if ( isset( $wp_query ) ) {
			if (is_front_page() || is_home()) {
				$cacheTags[] = $this->cf_tag_prefix.'index';
				$cacheTags = $cacheTags + $this->get_cache_tag_terms($theID);
			}
			if (is_page()) {
				$cacheTags[] = $this->cf_tag_prefix.'page';
				$cacheTags[] = $this->cf_tag_prefix.'page-'.$theID;
				$cacheTags = $cacheTags + $this->get_cache_tag_terms($theID);
			}
			if (is_single()) {
				$type = get_queried_object()->post_type;
				$cacheTags[] = $this->cf_tag_prefix.$type;
				$cacheTags[] = $this->cf_tag_prefix.$type.'-'.$theID;
				$cacheTags = $cacheTags + $this->get_cache_tag_terms($theID);
    		}
			if (is_category()) {
				$cacheTags[] = $this->cf_tag_prefix.'category';
				$cacheTags[] = $this->cf_tag_prefix.'category-'.$theID;
			}
			if (is_tag()) {
				$cacheTags[] = '$this->cf_tag_prefix.tag';
				$cacheTags[] = $this->cf_tag_prefix.'tag-'.$theID;
			}
			if (is_author()) {
				$cacheTags[] = $this->cf_tag_prefix.'author';
				$cacheTags[] = $this->cf_tag_prefix.'author-'.$theID;
			}
			if (is_search()) $cacheTags[] = $this->cf_tag_prefix.'search';
			if (is_attachment()) $cacheTags[] = $this->cf_tag_prefix.'attachment';
			if (is_tax()) $cacheTags[] = $this->cf_tag_prefix.'taxonomy';
			if (is_date()) $cacheTags[] = $this->cf_tag_prefix.'date';
			if (is_archive()) $cacheTags[] = $this->cf_tag_prefix.'archive';
			if (is_feed()) $cacheTags[] = $this->cf_tag_prefix.'feed';
		}
		if (function_exists('is_product') && is_product()) {
			// is_single() already added 'product','
			$cacheTags = $cacheTags + $this->get_cache_tag_terms($theID,['product_cat','product_tag']);
		}
		if (function_exists('is_product_category') && is_product_category()) {
			$cacheTags[] = $this->cf_tag_prefix.'product_cat';
			$cacheTags[] = $this->cf_tag_prefix.'product_cat-'.$theID;
		}
		if (function_exists('is_product_tag') && is_product_tag()) {
			$cacheTags[] = $this->cf_tag_prefix.'product_tag';
			$cacheTags[] = $this->cf_tag_prefix.'product_tag-'.$theID;
		}
		if (function_exists('is_shop') && is_shop()) $cacheTags[] = $this->cf_tag_prefix.'shop';

		if (isset($headers['content-type'])) {
			list($contentType,) = explode(';',$headers['content-type']);
			$cacheTags[] = trim($contentType);
		} else
		if (defined('REST_REQUEST') && REST_REQUEST) {
			$cacheTags[] = 'application/json';
		} else
		if (wp_is_json_request()) {
			$cacheTags[] = 'application/json';
		} else
		if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
			$cacheTags[] = 'text/xml';
		} else
		if (wp_is_xml_request()) {
			$cacheTags[] = 'text/xml';
		} else
		if (array_key_exists('REQUEST_URI', $_SERVER)) {
			$mime_types = array_merge(wp_get_mime_types(),[
				'json'		=> 'application/json',
				'xml'		=> 'text/xml',
				'rss'		=> 'application/rss+xml',
			]);
			$type = wp_check_filetype($_SERVER['REQUEST_URI'],$mime_types);
			$cacheTags[] = ($type['type']) ? $type['type'] : get_option( 'html_type' );
		} else {
			$cacheTags[] = get_option( 'html_type' );
		}

		return ltrim( rtrim($headers['cache-tag'] ?? '',',;') .
				','.implode(',',array_unique(array_filter($cacheTags))), ',');
	}


	/**
	 *
	 * Purging cache
	 *
	 */


	/**
	 * Purge the cloudflare cache
	 *
	 * @param array caches purged - only with 'after_flush_caches'
	 */
	public function purge_cloudflare_cache($caches = [])
	{
		static $onlyOnce = 0;
		if ($onlyOnce++) return $caches;

		$method = (defined('CLOUDFLARE_CACHE_PURGE') &&
				 (CLOUDFLARE_CACHE_PURGE == 'hard' || CLOUDFLARE_CACHE_PURGE == 'purge'))
		? '/purge_cache' : '/invalidate_cache';

		if ($this->get_cloudflare_auth() && $this->cf_zone_url)
		{
			$site_url = parse_url( get_site_url() );
			$site_url = trailingslashit($site_url['host'] . ($site_url['path'] ?? '')).'*';

			$result = $this->cf_api_request('post',$this->cf_zone_url . $method,
				[
					'body' => [ "prefixes" => [$site_url] ]
				]
			);

			if ($result['success']) {
				if (is_array($caches)) $caches[] = 'Cloudflare Cache';
				$this->add_admin_notice('The Cloudflare cache for '.$site_url.' has been purged','success');
				$this->logNotice(current_action().': The Cloudflare cache has been purged');
			} else {
				$this->add_admin_notice('Cloudflare Cache Purge: '.$result['errors'][0]['message'],'error');
				$this->logError(current_action().": Cloudflare Cache Purge: (".$result['errors'][0]['code'].") ".$result['errors'][0]['message']);
			}
		}
		//header("x-html-edge-cache: purgeall");
		return $caches;
	}


	/**
	 * Purge the cloudflare cache by cache-tag(s)
	 *
	 * @param string type (page/post/category/tag)
	 * @param int[] object id number(s)
	 * @param object optional
	 */
	public function purge_cloudflare_tags($type,$ids,$object=null)
	{
		if ($type == 'revision') return;

		if (!$this->is_option('cloudflare_edge_cache','wp_edge_cache'))
		{
			return $this->purge_cloudflare_cache();
		}

		if (!is_array($ids)) $ids = array($ids);
		$cacheTags = [];
		foreach ($ids as $id) {
			$cacheTags[] = $this->cf_tag_prefix.$type.'-'.$id;
			if ($type == 'post') {
				$cacheTags = $cacheTags + $this->get_cache_tag_terms($id);
			}
		}
		$cacheTags = array_unique($cacheTags);

		$method = (defined('CLOUDFLARE_CACHE_PURGE') &&
				 (CLOUDFLARE_CACHE_PURGE == 'hard' || CLOUDFLARE_CACHE_PURGE == 'purge'))
		? '/purge_cache' : '/invalidate_cache';

		if (!empty($cacheTags) && $this->get_cloudflare_auth() && $this->cf_zone_url)
		{
			$result = $this->cf_api_request('post',$this->cf_zone_url . $method,
				[
					'body' => [ "tags" => $cacheTags ]
				]
			);

			if ($result['success']) {
				$this->add_admin_notice('The Cloudflare cache has been purged using tags','success',implode(',',$cacheTags));
				$this->logNotice($cacheTags,current_action().': The Cloudflare cache has been purged using tags');
			} else {
				$this->add_admin_notice('Cloudflare Tag Purge: '.$result['errors'][0]['message'],'error');
				$this->logError(current_action().": Cloudflare Tag Purge: (".$result['errors'][0]['code'].") ".$result['errors'][0]['message']);
			}
		}
	}


	/**
	 *
	 * Pause/Resume
	 *
	 */


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
				$this->logDebug("The Cloudflare service for {$site_id} has been {$option}d");
			} else {
				$this->add_admin_notice('Cloudflare Service {$option}: '.$result['errors'][0]['message'],'error');
				$this->logError("Cloudflare Service {$option}: (".$result['errors'][0]['code'].") ".$result['errors'][0]['message']);
			}
		}
		return $option;
	}


	/**
	 *
	 * Handle Rate Limiting
	 *
	 */


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
				return $value;
			}

			$limit = (defined('CLOUDFLARE_TEMP_RATE_LIMIT')) ? CLOUDFLARE_TEMP_RATE_LIMIT : [500,10];
			$period = 10;
			if (is_array($limit)) list($limit, $period) = $limit;
			if (!is_numeric($limit) || $limit < 100) $limit = 500;
			if (!is_numeric($period) || $period < 10) $period = 10;

			$rule = [
				[
					'ratelimit' => [
						'characteristics' => [
							'ip.src',
							'cf.colo.id'
						],
						'period' => $period, // 10 required, free plan
						'requests_per_period' => $limit,
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
				$this->add_admin_notice('Cloudflare Ratelimit: '.$result['errors'][0]['message'],'error');
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
							$this->add_admin_notice('Cloudflare Ratelimit: '.$result['errors'][0]['message'],'error');
						}
						return $value;
					}
				}
			}
			$this->add_admin_notice("Cloudflare Ratelimit: <em>".self::RATE_LIMIT_DESC.'</em> not found.','warning');
		}
	}


	/**
	 *
	 * Handle Blacklisting (on Risk Assessment)
	 *
	 */


	/**
	 * Add IP to Cloudflare blacklist (IP access rules)
	 * risk_assessment_blocked
	 *
	 * @param string	$ipAddress
	 * @param array		$data
	 */
	public function add_ip_to_blacklist(string $ipAddress, array $data)
	{
		if (is_user_logged_in() && $ipAddress == $this->getVisitorIP()) return;

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
			if ($location) {
				$comment .= "; [".trim($location.' '.$data['CountryCode'])."]";
			}

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
	 * Remove IP from Cloudflare blacklist (IP access rules)
	 * risk_assessment_unblocked
	 *
	 * @param string $ipAddress
	 */
	public function remove_ip_from_blacklist(string $ipAddress)
	{
		$this->blacklist_purge($ipAddress);
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
			$blacklist = $this->get_transient(self::TRANSIENT_BLACKLIST, function() use($verbose,$create)
				{
					return $this->get_blacklist_list($verbose,$create);
				},
				self::TRANSIENT_BLACKLIST_TTL
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
			$this->add_admin_notice('Cloudflare Rule: '.$result['errors'][0]['message'],'error');
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
	public function blacklist_purge(?string $ipAddress = null)
	{
		$time = $this->get_option('cloudflare_blacklist_purge') ?: time();
		$cutoffTimestamp = time() - (int)$time;

		if ( ($cutoffTimestamp || $ipAddress) && ($list_id = $this->get_blacklist_id(false)) )
		{
			$list_url		= $this->cf_account_url . "/rules/lists";
			$page 			= 1;
			$maxPages 		= 20;
			$totalItems 	= 0;
			$itemsToDelete 	= [];

			while ($page <= $maxPages)
			{
				$search 	= (!empty($ipAddress))
					? "search={$ipAddress}" : 'per_page=500&page={$page}';

				$result = $this->cf_api_request('get',$list_url."/{$list_id}/items?{$search}");
				if (!$result['success']) return;

				$items 		= $result['result'];
				$count 		= count($items);
				if ($count == 0) break;
				$totalItems += $count;

				foreach ($items as $item) {
					if (!empty($ipAddress)) {
						$itemsToDelete[] = ['id' => $item['id']];
						$page = $maxPages;
					} else {
						$created = strtotime($item['created_on']);
						if ($created < $cutoffTimestamp) {
							$itemsToDelete[] = ['id' => $item['id']];
						}
					}
				}
				$page++;
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
				$this->logNotice("Cloudflare Blacklist removed {$count} of {$totalItems} IP addresses");
			} else {
				$this->logDebug([$result,$itemsToDelete],__METHOD__);
			}
		}
	}


	/**
	 *
	 * Cloudflare API
	 *
	 */


	/**
	 * Cloudflare API request
	 *
	 */
	private function cf_api_request( string $method, string $url, array $args = [] )
	{
		$args['method'] 	= strtoupper($method);
		if ($this->cloudflare_token) {
			$args['headers']	= [
				'Accept'		=> 'application/json',
				'Authorization' => "Bearer {$this->cloudflare_token}",
			];
		} else {
			$args['headers']	= [
				'Accept'		=> 'application/json',
				'X-Auth-Key'	=> $this->cloudflare_key,
				'X-Auth-Email'	=> $this->cloudflare_email,
			];
		}

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

		$status = (int)wp_remote_retrieve_response_code($result);
        if ($status >= 300 && !isset($result['success']))
        {
			return $this->cloudflare_api_error(
					'http_status',
					"{$status}: ".wp_remote_retrieve_response_message($result),
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
					[
						'code'		=> $code,
						'message'	=> $message,
					]
				]
			];
	}
}
/**
* return a new instance of this class
*/
if (isset($this)) return new cloudflare_extension($this);
?>
