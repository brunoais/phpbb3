<?php

define('IN_PHPBB', true);

require "includes/bbcode/bold_parser.php";
require "includes/bbcode/italic_parser.php";
require "includes/bbcode/underline_parser.php";
require "includes/bbcode/list_parser.php";
require "includes/bbcode/ 42_parser.php";

require "includes/bbcode/bbcode_parser.php";
	
	// $string =
// '[/abc][abc=aij] [/ubc][ubc][ab]a [abc=badOverride][abc="I got a \"child\"!"][ubc rightBoss="true"] YAY[/ubc] [/abc][/ubc][/abc][abc param1="it is \"val1\"" param2="it is \"val2\"" ] [/ubc][/abc][/ab]b [abc][/abc] c[/ubc][ubc][/ubc][/ubc][/ubc][ubc]';
	
	$string = '
	[abc child="0"]
		[abc child="0,0"] 
		[/abc]
		[ubc child="0,1"]
			[abc child="0,1,0"] 
				[ubc child="0,1,0,0"] 
				[/ubc]
			[/abc]
		[/ubc]
	[/abc]
	[abc child="1"]
		[abc child="1,0"]
			[ubc child="1,0,0"] 
			[/ubc]
		[/abc]
		[ubc child="1,1"]
			[abc child="1,1,0"] 
			[/abc]
		[/ubc]
	[/abc]';
	
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
	
	$string = '
[list]
WTFelem
[*]elem1
[*][list][*]elem2 [/list]
[*]elem3
[/list]
	[b]ending[/b]';
$string = '
[list]
	[*]elem1
	[*]
	[list]
		[*][b]elem2[/b]
		[*]
		[list]
			[*]yeah
			[*][*]yep
		[/list]
	[/list]
	[*]elem3
[/list]
	[b]ending[/b]';
	
	// $string = '
	// [b child="0"]
		// [u child="0,1"]
			// [b child="0,1,0"] 
			// [/b]
		// [/u]
	// [/b]
	// [b child="1"]
	// another bold
		// [b child="1,0"]
		// [/b]
	// [/b]';
	
	// $string = '';
	
	// This is built using the result of the DB query.
	$bbcode_tags = array(
				'b' => array(
							'flags'		=> 0,
							'callback'	=> new phpbb_bbcode_bold_parser()
							),
				'i' => array(
							'flags'		=> 0,
							'callback'	=> new phpbb_bbcode_italic_parser()
							),
				'u' => array(
							'flags'		=> 0,
							'callback'	=> new phpbb_bbcode_underline_parser()
							),
				'list' => array(
							'flags'		=> 0,
							'callback'	=> new phpbb_bbcode_list_parser()
							),
				'*' => array(
							'flags'		=> 0,
							'callback'	=> new phpbb_bbcode_¸42_parser()
							),
				);




$start = microtime(true);
$parser = new phpbb_bbcode_bbcode_parser($string, $bbcode_tags);

// $final_string = $parser->parse();
$final_string = $parser->parse_phase1();

$end = microtime(true);

// var_dump($string, $final_string);
// echo "<br><br>";
var_dump($end - $start);

$start2 = microtime(true);
$final_string = $parser->parse_phase2();
$end2 = microtime(true);
// echo "<br><br>";
var_dump($final_string);
// echo "<br><br>";
var_dump($end2 - $start);
// var_dump(0.00005);
