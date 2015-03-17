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

namespace phpbb\bbcode\convert_editor;

/**
* 
*/
class sce
{

	/**
	 * Config object
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * Database connection
	 * @var \phpbb\db\driver\driver_interface
	 */
	protected $db;

	/**
	* Event dispatcher object
	* @var \phpbb\event\dispatcher_interface
	*/
	protected $phpbb_dispatcher;


	/**
	 * Constructor
	 * 
	 *
	 * @param string|bool $error Any error that occurs is passed on through this reference variable otherwise false
	 * @param string $phpbb_root_path Relative path to phpBB root
	 * @param string $phpEx PHP file extension
	 * @param \phpbb\auth\auth $auth Auth object
	 * @param \phpbb\config\config $config Config object
	 * @param \phpbb\db\driver\driver_interface Database object
	 */
	public function __construct(&$error, $phpbb_root_path, $phpEx, \phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\event\dispatcher_interface $phpbb_dispatcher)
	{
		$this->config = $config;
		$this->db = $db;
		$this->phpbb_dispatcher = $phpbb_dispatcher;


		$error = false;
	}
	
	/**
	 * This converts the BBCode in the format that is in the database into a javascript output
	 * which is the instructions for the WYSIWYG editor on how to display all the BBCode and which BBCode
	 * to show up on the BBCode button list
	 *
	 *
	 *
	 */
	public function convert_bbcode_to_editor()
	{
		
		
	}

	/**
	 * This converts the smilies in the format that is in the database into a javascript output
	 * which is the instructions for the WYSIWYG editor on how to display all the smilies and which smilies
	 * to show up on the smilies list
	 *
	 *
	 *
	 */
	public function convert_smilies_to_editor()
	{
		
		
	}
	
}
