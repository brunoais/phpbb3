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
		
		$user = new phpbb_mock_user;
		$request = new phpbb_mock_request;
		
	}
	
	public function test_tags()
	{
		return array(
			array(
				'bold',
				'[b]bold[/b]',
				'<span style="font-weight: bold">bold</span>'
			),
			array(
				'italic',
				'[i]italic[/i]',
				'<span style="font-style: italic">italic</span>'
			),
			array(
				'underline',
				'[u]underlined[/u]',
				'<span style="text-decoration: underline">underlined</span>'
			),
			array(
				'quote (uncited)',
				'[quote]quoted[/quote]',
				'<blockquote class="uncited"><div>quoted</div></blockquote>'
			),
			array(
				'quote (cited)',
				'[quote="hi"]quoted by[/quote]',
				'<blockquote><div><cite>hi wrote:</cite>quoted by</div></blockquote>'
			),
			array(
				'code',
				'[code]unparsed[/code]',
				'<dl class="codebox"><dt>Code: <a href="#" onclick="selectCode(this); return false;">Select all</a></dt><dd><code>unparsed</code></dd></dl>'
			),
			array(
				'list',
				'[list][*]list[/list]',
				'<ul><li>list</li></ul>'
			),
			array(
				'list with known parameter',
				'[list=1][*]list1[/list]',
				'<ol style="list-style-type: decimal"><li>list1</li></ol>'
			),
			array(
				'image',
				'[img]http://area51.phpbb.com/phpBB/images/smilies/icon_e_biggrin.gif[/img]',
				'<img src="http://area51.phpbb.com/phpBB/images/smilies/icon_e_biggrin.gif" alt="Image">'
			),
			array(
				'url',
				'[url]http://link.document.com/allok[/url]',
				'<a href="http://link.document.com/allok" class="postlink">http://link.document.com/allok</a>'
			),
			array(
				'url with text',
				'[url=http://link.document.com/allok]urlok[/url]',
				'<a href="http://link.document.com/allok" class="postlink">urlok</a>'
			),
			array(
				'color',
				'[color=#FF0000]red[/color]',
				'<span style="color: #FF0000">red</span>'
			),
		);
	}
	
	
	/**
	* @dataProvider test_tags
	*/
	public function test_parse_string($name, $input, $expected){
		$parser_part1 = new parse_message($input);
		// Test only the BBCode parsing
		$parser_part1->parse(true, false, false);
		
		$parser_part2 = new bbcode($parser_part1->bbcode_bitfield);
		$output = $parser_part1->message;
		
		$parser_part2->bbcode_second_pass($output, $parser_part1->bbcode_uid);
		
		$this->assertEquals($expected, $output, $name);
	}



}
