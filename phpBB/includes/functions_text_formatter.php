<?php
/**
*
* @package phpBB3
* @copyright (c) 2005 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

use s9e\TextFormatter\Configurator\Items\AttributeFilters\Regexp as RegexpFilter;
use s9e\TextFormatter\Configurator\Items\AttributeFilter;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* Filter the value used in a [size] BBCode
*
* @see bbcode_firstpass::bbcode_size()
*
* @param  string $size Original size
* @param  string $mode Either "post" or "sig"
* @return mixed        Original value if valid, FALSE otherwise
*/
function filter_font_size($size, $mode)
{
	global $config;

	// Test whether there's a limit in this mode
	$name = 'max_' . $mode . '_font_size';
	if (isset($config[$name]) && $config[$name] > 0 && $config[$name] < $size)
	{
		return false;
	}

	if ($size < 1)
	{
		return false;
	}

	return $size;
}

/**
* Generate and return a new configured instance of s9e\TextFormatter\Configurator
*
* @return s9e\TextFormatter\Configurator
*/
function get_text_formatter_configurator()
{
	global $config, $db, $user;
	global $phpbb_root_path;

	// Create a new Configurator
	$configurator = new s9e\TextFormatter\Configurator;

	// Apply some compatibility settings
	$configurator->stylesheet->setOutputMethod('xml');

	// Create custom filters for BBCode tokens that are supported in phpBB but not in
	// s9e\TextFormatter
	$regexp = '#^' . get_preg_expression('relative_url') . '$#D';
	$configurator->attributeFilters
		->add('#local_url', new RegexpFilter($regexp))
		->markAsSafeAsURL();

	$regexp = (phpbb_pcre_utf8_support())
		? '!^([\p{L}\p{N}\-+,_. ]+)$!uD'
		: '!^([a-zA-Z0-9\-+,_. ]+)$!uD';
	$configurator->attributeFilters->add('#inttext', new RegexpFilter($regexp));

	// Also create a custom filter for phpBB's per-mode font size limits
	$configurator->attributeFilters
		->add('#fontsize', 'filter_font_size')
		->addParameterByName('mode')
		->markAsSafeInCSS();

	// Add default BBCodes
	$configurator->BBCodes->repositories->add('phpbb', __DIR__ . '/bbcodes.xml');
	$configurator->BBCodes->addFromRepository('B',     'phpbb');
	$configurator->BBCodes->addFromRepository('CODE',  'phpbb');
	$configurator->BBCodes->addFromRepository('COLOR', 'phpbb');
	$configurator->BBCodes->addFromRepository('EMAIL', 'phpbb');
	$configurator->BBCodes->addFromRepository('FLASH', 'phpbb');
	$configurator->BBCodes->addFromRepository('I',     'phpbb');
	$configurator->BBCodes->addFromRepository('IMG',   'phpbb');
	$configurator->BBCodes->addFromRepository('LIST',  'phpbb');
	$configurator->BBCodes->addFromRepository('*',     'phpbb');
	$configurator->BBCodes->addFromRepository('QUOTE', 'phpbb');
	$configurator->BBCodes->addFromRepository('SIZE',  'phpbb');
	$configurator->BBCodes->addFromRepository('U',     'phpbb');

	// Load custom BBCodes
	$sql = 'SELECT bbcode_match, bbcode_tpl FROM ' . BBCODES_TABLE;
	$result = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($result))
	{
		try
		{
			$configurator->BBCodes->addCustom($row['bbcode_match'], $row['bbcode_tpl']);
		}
		catch (Exception $e)
		{
			/**
			* @todo log an error?
			*/
		}
	}
	$db->sql_freeresult($result);

	// Load smilies
	// NOTE: smilies that are displayed on the posting page are processed first because they're
	//       typically the most used smilies and it ends up producing a slightly more efficient
	//       renderer
	$sql = 'SELECT code, emotion, smiley_url, smiley_width, smiley_height
		FROM ' . SMILIES_TABLE . '
		ORDER BY display_on_posting DESC';
	$result = $db->sql_query($sql);
	$root_path = (defined('PHPBB_USE_BOARD_URL_PATH') && PHPBB_USE_BOARD_URL_PATH) ? generate_board_url() . '/' : $phpbb_root_path;
	while ($row = $db->sql_fetchrow($result))
	{
		$configurator->Emoticons->add(
			$row['code'],
			'<img class="smilies" src="' . $root_path . $config['smilies_path'] . '/' . $row['smiley_url'] . '" alt="{.}" title="' . $row['emotion'] . '"/>'
		);
	}
	$db->sql_freeresult($result);

	// Load the censored words
	$sql = 'SELECT word, replacement FROM ' . WORDS_TABLE;
	$result = $db->sql_query($sql);

	while ($row = $db->sql_fetchrow($result))
	{
		$configurator->Censor->add($row['word'],  $row['replacement']);
	}
	$db->sql_freeresult($result);

	// Load the magic links plugins. We do that after BBCodes so that they use the same tags
	$configurator->plugins->load('Autoemail');
	$configurator->plugins->load('Autolink');

	return $configurator;
}

