<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	<title></title>
	<style type="text/css">
		body{
			white-space:pre;
		}
	</style>
</head>
<body>
<?php
	$string = '[/abc][abc=aij] [/ubc][ubc][ab]a [abc="uij]f\"fu"] [/abc][/ubc][abc param1="it is \"val1\"" param2="it is \"val2\"" ] [/ubc][/abc][/ab]b [abc][/abc] c[/ubc][ubc][/ubc][/ubc][/ubc][ubc]';
	
	// $BBCode_tags = array('abc', 'cbc', 'dbc');
	// $BBCode_tags = array('abc', 'ubc');
	$BBCode_tags = array('ubc');

	// The list of BBCodes for the regex matcher
	$regexedBBCode = implode('|', $BBCode_tags);
	
	function parseInnerParameters($parametersString){
		
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
	'%',$string, $matched, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);
	
	$orderedTags = array();
	
	$tagsKind = array();
	
	foreach($matched AS $match){
		if (isset($match[6][0])){
			// It's a closing tag
			if(isset($tagsKind[$match[6][0]]['startingTags'])){
				// If there's already an opening tag
				// $orderedTags[] = array(	'name' => $match[6][0],
										// 'type' => 'closing_tag');
				$tagsKind[$match[6][0]]['endingTags'][] = array('name' => $match[6][0],
																'start_position' => $match[6][1],
																'end_position' => $match[7][1]);
			}
			// If there's still no opening tag for this tag, this closing tag will not match any opening tag,
			// so no need to register it
		}elseif (isset($match[1][0]) && $match[1][0] != ""){
			// It's an opening tag.
			$tag = array();
			$tag['name'] = $match[1][0];
			$tag['start_position'] = $match[1][1];
			$tag['end_position'] = $match[5][1];
			
			// Only one of these will ever match
			if ($match[4][1] > -1){
				// multiple parameters
				$tag['parameters'] = parseInnerParameters($match[4][0]);
			}elseif ($match[3][1] > -1){
				// 1 parameter bounded by the end of the start tag
				$tag['parameters'] = $match[3][0];
			}elseif ($match[2][1] > -1){
				// 1 parameter bounded by quotes
				// Replace currently needed due to the way this works.
				$tag['parameters'] = str_replace('\"', '&quot;', $match[2][0]);
			}
			
			// $orderedTags[] = array(	'name' => $match[1][0],
									// 'type' => 'opening_tag');
			$tagsKind[$tag['name']]['startingTags'][] = $tag;
		
		}
	}
	
	// var_dump($orderedTags);
	// echo "\n\n\n";
	// var_dump($endTags);
	// echo "\n\n\n";
	// var_dump($tagsKind);

	
	$tagsTree;
	
	$tagsKinds = $tagsKind;
	
	$BBCodeTree = array();
	
	$BBCodeTagMatch = array();
		
	foreach ($tagsKind as $BBCodeName => $data){
		
		echo "\n\n\n";
		
		while ($data['startingTags'] != array() && $data['endingTags'] != array()){
			// There's, at least, one possible
			
			// Got a closing tag!
			$endingTag = array_shift($data['endingTags']);
			
			reset($data['startingTags']);
			
			// Find an appropriate opening tag
			while(	next($data['startingTags']) !== false &&
					current($data['startingTags'])['end_position'] < $endingTag['start_position']);
			
			// The test showed that the next element is beyond what I'm looking for, so the previous is the one I want
			// Notice: 	This assumes that the previous step went as expected.
			// 			If there's no opening tag before a close tag, that close tag is not matched.
			prev($data['startingTags']);
			
			
			if(current($data['startingTags']) === false){
				// If I go beyond the top limits of the array. The only way to get back is by using end(), prev() will not work.
				end($data['startingTags']);
			}
			
			if(current($data['startingTags'])['end_position'] < $endingTag['start_position']){
								
				// K'ay, this is a match for that closing tag
				$BBCodeTagMatch[$BBCodeName][] = array(
												'start_tag' => current($data['startingTags']),
												'end_tag' => $endingTag
											);
				
				unset($data['startingTags'][key($data['startingTags'])]);
				
			}else{
				// Oh dear... no match for this closing tag...
				// Malformed BBcode... I don't care, I'll see what I can do with the rest, anyway
				// continue;
			}
		}
	}
	
	echo "\n\n\n";
	var_dump($BBCodeTagMatch);

	
	?>
</body>
</html>
