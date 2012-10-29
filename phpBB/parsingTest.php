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
	$string = '[/abc][abc=aij] [/ubc][ubc][ab]a [abc="uij]f\"fu"] [/abc][/ubc][abc uuo="wiu\"" ] [/ubc][/abc][/ab]b [/abc] c';
	
	$BBCode_tags = array('abc', 'ubc');
	
	
// Not so good attempt to try to find the BBCode tags in the string
// DISCARDED: There's no guarantee that it will follow the tree-like way of the BBCode.

	preg_match_all('%\[abc.*?\[/abc\]%', $string, $string_match);

	// var_dump($string, $string_match);
	?>
<?php
	// Trying to find all the BBCode tags for the BBCode [abc].
	// The idea seems to be viable.
	$posInit = array();
	$posInit[] = strpos($string, '[abc');
	$posInit[] = strpos($string, '[abc', $posInit[0] + 1);
	// $posInit[] = strpos($string, '[abc', $posInit[1] + 1);

	// var_dump($posInit);
	
	// After knowing where the start tags are, try to find the closing tags for each tag
	// Seems to be working
	$posEnd = array();
	$posEnd[] = strpos($string, '[/abc]', $posInit[1] + 1);
	$posEnd[] = strpos($string, '[/abc]', $posEnd[0] + 1);
	// $posEnd[] = strpos($string, '[/abc]', $posInit[1] + 1);
	
	
	// var_dump(substr($string, $posInit[0], $posEnd[1] - $posInit[0] + strlen('[/abc]')));
	// var_dump(substr($string, $posInit[1], $posEnd[0] - $posInit[1] + strlen('[/abc]')));
	
	?>
<?php
	
	// Find and register all what's inside the tag itself (Attempt1)
	
	// preg_match('%\[abc(?:=(?:"([^"]*(?:\\\\.[^"\\\\]*)*)"|([^"\\\\][^]]+)))?\]%', $string, $parametersFound);
	
	// var_dump($parametersFound);
	
	?>
<?php
	
	// Find and register all what's inside the tag itself (Attempt2)
	
	$subject = substr($string, $posInit[1], $posEnd[0] - $posInit[1] + strlen('[/abc]'));
	
	$callback_replace = preg_replace_callback('%\[([A-z-][A-z0-9-]*)' .
	'(?:' . 
	'(?:=(?:'.
	'"([^"]*(?:\\\\.[^"\\\\]*)*)"'.
	'|'.
	'([^"\\\\][^]]+)))'.
	'|'.
	'((?:\s+(?:[A-z][A-z0-9]+)=(?:"(?:[^"\\\\]*(?:\\\\.[^"\\\\]*)*)"))+)\s*'.
	')?'.
	'\]%',
			function ($matches){
				$returner = '['. $matches[1] .' ';
				
				if(isset($matches[2])){
					$returner .= 'default="' . str_replace('\"', '&quot;',$matches[2]) . '"';
				}elseif(isset($matches[3])){
					$returner .= 'default="' . $matches[3] . '"';
				}elseif(isset($matches[4])){
					$returner .= $matches[4];
				}
				$returner .= '"]';
				// var_dump($returner);
				return $returner;
			}, $string);
	
	
	// var_dump($callback_replace);
	
	?>	
	Different try
	reason:
	The previous one was not giving the position of the matches, 2 scans were needed. This should solve that.
	
<?php
	
	$regexedBBCode = implode('|', $BBCode_tags);
	
	
	preg_match_all(
	'%'.
	'\[('.$regexedBBCode.')' .
	'(?:' . 
	'(?:=(?:'.
	'"([^"]*(?:\\\\.[^"\\\\]*)*)"'.
	'|'.
	'([^"\\\\][^]]+)))'.
	'|'.
	'((?:\s+(?:[A-z][A-z0-9]+)=(?:"(?:[^"\\\\]*(?:\\\\.[^"\\\\]*)*)"))+)\s*'.
	')?'.
	'\]'.
	'|'.
	'\[/('.$regexedBBCode.')\]'.
	'%',$string, $matched,  PREG_SET_ORDER  | PREG_OFFSET_CAPTURE);
	
	// var_dump($matched);
	
	$startingTags = array();
	$endTags = array();
	
	$theTags = array();
	
	foreach($matched AS $match){
		if (isset($match[1][0]) && $match[1][0] != ""){
			$tag = array();
			$tag['name'] = $match[1][0];
			$tag['position'] = $match[1][1];
			// var_dump($match[1][0] . " at position: " . $match[1][1]);
			
			// Only one of these will ever match
			
			if (isset($match[2][0])){
				// 1 parameter bounded by quotes
				$tag['parameters'] = str_replace('\"', '&quot;', $match[2][0]);
			}elseif (isset($match[3][0])){
				// 1 parameter bounded by the end of the start tag
				$tag['parameters'] = $match[3][0];
			}elseif (isset($match[4][0])){
				// multiple parameters
				$tag['parameters'] = $match[4][0];
			}
			
			$startingTags[$tag['name']][] = $tag;
			$theTags[$tag['name']]['startingTags'][] = $tag;
		
		}elseif (isset($match[5][0])){
			$endTags[$match[5][0]][] = $match[5];
			if(isset($theTags[$match[5][0]]['startingTags'])){
				$theTags[$match[5][0]]['endingTags'][] = array('name' => $match[5][0], 'position' => $match[5][1]);
			}
		}
	}
	
	// var_dump($startingTags);
	// echo "\n\n\n";
	// var_dump($endTags);
	// echo "\n\n\n";
	var_dump($theTags);
	?>
</body>
</html>
