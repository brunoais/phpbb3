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

require_once dirname(__FILE__) . '/../../phpBB/includes/functions.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/functions_content.php';
require_once dirname(__FILE__) . '/../../phpBB/includes/utf/utf_tools.php';

class phpbb_profilefield_type_string_test extends phpbb_test_case
{
	protected $cp;
	protected $field_options;

	/**
	* Sets up basic test objects
	*
	* @access public
	* @return null
	*/
	public function setUp()
	{
		global $request, $user, $cache;

		$user = $this->getMock('\phpbb\user');
		$cache = new phpbb_mock_cache;
		$user->expects($this->any())
			->method('lang')
			->will($this->returnCallback(array($this, 'return_callback_implode')));

		$request = $this->getMock('\phpbb\request\request');
		$template = $this->getMock('\phpbb\template\template');

		$this->cp = new \phpbb\profilefields\type\type_string(
			$request,
			$template,
			$user
		);

		$this->field_options = array(
			'field_type'       => '\phpbb\profilefields\type\type_string',
			'field_name' 	   => 'field',
			'field_id'	 	   => 1,
			'lang_id'	 	   => 1,
			'lang_name'        => 'field',
			'field_required'   => false,
			'field_validation' => '.*',
		);
	}

	public function validate_profile_field_data()
	{
		return array(
			array(
				'',
				array('field_required' => true),
				'FIELD_REQUIRED-field',
				'Field should not accept empty values for required fields',
			),
			array(
				null,
				array('field_required' => true),
				'FIELD_REQUIRED-field',
				'Field should not accept empty values for required field',
			),
			array(
				0,
				array('field_required' => true),
				false,
				'Field should accept a non-empty input',
			),
			array(
				'false',
				array('field_required' => true),
				false,
				'Field should accept a non-empty input',
			),
			array(
				10,
				array('field_required' => true),
				false,
				'Field should accept a non-empty input',
			),
			array(
				'tas',
				array('field_minlen' => 2, 'field_maxlen' => 5),
				false,
				'Field should accept value of correct length',
			),
			array(
				't',
				array('field_minlen' => 2, 'field_maxlen' => 5),
				'FIELD_TOO_SHORT-2-field',
				'Field should reject value of incorrect length',
			),
			array(
				'this is a long string',
				array('field_minlen' => 2, 'field_maxlen' => 5),
				'FIELD_TOO_LONG-5-field',
				'Field should reject value of incorrect length',
			),
			array(
				'H3110',
				array('field_validation' => '[0-9]+'),
				'FIELD_INVALID_CHARS_NUMBERS_ONLY-field',
				'Required field should reject characters in a numbers-only field',
			),
			array(
				'&lt;&gt;&quot;&amp;%&amp;&gt;&lt;&gt;',
				array('field_maxlen' => 10, 'field_minlen' => 2),
				false,
				'Optional field should accept html entities',
			),
			array(
				'ö ä ü ß',
				array(),
				false,
				'Required field should accept UTF-8 string',
			),
			array(
				'This ö ä string has to b',
				array('field_maxlen' => 10),
				'FIELD_TOO_LONG-10-field',
				'Required field should reject an UTF-8 string which is too long',
			),
			array(
				'ö äö äö ä',
				array('field_validation' => '[\w]+'),
				'FIELD_INVALID_CHARS_ALPHA_ONLY-field',
				'Required field should reject UTF-8 in alpha only field',
			),
			array(
				'Hello',
				array('field_validation' => '[\w]+'),
				false,
				'Required field should accept a characters only field',
			),
			array(
				'Valid.Username123',
				array('field_validation' => '[\w.]+'),
				false,
				'Required field should accept a alphanumeric field with dots',
			),
			array(
				'Invalid.,username123',
				array('field_validation' => '[\w.]+'),
				'FIELD_INVALID_CHARS_ALPHA_DOTS-field',
				'Required field should reject field with comma',
			),
			array(
				'skype.test.name,_this',
				array('field_validation' => '[a-zA-Z][\w\.,\-_]+'),
				false,
				'Required field should accept alphanumeric field with punctuations',
			),
			array(
				'1skype.this.should.faila',
				array('field_validation' => '[a-zA-Z][\w\.,\-_]+'),
				'FIELD_INVALID_CHARS_ALPHA_PUNCTUATION-field',
				'Required field should reject field having invalid input for the given validation',
			),
		);
	}

	/**
	* @dataProvider validate_profile_field_data
	*/
	public function test_validate_profile_field($value, $field_options, $expected, $description)
	{
		$field_options = array_merge($this->field_options, $field_options);

		$result = $this->cp->validate_profile_field($value, $field_options);

		$this->assertSame($expected, $result, $description);
	}

	public function profile_value_data()
	{
		return array(
			array(
				'test',
				array('field_show_novalue' => true),
				'test',
				'Field should output the given value',
			),
			array(
				'test',
				array('field_show_novalue' => false),
				'test',
				'Field should output the given value',
			),
			array(
				'',
				array('field_show_novalue' => true),
				'',
				'Field should output nothing for empty value',
			),
			array(
				'',
				array('field_show_novalue' => false),
				null,
				'Field should simply output null for empty vlaue',
			),
		);
	}


	/**
	* @dataProvider profile_value_data
	*/
	public function test_get_profile_value($value, $field_options, $expected, $description)
	{
		$field_options = array_merge($this->field_options, $field_options);

		$result = $this->cp->get_profile_value($value, $field_options);

		$this->assertSame($expected, $result, $description);
	}

	public function profile_value_raw_data()
	{
		return array(
			array(
				'[b]bbcode test[/b]',
				array('field_show_novalue' => true),
				'[b]bbcode test[/b]',
				'Field should return the correct raw value',
			),
			array(
				'[b]bbcode test[/b]',
				array('field_show_novalue' => false),
				'[b]bbcode test[/b]',
				'Field should return correct raw value',
			),
			array(
				125,
				array('field_show_novalue' => false),
				125,
				'Field should return value of integer as is',
			),
			array(
				0,
				array('field_show_novalue' => false),
				null,
				'Field should return null for empty integer without show_novalue',
			),
			array(
				0,
				array('field_show_novalue' => true),
				0,
				'Field should return 0 for empty integer with show_novalue',
			),
			array(
				null,
				array('field_show_novalue' => true),
				null,
				'field should return null value as is',
			),
		);
	}

	/**
	* @dataProvider profile_value_raw_data
	*/
	public function test_get_profile_value_raw($value, $field_options, $expected, $description)
	{
		$field_options = array_merge($this->field_options, $field_options);

		$result = $this->cp->get_profile_value_raw($value, $field_options);

		$this->assertSame($expected, $result, $description);
	}

	public function return_callback_implode()
	{
		return implode('-', func_get_args());
	}
}
