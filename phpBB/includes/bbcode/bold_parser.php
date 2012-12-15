<?php

class phpbb_bbcode_bold_parser{
	public function parse($name, $parameters, $inside){
		return "<b>$inside</b>";
	}
}
