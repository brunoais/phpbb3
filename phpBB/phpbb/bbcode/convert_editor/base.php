<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\bbcode\convert_editor;

use \phpbb\request\request_interface;
use \phpbb\bbcode\js_regex_helper;

/**
* 
*/
abstract class base
{

	const HAS_BUTTON_MODE_ICON 		= 0x1;
	const HAS_BUTTON_MODE_TEXT 		= 0x2;
	const HAS_BUTTON_MODE_ICON_TEXT = 0x4;
	// Some buttons are icons, others are text. It depends on what it is.
	const HAS_BUTTON_MODE_MIXED 	= 0x8;
	
	const DEFAULT_WYSIWYG_MODE	= 0x1;
	const DEFAULT_SOURCE_MODE	= 0x2;
	const DEFAULT_MIXED_MODE	= 0x4;
	
	const EDITOR_CONFIG_BASENAME = 'editor_config_';
	
	protected $toolbar_default_ordering;
	
	/**
	 * cache object
	 * @var \phpbb\cache\driver\driver_interface
	 */
	protected $cache;

	/**
	 * cache name prefix (includes the path)
	 * @var string
	 */
	protected $cache_prefix;

	/**
	 * Config object
	 * @var \phpbb\config\config
	 */
	protected $config;
	
	/**
	* Event dispatcher object
	* @var \phpbb\event\dispatcher_interface
	*/
	protected $phpbb_dispatcher;
	
	/**
	* Template object
	* @var \phpbb\template\template
	*/
	protected $template;
	
	/**
	* Request object
	* @var \phpbb\request\request
	*/
	protected $request;
	
	/**
	 * Dictate the name of the handler.
	 * The result should only contain alphanumeric characters
	 *
	 */
	protected function get_name()
	{
		return '';
	}
	
	/**
	 * Constructor
	 * 
	 *
	 * @param \phpbb\cache\driver\driver_interface $cache Cache object
	 * @param string $cache_prefix A string to prefix to the cache file name (includes the path)
	 * @param \phpbb\config\config $config Config object
	 * @param \phpbb\event\dispatcher_interface $phpbb_dispatcher Where to send events to
	 */
	protected function __construct(\phpbb\cache\driver\driver_interface $cache, $cache_prefix,
		\phpbb\config\config $config, \phpbb\event\dispatcher_interface $phpbb_dispatcher, 
		\phpbb\request\request $request, \phpbb\template\template $template)
	{
		$this->cache = $cache;
		$this->cache_prefix = $cache_prefix;
		$this->config = $config;
		$this->phpbb_dispatcher = $phpbb_dispatcher;
		$this->template = $template;
		$this->request = $request;
		
		
		$this->toolbar_default_ordering = array(
			array('b', 'i', 'u'),
			array('quote', 'code'),
			array('list', '*'),
			array('img', 'url'),
			array('flash'),
			array('size', 'color'),
		);
		
	}
	
	/**
	 * This is the list of HTML void elements as defined by w3c
	 * @link http://www.w3.org/TR/html-markup/syntax.html#void-elements
	 * This list is meant to be used with the php isset() construct
	 *
	*/
	// const HTML_VOID_ELEMENTS = array(
									// 'area'		=> 1,
									// 'base'		=> 1,
									// 'br'		=> 1,
									// 'col'		=> 1,
									// 'command'	=> 1,
									// 'embed'		=> 1,
									// 'hr'		=> 1,
									// 'img'		=> 1,
									// 'input'		=> 1,
									// 'keygen'	=> 1,
									// 'link'		=> 1,
									// 'meta'		=> 1,
									// 'param'		=> 1,
									// 'source'	=> 1,
									// 'track'		=> 1,
									// 'wbr'	=> 1,
								// );
	
	
	abstract protected function generate_editor_setup_javascript($text_formatter_factory);
	
	abstract protected function get_static_javascript_variables();
	abstract protected function get_dynamic_javascript_variables();
	
	public function purge_cache($force_all = false)
	{
		$cache_name = $this->get_name();
		
		$this->cache->destroy('wysiwyg_dynamic_js_vars' . $cache_name);
		$this->cache->destroy('wysiwyg_etag' . $cache_name);
		$this->cache->destroy('wysiwyg_etag_gzip' . $cache_name);
		
		if ($force_all)
		{
			$file_name = $this->cache_prefix . '.' . $cache_name . '.js';
			@unlink($file_name);
			@unlink($file_name . '.gz');
		}
	}

