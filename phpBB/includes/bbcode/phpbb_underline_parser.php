<?php

class phpbb_underline_parser{
	public function parse($name, $parameters, $inside){
		return "<u>$inside</u>";
	}
}