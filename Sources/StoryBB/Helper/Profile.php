<?php

/**
 * Helper for profile pages.
 *
 * @package StoryBB (storybb.org) - A roleplayer's forum software
 * @copyright 2021 StoryBB and individual contributors (see contributors.txt)
 * @license 3-clause BSD (see accompanying LICENSE file)
 *
 * @version 1.0 Alpha 1
 */

namespace StoryBB\Helper;

use StoryBB\Helper\IP;

class Profile
{
	/**
	 * Get the number of user errors
	 *
	 * @param string $where A query to limit which errors are counted
	 * @param array $where_vars The parameters for $where
	 * @return int Number of user errors
	 */
	public static function list_getUserErrorCount($where, $where_vars = [])
	{
		global $smcFunc;

		$request = $smcFunc['db']->query('', '
			SELECT COUNT(*) AS error_count
			FROM {db_prefix}log_errors
			WHERE ' . $where,
			$where_vars
		);
		list ($count) = $smcFunc['db']->fetch_row($request);
		$smcFunc['db']->free_result($request);

		return (int) $count;
	}

	/**
	 * Gets all of the errors generated by a user's actions. Callback for the list in track_activity
	 *
	 * @param int $start Which item to start with (for pagination purposes)
	 * @param int $items_per_page How many items to show on each page
	 * @param string $sort A string indicating how to sort the results
	 * @param string $where A query indicating how to filter the results (eg 'id_member={int:id_member}')
	 * @param array $where_vars An array of parameters for $where
	 * @return array An array of information about the error messages
	 */
	public static function list_getUserErrors($start, $items_per_page, $sort, $where, $where_vars = [])
	{
		global $smcFunc, $txt, $scripturl;

		// Get a list of error messages from this ip (range).
		$request = $smcFunc['db']->query('', '
			SELECT
				le.log_time, le.ip, le.url, le.message, COALESCE(mem.id_member, 0) AS id_member,
				COALESCE(mem.real_name, {string:guest_title}) AS display_name, mem.member_name
			FROM {db_prefix}log_errors AS le
				LEFT JOIN {db_prefix}members AS mem ON (mem.id_member = le.id_member)
			WHERE ' . $where . '
			ORDER BY {raw:sort}
			LIMIT {int:start}, {int:max}',
			array_merge($where_vars, [
				'guest_title' => $txt['guest_title'],
				'sort' => $sort,
				'start' => $start,
				'max' => $items_per_page,
			])
		);
		$error_messages = [];
		while ($row = $smcFunc['db']->fetch_assoc($request))
			$error_messages[] = [
				'ip' => IP::format($row['ip']),
				'member_link' => $row['id_member'] > 0 ? '<a href="' . $scripturl . '?action=profile;u=' . $row['id_member'] . '">' . $row['display_name'] . '</a>' : $row['display_name'],
				'message' => strtr($row['message'], ['&lt;span class=&quot;remove&quot;&gt;' => '', '&lt;/span&gt;' => '']),
				'url' => $row['url'],
				'time' => timeformat($row['log_time']),
				'timestamp' => forum_time(true, $row['log_time']),
			];
		$smcFunc['db']->free_result($request);

		return $error_messages;
	}
}
