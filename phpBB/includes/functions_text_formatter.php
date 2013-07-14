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
use s9e\TextFormatter\Parser\BuiltInFilters;

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
* Filter an image's URL to enforce restrictions on its dimensions
*
* @see bbcode_firstpass::bbcode_img()
*
* @param  string                          $url        Original URL
* @param  array                           $url_config Config used by the URL filter
* @param  string                          $mode       Either "post" or "sig"
* @param  s9e\TextFormatter\Parser\Logger $logger     Parser's logger
* @return mixed                                       Original value if valid, FALSE otherwise
*/
function filter_img_url($url, array $url_config, $mode, Logger $logger)
{
	global $user, $config;

	// Validate the URL
	$url = BuiltInFilters::filterUrl($url, $url_config, $logger);

	if ($url === false)
	{
		return false;
	}

	$max_height	= $config['max_' . $mode . '_img_height'];
	$max_width	= $config['max_' . $mode . '_img_width'];

	if ($max_height || $max_width)
	{
		$stats = @getimagesize($url);

		if ($stats === false)
		{
			$logger->error($user->lang['UNABLE_GET_IMAGE_SIZE']);

			return false;
		}

		if ($max_height && $max_height < $stats[1])
		{
			$logger->error($user->lang('MAX_IMG_HEIGHT_EXCEEDED', (int) $max_height));
			$url = false;
		}

		if ($max_width && $max_width < $stats[0])
		{
			$logger->error($user->lang('MAX_IMG_WIDTH_EXCEEDED', (int) $max_width));
			$url = false;
		}
	}

	return $url;
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
	$filter = new RegexpFilter('#^' . get_preg_expression('relative_url') . '$#D');
	$configurator->attributeFilters->add('#local_url', $filter);
	$configurator->attributeFilters->add('#relative_url', $filter);

	$regexp = (phpbb_pcre_utf8_support())
		? '!^([\p{L}\p{N}\-+,_. ]+)$!uD'
		: '!^([a-zA-Z0-9\-+,_. ]+)$!uD';
	$configurator->attributeFilters->add('#inttext', new RegexpFilter($regexp));

	// Create a custom filter for phpBB's per-mode font size limits
	$configurator->attributeFilters
		->add('#fontsize', 'filter_font_size')
		->addParameterByName('mode')
		->markAsSafeInCSS();

	// Create a custom filter for image URLs
	$configurator->attributeFilters
		->add('#imageurl', 'filter_img_url')
		->addParameterByName('urlConfig')
		->addParameterByName('mode')
		->addParameterByName('logger');

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

	while ($row = $db->sql_fetchrow($result))
	{
		$configurator->Emoticons->add(
			$row['code'],
			'<img class="smilies" src="{$T_SMILIES_PATH}' . $row['smiley_url'] . '" alt="{.}" title="' . htmlspecialchars($row['emotion']) . '"/>'
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

	// Otherwise, regenerate a set of parser/renderer
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

	/**
	* @todo set BOARD_URL, T_SMILIES_PATH and other template variables
	*/

	return $renderer;
}

/**
* Generate, cache and return a set of parser and renderer(s)
*
* @return array Associative array containing a "parser" element and a "renderer" element
*/
function regenerate_text_formatter()
{
	$text_formatter = get_text_formatter();
	cache_text_formatter($text_formatter);

	return $text_formatter;
}

/**
* Generate and return a set of parser and renderer(s)
*
* @param  s9e\TextFormatter\Configurator $configurator A configured instance of the configurator. If none is given, one will be created automatically
* @return array Associative array containing a "parser" element and a "renderer" element
*/
function get_text_formatter(s9e\TextFormatter\Configurator $configurator = null)
{
	// Generate a configured instance of Configurator
	if (!isset($configurator))
	{
		$configurator = get_text_formatter_configurator();
	}

	// Generate an instance of Renderer using the PHP backend
	$renderer = $configurator->getRenderer('PHP', 'phpbb_text_formatter_renderer');

	// Finalize the configuration by setting the automatic tag rules
	$configurator->addHTML5Rules(array('renderer' => $renderer));

	// Collect the lang strings used in the stylesheet
	$renderer->lang = array();
	foreach ($configurator->stylesheet->getUsedParameters() as $paramName => $expr)
	{
		if (preg_match('#^L_(\\w+)$#', $paramName, $m))
		{
			$renderer->lang[] = $m[1];
		}
	}

	// Generate a new Parser
	$parser = $configurator->getParser();

	return array('parser' => $parser, 'renderer' => $renderer);
}

/**
* Cache given set of parser and renderer
*
* @param  array Associative array containing a "parser" element and a "renderer" element
* @return null
*/
public function cache_text_formatter(array $text_formatter)
{
	global $cache;
	global $phpbb_root_path;

	// Save the renderer's source file in the cache/ directory
	$php = '<?php ' . $renderer->source;
	file_put_contents($phpbb_root_path . 'cache/class.text_formatter_renderer.php', $php);

	// Save the objects to phpBB's cache
	$cache->put('_text_formatter_parser', $parser);
	$cache->put('_text_formatter_renderer', $renderer);
}
