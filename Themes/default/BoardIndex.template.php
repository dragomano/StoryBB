<?php

use LightnCandy\LightnCandy;

/**
 * @package StoryBB (storybb.org) - A roleplayer's forum software
 * @copyright 2017 StoryBB and individual contributors (see contributors.txt)
 * @license 3-clause BSD (see accompanying LICENSE file)
 *
 * @version 3.0 Alpha 1
 */

/**
 * The top part of the outer layer of the boardindex
 */
function approval_helper($string, $unapproved_topics, $unapproved_posts)
{
    return new \LightnCandy\SafeString(sprintf($string,
    	$unapproved_topics,
    	$unapproved_posts
    ));
}

function moderators_helper($link_moderators, $txt_moderator, $txt_moderators)
{
	$moderators_string = ( count($link_moderators) == 1 ) ? $txt_moderator.":" : $txt_moderators.":";
	foreach ( $link_moderators as $cur_moderator ) 
	{
		$moderators_string .= $cur_moderator;
	}
	return new \LightnCandy\SafeString($moderators_string);
}

function comma_format_helper($number, $override_decimal_count = false)
{
	return new \LightnCandy\SafeString(comma_format($number, $override_decimal_count));
}

function template_boardindex_outer_above()
{

}

function include_ic_partial($template) {
    $func = 'template_ic_block_' . $template;
	return $func();
}

/**
 * This actually displays the board index
 */
function template_main()
{
	global $context, $txt, $scripturl, $options, $settings;

    $data = Array(
        'context' => $context,
        'txt' => $txt,
        'scripturl' => $scripturl,
        'options' => $options,
        'settings' => $settings,
    );
    
    $template = loadTemplateFile('board_main');

    $phpStr = compileTemplate($template, [
        'helpers' => [
            'approvals' => 'approval_helper',
            'link_moderators' => 'moderators_helper',
            'comma_format' => 'comma_format_helper',
            'partial_helper' => 'include_ic_partial',
            'json' => 'stringhelper_json',
            'jsontext' => 'stringhelper_string_json'
        ]
    ]);

	$renderer = LightnCandy::prepare($phpStr);
	return $renderer($data);
}

/**
 * The recent posts section of the info center
 */
function template_ic_block_recent()
{
	global $context, $scripturl, $settings, $txt;
	
	$data = Array(
        'context' => $context,
        'txt' => $txt,
        'scripturl' => $scripturl,
        'settings' => $settings
    );
    
    $template = loadTemplatePartial('board_ic_recent');

    $phpStr = compileTemplate($template, [
        'helpers' => [
            'partial_helper' => 'include_ic_partial',
            'JavaScriptEscape' => 'JSEscape',
        ]
    ]);

	$renderer = LightnCandy::prepare($phpStr);
	return $renderer($data);
}

/**
 * The stats section of the info center
 */
function template_ic_block_stats()
{
	global $scripturl, $txt, $context, $settings;
	$data = Array(
        'context' => $context,
        'txt' => $txt,
        'scripturl' => $scripturl,
        'settings' => $settings
    );
    
    $template = loadTemplatePartial('board_ic_stats');

    $phpStr = compileTemplate($template);

	$renderer = LightnCandy::prepare($phpStr);
	return $renderer($data);
}

/**
 * The who's online section of the admin center
 */
function template_ic_block_online()
{
	global $context, $scripturl, $txt, $modSettings, $settings;
	
	$data = [
        'context' => $context,
        'scripturl' => $scripturl,
        'txt' => $txt,
        'modSettings' => $modSettings,
        'settings' => $settings,
    ];
    
    $template = loadTemplatePartial('board_ic_online');

    $phpStr = compileTemplate($template, [
        'helpers' => Array(
            'implode' => 'implode_sep',
            'comma_format' => 'comma_format_helper'
        )
    ]);

	$renderer = LightnCandy::prepare($phpStr);
	return $renderer($data);
}

?>