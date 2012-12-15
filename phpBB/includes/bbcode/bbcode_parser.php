<?php
/**
*
* @package phpBB3
* @copyright (c) 2012 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}
	
class phpbb_bbcode_bbcode_parser
{
	
	protected $bbcode_tags;
	
	protected $string;
	
	protected $bbcode_ordered_tag_list;
	
	protected $tags_kind;
	
	protected $bbcode_tree;
	
	protected $parse_result;
	
	
	public function __construct(&$string, &$bbcode_tags)
	{
		// The list of BBCodes for the regex matcher
		
		$this->tags_kind = array();
		
		$this->bbcode_tree = array();
		
		$this->bbcode_tags = &$bbcode_tags;
		
		$this->string = &$string;
		
	}
	
	// Step 1: Find opening and closing tags in the text.
	
	protected static function parse_inner_parameters($parameters_string)
	{
	
		// This will parse all parameters in this multiparameter tag
		// These parameters must follow about the same rules as the parameters in XML.
		// As usual, if it is invalid, it is just ignored
		preg_match_all(
		'%([A-z][A-z0-9-]+)=(?:"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)")%', $parameters_string, $parameters_match, PREG_SET_ORDER);
		
		$parameters = array();
		
		foreach ($parameters_match AS $parameter)
		{			
			$parameters[$parameter[1]] = str_replace('\"', '&quot;', $parameter[2]);
		}
		
		return $parameters;
	}
	
	public function step1()
	{
		$bbcode_tags;
		
		foreach ($this->bbcode_tags as $tag => $unused)
		{
			$bbcode_tags[] = preg_quote($tag, '%');
		}
		
		$regexedBBCode = implode('|', $bbcode_tags);
		
		preg_match_all(
		'%'.
		// Parse the opening of the tag. E.g. [abc
		'\[('.$regexedBBCode.')' .
		// If the tag has insides:
		'(?:' . 
		// Is this single parameter (starts with a "=")?
		'(?:=(?:'.
		// This starts with a '"', so it will also end with a '"'. If the user wants to write a literal '"' he must escape it with \.
		// Ex: [abc="He said \"Hello!\""]
		'"([^"]*(?:\\\\.[^"\\\\]*)*)"'.
		'|'.
		// This does not start with a '"', then it ends at the first "]" it has.
		// Note that you cannot escape the "]" character like the other alternative
		'([^"\\\\][^]]+)))'.
		'|'.
		// Oh! This is a multi parameter tag! Get all parameters to be parsed later.
		'((?:\s+(?:[A-z][A-z0-9]+)=(?:"(?:[^"\\\\]*(?:\\\\.[^"\\\\]*)*)"))+)\s*'.
		// The "?" is here because the insides are optional. The tag [abc] is a valid tag.
		')?'.
		// Tag ends here. Also grab the character where this happens. It'll be useful later
		'\]()'.
		'|'.
		// It's a closing tag. They are also useful to match with the opening tags
		'\[/('.$regexedBBCode.')\]()'.
		'%',$this->string, $matched, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
		
		
		foreach ($matched AS $match)
		{
			if (isset($match[6][0]))
			{
				// It's a closing tag
				if (isset($this->tags_kind[$match[6][0]]['starting_tags']))
				{
					$this->tags_kind[$match[6][0]]['ending_tags'][] = array('name' => $match[6][0],
																	'start_position' => $match[6][1] - 1,
																	'end_position' => $match[7][1]);
				/* 
				}
				else
				{ */
					// If there's still no opening tag for this tag, this closing tag will not match any opening tag,
					// so no need to register it
				}
			}
			elseif (isset($match[1][0]) && $match[1][0] != "")
			{
				// It's an opening tag.
				$tag = array();
				$tag['name'] = $match[1][0];
				$tag['start_position'] = $match[1][1];
				$tag['end_position'] = $match[5][1];
				
				// Only one of these will ever match
				if ($match[4][1] > -1)
				{
					// multiple parameters
					$tag['parameters'] = self::parse_inner_parameters($match[4][0]);
				}
				elseif ($match[3][1] > -1)
				{
					// 1 parameter bounded by the end of the start tag
					$tag['parameters'] = $match[3][0];
				}
				elseif ($match[2][1] > -1)
				{
					// 1 parameter bounded by quotes
					// Replace currently needed due to the way this works.
					$tag['parameters'] = str_replace('\"', '&quot;', $match[2][0]);
				}
				
				$this->tags_kind[$tag['name']]['starting_tags'][] = $tag;
			
			}
		}
		
	}

	public function step2()
	{
		
		$tag_stack = array();
		
		// Step 2: Pair opening and closing tags. 
			
		foreach ($this->tags_kind as $bbcode_name => &$data)
		{
			
			// echo "\n\n\n";
			
			while ($data['starting_tags'] != array() && current($data['ending_tags']) != false)
			{
				// There's, at least, one possible
				
				// Got a closing tag!
				$ending_tag = current($data['ending_tags']);
				
				reset($data['starting_tags']);
				
				// Find an appropriate opening tag
				while ( next($data['starting_tags']) !== false )
				{
						$temp = current($data['starting_tags']);
						if ($temp['end_position'] >= $ending_tag['start_position'])
						{
							break;
						}
					}
				
				// The test showed that the next element is beyond what I'm looking for, so the previous is the one I want
				// Notice: 	This assumes that the previous step went as expected.
				// 			If there's no opening tag before a close tag, that close tag is not matched.
				prev($data['starting_tags']);
				
				$current_start_tag = current($data['starting_tags']);
				
				if ($current_start_tag === false)
				{
					// If I go beyond the top limits of the array. The only way to get back is by using end(), prev() will not work.
					$current_start_tag = end($data['starting_tags']);
				}
				
				if ($current_start_tag['end_position'] < $ending_tag['start_position'])
				{
									
					// K'ay, this is a match for that closing tag
					
					$this->bbcode_ordered_tag_list[$current_start_tag['end_position']] = 
												array(
													'start_tag' => $current_start_tag,
													'end_tag' => $ending_tag
												);
					
					unset($data['starting_tags'][key($data['starting_tags'])]);
					
				}
				else
				{
					// Oh dear... no match for this closing tag...
					// Malformed BBcode... I don't care, I'll see what I can do with the rest, anyway
					// continue;
				}
				next($data['ending_tags']);
			}
		}
	}
	
	
	
	public function step3()
	{
		ksort($this->bbcode_ordered_tag_list);
		// Step 3: Build the tree of tags
		
		// push the first element into the tree	
		$this->bbcode_tree[] = &$this->bbcode_ordered_tag_list[key($this->bbcode_ordered_tag_list)];
		// and also make it the first parent that will receive the child Nodes
		$current_parent = &$this->bbcode_ordered_tag_list[key($this->bbcode_ordered_tag_list)];
		
		// Get the next element of the list and start crackin'!
		next($this->bbcode_ordered_tag_list);
		
		while (current($this->bbcode_ordered_tag_list) !== false)
		{
			// While we didn't check about all tags found
			$current = current($this->bbcode_ordered_tag_list);
			// Check if this tag is inside the current parent
			if ($current['start_tag']['start_position'] <= $current_parent['end_tag']['end_position'])
			{
				if ($current['end_tag']['end_position'] <= $current_parent['end_tag']['end_position'])
				{
					// Tag is inside this parent. So this tag is part of this parent's children
					
					// push the previous parent
					$tag_stack[] = &$current_parent;
		
					// Make this tag children of the current parent tag
					$current_parent['children'][] = &$this->bbcode_ordered_tag_list[key($this->bbcode_ordered_tag_list)];

					// Update the parent tag
					$current_parent = &$this->bbcode_ordered_tag_list[key($this->bbcode_ordered_tag_list)];

				}
				else
				{
					// Bad nesting. This tag is meant to dissapear from this world! Well, not really... Just read it as text.
					unset($this->bbcode_ordered_tag_list[key($this->bbcode_ordered_tag_list)]);
				}
				next($this->bbcode_ordered_tag_list);
			}
			else /* if (current($this->bbcode_ordered_tag_list)['start_tag']['start_position'] > $current_parent['end_tag']['end_position']) */
			{
				// Close previous tag here. There are no more children.
				
				// var_dump("closing", $current_parent['start_tag']['parameters']['child']);
				
				if (end($tag_stack) === false)
				{
					// var_dump("stackEmpty", $current_parent['start_tag']['parameters']['child']);
					
					// This tag belongs to the root, so it needs to be directly added to the tree's root
					
					$current_parent = &$this->bbcode_ordered_tag_list[key($this->bbcode_ordered_tag_list)];
					$this->bbcode_tree[] = &$this->bbcode_ordered_tag_list[key($this->bbcode_ordered_tag_list)];
					
					next($this->bbcode_ordered_tag_list);
					// var_dump("nextVictim", $current_parent['start_tag']['parameters']['child']);
				}
				else
				{
					// Process the closing of the tag
					$current_parent = &$tag_stack[key($tag_stack)];
					// Pop from the stack
					unset($tag_stack[key($tag_stack)]);
					
					// Really! no next() here. 
				}
			}
			
		}
	}
	
	// Step 4: Filter out child nodes that are not allowed.
	
	
	
	
	// Step 5: (Is there a step5)?
	
	
	// Step 6: Build the tree with the current known nodes
	
	protected function join_contents_to_element(&$element)
	{
		
		//assumes that if the tag does not have children, the key children is not set
		if (isset($element['children']))
		{
			// for each child
			// This assumes that the children are properly sorted by the ['start_tag']['start_position']
			
			// Well... needs a better name and... we cheat for the first iteration
			$previous_child['end_tag']['end_position'] = &$element['start_tag']['end_position'];
			
			foreach ($element['children'] as $child_key => &$child)
			{
				$element['text'][] = substr(
										$this->string,
										$previous_child['end_tag']['end_position'],
										$child['start_tag']['start_position'] -
											$previous_child['end_tag']['end_position'] - 1);
				self::join_contents_to_element($child);
				
				$previous_child = &$child;
			}
			// remmeber that by the spec, $child is still set with the last child of the array
			$element['text'][] = substr(
									$this->string,
									$child['end_tag']['end_position'],
									$element['end_tag']['start_position'] -
										$child['end_tag']['end_position'] - 1);
		}
		else
		{
			if ($element['end_tag']['start_position'] -
										$element['start_tag']['end_position'] - 1 === 0)
			{
				$element['text'][] = "";
			}
			else
			{
				$element['text'][] = substr(
										$this->string,
										$element['start_tag']['end_position'],
										$element['end_tag']['start_position'] -
											$element['start_tag']['end_position'] - 1);
			}
		}
		
	}
	
	protected function step6()
	{
		foreach ($this->bbcode_tree as &$rootBBCode)
		{
			self::join_contents_to_element($rootBBCode);		
		}
		$this->parse_result = &$this->bbcode_tree;
	}

	
	protected function replace_with_bbcode(&$element, $deepness)
	{
		
		$final_string = '';
		
		//assumes that if the tag does not have children, the key children is not set
		if (isset($element['children']))
		{
			// for each child
			// This assumes that the children are properly sorted by the ['start_tag']['start_position']
			
			// Well... needs a better name and... we cheat for the first iteration
			$previous_child['end_tag']['end_position'] = &$element['start_tag']['end_position'];
			
			foreach ($element['children'] as $child_key => &$child)
			{
				$final_string .= substr(
										$this->string,
										$previous_child['end_tag']['end_position'],
										$child['start_tag']['start_position'] -
											$previous_child['end_tag']['end_position'] - 1);
				
				$final_string .= self::replace_with_bbcode($child, $deepness + 1);
				
				$previous_child = &$child;
			}
			// remmeber that, by the spec, $child is still set with the last child of the array
			$final_string .= substr(
									$this->string,
									$child['end_tag']['end_position'],
									$element['end_tag']['start_position'] -
										$child['end_tag']['end_position'] - 1);
		}
		else
		{
			if ($element['end_tag']['start_position'] -
										$element['start_tag']['end_position'] - 1 === 0)
			{
				$final_string .= "";
			}
			else
			{
				$final_string .= substr(
										$this->string,
										$element['start_tag']['end_position'],
										$element['end_tag']['start_position'] -
											$element['start_tag']['end_position'] - 1);
			}
		}
		
		return $this->bbcode_tags[$element['start_tag']['name']]['callback']->parse(
							$element['start_tag']['name'],
							isset($element['start_tag']['parameters'])? 
								$element['start_tag']['parameters'] :
								null,
							$final_string,
							$deepness);
		
	}
	
	protected function step7()
	{
		
		$final_string = '';
		// Fake previous_child to give a kickstart
		$previous_child = array();
		$previous_child['end_tag']['end_position'] = 0;
		
		foreach ($this->bbcode_tree as &$rootBBCode)
		{
			$final_string .= substr(		$this->string,
										$previous_child['end_tag']['end_position'],
										$rootBBCode['start_tag']['start_position'] -
											$previous_child['end_tag']['end_position'] - 1);
											
			$final_string .= self::replace_with_bbcode($rootBBCode, 0);	
			$previous_child = &$rootBBCode;
		}

		$final_string .= substr(		$this->string,
									$previous_child['end_tag']['end_position'],
									strlen($this->string) -
										$previous_child['end_tag']['end_position']);

		$this->parse_result = &$final_string;
	}
	

	public function parse_phase1()
	{
		$this->step1();
		$this->step2();
		$this->step3();
		// $this->step4();
		// $this->step5();
		$this->step6();
		return $this->parse_result;
	}
	public function parse_phase2()
	{
		$this->step7();
		return $this->parse_result;
	}
	
	public function parse()
	{
		$this->parsePhase1();
		$this->parsePhase2();
		return $this->parse_result;
	}
	
	public function get_result()
	{
		return $this->parse_result;
	}
	
}

