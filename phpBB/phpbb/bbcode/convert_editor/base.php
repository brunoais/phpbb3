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

/**
* 
*/
class base
{

	const HAS_BUTTON_MODE_ICON 		= 0x1;
	const HAS_BUTTON_MODE_TEXT 		= 0x2;
	const HAS_BUTTON_MODE_ICON_TEXT = 0x4;
	// Some buttons are icons, others are text. It depends on what it is.
	const HAS_BUTTON_MODE_MIXED 	= 0x8;
	
	const DEFAULT_WYSIWYG_MODE	= 0x1;
	const DEFAULT_SOURCE_MODE	= 0x3;
	
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
	

	/**
	 *
	 * Just the existence of this method means that this class shouldn't be instantiated.
	 *
	 */
	public static function not_instantiable()
	{
		
	}
	
	
	/**
	 * This returns the javascript calculated
	 *
	 *
	 *
	 */
	public function get_setup_javascript()
	{
		
		
	}

	/**
	 * This returns the javascript calculated
	 *
	 *
	 *
	 */
	public function get_javascript_regex_matches()
	{
		
		ob_start();
		// Keep the <script> part to ease the syntax highlighters
		?>
<script>
		var tokenRegexTranslator = {
			'ALPHANUM': /^[0-9A-Za-z]+$/,
			'SIMPLETEXT': /^[a-z0-9,.\-+_]+$/i,
			'IDENTIFIER': /^[a-z0-9-_]+$/i,
			'INTTEXT': /^[a-zA-Z\u00C0-\u017F]+,\s[a-zA-Z\u00C0-\u017F]+$/,
			'NUMBER': /^[0-9]+$/,
			
			'EMAIL': /[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+(?:[A-Z]{2}|com|org|net|edu|gov|mil|me|biz|info|mobi|name|aero|asia|jobs|museum)\b/
			
			'URL': /^(?:(?:https?|ftps?):\/\/)?(?:(?:[a-z]+@)?(([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})|((?:[A-F0-9]{1,4}:){7}[A-F0-9]{1,4})|((?:[A-F0-9]{1,4}:){1,4}:(?:[A-F0-9]{1,4}:){0,4}[A-F0-9]{1,4})|(?:(?:[a-z0-9-]+\.)+[a-z]{2,7})|localhost))?(?:\/([a-z0-9-\/.]*))?(?:\?((?:[^=]+=[^&]+&)*(?:[^=]+=[^#$]+)?))?(?:#[^$]*)?$/i
			'LOCAL_URL': /^(?:\/([a-z0-9-\/.]*))?(?:\?((?:[^=]+=[^&]+&)*(?:[^=]+=[^#$]+)?))?(?:#[^$]*)?$/
			'RELATIVE_URL': /^(?:\/([a-z0-9-\/.]*))?(?:\?((?:[^=]+=[^&]+&)*(?:[^=]+=[^#$]+)?))?(?:#[^$]*)?$/
			
			'COLOR': /^(?:#[0-9a-f]{3,6}|rgb\(\d{1,3}, *\d{1,3}, *\d{1,3}\)|aqua|black|blue|fuchsia|gray|green|lime|maroon|navy|olive|orange|purple|red|silver|teal|white|yellow)$/i,
		}
<?php	
		// Remove the <script> part
		$filters_output = substr(ob_get_clean(), 11);
		
		
	}
	
	public function get_container_tags($child_nodes)
	{
		
		foreach ($child_nodes as $child_node)
		{
			if(isset($child_node['case']))
			{
				$containers = array();
				foreach($child_node['case'] as $case)
				{
					$tag = $this->get_container_tags($case['children']);
					if(is_array($tag))
					{
						array_merge($containers, $tag);
					}
					else
					{
						$containers[] = $tag;
					}
				}
				
				return $containers;
			}
			else if($child_node['xsl'])
			{
				return $this->get_container_tags($child_node['children']);
			}
			else
			{
				return $child_node['tagName'];
			}
		}
		
		return null;
		
	}
	
}