	public function recalculate_editor_setup_javascript($text_formatter_factory)
	{
		$cache_name = $this->get_name();
		
		$this->generate_editor_setup_javascript($text_formatter_factory);
		
		$editor_setup_vars = $this->get_static_javascript_variables();
		$editor_dynamic_vars = $this->get_dynamic_javascript_variables();
		
		$setup_javascript = $this->template->set_filenames(array(
			'editor_config' => self::EDITOR_CONFIG_BASENAME . strtolower($cache_name) . '.js',
		))
			->assign_vars($editor_setup_vars)
			->assign_vars(array(
				'EDITOR_JS_GLOBAL_OBJ' => \phpbb\bbcode\xsl_parse_helper::EDITOR_JS_GLOBAL_OBJ,
			))
			->assign_display('editor_config');
		
		
		$gzip_setup_javascript = gzencode($setup_javascript, 9);
		
		
		$etag = sha1($setup_javascript);
		$uncompressed_etag = $cache_name . '|normal|' . $etag;
		$compressed_etag = $cache_name . '|gzip|'. $etag;
		
		$file_name = $this->cache_prefix . '.' . $cache_name . '.js';
		file_put_contents($file_name, $setup_javascript, LOCK_EX);
		file_put_contents($file_name . '.gz', $gzip_setup_javascript, LOCK_EX);
		
		// Only change or create the cached information after changing the files
		// This prevents corrupted data in the client
		$this->cache->put('wysiwyg_dynamic_js_vars' . $cache_name, $editor_dynamic_vars);
		$this->cache->put('wysiwyg_etag' . $cache_name, $uncompressed_etag);
		$this->cache->put('wysiwyg_etag_gzip' . $cache_name, $compressed_etag);
		
		$this->config->increment('bbcode_version', 1);
	}
	
	/**
	 * This returns the javascript calculated
	 *
	 *
	 *
	 */
	public function get_setup_javascript($use_gz_version = false)
	{
		$file_name = $this->cache_prefix . '.' . $this->get_name() . '.js';
		
		if($use_gz_version)
		{
			$file_name .= '.gz';
		}
		
		if (!file_exists($file_name))
		{
			return false;
		}
		
		$read_js = file_get_contents($file_name);
		
		
		return $read_js;
	}
	
	/**
	 * This handles all the required HTTP cache headers additionally to
	 * get_setup_javascript()
	 * If successful (returning true), the headers were set and the setup javascript was outputed to the user or
	 * the headers were set and status code 304 NOT MODIFIED was sent.
	 *
	 * @return boolean|null true on success, false on failure, null if no setup has been called yet (should never happen)
	 */
	public function handle_user_request_setup_javascript($text_formatter_factory = null)
	{
		$cache_name = $this->get_name();
		
		$accepted_encodings = $this->request->variable("HTTP_ACCEPT_ENCODING", '', request_interface::SERVER);
		
		header('Content-Type: application/javascript', true);
		header('Vary: Accept-Encoding', true);
		// 1 week
		header('Cache-Control: public, max-age=604800', true);
		
		$etag_match = $this->request->variable("HTTP_IF_NONE_MATCH", '', request_interface::SERVER);
		
		if ($etag_match)
		{
			$etag_data = explode('|', $etag_match, 3);
			$current_etag = null;
			
			if($etag_data[1] === 'gzip')
			{
				$current_etag = $this->cache->get('wysiwyg_etag_gzip'. $cache_name);
				header('Content-Encoding: gzip', true);
			}
			else
			{
				$current_etag = $this->cache->get('wysiwyg_etag' . $cache_name);
			}
			
			if ($current_etag === $etag_match)
			{
				// not modified
				header('', false, 304);
				return true;
			}
		}
		
		$accepts_gzip = strpos($accepted_encodings, 'gzip') !== false;
		
		$setup_javascript = $this->get_setup_javascript($accepts_gzip);
		if ($setup_javascript !== false)
		{
			if($accepts_gzip)
			{
				header('Content-Encoding: gzip', true);
			}
			echo $setup_javascript;
			return true;
		}
		if (empty($text_formatter_factory))
		{
			return false;
		}
		$this->recalculate_editor_setup_javascript($text_formatter_factory);
		$setup_javascript = $this->get_setup_javascript();
		if ($setup_javascript !== false)
		{
			if($accepts_gzip)
			{
				header('Content-Encoding: gzip', true);
			}
			echo $setup_javascript;
			return true;
		}
		return null;
	}
	
	public function get_request_variables()
	{
		$cache_name = $this->get_name();
		$dynamic_javascript_vars = $this->cache->get('wysiwyg_dynamic_js_vars' . $cache_name);
		
		if($dynamic_javascript_vars === false)
		{
			return false;
		}
		return $dynamic_javascript_vars;
		
	}
	

