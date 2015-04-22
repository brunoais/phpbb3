<?php
/**
*
* This file is part of the phpBB Forum Software package.
*
* @copyright (c) phpBB Limited <https://www.phpbb.com>
* @license GNU General Public License, version 2 (GPL-2.0)
*
* For full copyright and license information, please see
* the docs/CREDITS.txt file.
*
*/

namespace phpbb\bbcode;


class text_formatter_data_access extends phpbb\textformatter\data_access
{
	/**
	* @var string Name of the BBCodes table
	*/
	protected $bbcodes_table;

	/**
	* @var \phpbb\db\driver\driver_interface
	*/
	protected $db;

	/**
	* @var string Name of the smilies table
	*/
	protected $smilies_table;

	/**
	* @var string Name of the styles table
	*/
	protected $styles_table;

	/**
	* @var string Path to the styles dir
	*/
	protected $styles_path;

	/**
	* @var string Name of the words table
	*/
	protected $words_table;

	/**
	* Constructor
	*
	* @param \phpbb\db\driver\driver_interface $db Database connection
	* @param string $bbcodes_table Name of the BBCodes table
	* @param string $smilies_table Name of the smilies table
	* @param string $styles_table  Name of the styles table
	* @param string $words_table   Name of the words table
	* @param string $styles_path   Path to the styles dir
	*/
	public function __construct(\phpbb\db\driver\driver_interface $db, $bbcodes_table, $smilies_table, $styles_table, $words_table, $styles_path)
	{
		$this->db = $db;

		$this->bbcodes_table = $bbcodes_table;
		$this->smilies_table = $smilies_table;
		$this->styles_table  = $styles_table;
		$this->words_table   = $words_table;

		$this->styles_path = $styles_path;
	}

	/**
	* Return the list of custom BBCodes
	*
	* @return array
	*/
	public function get_bbcodes()
	{
		$sql = 'SELECT bbcode_match, bbcode_tpl FROM ' . $this->bbcodes_table;
		$result = $this->db->sql_query($sql);
		$rows = $this->db->sql_fetchrowset($result);
		$this->db->sql_freeresult($result);

		return $rows;
	}

}
