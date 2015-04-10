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

namespace phpbb\bbcode;

use DOMDocument;
use Exception;

/**
* 
*/
class xsl_parse_helper
{

	private $choose_num;
	private $current_bbcode;
	private $conditions_document;
	
	const XSLNS = "http://www.w3.org/1999/XSL/Transform";
	
	
	public function parse_tag_templates($bbcodes, $tags){
		
		$this->conditions_document = new DOMDocument();
		$this->conditions_document->loadXML(
			'<?xml version="1.0"?>
			<xsl:stylesheet version="1.0" xmlns:xsl="' . self::XSLNS . '">
			<xsl:param name="type" select="$type"/>
			<xsl:output method="text" encoding="iso-8859-1" indent="yes"/>
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
			try{
				$this->current_bbcode = $bbcode->tagName;
				$this->choose_num = 0;
				$parseTrees[$bbcode->tagName] = $this->parse_tag_template($tags[$bbcode->tagName]->template);
			}catch(Exception $e){
				var_dump($e->getMessage());
			}
		}
		
		// var_dump($tags['list']->template->__toString());
		// try{
			// $this->current_bbcode = 'list';
			// $this->choose_num = 0;
			// $parseTrees['list'] = $this->parse_tag_template($tags['list']->template);
		// }catch(Exception $e){
			// var_dump($e->getMessage());
			// var_dump($e->getTraceAsString());
			// return;
		// }
		
		
		var_dump($parseTrees);
		exit;
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
	
	protected function parse_tag_template_childNode($currentNode){
		
		$current = array(
			'xsl' => $currentNode->prefix === 'xsl',
			'tagName' => $currentNode->localName,
			'node' => $currentNode,
			'children' => array(),
		);
		
		if($current['xsl']){
			
			switch($current['tagName']){
				case 'copy-of':
					// TODO: How to handle a deep copy
					break;
				case 'value-of':
					// $this->identifyValue($currentNode);
					
					break;
				case 'if':
				case 'choose':
					return $this->translateConditions($currentNode);
					
					break;
				case 'for-each':
				
				break;
				case 'apply-templates':
				case 'text':
				break;
				case 'comment':
					return;
				break;
				default:
					throw new Exception (
						'Tag ' . $currentNode->tagName . ' is not recognized to translate for WYSIWYG'
					);
			}
			
			
			foreach ($currentNode->attributes as $attr)
			{
				
				preg_match_all("%(([@$])(?:([SL])_)?([a-zA-Z_0-9]+))%", $attr->textContent,
					$variables_match, PREG_SET_ORDER);
				
				$attr->nodeName, 
				
			}
		}else{
			
			foreach ($currentNode->attributes as $attr)
			{
				
				preg_match_all("%{(([@$])(?:([SL])_)?([a-zA-Z_0-9]+))}%", $attr->textContent,
					$variables_match, PREG_SET_ORDER);
				
			
				
			}
			
		}
		
		
		
			
		
		if ($currentNode->hasChildNodes()){
			foreach($currentNode->childNodes AS $childNode){
				$current['children'][] = $this->parse_tag_template_childNode($childNode);
			}
		}
		
		return $current;
	}
	


	public function translateConditions($currentNode){
		$data = array(
			'num' => $this->choose_num,
			'case' => array(),
		);
		
		$template = $this->conditions_document->createElementNS(self::XSLNS, 'xsl:template');
		$template->setAttribute('match', $this->current_bbcode . "[@d='" . $this->choose_num . "']");
		
		$choose = $this->conditions_document->createElementNS(self::XSLNS, 'xsl:choose');
		
		$chr = 'a';
		
		$case = &$data['case'];
		
		foreach ($currentNode->childNodes AS $whenNode){
			// This can either be a "xsl:when" or "xsl:otherwise".
			// additionally, for the "xsl:when", I want the exact same @test attr
			$when = $this->conditions_document->importNode($whenNode->cloneNode(false));
			// <xsl:when test>$chr</xsl:when>
			$when->appendChild($this->conditions_document->createTextNode($chr));
			
			$case[$chr] = array();
			foreach ($whenNode->childNodes as $childNode){
				$case[$chr][] = $this->parse_tag_template_childNode($childNode);
			}
			
			$choose->appendChild($when);
			$chr++;
		}
		
		$template->appendChild($choose);
		
		$this->conditions_document->firstChild->appendChild($template);
		
		return $data;
		
		// var_dump($this->conditions_document->saveXML($template));
		// exit;
	}
}



		// $xslt = new XSLTProcessor();
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
				