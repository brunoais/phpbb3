<?php

class ExampleParser{
	/**
	 * This method's job is to translate the input of the user into.
	 * 
	 * Note that the string $inside may contain any utf-8 characters that were not parsed incuding HTML contents translated from other tags. For example, If you are a parser for the [b] tag (and allow [b] inside [b]):
	 * For an input like this:
	 * [b]some[i]italic[b]bold[/b][/i]contents[/b]
	 * you get the output here twice. The first time you get:
	 * bold
	 * the second time you get
	 * some<i>italic<b>bold</b></i>contents
	 * (this assumes that the BBCode transformers are working properly)
	 * 
	 * For the $parameters parameter, there are 3 cases if the information that is received. That depends on the BBCode that was written.
	 * 
	 * @param  string $name Shows the name of the BBCode tag. It's just a convenient parameter if you want to use the same method (and same object) to parse different BBCodes, for example.
	 * @param  array|string|null $parameters A key pair with the parameters that the user wrote inside the tag. A string with a single parameter or null if no parameters provided
	 * @param  string $inside The string that the user wrote between the BBCode tags
	 * @param integer $deepness Shows the level of how deep this is in the tree
	 * @return  string The result of parsing this text with these parameters.
	 */
	public function parse($name, $parameters, $inside, $deepness){
		$output = "<example";
	
		foreach($parameters AS $paramName => $value){
			$output .= " $paramName='$value'";
		}
		
		$output .= ">";
		$output .= "$inside</example>";
		
		return $output;
		
	}
}
