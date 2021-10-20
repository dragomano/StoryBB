<?php

/**
 * Return the value absolutely unfiltered for a generic list column.
 *
 * @package StoryBB (storybb.org) - A roleplayer's forum software
 * @copyright 2021 StoryBB and individual contributors (see contributors.txt)
 * @license 3-clause BSD (see accompanying LICENSE file)
 *
 * @version 1.0 Alpha 1
 */

namespace StoryBB\Helper\GenericList\Column;

use StoryBB\App;
use StoryBB\Phrase;

class ToggleColumn extends AbstractColumn
{
	protected $attributes = [];
	protected $url;
	protected $use_column = '';
	protected $used_column_output_as = '';

	public function get_value(array $row, string $column_id)
	{
		if (!empty($row[$column_id]))
		{
			$new_state = 0;
			$icon = '<i class="fas fa-fw fa-toggle-on" title="' . (new Phrase('General:enabled')) . '"></i>';
		}
		else
		{
			$new_state = 1;
			$icon = '<i class="fas fa-fw fa-toggle-off" title="' . (new Phrase('General:diabled')) . '"></i>';
		}

		$form = '
			<form action="' . $this->url . '" method="post">
				<button type="submit" class="fa-button" role="button">' . $icon . '</button>';
		foreach ($this->attributes as $key => $value)
		{
			$form .= '
				<input type="hidden" name="' . $key . '" value="' . $value . '">';
		}

		if ($this->use_column && $this->used_column_output_as && isset($row[$this->use_column]))
		{
			$form .= '
				<input type="hidden" name="' . $this->used_column_output_as . '" value="' . $row[$this->use_column] . '">';
		}

		$form .= '
				<input type="hidden" name="new_state" value="' . $new_state . '">';

		$session = App::container()->get('session');
		$form .= '
				<input type="hidden" name="' . $session->get('session_var') . '" value="' . $session->get('session_value') . '">';

		$form .= '
			</form>';
		return $form;
	}

	public function set_attr(string $key, string $value)
	{
		$this->attributes[$key] = $value;
		return $this;
	}

	public function set_destination(string $url)
	{
		$this->url = $url;
		return $this;
	}

	public function use_column_as(string $column, string $output_as)
	{
		$this->use_column = $column;
		$this->used_column_output_as = $output_as;
		return $this;
	}
}
