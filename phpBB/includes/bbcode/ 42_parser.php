<?php

class phpbb_bbcode_¸42_parser{
	
		public function after_step1(&$string, &$tags_kind){
		
		// if(isset($tags_kind['*']['ending_tags']))
		// {
			// // I'll assume that the user has taken care of closing them all
			// return;
		// }
		// return;
		
		foreach($tags_kind['list']['starting_tags'] AS $starting_places)
		{
			$lists_start_pos[$starting_places['end_position']] = $starting_places['end_position'];
		}
		foreach($tags_kind['list']['ending_tags'] AS $starting_places)
		{
			$lists_end_pos[$starting_places['start_position']] = $starting_places['start_position'];
			$lists_end_end_pos[$starting_places['end_position']] = $starting_places['end_position'];
		}
		
		foreach($tags_kind['*']['starting_tags'] AS $starting_places)
		{
			$list_items_start_pos[$starting_places['start_position']] = $starting_places['start_position'];
		}
		
		if(isset($tags_kind['*']['ending_tags']))
		{
			foreach($tags_kind['*']['ending_tags'] AS $starting_places)
			{
				$list_items_end_pos[$starting_places['end_position']] = $starting_places['end_position'];
			}
		}
		else
		{
			$list_items_end_pos = array();
		}
		reset($lists_start_pos);
		
		reset($list_items_start_pos);
		reset($list_items_end_pos);
		
		
		foreach ($lists_end_pos AS $list_end_pos)
		{
			$i;
			for($i = $list_end_pos - 2; $string[$i] === "\t" || $string[$i] === ' ' ; $i--);
			
			if ($string[$i] === "\n")
			{
				$position = $i + 1;
			}
			else
			{
				$position = $list_end_pos - 1;
			}
			$tags_kind['*']['ending_tags'][$position] = array( 
												'name' => '*',
												'start_position' => $position,
												'end_position' => $position - 1,
											);
		}
		
		reset($lists_end_pos);
		$list_nesting = 0;
		$has_more_opens = true;
		$has_more_closes = $list_items_end_pos !== array();
		
		foreach ($list_items_start_pos AS $list_item_start_pos)
		{
			if($has_more_opens && current($lists_start_pos) < $list_item_start_pos)
			{
				// var_dump("start", current($lists_start_pos), $list_item_start_pos);
				$list_nesting++;
				$has_more_opens = next($lists_start_pos) !== false;
				// I just opened one tag, so it is not supposed to close the previous list item.
				continue;
			}
			if(current($lists_end_end_pos) < $list_item_start_pos)
			{
				next($lists_end_end_pos);
				$list_nesting = $list_nesting === 0 ? $list_nesting : $list_nesting - 1;
				// continue;
			}
			
			if($has_more_closes && current($list_items_end_pos) < $list_item_start_pos)
			{
				$has_more_closes = next($list_items_end_pos) !== false;
				continue;
			}
			
			if($list_nesting === 0)
			{
				// Not inside a list, the [*] will be invalid, anyway
				continue;
			}
			
			$i;
			for($i = $list_item_start_pos - 2; $string[$i] === "\t" || $string[$i] === ' ' ; $i--);
			if ($string[$i] === "\n")
			{
				$position = $i + 1;
			}
			else
			{
				$position = $list_item_start_pos;
			}
			$tags_kind['*']['ending_tags'][$position] = array( 
												'name' => '*',
												'start_position' => $position,
												'end_position' => $position - 1 ,
											);
		}
		
		ksort($tags_kind['*']['ending_tags']);
		
		// var_dump($tags_kind['*']);
		// exit;
		
		// var_dump($tags_kind['list']);
		echo "\n\n";
		// var_dump($lists_start_pos, $lists_end_pos);
		// exit;
		
		return;
		// Just keep this, for now.
		
		// var_dump(isset($tags_kind['*']['ending_tags']));
		
		$previous_tag = reset($tags_kind['*']['starting_tags']);
		$previous_starting = $previous_tag['end_position'];
		
		$current_start_tag;
		while($current_start_tag = next($tags_kind['*']['starting_tags'])){
			// echo substr($string, $previous_starting, $current_start_tag['start_position'] - $previous_starting - 1 );
			
			preg_match('%\r?\n[\t ]*%',
						substr($string, $previous_starting, $current_start_tag['start_position'] - $previous_starting - 1 ),
						$match_result,
						PREG_OFFSET_CAPTURE);
			var_dump($match_result[0][1]);
			var_dump(substr($string, $previous_starting + $match_result[0][1], $current_start_tag['start_position'] - ($previous_starting + $match_result[0][1]) - 1 ));
			
			$tags_kind['*']['ending_tags'][] = array( 
													'name' => '*',
													'start_position' => $current_start_tag['start_position'] - 1,
													'end_position' => $current_start_tag['start_position'] - 1,
												);

			$previous_starting = $current_start_tag['end_position'];
			// exit;
		}
		
		foreach($tags_kind['list']['ending_tags'] AS $list_ending_tag)
		{
			// echo substr($string, $previous_starting, $list_ending_tag['start_position'] - $previous_starting - 1 );
			$tags_kind['*']['ending_tags'][] = array( 
														'name' => '*',
														'start_position' => $list_ending_tag['start_position'] - 1,
														'end_position' => $list_ending_tag['start_position'] - 1,
													);
		}
		exit;
	}
	
	
	
	public function parse($name, $parameters, $inside){
		// echo $inside;
		return "<li>$inside</li>";
	}
}
