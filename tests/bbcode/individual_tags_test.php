<?php
/**
*
* @package testing
* @version $Id$
* @copyright (c) 2008 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

require_once __DIR__ . '/../../phpBB/includes/request/request.php';
require_once __DIR__ . '/../../phpBB/includes/user.php';
require_once __DIR__ . '/../../phpBB/includes/bbcode.php';
require_once __DIR__ . '/../../phpBB/includes/functions.php';
require_once __DIR__ . '/../../phpBB/includes/utf/utf_tools.php';
require_once __DIR__ . '/../../phpBB/includes/functions_content.php';
require_once __DIR__ . '/../../phpBB/includes/message_parser.php';

class phpbb_bbcode_parser_test extends phpbb_database_test_case
{
	
	private $db;
	private $config;
	private $phpbb_extension_manager;
	
	public function getDataSet()
	{
		return $this->createXMLDataSet(dirname(__FILE__).'/fixtures/config.xml');
	}
	
	public function setUp()
	{
		global $db, $config, $request, $user, $phpbb_extension_manager, $table_prefix;
		
		$db = $this->db = $this->new_dbal();
		$config = $this->config = new phpbb_config(array('rand_seed' => '', 'rand_seed_last_update' => '0'));
		set_config(null, null, null, $this->config);
		
		$phpbb_extension_manager = new phpbb_extension_manager($db, $config, $table_prefix . 'extensions', __DIR__ . '/../../phpBB/');
		
		$request = new phpbb_request();
		
		$user = new phpbb_user();
		
	}
	
	private function parse_string($input){
		$parser_part1 = new parse_message($input);
		// Test only the BBCode parsing
		$parser_part1->parse(true, false, false);
		
		$parser_part2 = new bbcode($parser_part1->bbcode_bitfield);
		$output = $parser_part1->message;
		
		$parser_part2->bbcode_second_pass($output, $parser_part1->bbcode_uid);
		
		return $output;
	}
	
	/**
	 * Test if the tag shipped with phpBB is parsing as it should
	 * (no parsing rules are checked here, just if the replacement (HTML) string is as it is supposed to)
	 * 
	 */
	public function test_unnested_default_tags_old()
	{
		// $this->markTestIncomplete('New bbcode parser has not been backported from feature/ascraeus-experiment yet.');
		
		$input_message=
		'[b]bold[/b]' .
		'[i]italic[/i]' .
		'[u]underlined[/u]' .
		'[quote]quoted[/quote]' .
		'[quote="hi"]quoted by[/quote]' .
		'[code]unparsed[/code]' .
		'[list][/list]' .
		'[list=1][/list]' .
		'[img]http://area51.phpbb.com/phpBB/images/smilies/icon_e_biggrin.gif[/img]' .
		'[url]http://link.document.com/allok[/url]' .
		'[url=http://link.document.com/allok]urlok[/url]' .
		'[color=#FF0000]red[/color]';
		
		$result = parse_string($input_message);
		
		$expected = 
			// [b]
			'<span style="font-weight: bold">bold</span>' .
			// [i]
			'<span style="font-style: italic">italic</span>' .
			// [u]
			'<span style="text-decoration: underline">underlined</span>' .
			// [quote]
			'<blockquote class="uncited"><div>quoted</div></blockquote>' .
			// [quote="name"]
			'<blockquote><div><cite>hi wrote:</cite>quoted by</div></blockquote>' .
			// [code]
			'<dl class="codebox"><dt>Code: <a href="#" onclick="selectCode(this); return false;">Select all</a></dt><dd><code>unparsed</code></dd></dl>' .
			// [list]
			'<ul></ul>' .
			// [list=1]
			'<ol style="list-style-type: decimal"></ol>' .
			// [img]
			'<img src="http://area51.phpbb.com/phpBB/images/smilies/icon_e_biggrin.gif" alt="Image">' .
			// [url]
			'<a href="http://link.document.com/allok" class="postlink">http://link.document.com/allok</a>' .
			// [url=]
			'<a href="http://link.document.com/allok" class="postlink">urlok</a>' .
			// [color=]
			'<span style="color: #FF0000">red</span>';

		$this->assertEquals($expected, $result, '$expected');
	}

}
