<?php
/**
*
* @package phpBB Extension - Topiclist
* @copyright (c) 2015 Sheer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/
namespace sheer\topiclist\migrations;

class topiclist_1_0_0 extends \phpbb\db\migration\migration
{
	public function effectively_installed()
	{
		return;
	}

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v310\dev');
	}

	public function update_schema()
	{
		return array(
		);
	}

	public function revert_schema()
	{
		return array(
		);
	}

	public function update_data()
	{
		return array(
			// Current version
			array('config.add', array('topiclist_version', '1.0.0')),
			array('module.add', array(
				'acp', 'ACP_BOARD_CONFIGURATION', array(
					'module_basename'	=> '\sheer\topiclist\acp\main_module',
					'module_mode'		=> 'manage',
					'module_auth'		=> 'ext_sheer/topiclist && acl_a_board',
				),
			)),
		);
	}
}