	protected function get_container_tags($child_nodes)
	{
		foreach ($child_nodes as $child_node)
		{
			if(isset($child_node['case']))
			{
				$containers = array();
				foreach($child_node['case'] as $case)
				{
					$tag = $this->get_container_tags($case['children']);
					$containers = array_merge($containers, $tag);
				}
				
				return $containers;
			}
			else if($child_node['xsl'])
			{
				return $this->get_container_tags($child_node['children']);
			}
			else
			{
				return array($child_node['tagName']);
			}
		}
		
		return array();
		
	}
	
	
	protected function get_bbcodes_for_tags($bbcodes)
	{
		$tag_to_BBCodes = array();
		
		foreach($bbcodes as $bbcode_name => $bbcode)
		{
			$tag_to_BBCodes[$bbcode->tagName][] = $bbcode_name;
		}
		
		return $tag_to_BBCodes;
	}
	
	protected function filter_out_tags_without_bbcode($bbcodes_for_tags, $tags)
	{
		foreach ($tags as $name => $data)
		{
			if(empty($bbcodes_for_tags[$name]))
			{
				$bbcodes_for_tags[$name] = array();
			}
		}
		
		return $bbcodes_for_tags;
	}
	
	protected function get_all_tag_names($tags)
	{
		$tagnames = array();
		foreach ($tags as $name => $data)
		{
			$tagnames[] = $name;
		}
		
		return $tagnames;
	}
	
	public function extract_and_normalize_bbcode_data($bbcodes, $tags, $low_case_names = true)
	{
		$bbcodes_data = array();
		
		$bbcodes_for_tags = $this->get_bbcodes_for_tags($bbcodes);
		$bbcodes_for_tags = $this->filter_out_tags_without_bbcode($bbcodes_for_tags, $tags);
		
		if ($low_case_names)
		{
			foreach($bbcodes_for_tags as &$bbcodes_for_tag)
			{
				$bbcodes_for_tag = array_map('strtolower', $bbcodes_for_tag);
			}
		}
		
		
		$tag_names = $this->get_all_tag_names($tags);
		
		foreach ($bbcodes as $bbcode_name => $bbcode){
			$this_data = $this->normalize_text_parser_data($bbcode, $tags[$bbcode->tagName], $tag_names);
			
			
			foreach(array('deniedChildren', 'allowedChildren', 
					'deniedDescendants', 'allowedDecendants') as $key)
			{
				$bbcode_list = array();
				foreach ($this_data[$key] as $current_tag)
				{
					foreach ($bbcodes_for_tags[$current_tag] as $bbcode)
					{
						$bbcode_list[] = $bbcode;
					}
				}
				
				$this_data[$key] = $bbcode_list;
			}
			if (isset($this_data['autoCloseOn'])){
				// var_dump($tags['li']);
				$bbcode_list = array();
			// var_dump($this_data['autoCloseOn']);
				foreach ($this_data['autoCloseOn'] as $current_tag)
				{
					foreach ($bbcodes_for_tags[$current_tag] as $bbcode)
					{
						$bbcode_list[] = $bbcode;
					}
				}
				
				$this_data['autoCloseOn'] = $bbcode_list;
			}
			
			if ($low_case_names)
			{
				$bbcodes_data[strtolower($bbcode_name)] = $this_data;
			}
			else
			{
				$bbcodes_data[$bbcode_name] = $this_data;
			}
		}
		
		return $bbcodes_data;
		
	}
	
