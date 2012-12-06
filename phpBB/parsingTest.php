<?php
	
	header('content-type: text/plain');

	abstract class ExampleParser{
		/**
		 * This method's job is to translate the input of the user into.
		 * 
		 * Note that the string $inside may contain any utf-8 characters that were not parsed incuding HTML contents translated from o ther tags. For example, If you are a parser for the [b] tag (and allow [b] inside [b]):
		 * For an input like this:
		 * [b]some[i]italic[b]bold[/b][/i]contents[/b]
		 * you get the output here twice. The first time you get:
		 * bold
		 * the second time you get
		 * some<i>italic<b>bold</b></i>contents
		 * (this assumes that the BBCode transformers are working properly)
		 * 
		 * @param  String $name Shows the name of the BBCode tag. It's just a convenient parameter if you want to use the same method (and same object) to parse different BBCodes, for example.
		 * @param  array $parameters A key pair with the parameters that the user wrote inside the tag
		 * @param  string $inside The string that the user wrote between the BBCode tags
		 * @param integer $deepness Shows the level of how deep this is in the tree
		 * @return  string The result of parsing this text with these parameters.
		 */
		public function parse($name, $paramenters, $inside, $deepness){
			$output = "<example";
		
			foreach($paramenters AS $paramName => $value){
				$output .= " $paramName='$value'";
			}
			
			$output .= ">";
			$output .= "$inside</example>";
			
			return $output;
			
		}
	}

class BoldParser{
	public function parse($name, $paramenters, $inside){
		return "<b>$inside</b>";
	}
}
class ItalicParser{
	public function parse($name, $paramenters, $inside){
		return "<i>$inside</i>";
	}
}
class UnderlineParser{
	public function parse($name, $paramenters, $inside){
		return "<u>$inside</u>";
	}
}
	
	
	
	
	// $string =
// '[/abc][abc=aij] [/ubc][ubc][ab]a [abc=badOverride][abc="I got a \"child\"!"][ubc rightBoss="true"] YAY[/ubc] [/abc][/ubc][/abc][abc param1="it is \"val1\"" param2="it is \"val2\"" ] [/ubc][/abc][/ab]b [abc][/abc] c[/ubc][ubc][/ubc][/ubc][/ubc][ubc]';
	
	// $string = '
	// [abc child="0"]
		// [abc child="0,0"] 
		// [/abc]
		// [ubc child="0,1"]
			// [abc child="0,1,0"] 
				// [ubc child="0,1,0,0"] 
				// [/ubc]
			// [/abc]
		// [/ubc]
	// [/abc]
	// [abc child="1"]
		// [abc child="1,0"]
			// [ubc child="1,0,0"] 
			// [/ubc]
		// [/abc]
		// [ubc child="1,1"]
			// [abc child="1,1,0"] 
			// [/abc]
		// [/ubc]
	// [/abc]';
	
	$string = '
	[b child="0"]
		[b child="0,0"] 
		boldy!
		[/b]
		[u child="0,1"]
			[b child="0,1,0"] 
				[u child="0,1,0,0"] 
					underlined bold
				[/u]
			[/b]
		[/u]
	[/b]
	[b child="1"]
	another bold
		[b child="1,0"]
			[u child="1,0,0"] 
			another underlined bold
			[/u]
		[/b]
		[u child="1,1"]
			[b child="1,1,0"] 
			[/b]
		[/u]
	[/b]';
	
