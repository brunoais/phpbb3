<?php
		
class BBCodeParser{
	
	protected $BBCode_tags;
	
	protected $string;
	
	protected $stringedBBCode;
	
	protected $BBCodeOrderedTagList;
	
	protected $orderedTags;
	
	protected $tagsKind;
	
	protected $BBCodeTree;
	
	protected $parseResult;
	
	
	public function __construct(&$string, &$BBCode_tags){
		// The list of BBCodes for the regex matcher
		
		$this->orderedTags = array();
		
		$this->tagsKind = array();
		
		$this->BBCodeTree = array();
		
		$this->BBCode_tags = &$BBCode_tags;
		
		$this->string = &$string;
		
	}
	
	// Step 1: Find opening and closing tags in the text.
	
	protected static function parseInnerParameters($parametersString){
	
		// This will parse all parameters in this multiparameter tag
		// These parameters must follow about the same rules as the parameters in XML.
		// As usual, if it is invalid, it is just ignored
		preg_match_all(
		'%([A-z][A-z0-9-]+)=(?:"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)")%', $parametersString, $parametersMatch, PREG_SET_ORDER);
		
		$parameters = array();
		
		foreach($parametersMatch AS $parameter){			
			$parameters[$parameter[1]] = str_replace('\"', '&quot;', $parameter[2]);
		}
		
		return $parameters;
	}
	
