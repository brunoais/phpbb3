<?php

class phpbb_bold_parser{
	public function parse($name, $parameters, $inside){
		return "<b>$inside</b>";
	}
}
