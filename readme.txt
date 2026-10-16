=== {eac}Doojigger Cloudflare Doololly for WordPress ===
Plugin URI:         https://eacdoojigger.earthasylum.com/cloudflare/
Author:             [EarthAsylum Consulting](https://www.earthasylum.com)
Stable tag:         2.0.0
Last Updated:       15-Oct-2026
Requires at least:  5.8
Tested up to:       7.1
Requires PHP:       8.1
Requires EAC:       3.3
Contributors:       kevinburkholder
Donate link:        https://github.com/sponsors/EarthAsylum
Support link:		https://github.com/EarthAsylum/eacCloudflare/issues
License:            GPLv3 or later
License URI:        https://www.gnu.org/licenses/gpl.html
Tags:               Cloudflare, rate-limit, blacklist, edge-caching, {eac}Doojigger
GitHub URI:         https://github.com/EarthAsylum/eacCloudflare

Provides limited control of Cloudflare settings, caching, blacklisting, and rate-limiting from WordPress.

== Description ==

__Cloudflare control from WordPress__

1. 	Pause/Resume Cloudflare service.
2. 	Override the browser cache time-to-live.
3. 	Set 'cf-edge-cache' header to trigger APO or custom rules.
	+	Custom rules can look for this header set to `cache, platform=wordpress` or `no-cache` or not set.
4. 	Add 'Cache-Tag' header with mime type that can be used for selective cache purge.
5. 	Set common Cloudflare options for speed & security directly from WordPress.
6. 	Automatically purge cloudflare cache (by host name).
7. 	Add a rate limiting rule to temporarily block IP addresses after 100 requests within 10 seconds.
8. 	Create and update Cloudflare IP blacklist (account) and blocking rule (zone) using risk assessment results.

> 	Recommended for free plans. Paid plans should use the official Cloudflare plugin with Automatic Platform Optimization (APO).

= Cloudflare Settings =

With your Cloudflare account information, we can set certain common Cloudflare options as well as automatically purge the Cloudflare cache when purging the local WordPress caches, add a rate-limiting rule to mitigate brute-force and DDoS attacks, and blacklist IP addresses identified by Risk Assessment.

__Account Email__

Your Cloudflare account email address.

__Global API Key__

Your Cloudflare global API key.
From your Account Profile → API Tokens → API Keys → Global API Key

__Domain Zone__

Your Cloudflare zone for this domain.

__Pause This Zone__

Pausing stops traffic from passing through the Cloudflare network, making your origin server IP address visible. Also, Cloudflare’s security and protection features become disabled.

__Browser Cache Time-To-Live__

Controls how long resources cached by client browsers remain valid.
Zone → Caching → Configuration

__Cloudflare Options__

Enable/disable common Cloudflare options for speed & security.
Zone → Speed → Settings
Zone → SSL/TLS → Edge Certificates
Zone → Security → Settings

__Rate Limit Rule__

Adds a rate limiting rule to temporarily block IP addresses after 100 requests within 10 seconds.
Set to work with free plans but may overwrite existing rule.
Zone → Security → Security rules

__Risk Assessment Blacklist__

When a request is blocked by the {eac}Doojigger Risk Assessment process, add the remote IP address to the Cloudflare blacklist to prevent further access to the site. This creates or updates a Cloudflare IP list (Account → Configurations → Lists) and adds a security rule (Zone → Security → Security Rules) to block the list.
Note: To use an existing IP list, add `[{eac}Cloudflare]` to its description.

__Blacklist Expiration__

Controls how long an IP address is blacklisted due to Risk Assessment. The Cloudflare blacklist is purged hourly.

= Filters and Actions =

__Filters__

1. 'cloudflare_purge_everything_actions' (array) - filter actions that trigger a cache purge.
2. 'cloudflare_use_cache' (bool) - override `cf-edge-cache` setting (cache/no-cache).

__Actions__

1. 'eacDoojigger_cloudflare_pause'   - pause the cloudflare service
2. 'eacDoojigger_cloudflare_resume'  - resume the cloudflare service
3. 'eacDoojigger_cloudflare_purge'   - purge the cloudflare cache


== Installation ==

**{eac}Doojigger Cloudflare Doololly** is an extension plugin to and requires installation and registration of [{eac}Doojigger](https://eacDoojigger.earthasylum.com/).

= Automatic Plugin Installation =

Due to the nature of this plugin, it is NOT available from the WordPress Plugin Repository and can not be installed from the WordPress Dashboard » *Plugins* » *Add New* » *Search* feature.

= Upload via WordPress Dashboard =

Installation of this plugin can be managed from the WordPress Dashboard » *Plugins* » *Add New* page. Click the [Upload Plugin] button, then select the eacmetapixel.zip file from your computer.

See [Managing Plugins -> Upload via WordPress Admin](https://wordpress.org/support/article/managing-plugins/#upload-via-wordpress-admin)

= Manual Plugin Installation =

You can install the plugin manually by extracting the eacmetapixel.zip file and uploading the 'eacmetapixel' folder to the 'wp-content/plugins' folder on your WordPress server.

See [Managing Plugins -> Manual Plugin Installation](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation-1)

= Settings =

Once installed and activated options for this extension will show in the 'Cloudflare' tab of {eac}Doojigger settings.


== Screenshots ==

1. {eac}Cloudflare Doololly
![{eac}Cloudflare Doololly](https://swregistry.earthasylum.com/software-updates/eaccloudflare/assets/screenshot-1.png)


== Copyright ==

= Copyright © 2026, EarthAsylum Consulting, distributed under the terms of the GNU GPL. =

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should receive a copy of the GNU General Public License along with this program. If not, see [https://www.gnu.org/licenses/](https://www.gnu.org/licenses/).


== Changelog ==

= Version 2.0.0 – October 15, 2026 =

+	First release as a Doohickey (plugin).
+	New Blacklisting for Risk assessment.
+	New default rate-limiting rule.
+	Updated cache handling.
+	Updates settings retrieval (as a group vs individually).

