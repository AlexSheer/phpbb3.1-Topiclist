<?php
/**
*
* @package phpBB Extension - Topiclist
* @copyright (c) 2013 phpBB Group
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace sheer\topiclist\controller;

use Symfony\Component\HttpFoundation\Response;

class topiclist
{
	protected $db;
	protected $template;
	protected $user;

	public function __construct(
		\phpbb\db\driver\driver_interface $db,
		\phpbb\template\template $template,
		\phpbb\user $user
	)
	{
		$this->db = $db;
		$this->template = $template;
		$this->user = $user;
	}

	public function main()
	{		$topiclist_files_exist = false;
		$sql = 'SELECT forum_id
			FROM ' . FORUMS_TABLE . '';
		$result = $this->db->sql_query($sql);
		while ($row = $this->db->sql_fetchrow($result))
		{
			if (file_exists('ext/sheer/topiclist/topiclist/' . $row['forum_id'] . '.html'))
			{
				$this->template->assign_block_vars('topiclist_row', array(
					'TOPICS'	=> file_get_contents('ext/sheer/topiclist/topiclist/' . $row['forum_id'] . '.html'),
				));
				$topiclist_files_exist = true;
			}
		}
		$this->db->sql_freeresult($result);

		$this->template->assign_vars(array(
			'TOPICLIST_FILES'	=> $topiclist_files_exist,
		));

		page_header($this->user->lang('TOPICLIST_TITLE'));
		$this->template->set_filenames(array(
			'body' => 'topiclist_body.html'));

		page_footer();
		return new Response($this->template->return_display('body'), 200);
	}


}