<?php
	
	require "includes/bbcode/phpbb_bold_parser.php";
	require "includes/bbcode/phpbb_italic_parser.php";
	require "includes/bbcode/phpbb_underline_parser.php";
	require "includes/bbcode/phpbb_list_parser.php";
	
	require "includes/bbcode/phpbb_bbcode_parser.php";
	
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
	
	// $string = '
	// [b child="0"]
		// [b child="0,0"] 
		// boldy!
		// [/b]
		// [u child="0,1"]
			// [b child="0,1,0"] 
				// [u child="0,1,0,0"] 
					// underlined bold
				// [/u]
			// [/b]
		// [/u]
	// [/b]
	// [b child="1"]
	// another bold
		// [b child="1,0"]
			// [u child="1,0,0"] 
			// another underlined bold
			// [/u]
		// [/b]
		// [u child="1,1"]
			// [b child="1,1,0"] 
			// [/b]
		// [/u]
	// [/b]';
	
	$string = '';
	
	// This is built using the result of the DB query.
	$BBCode_tags = array(
				'b' => array(
							'flags'		=> 0,
							'callback'	=> new phpbb_bold_parser()
							),
				'i' => array(
							'flags'		=> 0,
							'callback'	=> new phpbb_italic_parser()
							),
				'u' => array(
							'flags'		=> 0,
							'callback'	=> new phpbb_underline_parser()
							),
				);




$start = microtime(true);
$parser = new BBCodeParser($string, $BBCode_tags);

$finalString = $parser->parse();

$end = microtime(true);

var_dump($string, $finalString);
echo "\n\n";
var_dump($end - $start);
				