	public function normalize_text_parser_data($bbcode, $tag, $tag_names = array())
	{
		$config = array();
		$config['useContent'] = array();
		
		foreach ($bbcode->contentAttributes as $use_content_attribute)
		{
			$config['useContent'][] = $use_content_attribute;
		}
		
		$config['defaultAttribute'] = $bbcode->defaultAttribute;
		$config['onlyParseIfClosed'] = $bbcode->forceLookahead;

		$config['attrPresets'] = array();
		foreach ($bbcode->predefinedAttributes as $name => $preset)
		{
			$config['attrPresets'][$name] = $preset;
		}
		// Only parse BBCode if the closing tag was written
		$config['onlyParseIfClosed'] = $bbcode->forceLookahead;
		// This may not be the same the the actual tag name. From the source code:
		// // Create [php] as an alias for [code=php]
		// $bbcode = $configurator->BBCodes->add('php');
		// $bbcode->tagName = 'CODE';
		// $bbcode->predefinedAttributes['lang'] = 'php';
		// That creates a "php" tag that is parsed the same way as a "code" tag with the language as "php"
		$config['bbcodeName'] = strtolower($bbcode->tagName);
		
		$attribute_list = &$config['attr'];
		$attribute_list = array();
		
		foreach ($tag->attributes as $name => $attribute)
		{
			$settings = array();
			$settings['required'] = $attribute->required;
			$settings['defaultValue'] = $attribute->defaultValue;
			$settings['filters'] = array();
			foreach ($attribute->filterChain as $filter)
			{
				$js_validation = $filter->getJS();
				if($js_validation)
				{
					$js_validation = $js_validation->__toString();
					if (strpos($js_validation, 'BuiltInFilters.') === 0)
					{
						$new_filter = array(
							'name'		=> str_replace('BuiltInFilters.', '', $js_validation),
							'extraVars' => '',
						);
						
						foreach ($filter->getVars() as $var)
						{
							$new_filter['extraVars'] .= ', ' . json_encode($var);
						}
						
						$settings['filters'][] = $new_filter;
					}
					else if (strpos($js_validation, 'function') === 0)
					{
						$new_filter = array(
							'inlineFunc' => $js_validation,
							'extraVars' => '',
						);
						
						foreach ($filter->getVars() as $var)
						{
							$new_filter['extraVars'] .= ', ' . json_encode($var);
						}
						
						$settings['filters'][] = $new_filter;
					}
					else if ($js_validation === '')
					{
						// Skip
						// TODO: See if it is feasable not to skip
						continue;
					}
					
				}
				// else
				// {
					// // TODO: Check better here what this is about
					// $settings['validations'][] = array(
						// 'name' 		=> explode('::', $validation->getCallback(), 2)[1],
						// 'extraVars'	=> array(),
					// );
				// }
				
			}
			$attribute_list[$name] = $settings;
		}
		
		// Some code to execute that, supposedly, eases handling to whomever is typing the BBCode
		$config['preProcessors'] = array();
		foreach ($tag->attributePreprocessors as $target_attribute => $pre_processor)
		{
			$regex = $pre_processor->getRegexp();
			$match_vs_attribute = array();
			$errors = null;
			$data = array(
				'sourceAttribute' => $target_attribute,
			);
			
			$transformation_results = js_regex_helper::to_js($regex);
			
			$data['regexFixed'] = $transformation_results['jsRegex'];
			$data['modifiersFixed'] = $transformation_results['modifiers'];
			$data['matchNumVsAttr'] = $transformation_results['matchesVsNames'];
			$config['preProcessors'][] = $data;
			// var_dump($data['regexFixed']);
			
		}
		
		$config['allowedChildren'] = array();
		$config['deniedChildren'] = array();
		$config['allowedDecendants'] = array();
		$config['deniedDescendants'] = array();
		
		foreach($tag->rules as $rule_name => $rule)
		{
			switch($rule_name)
			{
				case 'denyChild':
					$config['deniedChildren'] = array_merge($config['deniedChildren'], $rule);
					$config['allowedChildren'] = array_merge(array_diff($tag_names, $rule), $config['allowedChildren']);
					
				break;
				case 'allowChild':
				
					$config['allowedChildren'] = array_merge($config['allowedChildren'], $rule);
					
				break;
				case 'denyDescendant':
					
					$config['deniedDescendants'] = array_merge($config['deniedDescendants'], $rule);
					$config['allowedDecendants'] = array_merge(array_diff($tag_names, $rule), $config['allowedDecendants']);
					
				break;
				case 'allowDescendant':
					
					$config['allowedDecendants'] = array_merge(array_diff($tag_names, $rule), $config['allowedDecendants']);
					
				break;
				case 'closeParent':
					
					$config['autoCloseOn'] = $rule;
					
				break;
				case 'ignoreSurroundingWhitespace':
					
					$config['trimWhitespace'] = $rule;
					
				break;
				case 'suspendAutoLineBreaks':
					
					$config['trimWhitespace'] = $rule;
					
				break;
				case 'autoClose':
					
					$config['autoClose'] = $rule;
					
				break;
				case 'ignoreTags':
					
					$config['ignoreBBCodeInside'] = $rule;
					
				break;
				case 'ignoreText':
					
					$config['ignoreTextInside'] = $rule;
					
				break;
				
				// no default
			}
		}
		
		
		$config['deniedChildren'] = array_unique($config['deniedChildren']);
		$config['allowedChildren'] = array_unique($config['allowedChildren']);
		$config['deniedDescendants'] = array_unique($config['deniedDescendants']);
		$config['allowedDecendants'] = array_unique($config['allowedDecendants']);
		
		
		return $config;
	}
	
}
