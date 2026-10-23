<?php
/**
* Extension: cloudflare_extension - enable Cloudflare API
*
* Recommended for and supports free plans.
*
* @see https://wordpress.org/plugins/cloudflare/
* @see https://developers.cloudflare.com/automatic-platform-optimization/
* @see https://blog.cloudflare.com/building-automatic-platform-optimization-for-wordpress-using-cloudflare-workers/
*
* @category		WordPress Plugin
* @package		{eac}Doojigger\Extensions
* @author		Kevin Burkholder <KBurkholder@EarthAsylum.com>
* @copyright	Copyright (c) 2026 EarthAsylum Consulting <www.EarthAsylum.com>
* @version 		26.1023.1
*/

defined( 'ABSPATH' ) or exit;

$this->registerExtensionOptions( $this->className,
	[
		'cloudflare_email'	=> array(
				'when' 		=> 	!empty($this->is_option('cloudflare_email')) && empty($this->is_option('cloudflare_token')),
				'type'		=>	'text',
				'label'		=>	'Account Email',
				'info'		=>	'Your Cloudflare account email address.',
				'default'	=>	get_bloginfo('admin_email'),
		),
		'cloudflare_key'	=> array(
				'when' 		=> 	!empty($this->is_option('cloudflare_email')) && empty($this->is_option('cloudflare_token')),
				'type'		=>	'text',
				'label'		=>	'Global API Key',
				'info'		=>	'Your Cloudflare global API key.'.
								'<br><small>From your <em>Account Profile</em> &rarr; API Tokens &rarr; API Keys &rarr; Global API Key</small>',
		),
		'cloudflare_token'	=> array(
				'type'		=>	(defined('CLOUDFLARE_API_TOKEN')) ? 'disabled' : 'text',
				'label'		=>	'Account API Token',
				'default'	=> 	(defined('CLOUDFLARE_API_TOKEN')) ? CLOUDFLARE_API_TOKEN : '',
				'info'		=>	"Your Cloudflare Account API Token.".
								"<br><small>From your <em>Account</em> &rarr; Manage Accountt &rarr; Account API Tokens &rarr; Create Token</small>".
								"<br><small>(encrypted when stored)</small>",
				'after' 	=> 	(!empty($this->is_option('cloudflare_email')) && empty($this->is_option('cloudflare_token')))
								? '<br>When saved, this token will replace email and global api key.' : '',
				'encrypt' 	=> 	true,
				'style'		=> 	'-webkit-text-security:disc;',
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
/*
$this->registerExtensionOptions( $this->className,
	[
		'_break1'	=> array(
				'type'		=>	'display',
				'label'		=>	' ',
				'default'	=>	' ',
		),
	]
);
*/
$this->registerExtensionOptions( $this->className,
	[
		'_cloudflare_link'	=> array(
				'type'		=>	'display',
				'label'		=>	'Your Dashboard',
		//		'info'		=>	'For full access to and management of your domain(s).',
				'default'	=>	"&rarr; <a href='https://dash.cloudflare.com/' target='_blank'>Log in to your Cloudflare Dashboard</a><br>".
								"&rarr; <a href='https://www.support.cloudflare.com/' target='_blank'>Visit the Cloudflare Help Center</a>",
				'style'		=> 	'line-height:2',
		),
	]
);
if ($this->is_option('cloudflare_zone'))
{
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

	$options 		= $this->get_cf_options();
	$optionNames 	= array_filter( self::CLOUDFLARE_OPTIONS,fn($v) => isset($options[$v]) );
	$optionValues 	= array_keys( array_filter($options,fn($v) => $v == 'on') );


	$blacklist_id = $this->get_blacklist_id(false,false) ?: 'blacklist';

	// cleanup prior version option names or options to reset
	$this->delete_option('cloudflare_options');

	$purge = (defined('CLOUDFLARE_CACHE_PURGE') &&
			 (CLOUDFLARE_CACHE_PURGE == 'hard' || CLOUDFLARE_CACHE_PURGE == 'purge'))
		? 'Purge' : 'Invalidate';

	$this->registerExtensionOptions( $this->className,
	[
		'_cloudflare_purge'=> array(
				'type'		=>	'button',
				'label'		=>	"{$purge} Cache",
				'default'	=>	$purge,
				'info'		=>	($purge == 'Purge')
									? "Deletes cached content in every Cloudflare data center and cache tier, including Cache Reserve."
									: "Marks cached content as stale in every Cloudflare data center and cache tier, including Cache Reserve.",
				'validate'	=>	[$this,'purge_cloudflare_cache'],
		),
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
		'cloudflare_security'=> array(
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
				'info'		=> 	"Cloudflare's security automatically protects your domain from malicious traffic.<br>".
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
		'cloudflare_cache_level'=> array(
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
				'options'	=>	['Support Edge Caching' => 'wp_edge_cache'],
				'info'		=>	'Support edge caching with specific http headers and inteligent cache purging by using cache tag(s). '.
								'Set cf-edge-cache &amp; x-html-edge-cache headers to support custom rules or Automatic Platform Optimization. '.
								'Additionally, adds cache-control (using Browser Cache Time-To-Live), '.
								'and cache-tag indicating the requested document type, id, and taxonomies.',
				'help'		=>	'[info]<br><small>'.
								'cache-control: public,max-age=14400<br>'.
								'cf-edge-cache: cache,platform=wordpress or no-cache.<br>'.
								'x-html-edge-cache: cache,bypass-cookies=wp-|wordpress|woocommerce or no-cache or purgeall.<br>'.
								'cache-tag: p-post,p-post-2829,p-category-3,p-tag-4,p-tag-72,text/html'.
								'</small><br>'.
								'With cache tags, edge caching is managed by purging specific tag(s) on specific WordPress actions.',
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
				'info'		=>	'Controls how long resources cached by client browsers remain valid '.
								'and is used in cache-control headers to support Edge Caching.',
				'help'		=>	'[info]<br><small>Zone &rarr; Caching &rarr; Configuration</small>',
				'validate'	=>	function($value){return $this->set_cf_setting('browser_cache_ttl',(int)$value);},
				'style'		=> 	'min-width:22ch;',
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
				'info'		=>	"Adds a rate limiting rule to temporarily block IP addresses after 500 requests within 10 seconds. ".
								"<br><small><strong><em>Intended to work with free plans.</em></strong></small>",
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
	]);
	if ( !empty($this->is_option('cloudflare_blacklist')) ||
		 (isset($_POST['cloudflare_blacklist']) && !empty($_POST['cloudflare_blacklist'])) )
	{
		$this->registerExtensionOptions( $this->className,
		[
			'cloudflare_blacklist_purge'=> array(
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
			// shared with security_ra.extension
			'_reset_ip' 	=> array(
				'when'		=> 	!empty($this->is_option('cloudflare_blacklist')) ||
								(isset($_POST['cloudflare_blacklist']) && !empty($_POST['cloudflare_blacklist'])),
				'type'		=>	'text',
				'label'		=>	"Reset This IP Address",
				'info'		=>	"Remove from blacklist &amp; reset risk tracking for this IP address.",
				'validate'	=> 	function($ip) {if ($ip) $this->do_action( 'clear_risk_assessment', $ip );},
				'style'		=> 	"max-width: 22ch;",
			),
		]);
	}
}
