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
	
	const XSLNS = "http://www.w3.org/1999/XSL/Transform";

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
	
	
	public function parse_tag_templates($bbcodes, $tags){
		
		$doc = new \DOMDocument();
		$doc->loadXML(
			'<?xml version="1.0"?>
			<xsl:stylesheet version="1.0" xmlns:xsl="' . base::XSLNS . '">
			<xsl:param name="type" select="$type"/>
			<xsl:output method="text" encoding="iso-8859-1" indent="no"/>
			</xsl:stylesheet>',
			LIBXML_DTDLOAD
		);
		
		$parseTrees = array();
		
		// [11] => xsl:copy-of
		// [20] => xsl:attribute -> https://msdn.microsoft.com/en-us/library/ms256232%28v=vs.110%29.aspx
		// [22] => xsl:value-of -> https://msdn.microsoft.com/en-us/library/ms256232%28v=vs.110%29.aspx

		// [17] => xsl:if -> https://developer.mozilla.org/en-US/docs/Web/XSLT/if
		// [25] => xsl:choose
		// [26] => xsl:when
		// [30] => xsl:otherwise
		// xsl:variable
		// xsl:for-each -> Postponed until examples arrive
		
		
		foreach($bbcodes as $bbcode){
			
			var_dump($tags[$bbcode->tagName]->template->__toString());
			$parseTrees[$bbcode->tagName] = $this->parse_tag_template($tags[$bbcode->tagName]->template);
			
		}
		
		var_dump($parseTrees);
	}
	
	public function parse_tag_template($template){
		
		$doc = $template->asDOM();
		// var_dump($doc->saveXML($doc->firstChild->firstChild));
		
		$top = array();
		// Childnodes of the template Element
		foreach($doc->firstChild->childNodes AS $childNode){
			$top[] = $this->parse_tag_template_childNode($childNode);
		}
		
		return $top;
	}
	
	public function parse_tag_template_childNode($parent){
		$current = array(
			'xsl' => $parent->prefix === 'xsl',
			'element' => $parent,
			'children' => array(),
		);
		
		if ($parent->hasChildNodes()){
			foreach($parent->childNodes AS $childNode){
				$current['children'][] = $this->parse_tag_template_childNode($childNode);
			}
		}
		
		return $current;
	}
	
}





		// $xslt = new \XSLTProcessor();
		// $xslt->importStylesheet(new \SimpleXMLElement(
		// '<?xml version="1.0"?\>
		// <xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
		// <xsl:param name="type" select="$type"/>
		// <xsl:output method="xml" encoding="iso-8859-1" indent="no"/>
		// <xsl:template match="data1[@d=\'1\']">
			// <xsl:choose>
				// <xsl:when test="not($type)">a</xsl:when>
				// <xsl:when test="contains(\'upperlowerdecim\',substring($type,1,5))">b</xsl:when>
				// <xsl:otherwise>c</xsl:otherwise>
			// </xsl:choose>
		// </xsl:template>
		// <xsl:template match="data1">
			// <xsl:choose>
				// <xsl:when test="not($type)">d</xsl:when>
				// <xsl:when test="contains(\'upperlowerdecim\',substring($type,1,5))">e</xsl:when>
				// <xsl:otherwise>f</xsl:otherwise>
			// </xsl:choose>
		// </xsl:template>
		// </xsl:stylesheet>'
				// ));
				
		// $xslt->setParameter('', 'type', '');
		
		// var_dump($xslt->transformToXml(new \SimpleXMLElement('<container><data1 d="1">u</data1><data1>u</data1><data1>u</data1></container>')));
				