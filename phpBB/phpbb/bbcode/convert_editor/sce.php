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

use s9e\TextFormatter\Configurator;
use s9e\TextFormatter\Plugins\BBCodes\Configurator as BBCodeConfigurator;
use s9e\TextFormatter\Configurator\Items\AttributeFilters\Regexp as RegexpFilter;
use s9e\TextFormatter\Configurator\Items\UnsafeTemplate;

/**
* 
*/
class sce extends base
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
	public function __construct($phpbb_root_path, $phpEx, \phpbb\auth\auth $auth, \phpbb\config\config $config, \phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\event\dispatcher_interface $phpbb_dispatcher)
	{
		$this->config = $config;
		$this->db = $db;
		$this->phpbb_dispatcher = $phpbb_dispatcher;

	}
	
	/**
	 * This converts the BBCode in the format that is in the database into a javascript output
	 * which is the instructions for the WYSIWYG editor on how to display all the BBCode and which BBCode
	 * to show up on the BBCode button list
	 *
	 *
	 *
	 */
	public function convert_bbcode_to_editor($text_formatter_factory)
	{
		$configurator = $text_formatter_factory->get_configurator();
		$configurator->addHTML5Rules();
		
		// var_dump($configurator->javascript);
		// var_dump($configurator->tags['LIST']);
		
		
		// foreach($configurator->BBCodes AS $bbcode)
		// {
			// var_dump($bbcode, $configurator->tags[$bbcode->tagName]);
			// echo "<br>\n";
		// }
		// exit;
		// $configurator->enableJavaScript();
		
		// foreach($configurator->BBCodes AS $bbcode)
		// {
			var_dump(/* $bbcode->tagName, */ $configurator->tags['list']->template->__toString());
			
			$this->parse_tag_templates($configurator->BBCodes, $configurator->tags);
			
			
		// }
		
		exit;
		foreach($configurator->BBCodes AS $bbcode)
		{
			var_dump($bbcode, $configurator->tags[$bbcode->tagName]);
			echo "<br>\n";
		}
		echo "-----------------------------------------------------------------
		-------------------------------------------------------------------
		--------------------------------------------------------------------
		-------------------------------------------------------------------------
		---------------------------------------------------------
		-----------------------------------------------------------------------
		-------------------------------
";
		// foreach( AS $bbcode)
		// {
			// var_dump($bbcode);
			// echo "<br>\n";
		// }
		exit;
		
	}
	/*
		TextFormatter 		to 			SCE
		defaultChildRule				{forced:allow}
		defaultDescendantRule			{forced:allow}
		CloseParent						allowedChildren
		CloseParent						closedBy
		ignoreSurroundingWhitespace		{browser automatic}
		isNormalized					{Not needed}
		isTransparent					?
		filterChain->items->callback	Use to know the RegEx to use for validation
		attributePreprocessors			Extra RegEx to copy/parse data from an attribute to another
		attributes->defaultValue		Default value of attribute
		rules->autoReopen				{Doesn't have. Is it required?}
		AttributeFilter::AsURL			{Requires manual check for URL}
		
		
	*/

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

	public static function get_name(){
		return 'SCE';
	}
	
	public static function get_available_button_modes()
	{
		return parent::HAS_BUTTON_MODE_ICON | parent::HAS_BUTTON_MODE_TEXT | parent::HAS_BUTTON_MODE_ICON_TEXT;
	}
	
}
