## {eac}Doojigger Cloudflare Doololly for WordPress
[![EarthAsylum Consulting](https://img.shields.io/badge/EarthAsylum-Consulting-0?&labelColor=6e9882&color=707070)](https://earthasylum.com/)
[![WordPress](https://img.shields.io/badge/WordPress-Plugins-grey?logo=wordpress&labelColor=blue)](https://wordpress.org/plugins/search/EarthAsylum/)
[![eacDoojigger](https://img.shields.io/badge/Requires-%7Beac%7DDoojigger-da821d)](https://eacDoojigger.earthasylum.com/)
[![Sponsorship](https://img.shields.io/static/v1?label=Sponsorship&message=%E2%9D%A4&logo=GitHub&color=bf3889)](https://github.com/sponsors/EarthAsylum)

<details><summary>Plugin Header</summary>

Plugin URI:         https://eacdoojigger.earthasylum.com/eaccloudflare/  
Author:             [EarthAsylum Consulting](https://www.earthasylum.com)  
Stable tag:         2.0.0  
Last Updated:       17-Oct-2026  
Requires at least:  5.8  
Tested up to:       7.1  
Requires PHP:       8.1  
Requires EAC:       3.3  
Contributors:       [kevinburkholder](https://profiles.wordpress.org/kevinburkholder)  
Donate link:        https://github.com/sponsors/EarthAsylum  
Support link:		https://github.com/EarthAsylum/eacCloudflare/issues  
License:            GPLv3 or later  
License URI:        https://www.gnu.org/licenses/gpl.html  
Tags:               Cloudflare, rate-limit, blacklist, edge-caching, {eac}Doojigger  
GitHub URI:         https://github.com/EarthAsylum/eacCloudflare  

</details>

> Provides limited control of Cloudflare settings, caching, blacklisting, and rate-limiting from WordPress.

### Description

__Cloudflare control from WordPress__

1. 	Pause/Resume Cloudflare service.
2. 	Override the caching mode and browser cache time-to-live.
3. 	Set the security level, including "under attack".
4. 	Set 'cf-edge-cache' header to trigger APO or custom rules.
	+	Custom rules can look for this header set to `cache, platform=wordpress` or `no-cache` or not set.
5. 	Add 'Cache-Tag' header with mime type that can be used for selective cache purge.
6. 	Set common Cloudflare options for speed & security directly from WordPress.
7. 	Automatically purge cloudflare cache (by host name).
8. 	Add a rate limiting rule to temporarily block IP addresses after 100 requests within 10 seconds.
9. 	Create and update Cloudflare IP blacklist (account) and blocking rule (zone) using risk assessment results.

#### Cloudflare Settings

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

__Security Level__

Cloudflare's security automatically protects your domain from malicious traffic.

`Off`: Applies a very low threshold, challenging only the most extreme threats.
`Low`: Challenges visitors who have a high threat score.
`Medium`: Challenges visitors with a moderate or high threat score.
`High`: Challenges any visitor with even a low threat score, plus additional suspicious profiles..
`Under Attack`: Performs additional security checks to help mitigate layer 7 DDoS attacks. Validated users access your website and suspicious traffic is blocked. It is designed to be used as one of the last resorts when a zone is under attack.

__Browser Cache Time-To-Live__

Controls how long resources cached by client browsers remain valid. (`Zone → Caching → Configuration`)

__Caching Level__

Caching levels determine how much of your website’s static content Cloudflare should cache.
Bypass: Cloudflare does not cache. (`Zone → Caching → Configuration`)  

+	`Basic`: Delivers resources from cache when there is no query string
+	`Simplified`: Delivers the same resource to everyone independent of the query string.
+	`Aggressive`: Caches all static content that has a query string.
+	`Everything`: Treats all content as static and caches all file types beyond the Cloudflare default cached content.

__Cloudflare Options__

Enable/disable several common Cloudflare options for speed & security. (`Zone → Speed → Settings`), (`Zone → SSL/TLS → Edge Certificates`), (`Zone → Security → Settings`)

__Rate Limit Rule__

Adds a rate limiting rule to temporarily block IP addresses after 100 requests within 10 seconds.
Set to work with free plans but may overwrite existing rule. (`Zone → Security → Security rules`)

__Risk Assessment Blacklist__

When a request is blocked by the {eac}Doojigger Risk Assessment process, add the remote IP address to the Cloudflare blacklist to prevent further access to the site. This creates or updates a Cloudflare IP list (`Account → Configurations → Lists`) and adds a security rule (`Zone → Security → Security Rules`) to block the list.
Note: To use an existing Cloudflare IP list, add `{eac}Cloudflare` to its description.  

__Blacklist Expiration__

Controls how long an IP address is blacklisted due to Risk Assessment. The Cloudflare blacklist is purged hourly.

#### Filters and Actions

__Filters__

+	'cloudflare_purge_everything_actions' (array) - filter actions that trigger a cache purge.
+	'cloudflare_use_cache' (bool) - override `cf-edge-cache` setting (cache/no-cache).

__Actions__

+	'eacDoojigger_cloudflare_pause'   - pause the cloudflare service
+	'eacDoojigger_cloudflare_resume'  - resume the cloudflare service
+	'eacDoojigger_cloudflare_purge'   - purge the cloudflare cache


### Installation

**{eac}Doojigger Cloudflare Doololly** is an extension plugin to and requires installation and registration of [{eac}Doojigger](https://eacDoojigger.earthasylum.com/).

#### Automatic Plugin Installation

Due to the nature of this plugin, it is NOT available from the WordPress Plugin Repository and can not be installed from the WordPress Dashboard » *Plugins* » *Add New* » *Search* feature.

#### Upload via WordPress Dashboard

Installation of this plugin can be managed from the WordPress Dashboard » *Plugins* » *Add New* page. Click the [Upload Plugin] button, then select the eacCloudflare.zip file from your computer.

See [Managing Plugins -> Upload via WordPress Admin](https://wordpress.org/support/article/managing-plugins/#upload-via-wordpress-admin)

#### Manual Plugin Installation

You can install the plugin manually by extracting the eacCloudflare.zip file and uploading the 'eacCloudflare' folder to the 'wp-content/plugins' folder on your WordPress server.

See [Managing Plugins -> Manual Plugin Installation](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation-1)

#### Settings

Once installed and activated options for this extension will show in the 'Cloudflare' tab of {eac}Doojigger settings.


### Screenshots

1. {eac}Cloudflare Doololly
![{eac}Cloudflare Doololly](https://raw.githubusercontent.com/EarthAsylum/eacCloudflare/main/.wp-assets/screenshot-1.png)


### Copyright

#### Copyright © 2026, EarthAsylum Consulting, distributed under the terms of the GNU GPL.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.  

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should receive a copy of the GNU General Public License along with this program. If not, see [https://www.gnu.org/licenses/](https://www.gnu.org/licenses/).


