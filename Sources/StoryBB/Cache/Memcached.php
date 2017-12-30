<?php

/**
 * @package StoryBB (storybb.org) - A roleplayer's forum software
 * @copyright 2017 StoryBB and individual contributors (see contributors.txt)
 * @license 3-clause BSD (see accompanying LICENSE file)
 *
 * @version 3.0 Alpha 1
 */

namespace StoryBB\Cache;

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * Our Cache API class
 * @package cacheAPI
 */
class Memcached extends API
{
	/**
	 * @var \Memcached The memcache instance.
	 */
	private $memcached = null;

	/**
	 * {@inheritDoc}
	 */
	public function isSupported($test = false)
	{
		global $cache_memcached;

		$supported = class_exists('memcached');

		if ($test)
			return $supported;
		return parent::isSupported() && $supported && !empty($cache_memcached);
	}

	/**
	 * {@inheritDoc}
	 */
	public function connect()
	{
		global $cache_memcached;

		$servers = explode(',', $cache_memcached);

		// memcached does not remove servers from the list upon completing the script under modes like FastCGI. So check to see if servers exist or not.
		$this->memcached = new Memcached;
		$currentServers = $this->memcached->getServerList();
		foreach ($servers as $server)
		{
			if (strpos($server,'/') !== false)
				$tempServer = array($server, 0);
			else
			{
				$server = explode(':', $server);
				$tempServer = array($server[0], isset($server[1]) ? $server[1] : 11211);
			}

			// Figure out if we have this server or not
			$foundServer = false;
			foreach ($currentServers as $currentServer)
			{
				if ($tempServer[0] == $currentServer['host'] && $tempServer[1] == $currentServer['port'])
				{
					$foundServer = true;
					break;
				}
			}

			// Found it?
			if (empty($foundServer))
				$this->memcached->addServer($tempServer[0], $tempServer[1]);
		}

		// Best guess is this worked.
		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'Memcached';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getData($key, $ttl = null)
	{
		$key = $this->prefix . strtr($key, ':/', '-_');

		$value = $this->memcached->get($key);

		// $value should return either data or false (from failure, key not found or empty array).
		if ($value === false)
			return null;
		return $value;
	}

	/**
	 * {@inheritDoc}
	 */
	public function putData($key, $value, $ttl = null)
	{
		$key = $this->prefix . strtr($key, ':/', '-_');

		return $this->memcached->set($key, $value, $ttl);
	}

	/**
	 * {@inheritDoc}
	 */
	public function cleanCache($type = '')
	{
		$this->invalidateCache();

		// Memcached accepts a delay parameter, always use 0 (instant).
		return $this->memcached->flush(0);
	}

	/**
	 * {@inheritDoc}
	 */
	public function quit()
	{
		return $this->memcached->quit();
	}

	/**
	 * {@inheritDoc}
	 */
	public function cacheSettings(array &$config_vars)
	{
		global $context, $txt;

		$config_vars[] = $txt['cache_memcache_settings'];
		$config_vars[] = array('cache_memcached', $txt['cache_memcache_servers'], 'file', 'text', 0, 'cache_memcached', 'postinput' => '<br /><div class="smalltext"><em>' . $txt['cache_memcache_servers_subtext'] . '</em></div>');

		if (!isset($context['settings_post_javascript']))
			$context['settings_post_javascript'] = '';

		$context['settings_post_javascript'] .= '
			$("#cache_accelerator").change(function (e) {
				var cache_type = e.currentTarget.value;
				$("#cache_memcached").prop("disabled", cache_type != "memcached");
			});';
	}
}

?>