//	$string = 'adasdsd[a]3231[b]dawdaw[c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][m][n][o][p][q][r][s][t][u][v][w][x][y][z][a][b][c][d][e][f][g][h][i][j][k][l][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a][/z][/y][/x][/w][/v][/u][/t][/s][/r][/q][/p][/o][/n][/m][/l][/k][/j][/i][/h][/g][/f][/e][/d][/c][/b][/a';
	
	
	// $BBCode_tags = array('abc', 'cbc', 'dbc');
	// $BBCode_tags = array('abc', 'ubc');
	// $BBCode_tags = array('b');
	// $BBCode_tags = array("a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z");
	// $BBCode_tags = array('ubc', 'abc');
	// $BBCode_tags = array('abc');
	// $BBCode_tags = array('ubc');
	
	
	$BBCode_tags = array(
				'b' => array(
							'flags'		=> 0,
							'callback'	=> new UnderlineParser()
							),
				'i' => array(
							'flags'		=> 0,
							'callback'	=> new ItalicParser()
							),
				'u' => array(
							'flags'		=> 0,
							'callback'	=> new UnderlineParser()
							),
				);

	// The list of BBCodes for the regex matcher
	$regexedBBCode = implode('|', array_keys($BBCode_tags));
	
	$start = microtime(true);
	
	// Step 1: Find opening and closing tags in the text.
	
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
			if (isset($tagsKind[$match[6][0]]['startingTags'])){
				// If there's already an opening tag
				// $orderedTags[] = array(	'name' => $match[6][0],
										// 'type' => 'closing_tag');
				$tagsKind[$match[6][0]]['endingTags'][] = array('name' => $match[6][0],
																'start_position' => $match[6][1] - 1,
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
	
	$BBCodeTagMatch = array();
	
	$BBCodeOrderedTagList = array();
	
	$middle1 = microtime(true);
	// Step 2: Pair opening and closing tags. 
		
	foreach ($tagsKind as $BBCodeName => &$data){
		
		// echo "\n\n\n";
		
		while ($data['startingTags'] != array() && current($data['endingTags']) != false){
			// There's, at least, one possible
			
			// Got a closing tag!
			$endingTag = current($data['endingTags']);
			
			reset($data['startingTags']);
			
			// Find an appropriate opening tag
			// while(	next($data['startingTags']) !== false &&
					// current($data['startingTags'])['end_position'] < $endingTag['start_position']);
			while(	next($data['startingTags']) !== false
					){
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
				// $BBCodeTagMatch[$BBCodeName][] = array(
												// 'start_tag' => $currentStartTag,
												// 'end_tag' => $endingTag
											// );
				
				$BBCodeOrderedTagList[$currentStartTag['end_position']] = 
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
	
	$middle2 = microtime(true);
	
	// echo "\n\n\n";
	// var_dump($BBCodeTagMatch);
	
	// echo "\n\n\n";
	ksort($BBCodeOrderedTagList);
	// var_dump($BBCodeOrderedTagList);
	
	// $BBCodeOrderedTagListBak = $BBCodeOrderedTagList;
	
	
	// Step 3: Build the tree of tags
	
	$BBCodeTree = array();
	
	$tagStack = array();
	
	// echo "\n\n\n";
	// var_dump($BBCodeOrderedTagList);
	
	
	// push the first element into the tree	
	$BBCodeTree[] = &$BBCodeOrderedTagList[key($BBCodeOrderedTagList)];
	// and also make it the first parent that will receive the child Nodes
	$currentParent = &$BBCodeOrderedTagList[key($BBCodeOrderedTagList)];
	
	// Get the next element of the list and start crackin'!
	next($BBCodeOrderedTagList);
	
	while(current($BBCodeOrderedTagList) !== false){
		// While we didn't check about all tags found
		
		// echo "\n";
		// var_dump("Currentparent", $currentParent['start_tag']['parameters']['child']);
		
		// Check if this tag is inside the current parent
		if (current($BBCodeOrderedTagList)['start_tag']['start_position'] <= $currentParent['end_tag']['end_position']){
			if (current($BBCodeOrderedTagList)['end_tag']['end_position'] <= $currentParent['end_tag']['end_position']){
				// Tag is inside this parent. So this tag is part of this parent's children
				
				// push the previous parent
				$tagStack[] = &$currentParent;
				
				// Only needed while debugging. For production porpuses (and for the sake of speed) this should be removed
				// if (!isset($currentParent['children'])){
					// // var_dump("newChild");
					// $currentParent['children'] = array();
				// }
				// var_dump("child", $BBCodeOrderedTagList[key($BBCodeOrderedTagList)]['start_tag']['parameters']['child']);
				// var_dump("pushInto", $currentParent['start_tag']['parameters']['child']);
				
				// Make this tag children of the current parent tag
				$currentParent['children'][] = &$BBCodeOrderedTagList[key($BBCodeOrderedTagList)];
				// echo "\n";
				// var_dump($currentParent);
				// Update the parent tag
				$currentParent = &$BBCodeOrderedTagList[key($BBCodeOrderedTagList)];
				
				// var_dump("newParent", $currentParent['start_tag']['parameters']['child']);
				
			}else{
				// Bad nesting. This tag is meant to dissapear from this world! Well, not really... Just read it as text.
				
				// var_dump('bad nesting ' . key($BBCodeOrderedTagList));
				// unset($BBCodeOrderedTagList[key($BBCodeOrderedTagList)]);
			}
			next($BBCodeOrderedTagList);
		}else /* if (current($BBCodeOrderedTagList)['start_tag']['start_position'] > $currentParent['end_tag']['end_position']) */{
			// Close previous tag here. There are no more children.
			
			// var_dump("closing", $currentParent['start_tag']['parameters']['child']);
			
			if (end($tagStack) === false){
				// var_dump("stackEmpty", $currentParent['start_tag']['parameters']['child']);
				
				// This tag belongs to the root, so it needs to be directly added to the tree's root
				
				$currentParent = &$BBCodeOrderedTagList[key($BBCodeOrderedTagList)];
				$BBCodeTree[] = &$BBCodeOrderedTagList[key($BBCodeOrderedTagList)];
				
				next($BBCodeOrderedTagList);
				// var_dump("nextVictim", $currentParent['start_tag']['parameters']['child']);
			}else{
				// Process the closing of the tag
				$currentParent = &$tagStack[key($tagStack)];
				// Pop from the stack
				unset($tagStack[key($tagStack)]);
				
				// var_dump("newParent", $currentParent['start_tag']['parameters']['child']);
				
				// Really! no next() here. 
			}
			
			// var_dump($currentParent);
		}
		
	}
	
	echo "\n\n\n";
	// var_dump($BBCodeTree);
	
	// Step 4: Filter out child nodes that are not allowed.
	
	
	
	
	// Step 5: (Is there a step5)?
	
	
	// Step 6: Build the tree with the current known nodes
	
	function joinContentsToElement(&$element){
		global $string;
		
		//assumes that if the tag does not have children, the key children is not set
		if(isset($element['children'])){
			// for each child
			// This assumes that the children are properly sorted by the ['start_tag']['start_position']
			
			// Well... needs a better name and... we cheat for the first iteration
			$previousChild['end_tag']['end_position'] = &$element['start_tag']['end_position'];
			
			foreach ($element['children'] as $childKey => &$child) {
				$element['text'][] = substr(
										$string,
										$previousChild['end_tag']['end_position'],
										$child['start_tag']['start_position'] -
											$previousChild['end_tag']['end_position'] - 1);
				joinContentsToElement($child);
				
				$previousChild = &$child;
			}
			// remmeber that by the spec, $child is still set with the last child of the array
			$element['text'][] = substr(
									$string,
									$child['end_tag']['end_position'],
									$element['end_tag']['start_position'] -
										$child['end_tag']['end_position'] - 1);
		}else{
			if($element['end_tag']['start_position'] -
										$element['start_tag']['end_position'] - 1 === 0){
				$element['text'][] = "";
			}else{
				$element['text'][] = substr(
										$string,
										$element['start_tag']['end_position'],
										$element['end_tag']['start_position'] -
											$element['start_tag']['end_position'] - 1);
			}
		}
		
	}
	
	foreach ($BBCodeTree as &$rootBBCode) {
		joinContentsToElement($rootBBCode);		
	}
	
	$end = microtime(true);
	
	echo "\n\n";
	// var_dump($BBCodeTree); 
	
	function parseBBCode($name, $paramenters, $inside){
		
		$output = "<$name";
		
		foreach($paramenters AS $paramName => $value){
			$output .= " $paramName='$value'";
		}
		
		$output .= ">";
		$output .= "$inside</$name>";
		
		return $output;
	}
	
	
	function replaceWithBBCode(&$element, $deepness){
		global $string, $BBCode_tags;
		
		$finalString = '';
		
		//assumes that if the tag does not have children, the key children is not set
		if(isset($element['children'])){
			// for each child
			// This assumes that the children are properly sorted by the ['start_tag']['start_position']
			
			// Well... needs a better name and... we cheat for the first iteration
			$previousChild['end_tag']['end_position'] = &$element['start_tag']['end_position'];
			
			foreach ($element['children'] as $childKey => &$child) {
				$finalString .= substr(
										$string,
										$previousChild['end_tag']['end_position'],
										$child['start_tag']['start_position'] -
											$previousChild['end_tag']['end_position'] - 1);
				
				$finalString .= replaceWithBBCode($child, $deepness + 1);
				
				$previousChild = &$child;
			}
			// remmeber that, by the spec, $child is still set with the last child of the array
			$finalString .= substr(
									$string,
									$child['end_tag']['end_position'],
									$element['end_tag']['start_position'] -
										$child['end_tag']['end_position'] - 1);
		}else{
			if($element['end_tag']['start_position'] -
										$element['start_tag']['end_position'] - 1 === 0){
				$finalString .= "";
			}else{
				$finalString .= substr(
										$string,
										$element['start_tag']['end_position'],
										$element['end_tag']['start_position'] -
											$element['start_tag']['end_position'] - 1);
			}
		}
		
		return $BBCode_tags[$element['start_tag']['name']]['callback']->parse(
							$element['start_tag']['name'],
							isset($element['start_tag']['parameters'])? 
								$element['start_tag']['parameters'] :
								array(),
							$finalString,
							$deepness);
		
	}
	
	$finalString = '';
	// Fake previousChild to give a kickstart
	$previousChild = array();
	$previousChild['end_tag']['end_position'] = 0;
	
	foreach ($BBCodeTree as &$rootBBCode) {
		$finalString .= substr(		$string,
									$previousChild['end_tag']['end_position'],
									$rootBBCode['start_tag']['start_position'] -
										$previousChild['end_tag']['end_position'] - 1);
										
		$finalString .= replaceWithBBCode($rootBBCode, 0);	
		$previousChild = &$rootBBCode;
	}

	$finalString .= substr(		$string,
								$previousChild['end_tag']['end_position'],
								strlen($string) -
									$previousChild['end_tag']['end_position']);

	var_dump($string, $finalString);

	var_dump($start, $middle1, $middle2, $end, "", $middle1 - $start, $end - $middle2, $middle2 - $middle1, "", $end - $start);

	?>
