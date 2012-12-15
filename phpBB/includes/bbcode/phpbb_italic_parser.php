<?php

class phpbb_italic_parser{
	public function parse($name, $parameters, $inside){
		return "<i>$inside</i>";
	}
}