	public function step1(){
		$bbcode_tags;
		
		foreach($this->BBCode_tags as $tag => $unused){
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
		
		
		foreach($matched AS $match){
			if (isset($match[6][0])){
				// It's a closing tag
				if (isset($this->tagsKind[$match[6][0]]['startingTags'])){
					$this->tagsKind[$match[6][0]]['endingTags'][] = array('name' => $match[6][0],
																	'start_position' => $match[6][1] - 1,
																	'end_position' => $match[7][1]);
				/* }else{ */
					// If there's still no opening tag for this tag, this closing tag will not match any opening tag,
					// so no need to register it
				}
			}elseif (isset($match[1][0]) && $match[1][0] != ""){
				// It's an opening tag.
				$tag = array();
				$tag['name'] = $match[1][0];
				$tag['start_position'] = $match[1][1];
				$tag['end_position'] = $match[5][1];
				
				// Only one of these will ever match
				if ($match[4][1] > -1){
					// multiple parameters
					$tag['parameters'] = self::parseInnerParameters($match[4][0]);
				}elseif ($match[3][1] > -1){
					// 1 parameter bounded by the end of the start tag
					$tag['parameters'] = $match[3][0];
				}elseif ($match[2][1] > -1){
					// 1 parameter bounded by quotes
					// Replace currently needed due to the way this works.
					$tag['parameters'] = str_replace('\"', '&quot;', $match[2][0]);
				}
				
				// $this->orderedTags[] = array(	'name' => $match[1][0],
										// 'type' => 'opening_tag');
				$this->tagsKind[$tag['name']]['startingTags'][] = $tag;
			
			}
		}
		
	}

	public function step2(){
		
		$tagStack = array();
		
		// Step 2: Pair opening and closing tags. 
			
		foreach ($this->tagsKind as $BBCodeName => &$data){
			
			// echo "\n\n\n";
			
			while ($data['startingTags'] != array() && current($data['endingTags']) != false){
				// There's, at least, one possible
				
				// Got a closing tag!
				$endingTag = current($data['endingTags']);
				
				reset($data['startingTags']);
				
				// Find an appropriate opening tag
				while( next($data['startingTags']) !== false ){
						$temp = current($data['startingTags']);
						if($temp['end_position'] >= $endingTag['start_position']){
							break;
						}
					}
				
				// The test showed that the next element is beyond what I'm looking for, so the previous is the one I want
				// Notice: 	This assumes that the previous step went as expected.
				// 			If there's no opening tag before a close tag, that close tag is not matched.
				prev($data['startingTags']);
				
				$currentStartTag = current($data['startingTags']);
				
				if ($currentStartTag === false){
					// If I go beyond the top limits of the array. The only way to get back is by using end(), prev() will not work.
					$currentStartTag = end($data['startingTags']);
				}
				
				if ($currentStartTag['end_position'] < $endingTag['start_position']){
									
					// K'ay, this is a match for that closing tag
					
					$this->BBCodeOrderedTagList[$currentStartTag['end_position']] = 
												array(
													'start_tag' => $currentStartTag,
													'end_tag' => $endingTag
												);
					
					unset($data['startingTags'][key($data['startingTags'])]);
					
				}else{
					// Oh dear... no match for this closing tag...
					// Malformed BBcode... I don't care, I'll see what I can do with the rest, anyway
					// continue;
				}
				next($data['endingTags']);
			}
		}
	}
	
	
	
	public function step3(){
		ksort($this->BBCodeOrderedTagList);
		// Step 3: Build the tree of tags
		
		// push the first element into the tree	
		$this->BBCodeTree[] = &$this->BBCodeOrderedTagList[key($this->BBCodeOrderedTagList)];
		// and also make it the first parent that will receive the child Nodes
		$currentParent = &$this->BBCodeOrderedTagList[key($this->BBCodeOrderedTagList)];
		
		// Get the next element of the list and start crackin'!
		next($this->BBCodeOrderedTagList);
		
		while(current($this->BBCodeOrderedTagList) !== false){
			// While we didn't check about all tags found
			$current = current($this->BBCodeOrderedTagList);
			// Check if this tag is inside the current parent
			if ($current['start_tag']['start_position'] <= $currentParent['end_tag']['end_position']){
				if ($current['end_tag']['end_position'] <= $currentParent['end_tag']['end_position']){
					// Tag is inside this parent. So this tag is part of this parent's children
					
					// push the previous parent
					$tagStack[] = &$currentParent;
		
					// Make this tag children of the current parent tag
					$currentParent['children'][] = &$this->BBCodeOrderedTagList[key($this->BBCodeOrderedTagList)];

					// Update the parent tag
					$currentParent = &$this->BBCodeOrderedTagList[key($this->BBCodeOrderedTagList)];

				}else{
					// Bad nesting. This tag is meant to dissapear from this world! Well, not really... Just read it as text.
					unset($this->BBCodeOrderedTagList[key($this->BBCodeOrderedTagList)]);
				}
				next($this->BBCodeOrderedTagList);
			}else /* if (current($this->BBCodeOrderedTagList)['start_tag']['start_position'] > $currentParent['end_tag']['end_position']) */{
				// Close previous tag here. There are no more children.
				
				// var_dump("closing", $currentParent['start_tag']['parameters']['child']);
				
				if (end($tagStack) === false){
					// var_dump("stackEmpty", $currentParent['start_tag']['parameters']['child']);
					
					// This tag belongs to the root, so it needs to be directly added to the tree's root
					
					$currentParent = &$this->BBCodeOrderedTagList[key($this->BBCodeOrderedTagList)];
					$this->BBCodeTree[] = &$this->BBCodeOrderedTagList[key($this->BBCodeOrderedTagList)];
					
					next($this->BBCodeOrderedTagList);
					// var_dump("nextVictim", $currentParent['start_tag']['parameters']['child']);
				}else{
					// Process the closing of the tag
					$currentParent = &$tagStack[key($tagStack)];
					// Pop from the stack
					unset($tagStack[key($tagStack)]);
					
					// Really! no next() here. 
				}
			}
			
		}
	}
	
	// Step 4: Filter out child nodes that are not allowed.
	
	
	
	
	// Step 5: (Is there a step5)?
	
	
	// Step 6: Build the tree with the current known nodes
	
	protected function joinContentsToElement(&$element){
		
		//assumes that if the tag does not have children, the key children is not set
		if(isset($element['children'])){
			// for each child
			// This assumes that the children are properly sorted by the ['start_tag']['start_position']
			
			// Well... needs a better name and... we cheat for the first iteration
			$previousChild['end_tag']['end_position'] = &$element['start_tag']['end_position'];
			
			foreach ($element['children'] as $childKey => &$child) {
				$element['text'][] = substr(
										$this->string,
										$previousChild['end_tag']['end_position'],
										$child['start_tag']['start_position'] -
											$previousChild['end_tag']['end_position'] - 1);
				self::joinContentsToElement($child);
				
				$previousChild = &$child;
			}
			// remmeber that by the spec, $child is still set with the last child of the array
			$element['text'][] = substr(
									$this->string,
									$child['end_tag']['end_position'],
									$element['end_tag']['start_position'] -
										$child['end_tag']['end_position'] - 1);
		}else{
			if($element['end_tag']['start_position'] -
										$element['start_tag']['end_position'] - 1 === 0){
				$element['text'][] = "";
			}else{
				$element['text'][] = substr(
										$this->string,
										$element['start_tag']['end_position'],
										$element['end_tag']['start_position'] -
											$element['start_tag']['end_position'] - 1);
			}
		}
		
	}
	
	protected function step6(){
		foreach ($this->BBCodeTree as &$rootBBCode) {
			self::joinContentsToElement($rootBBCode);		
		}
	}

	
	protected function replaceWithBBCode(&$element, $deepness){
		
		$finalString = '';
		
		//assumes that if the tag does not have children, the key children is not set
		if(isset($element['children'])){
			// for each child
			// This assumes that the children are properly sorted by the ['start_tag']['start_position']
			
			// Well... needs a better name and... we cheat for the first iteration
			$previousChild['end_tag']['end_position'] = &$element['start_tag']['end_position'];
			
			foreach ($element['children'] as $childKey => &$child) {
				$finalString .= substr(
										$this->string,
										$previousChild['end_tag']['end_position'],
										$child['start_tag']['start_position'] -
											$previousChild['end_tag']['end_position'] - 1);
				
				$finalString .= self::replaceWithBBCode($child, $deepness + 1);
				
				$previousChild = &$child;
			}
			// remmeber that, by the spec, $child is still set with the last child of the array
			$finalString .= substr(
									$this->string,
									$child['end_tag']['end_position'],
									$element['end_tag']['start_position'] -
										$child['end_tag']['end_position'] - 1);
		}else{
			if($element['end_tag']['start_position'] -
										$element['start_tag']['end_position'] - 1 === 0){
				$finalString .= "";
			}else{
				$finalString .= substr(
										$this->string,
										$element['start_tag']['end_position'],
										$element['end_tag']['start_position'] -
											$element['start_tag']['end_position'] - 1);
			}
		}
		
		return $this->BBCode_tags[$element['start_tag']['name']]['callback']->parse(
							$element['start_tag']['name'],
							isset($element['start_tag']['parameters'])? 
								$element['start_tag']['parameters'] :
								null,
							$finalString,
							$deepness);
		
	}
	
	protected function step7(){
		
		$finalString = '';
		// Fake previousChild to give a kickstart
		$previousChild = array();
		$previousChild['end_tag']['end_position'] = 0;
		
		foreach ($this->BBCodeTree as &$rootBBCode) {
			$finalString .= substr(		$this->string,
										$previousChild['end_tag']['end_position'],
										$rootBBCode['start_tag']['start_position'] -
											$previousChild['end_tag']['end_position'] - 1);
											
			$finalString .= self::replaceWithBBCode($rootBBCode, 0);	
			$previousChild = &$rootBBCode;
		}

		$finalString .= substr(		$this->string,
									$previousChild['end_tag']['end_position'],
									strlen($this->string) -
										$previousChild['end_tag']['end_position']);

		$this->parseResult = &$finalString;
	}
	
	public function parsePhase1(){
		$this->step1();
		$this->step2();
		$this->step3();
		// $this->step4();
		// $this->step5();
		$this->step6();
	}
	public function parsePhase2(){
		$this->step7();
	}
	
	public function parse(){
		$this->parsePhase1();
		$this->parsePhase2();
		return $this->parseResult;
	}
	
	public function getResult(){
		return $this->parseResult;
	}
	
}

?>
