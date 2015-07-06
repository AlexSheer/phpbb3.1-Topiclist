<?php
/**
*
* @package phpBB Extension - Topiclist
* @copyright (c) 2015 Sheer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace sheer\topiclist\acp;

class main_info
{
	function module()
	{
		return array(
			'filename'	=> '\sheer\topiclist\acp\main_module',
			'title'		=> 'ACP_TOPICLIST',
			'modes'		=> array(
				'manage'	=> array('title' => 'ACP_TOPICLIST', 'auth' => 'ext_sheer/topiclist && acl_a_board', 'cat' => array('ACP_BOARD_CONFIGURATION')),
			),
		);
	}
}
