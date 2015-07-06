<?php
/**
*
* @package phpBB Extension - Topiclist
* @copyright (c) 2015 Sheer
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace sheer\topiclist\acp;

class main_module
{
	var $u_action;

	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$this->tpl_name = 'acp_topiclist';
		$this->page_title = $user->lang('ACP_TOPICLIST_SETTINGS');

		$action = request_var('action', '');
		$submit = request_var('submit', false);
		$delall = request_var('delall', false);
		$ex_forums = request_var('mark', array(0));
		$sql_forums = $sql_where = '';

		if($delall && !empty($ex_forums))
		{
			$files = array();
			$handle = opendir($phpbb_root_path . 'ext/sheer/topiclist/topiclist/');
			if($handle)
			{
				foreach($ex_forums as $fid)
				{
					@unlink($phpbb_root_path . 'ext/sheer/topiclist/topiclist/' . $fid . '.html');
				}
			}
			closedir($handle);
		}

		if($submit)
		{
			//$sql_where = '';
			$action = 'generate';
			//$forum = 0;
//			print "<pre>"; print_r($ex_forums);print "</pre>";
			if(!empty($ex_forums))
			{
				$sql_forums = 'AND '. $db->sql_in_set('forum_id', $ex_forums) . '';
			}
		}


		$template->assign_vars(array(
			'U_ACTION'	=> $this->u_action,
		));

		if ($mode != 'manage')
		{
			return;
		}
		switch ($action)
		{
			case "generate":
				$forums = array();
				$forum = request_var('f', 0);
				if($forum)
				{
					$sql_where = 'AND forum_id = ' . $forum ;
				}
				$url = generate_board_url();
				$sql = 'SELECT forum_name, forum_id
					FROM ' . FORUMS_TABLE . '
					WHERE forum_type NOT IN (' . FORUM_CAT . ', ' . FORUM_LINK . ')
					' . $sql_where . '
					' . $sql_forums;
//print "$sql<br />";
				if ($sql_where || $sql_forums)
				{
					$res = $db->sql_query($sql);
					while($row = $db->sql_fetchrow($res))
					{
						$forums[$row['forum_id']] = $row['forum_name'];
					}
					foreach($forums as $forum_id => $forum_name)
					{
						$html_content = '<li><b><a href="' . $url . '/viewforum.' . $phpEx . '?f=' . $forum_id . '">' . $forum_name . '</a></b></li>';
						$html_content .= '<ul>';
						$sql = 'SELECT topic_id, forum_id, topic_title, topic_type, topic_posts_approved
							FROM ' . TOPICS_TABLE . '
							WHERE forum_id = ' . $forum_id . '
							AND topic_visibility = 1';
						$result = $db->sql_query($sql);
						while ($row = $db->sql_fetchrow($result))
						{
							$fid = $row['forum_id'];
							$pages = $row['topic_posts_approved'] / $config['posts_per_page'];
							$pages = (int) $pages;
							if ($pages <= 1)
							{
								$html_content .= '<li><a href="' . $url . '/viewtopic.' . $phpEx . '?f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'] . '">' . $row['topic_title'] . '</a></li>';
							}
							else
							{
								$html_content .= '<li><a href="' . $url . '/viewtopic.' . $phpEx . '?f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'] . '">' . $row['topic_title'] . '</a><br />[ ';
								for ($i=1; $i<=$pages; $i++)
								{
									$html_content .= '<a href="' . $url . '/viewtopic.' . $phpEx . '?f=' . $row['forum_id'] . '&amp;t=' . $row['topic_id'] . '&amp;start=' . $i * $config['posts_per_page'] . '">' . ($i+1) . '</a> ';
								}
								$html_content .= ']</li>';
							}
						}
						$db->sql_freeresult($result);
						$html_content .= '</ul>';

						$file_name = '' . $phpbb_root_path . 'ext/sheer/topiclist/topiclist/' . $forum_id . '.html';
						$w = fopen($file_name, 'w');
						fwrite($w, $html_content);
						fclose($w);
					}
					$db->sql_freeresult($res);
				}

			break;

			case "delete":
				$forum = request_var('f', 0);
				$file_name = '' . $phpbb_root_path . 'ext/sheer/topiclist/topiclist/' . $forum . '.html';
				if (file_exists($file_name))
				{
					unlink($file_name);
				}
			break;
		}

		$sql = 'SELECT forum_id, forum_status, forum_image, forum_name, forum_type, left_id, right_id, forum_desc, forum_desc_uid, forum_desc_bitfield, forum_desc_options, forum_topics_approved, forum_posts_approved
			FROM ' . FORUMS_TABLE . '
			ORDER BY forum_id';
		$result = $db->sql_query($sql);

		if ($row = $db->sql_fetchrow($result))
		{
			do
			{
				$forum_type = $row['forum_type'];

				if ($row['forum_status'] == ITEM_LOCKED)
				{
					$folder_image = '<img src="images/icon_folder_lock.gif" alt="' . $user->lang['LOCKED'] . '" />';
				}
				else
				{
					switch ($forum_type)
					{
						case FORUM_LINK:
							$folder_image = '<img src="images/icon_folder_link.gif" alt="' . $user->lang['LINK'] . '" />';
						break;

						default:
							$folder_image = ($row['left_id'] + 1 != $row['right_id']) ? '<img src="images/icon_subfolder.gif" alt="' . $user->lang['SUBFORUM'] . '" />' : '<img src="images/icon_folder.gif" alt="' . $user->lang['FOLDER'] . '" />';
						break;
					}
				}

				$url = $this->u_action . "&amp;f={$row['forum_id']}";

				$template->assign_block_vars('forums', array(
					'ID'				=> $row['forum_id'],
					'FOLDER_IMAGE'		=> $folder_image,
					'FORUM_IMAGE'		=> ($row['forum_image']) ? '<img src="' . $phpbb_root_path . $row['forum_image'] . '" alt="" />' : '',
					'FORUM_IMAGE_SRC'	=> ($row['forum_image']) ? $phpbb_root_path . $row['forum_image'] : '',
					'FORUM_NAME'		=> $row['forum_name'],
					'FORUM_DESCRIPTION'	=> generate_text_for_display($row['forum_desc'], $row['forum_desc_uid'], $row['forum_desc_bitfield'], $row['forum_desc_options']),
					'FORUM_TOPICS'		=> $row['forum_topics_approved'],
					'FORUM_POSTS'		=> $row['forum_posts_approved'],
					'S_FORUM_LINK'		=> ($forum_type == FORUM_LINK) ? true : false,
					'S_FORUM_POST'		=> ($forum_type == FORUM_POST) ? true : false,
					'S_DELETE'			=> (file_exists('' . $phpbb_root_path . 'ext/sheer/topiclist/topiclist/' . $row['forum_id'] . '.html')) ? true : false,
					'U_GENERATE'		=> ($forum_type != FORUM_CAT) ? append_sid($url . '&amp;action=generate') : '',
					'U_DELETE'			=> ($forum_type != FORUM_CAT) ? append_sid($url . '&amp;action=delete') : '',
				));
			}
			while ($row = $db->sql_fetchrow($result));
		}
		$db->sql_freeresult($result);
	}
}