/**
* Get a (cached) instance of s9e\TextFormatter\Parser
*
* @return s9e\TextFormatter\Parser
*/
function get_text_formatter_parser()
{
	global $cache;

	// Try to get the parser from the cache
	$parser = $cache->get('_text_formatter_parser');

	// Otherwise, regenerate a set of parser/renderers
	if ($parser === false)
	{
		$text_formatter = regenerate_text_formatter();
		$parser = $text_formatter['parser'];
	}

	return $parser;
}

/**
* Get a (cached) instance of s9e\TextFormatter\Renderer
*
* @return s9e\TextFormatter\Renderer
*/
function get_text_formatter_renderer()
{
	global $cache, $user;

	// Test whether the renderer class already exists and load it accordingly
	if (!class_exists('phpbb_text_formatter_renderer', false))
	{
		global $phpbb_root_path;
		$filepath = $phpbb_root_path . 'cache/class.text_formatter_renderer.php';

		if (file_exists($filepath))
		{
			include($filepath);
		}
	}

	// Try to get the renderer from the cache
	$renderer = false;
	if (class_exists('phpbb_text_formatter_renderer', false))
	{
		$renderer = $cache->get('_text_formatter_renderer_php');
	}

	// Generate a new renderer
	if ($renderer === false)
	{
		$text_formatter = regenerate_text_formatter();
		$renderer = $text_formatter['renderer'];
	}

	// Set the localized strings
	foreach ($renderer->lang as $str)
	{
		if (isset($user->lang[$str]))
		{
			$renderer->setParameter('L_' . $str, $user->lang[$str]);
		}
	}

	return $renderer;
}

/**
* Generate, cache and return a set of parser and renderer(s)
*
* @return array Associative array containing a "parser" element and a "renderers" array
*/
function regenerate_text_formatter()
{
	return generate_text_formatter(true);
}

/**
* Generate, optionally cache and return a set of parser and renderer(s)
*
* @param  bool  $use_cache Whether the parser and renderers should be cached
* @return array            Associative array containing a "parser" element and a "renderers" array
*/
function generate_text_formatter($use_cache = false)
{
	global $cache;
	global $phpbb_root_path;

	// Generate a configured instance of Configurator
	$configurator = get_text_formatter_configurator();

	// Generate a new Parser
	$parser = $configurator->getParser();

	// Generate an instance of Renderer using the PHP backend
	$renderer = $configurator->getRenderer('PHP', 'phpbb_text_formatter_renderer');

	// Collect the lang strings used in the stylesheet
	$renderer->lang = array();
	foreach ($configurator->stylesheet->getUsedParameters() as $paramName => $expr)
	{
		if (preg_match('#^L_(\\w+)$#', $paramName, $m))
		{
			$renderer->lang[] = $m[1];
		}
	}

	// Save the generated objects/files to phpBB's cache
	if ($use_cache)
	{
		// Save the renderer's source file in the cache/ directory
		$php = '<?php ' . $renderer->source;
		file_put_contents($phpbb_root_path . 'cache/class.text_formatter_renderer.php', $php);

		// Remove the source from the renderer so we don't unnecessarily bloat the cache
		unset($renderer->source);

		// Save the objects to phpBB's cache
		$cache->put('_text_formatter_parser', $parser);
		$cache->put('_text_formatter_renderer', $renderer);
	}

	return array('parser' => $parser, 'renderer' => $renderer);
}
