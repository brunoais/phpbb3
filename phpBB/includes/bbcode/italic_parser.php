<?php

class phpbb_bbcode_italic_parser{
	public function parse($name, $parameters, $inside){
		return "<i>$inside</i>";
	}
}
