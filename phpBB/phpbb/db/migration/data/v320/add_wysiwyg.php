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

namespace phpbb\db\migration\data\v320;

class add_wysiwyg extends \phpbb\db\migration\migration
{
	public static function depends_on()
	{
		// return array('\phpbb\db\migration\data\v310\config_db_text');
		return array();
	}
	
	
	public function update_schema()
	{
		return array(
			'add_columns'		=> array(
				$this->table_prefix . 'users'	=> array(
					'COLUMNS'			=> array(
						'user_wysiwyg_default_mode'		=> array('TINT:2', 0),
						'user_wysiwyg_buttons_mode'	=> array('TINT:2', 1),
					),
				),
			),
		);
	}

	public function revert_schema()
	{
		return array(
			'drop_columns'		=> array(
				$this->table_prefix . 'users'	=> array(
						'user_wysiwyg_default_mode',
						'user_wysiwyg_buttons_mode',
					),
				),
		);
	}

	public function update_data()
	{
		return array(
			//ACP
			array('config.add', array('wysiwyg_type', 'phpbb\bbcode\convert_editor\sce')),
			array('config.add', array('wysiwyg_default_default_mode', 0)),
			array('config.add', array('wysiwyg_default_buttons_mode', 1)),

			//UCP
			array('module.add', array(
				'ucp_prefs',
				0,
				array(
					'personal', 'post', 'wysiwyg', 'view',
				),
			)),
		);
	}

}