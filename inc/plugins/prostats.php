<?php
/*
 ______________________________________________________

| Plugin ProStats 1.6.3								  |
| (c) 2008-2009 by SaeedGh (SaeehGhMail@Gmail.com)	  |
| Website: http://www.mybbhelp.ir					  |
| Last edit: 2009-04-21								  |
 _____________________________________________________
 
*/

if(!defined("IN_MYBB")) {
	die("Direct initialization of this file is not allowed.");
}


$plugins->add_hook("index_start", "prostats_run_index");
$plugins->add_hook("portal_start", "prostats_run_portal");


function prostats_info()
{
	return array(
		"name"		=> "<img border=\"0\" src=\"../images/MybbHelp_small.gif\" align=\"absbottom\" /> <img border=\"0\" src=\"../images/ProStats.gif\" align=\"absbottom\" /> ProStats",
		"title"		=> "ProStats",
		"description" => "Professional stats for MyBB.",
		"website"	 => "http://www.mybbhelp.ir",
		"author"	  => "SaeedGh",
		"authorsite"  => "http://www.mybbhelp.ir",
		"version"	 => "1.6.3",
		'guid'		=> '124b68d05dcdaf6b7971050baddf340f',
		'compatibility' => '14*'
	);
}


function prostats_activate()
{
	global $db;
	
	require MYBB_ROOT."inc/adminfunctions_templates.php";
	find_replace_templatesets("index", '#{\$header}(\r?)\n#', "{\$header}\n{\$ps_header_index}\n");
	find_replace_templatesets("index", '#{\$forums}(\r?)\n#', "{\$forums}\n{\$ps_footer_index}\n");
	find_replace_templatesets("portal", '#{\$header}(\r?)\n#', "{\$header}\n{\$ps_header_portal}\n");
	find_replace_templatesets("portal", '#{\$footer}(\r?)\n#', "{\$ps_footer_portal}\n{\$footer}\n");
	
	$extra_cells = "select\n0=--\n1=Top posters\n2=Top referrers\n3=Most replies\n4=Most viewed\n5=Most thanks\n6=New members\n7=Top downloads";

	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats",
		"template" => "<table width=\"100%\" border=\"0\" cellspacing=\"{\$theme[borderwidth]}\" cellpadding=\"0\" class=\"tborder\">
		<thead>
		<tr><td colspan=\"{\$num_columns}\"><table  border=\"0\" cellspacing=\"0\" cellpadding=\"{\$theme[tablespace]}\" width=\"100%\"><tr class=\"thead\"><td><strong>{\$lang->prostats_prostats}</strong></td></tr></table></td>
		</tr>
		</thead>
		<tbody>
		{\$trow_message_top}
		<tr valign=\"top\">
		{\$prostats_content}
		</tr>
		{\$trow_message_down}
		</tbody>
		</table>
		<!-- Don\'t remove this part! you can remove copyright by changing settings of plugin. -->
		{\$prostats_copyright}
		<br />",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_readstate_icon",
		"template" => "<img src=\"{\$mybb->settings[\'bburl\']}/images/ps_mini{\$lightbulb[\'folder\']}.gif\" align=\"absmiddle\" alt=\"\" />&nbsp;",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_newmembers",
		"template" => "<td><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"{\$theme[tablespace]}\">
		<tr class=\"tcat smalltext\">
		<td colspan=\"2\">{\$lang->prostats_newest_members}</td>
		</tr>
		<tr>
<td colspan=\"2\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
		{\$newmembers_row}
</table></td></tr>
		</table></td>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
		
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_newmembers_row",
		"template" => "<tr class=\"smalltext\">
		<td><a href=\"{\$profilelink}\">{\$username}</a></td>
		<td align=\"{\$ps_align}\">{\$regdate}</td>
		</tr>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
		
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_topposters",
		"template" => "<td><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"{\$theme[tablespace]}\">
		<tr class=\"tcat smalltext\">
		<td colspan=\"2\">{\$lang->prostats_top_posters}</td>
		</tr>
		<tr>
<td colspan=\"2\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
		{\$topposters_row}
</table></td></tr>
		</table></td>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
		
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_topposters_row",
		"template" => "<tr class=\"smalltext\">
		<td><a href=\"{\$profilelink}\">{\$username}</a></td>
		<td align=\"{\$ps_align}\"><a href=\"search.php?action=finduser&amp;uid={\$uid}\">{\$postnum}</a></td>
		</tr>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_topreferrers",
		"template" => "<td><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"{\$theme[tablespace]}\">
		<tr class=\"tcat smalltext\">
		<td colspan=\"2\">{\$lang->prostats_top_topreferrers}</td>
		</tr>
		<tr>
<td colspan=\"2\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
		{\$topreferrers_row}
</table></td></tr>
		</table></td>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
		
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_topreferrers_row",
		"template" => "<tr class=\"smalltext\">
		<td><a href=\"{\$profilelink}\">{\$username}</a></td>
		<td align=\"{\$ps_align}\">{\$refnum}</td>
		</tr>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_mostthanks",
		"template" => "<td><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"{\$theme[tablespace]}\">
		<tr class=\"tcat smalltext\">
		<td colspan=\"2\">{\$lang->prostats_most_thanks}</td>
		</tr>
		<tr>
<td colspan=\"2\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
		{\$mostthanks_row}
</table></td></tr>
		</table></td>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
		
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_mostthanks_row",
		"template" => "<tr class=\"smalltext\">
		<td><a href=\"{\$profilelink}\">{\$username}</a></td>
		<td align=\"{\$ps_align}\">{\$thxnum}</td>
		</tr>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_newthreads",
		"template" => "<td class=\"{\$trow}\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"{\$theme[tablespace]}\">
		<tr class=\"tcat smalltext\">
		<td colspan=\"{\$colspan}\">{\$lang->prostats_newest_threads}</td>
		</tr>
		<tr>
		<td colspan=\"{\$colspan}\">
<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
		<tr class=\"{\$trow} smalltext\">
		{\$newthreads_cols_name}
		</tr>
		{\$newthreads_row}
</table></td>
		</tr>
		</table></td>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_newthreads_row",
		"template" => "<tr class=\"{\$trow} smalltext\">
		{\$newthreads_cols}
		</tr>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_newthreads_specialchar",
		"template" => "<a href=\"{\$threadlink}\" style=\"text-decoration: none;\"><font face=\"arial\" style=\"line-height:10px;\">▼</font></a>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_mostreplies",
		"template" => "<td><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"{\$theme[tablespace]}\">
		<tr class=\"tcat smalltext\">
		<td colspan=\"2\">{\$lang->prostats_most_replies}</td>
		</tr>
		<tr>
<td colspan=\"2\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
		{\$mostreplies_row}
</table></td></tr>
		</table></td>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
		
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_mostreplies_row",
		"template" => "<tr class=\"smalltext\">
		<td>{\$readstate_icon}<a href=\"{\$threadlink}\" title=\"{\$subject_long}\">{\$subject}</a></td>
		<td align=\"{\$ps_align}\"><a href=\"javascript:MyBB.whoPosted({\$tid});\">{\$replies}</a></td>
		</tr>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
		
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_mostviews",
		"template" => "<td><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"{\$theme[tablespace]}\">
		<tr class=\"tcat smalltext\">
		<td colspan=\"2\">{\$lang->prostats_most_views}</td>
		</tr>
		<tr>
<td colspan=\"2\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
		{\$mostviews_row}
</table></td></tr>
		</table></td>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
		
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_mostviews_row",
		"template" => "<tr class=\"smalltext\">
		<td>{\$readstate_icon}<a href=\"{\$threadlink}\" title=\"{\$subject_long}\">{\$subject}</a></td>
		<td align=\"{\$ps_align}\">{\$views}</td>
		</tr>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
		
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_newposts",
		"template" => "<td><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"{\$theme[tablespace]}\">
		<tr class=\"tcat smalltext\">
		<td colspan=\"2\">{\$lang->prostats_newest_posts}</td>
		</tr>
		<tr>
<td colspan=\"2\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
		{\$newposts_row}
</table></td></tr>
		</table></td>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
		
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_newposts_row",
		"template" => "<tr class=\"smalltext\">
		<td><a href=\"{\$postlink}\" title=\"{\$subject_long}\">{\$subject}</a></td>
		<td align=\"right\"><a href=\"{\$profilelink}\">{\$username}</a></td>
		</tr>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_topdownloads",
		"template" => "<td><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"{\$theme[tablespace]}\">
		<tr class=\"tcat smalltext\">
		<td colspan=\"2\">{\$lang->prostats_top_downloads}</td>
		</tr>
		<tr>
<td colspan=\"2\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
		{\$topdownloads_row}
</table></td></tr>
		</table></td>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
		
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_topdownloads_row",
		"template" => "<tr class=\"smalltext\">
		<td><img src=\"{\$attach_icon}\" width=\"11\" height=\"11\" align=\"absmiddle\" alt=\"\" />&nbsp;<a href=\"{\$postlink}\" title=\"{\$subject_long}\">{\$subject}</a></td>
		<td align=\"{\$ps_align}\">{\$downloadnum}</td>
		</tr>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_message",
		"template" => "<tr class=\"trow1\">
		<td colspan=\"{\$num_columns}\">
		<table  border=\"0\" cellspacing=\"0\" cellpadding=\"{\$theme[tablespace]}\" width=\"100%\">
		<tr class=\"smalltext\">
		<td>
		{\$prostats_message}
		</td>
		</tr>
		</table>
		</td>
		</tr>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_onerowextra",
		"template" => "<td class=\"{\$trow}\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td>{\$single_extra_content}</td></tr></table></td>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_tworowextra",
		"template" => "<td class=\"{\$trow}\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr><td>{\$extra_content_one}</td></tr><tr><td>{\$extra_content_two}</td></tr></table></td>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$ps_group = array(
		"gid"			=> "NULL",
		"name"			=> "ProStats",
		"title"			=> "☼ MybbHelp » ProStats",
		"description"	=> "Professional stats for MyBB.",
		"disporder"		=> "1",
		"isdefault"		=> "1",
	);
	
	$db->insert_query("settinggroups", $ps_group);
	$gid = $db->insert_id();
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_index",
		"title"			=> "Show in index",
		"description"	=> "Show ProStat table in index page.",
		"optionscode"	=> "yesno",
		"value"			=> '1',
		"disporder"		=> '1',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_portal",
		"title"			=> "Show in portal",
		"description"	=> "Show ProStat table in index page.",
		"optionscode"	=> "yesno",
		"value"			=> '0',
		"disporder"		=> '3',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_direction",
		"title"			=> "Table direction",
		"description"	=> "Direction of stats in your board.",
		"optionscode"	=> "select\n0=LTR (Left to Right)\n1=RTL (Right to Left)",
		"value"			=> '1',
		"disporder"		=> '5',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_position",
		"title"			=> "Table position",
		"description"	=> "Position of stats in your board.",
		"optionscode"	=> "select\n0=Top (Header)\n1=Bottom (Footer)",
		"value"			=> '1',
		"disporder"		=> '10',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_format_name",
		"title"			=> "Style usernames",
		"description"	=> "Style the username in true color, font and ... .",
		"optionscode"	=> "yesno",
		"value"			=> '1',
		"disporder"		=> '20',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_subject_length",
		"title"			=> "Subject length",
		"description"	=> "Maximum length of topic/post subjects. (0 will remove this limitation)",
		"optionscode"	=> "text",
		"value"			=> '25',
		"disporder"		=> '30',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_num_rows",
		"title"			=> "Number of rows",
		"description"	=> "How much items must be shown? (minimum = 3)",
		"optionscode"	=> "text",
		"value"			=> '11',
		"disporder"		=> '41',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_date_format",
		"title"			=> "Date and time format",
		"description"	=> "The format of date and time, use in ProStats table.",
		"optionscode"	=> "text",
		"value"			=> 'm-d, H:i',
		"disporder"		=> '42',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_date_format_ty",
		"title"			=> "Replace format",
		"description"	=> "A part of date format that must be replace with \"Today\" or \"Tomorrow\".",
		"optionscode"	=> "text",
		"value"			=> 'm-d',
		"disporder"		=> '43',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_trow_message",
		"title"			=> "Message block",
		"description"	=> "This is block on top/bottom of ProStats table that you can put your HTML content in it.",
		"optionscode"	=> "textarea",
		"value"			=> '',
		"disporder"		=> '45',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_trow_message_pos",
		"title"			=> "Message block position",
		"description"	=> "The position of message block in ProStats table.",
		"optionscode"	=> "select\n0=Top\n1=Down (Default)",
		"value"			=> '1',
		"disporder"		=> '46',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_last_topics",
		"title"			=> "Show last topics",
		"description"	=> "Show last topics in ProStats table.",
		"optionscode"	=> "yesno",
		"value"			=> '1',
		"disporder"		=> '50',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_last_topics_cells",
		"title"			=> "Stats of last topics",
		"description"	=> "What type of stats you want to be shown for last topics?<br />Your choices are: <strong>New_threads, Date, Starter, Last_sender, Forum</strong><br />Separate them by comma (\",\").",
		"optionscode"	=> "text",
		"value"			=> 'New_threads, Date, Starter, Last_sender, Forum',
		"disporder"		=> '55',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_last_topics_pos",
		"title"			=> "Last topics position",
		"description"	=> "The position of Last topics field. first you need to set \"Table direction\" correctly.",
		"optionscode"	=> "select\n0=Left (in LTR themes)\n1=Right (in LTR themes)",
		"value"			=> '0',
		"disporder"		=> '60',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_cell_1",
		"title"			=> "Extra cell 1",
		"description"	=> "",
		"optionscode"	=> $extra_cells,
		"value"			=> '4',
		"disporder"		=> '62',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_cell_2",
		"title"			=> "Extra cell 2",
		"description"	=> "",
		"optionscode"	=> $extra_cells,
		"value"			=> '2',
		"disporder"		=> '64',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_cell_3",
		"title"			=> "Extra cell 3",
		"description"	=> "",
		"optionscode"	=> $extra_cells,
		"value"			=> '3',
		"disporder"		=> '66',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_cell_4",
		"title"			=> "Extra cell 4",
		"description"	=> "",
		"optionscode"	=> $extra_cells,
		"value"			=> '1',
		"disporder"		=> '68',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_cell_5",
		"title"			=> "Extra cell 5",
		"description"	=> "",
		"optionscode"	=> $extra_cells,
		"value"			=> '5',
		"disporder"		=> '70',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_cell_6",
		"title"			=> "Extra cell 6",
		"description"	=> "",
		"optionscode"	=> $extra_cells,
		"value"			=> '6',
		"disporder"		=> '72',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_rmcopyright",
		"title"			=> "Remove Copyright link",
		"description"	=> "Ok! in this version you can do it!<br />But if you remove copyright link, MybbHelp.ir and all associated websites will not support your forum! B-)",
		"optionscode"	=> "yesno",
		"value"			=> '0',
		"disporder"		=> '80',
		"gid"			=> intval($gid),
	);
		
	foreach ($ps as $p)
	{
		$db->insert_query("settings", $p);
	}
		
	rebuild_settings();
}


function prostats_deactivate()
{
	global $db;
	
	require MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("index", '#{\$ps_header_index}(\r?)\n#', "", 0);
	find_replace_templatesets("index", '#{\$ps_footer_index}(\r?)\n#', "", 0);
	find_replace_templatesets("portal", '#{\$ps_header_portal}(\r?)\n#', "", 0);
	find_replace_templatesets("portal", '#{\$ps_footer_portal}(\r?)\n#', "", 0);
	
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_readstate_icon'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_newmembers'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_newmembers_row'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_topposters'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_topposters_row'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_topreferrers'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_topreferrers_row'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_mostthanks'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_mostthanks_row'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_newthreads'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_newthreads_row'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_newthreads_specialchar'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_mostreplies'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_mostreplies_row'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_mostviews'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_mostviews_row'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_newposts'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_newposts_row'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_topdownloads'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_topdownloads_row'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_message'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_onerowextra'");
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title='prostats_tworowextra'");
	
	$db->delete_query("settings","name IN ('ps_index','ps_portal','ps_direction','ps_position','ps_format_name','ps_subject_length','ps_num_rows','ps_date_format','ps_date_format_ty','ps_trow_message','ps_trow_message_pos','ps_last_topics','ps_last_topics_cells','ps_last_topics_pos','ps_cell_1','ps_cell_2','ps_cell_3','ps_cell_4','ps_cell_5','ps_cell_6','ps_rmcopyright')");
	$db->delete_query("settinggroups","name='ProStats'");
}


function prostats_run_index()
{
	global $mybb, $ps_header_index, $ps_footer_index, $ps_header_portal, $ps_footer_portal;
	if(ceil($mybb->settings['ps_num_rows']) != $mybb->settings['ps_num_rows'] || ceil($mybb->settings['ps_subject_length']) != $mybb->settings['ps_subject_length']){return false;}
	
	if(!$mybb->settings['ps_index']) {return false;}
	if(intval($mybb->settings['ps_num_rows']) < 3) {return false;}
	
	$numofrows = $mybb->settings['ps_num_rows'];
	$prostats_tbl = "";
	
	$prostats_tbl = ps_MakeTable();

	if($mybb->settings['ps_position'] == 0)
	{
		$ps_header_index = $prostats_tbl;
	}
	else if($mybb->settings['ps_position'] == 1)
	{
		$ps_footer_index = $prostats_tbl;
	}
}

function prostats_run_portal()
{
	global $mybb, $ps_header_index, $ps_footer_index, $ps_header_portal, $ps_footer_portal;
	if(ceil($mybb->settings['ps_num_rows']) != $mybb->settings['ps_num_rows'] || ceil($mybb->settings['ps_subject_length']) != $mybb->settings['ps_subject_length']){return false;}
	
	if(!$mybb->settings['ps_portal']) {return false;}
	if(intval($mybb->settings['ps_num_rows']) < 3) {return false;}
	
	$numofrows = $mybb->settings['ps_num_rows'];
	$prostats_tbl = "";
	
	$prostats_tbl = ps_MakeTable();

	if($mybb->settings['ps_position'] == 0)
	{
		$ps_header_portal = $prostats_tbl;
	}
	else if($mybb->settings['ps_position'] == 1)
	{
		$ps_footer_portal = $prostats_tbl;
	}
}

function ps_GetLastTopics($NumOfRows)
{
	global $mybb, $db, $templates, $theme, $lang, $unviewwhere, $parser, $lightbulb, $trow, $newthreads_cols_name, $newthreads_cols, $colspan;

	$query = $db->query ("
		SELECT t.subject,t.username,t.uid,t.tid,t.fid,t.lastpost,t.lastposter,t.lastposteruid,t.replies,tr.uid AS truid,tr.dateline,f.name 
		FROM ".TABLE_PREFIX."threads t 
		LEFT JOIN ".TABLE_PREFIX."threadsread tr ON (tr.tid=t.tid AND tr.uid='".$mybb->user['uid']."') 
		LEFT JOIN ".TABLE_PREFIX."forums f ON (f.fid = t.fid) 
		WHERE t.visible='1' 
		".ps_GetUnviewable("t")." 
		AND t.closed NOT LIKE 'moved|%' 
		ORDER BY t.lastpost DESC 
		LIMIT 0,".$NumOfRows);
		
		$newthreads_cols_name = "";
		$newthreads_cols = "";
		$colspan = 0;
		$active_cells = "";
		
		$last_topics_cells_arr = escaped_explode(",", htmlspecialchars_uni($mybb->settings['ps_last_topics_cells']),20);
		
		foreach($last_topics_cells_arr as $last_topics_cell)
		{
			++$colspan;
			
			switch($last_topics_cell)
			{
				case "New_threads" : 
					$active_cells['New_threads']=1;
					$newthreads_cols_name .= "<td>".$lang->prostats_topic."</td>";
					$cell_order[$colspan]='New_threads';
					break;
				case "Date" :
					$active_cells['Date']=1;
					$newthreads_cols_name .= "<td> &nbsp; &nbsp; &nbsp;".$lang->prostats_datetime."</td>";
					$cell_order[$colspan]='Date';
					break;
				case "Starter" :
					$active_cells['Starter']=1;
					$newthreads_cols_name .= "<td>".$lang->prostats_author."</td>";
					$cell_order[$colspan]='Starter';
					break;
				case "Last_sender" :
					$active_cells['Last_sender']=1;
					$newthreads_cols_name .= "<td>".$lang->prostats_last_sender."</td>";
					$cell_order[$colspan]='Last_sender';
					break;
				case "Forum" :
					$active_cells['Forum']=1;
					$newthreads_cols_name .= "<td>".$lang->prostats_forum."</td>";
					$cell_order[$colspan]='Forum';
					break;
				default: --$colspan;
			}
		}

	$trow = "trow1";
	
	while ($newest_threads = $db->fetch_array($query))
	{

		$subject_long = htmlspecialchars_uni($parser->parse_badwords($newest_threads['subject']));
	
		$tid = $newest_threads['tid'];
		$fuid = $newest_threads['uid'];
		$fid = $newest_threads['fid'];
		$lightbulb['folder'] = "off";
		$newthreads_cols = "";
		
		if($mybb->user['uid'])
		{
			if($newest_threads['dateline'] && $newest_threads['truid'] == $mybb->user['uid'])
			{
				if($newest_threads['lastpost'] > $newest_threads['dateline'])
				{
					$lightbulb['folder'] = "on";
				}
			}
			else
			{
				if($newest_threads['lastpost'] > $mybb->user['lastvisit'])
				{
					$lightbulb['folder'] = "on";
				}
			}
		}

		$dateformat = $mybb->settings['ps_date_format'];
		
		if($active_cells['Date'])
		{
			$isty = ps_GetTY($mybb->settings['ps_date_format_ty'], $newest_threads['lastpost'], $offset="", $ty=1);
			if($isty)
			{
				$dateformat = preg_replace('#'.$mybb->settings['ps_date_format_ty'].'#', "vvv", $dateformat);
				$datetime = my_date($dateformat, $newest_threads['lastpost'], NULL, 1);
				$datetime = preg_replace('#vvv#', $isty, $datetime);
			}
			else
			{
				$datetime = my_date($dateformat, $newest_threads['lastpost'], NULL, 1);
			}
		}
		
		if($active_cells['New_threads'])
		{
			$subject = htmlspecialchars_uni(ps_SubjectLength($parser->parse_badwords($newest_threads['subject'])));
			$threadlink = get_thread_link($tid,NULL,"lastpost");
			eval("\$readstate_icon = \"".$templates->get("prostats_readstate_icon")."\";");
			eval("\$newthreads_specialchar = \"".$templates->get("prostats_newthreads_specialchar")."\";");
		}
		
		if($active_cells['Starter'])
		{
			$username = ps_FormatNameDb($fuid, htmlspecialchars_uni($newest_threads['username']));
			$profilelink = get_profile_link($fuid);
		}
		
		if($active_cells['Last_sender'])
		{
			$lastposter_uname = ps_FormatNameDb($newest_threads['lastposteruid'], htmlspecialchars_uni($newest_threads['lastposter']));
			$lastposter_profile = get_profile_link($newest_threads['lastposteruid']);
		}
		
		if($active_cells['Forum'])
		{
			$forumlink = get_forum_link($fid);
			$forum_longname = $parser->parse_badwords(strip_tags($newest_threads['name']));
			$forumname = htmlspecialchars_uni(ps_SubjectLength($forum_longname, NULL, true));		
		}
		
		for($i=1;$i<=$colspan;++$i)
		{
			switch($cell_order[$i])
			{
				case "New_threads" : 
					$newthreads_cols .= "<td>".$readstate_icon."<a href=\"".$threadlink."\" title=\"".$subject_long."\">".$subject."</a></td>";
					break;
				case "Date" :
					$newthreads_cols .= "<td>".$newthreads_specialchar.$datetime."</td>";
					break;
				case "Starter" :
					$newthreads_cols .= "<td><a href=\"".$profilelink."\">".$username."</a></td>";
					break;
				case "Last_sender" :
					$newthreads_cols .= "<td><a href=\"".$lastposter_profile."\">".$lastposter_uname."</a></td>";
					break;
				case "Forum" :
					$newthreads_cols.= "<td><a href=\"".$forumlink."\" title=\"".$forum_longname."\">".$forumname."</a></td>";
					break;
				default: NULL;
			}
		}

		eval("\$newthreads_row .= \"".$templates->get("prostats_newthreads_row")."\";");
	}
	eval("\$newthreads = \"".$templates->get("prostats_newthreads")."\";");
	
	return $newthreads;
}


function ps_GetTopPosters($NumOfRows)
{
	global $mybb, $db, $templates, $theme, $lang, $ps_align;

	$query = $db->query("SELECT username,postnum,uid,usergroup,displaygroup FROM ".TABLE_PREFIX."users ORDER BY postnum DESC LIMIT 0,".$NumOfRows);

	while ($topposters = $db->fetch_array($query))
	{
		$uid = $topposters['uid'];
		$username = ps_FormatName(htmlspecialchars_uni($topposters['username']), $topposters['usergroup'], $topposters['displaygroup']);
		$postnum = $topposters['postnum'];
		
		$profilelink = get_profile_link($uid);
		
		eval("\$topposters_row .= \"".$templates->get("prostats_topposters_row")."\";");
	}
	eval("\$column_topposters = \"".$templates->get("prostats_topposters")."\";");

	return $column_topposters;
}


function ps_GetTopReferrers($NumOfRows)
{
	global $mybb, $db, $templates, $theme, $lang, $ps_align;

	$query = $db->query("
	SELECT u.uid,u.username,u.usergroup,u.displaygroup,count(*) as refcount 
	FROM ".TABLE_PREFIX."users u 
	LEFT JOIN ".TABLE_PREFIX."users r ON (r.referrer = u.uid) 
	WHERE r.referrer = u.uid 
	GROUP BY r.referrer DESC 
	ORDER BY refcount DESC 
	LIMIT 0 ,".$NumOfRows);

	while ($topreferrer = $db->fetch_array($query)) {
		$uid = $topreferrer['uid'];
		$username = ps_FormatName(htmlspecialchars_uni($topreferrer['username']), $topreferrer['usergroup'], $topreferrer['displaygroup']);
		$refnum = $topreferrer['refcount'];
		
		$profilelink = get_profile_link($uid);
		
		eval("\$topreferrers_row .= \"".$templates->get("prostats_topreferrers_row")."\";");
	}
	eval("\$column_topreferrers = \"".$templates->get("prostats_topreferrers")."\";");

	return $column_topreferrers;
}


function ps_GetMostReplies($NumOfRows)
{
	global $mybb, $db, $templates, $theme, $lang, $unviewwhere, $parser, $ps_align;

	$query = $db->query ("
		SELECT t.subject,t.tid,t.replies,t.lastpost,tr.uid AS truid,tr.dateline 
		FROM ".TABLE_PREFIX."threads t 
		LEFT JOIN ".TABLE_PREFIX."threadsread tr ON (tr.tid=t.tid AND tr.uid='".$mybb->user['uid']."') 
		LEFT JOIN ".TABLE_PREFIX."forums f ON (f.fid = t.fid) 
		WHERE t.visible='1' 
		".ps_GetUnviewable("t")." 
		AND t.closed NOT LIKE 'moved|%' 
		ORDER BY t.replies DESC 
		LIMIT 0,".$NumOfRows);

	while ($most_replies = $db->fetch_array($query))
	{
		$subject_long = htmlspecialchars_uni($parser->parse_badwords($most_replies['subject']));
		$tid = $most_replies['tid'];
		$subject = htmlspecialchars_uni(ps_SubjectLength($parser->parse_badwords($most_replies['subject']), NULL, true));
		$replies = $most_replies['replies'];
		$lightbulb['folder'] = "off";

		if($mybb->user['uid'])
		{
			if($most_replies['dateline'] && $most_replies['truid'] == $mybb->user['uid'])
			{
				if($most_replies['lastpost'] > $most_replies['dateline'])
				{
					$lightbulb['folder'] = "on";
				}
			}
			else
			{
				if($most_replies['lastpost'] > $mybb->user['lastvisit'])
				{
					$lightbulb['folder'] = "on";
				}
			}
		}
		
		$threadlink = get_thread_link($tid);
		
		eval("\$readstate_icon = \"".$templates->get("prostats_readstate_icon")."\";");
		eval("\$mostreplies_row .= \"".$templates->get("prostats_mostreplies_row")."\";");
	}
	eval("\$column_mostreplies = \"".$templates->get("prostats_mostreplies")."\";");

	return $column_mostreplies;
}


function ps_GetMostViewed($NumOfRows)
{
	global $mybb, $db, $templates, $theme, $lang, $unviewwhere, $parser, $ps_align;

	$query = $db->query ("
		SELECT t.subject,t.tid,t.lastpost,t.views,tr.uid AS truid,tr.dateline 
		FROM ".TABLE_PREFIX."threads t 
		LEFT JOIN ".TABLE_PREFIX."threadsread tr ON (tr.tid=t.tid AND tr.uid='".$mybb->user['uid']."') 
		LEFT JOIN ".TABLE_PREFIX."forums f ON (f.fid = t.fid) 
		WHERE t.visible='1' 
		".ps_GetUnviewable("t")." 
		AND t.closed NOT LIKE 'moved|%' 
		ORDER BY t.views DESC 
		LIMIT 0,".$NumOfRows);

	while ($most_views = $db->fetch_array($query))
	{
		$subject_long = htmlspecialchars_uni($parser->parse_badwords($most_views['subject']));
		$tid = $most_views['tid'];
		$subject = htmlspecialchars_uni(ps_SubjectLength($parser->parse_badwords($most_views['subject']), NULL, true));
		$views = $most_views['views'];
		$lightbulb['folder'] = "off";

		if($mybb->user['uid'])
		{
			if($most_views['dateline'] && $most_views['truid'] == $mybb->user['uid'])
			{
				if($most_views['lastpost'] > $most_views['dateline'])
				{
					$lightbulb['folder'] = "on";
				}
			}
			else
			{
				if($most_views['lastpost'] > $mybb->user['lastvisit'])
				{
					$lightbulb['folder'] = "on";
				}
			}
		}
		
		$threadlink = get_thread_link($tid);
		
		eval("\$readstate_icon = \"".$templates->get("prostats_readstate_icon")."\";");
		eval("\$mostviews_row .= \"".$templates->get("prostats_mostviews_row")."\";");
	}
	eval("\$column_mostviews = \"".$templates->get("prostats_mostviews")."\";");

	return $column_mostviews;
}


function ps_GetMostThanks($NumOfRows)
{
	global $mybb, $db, $templates, $theme, $lang, $ps_align;
	
	if (!$db->field_exists("thxcount","users"))		
	{
		$username = "<div align=\"center\"><small>".$lang->prostats_err_thxplugin."</small></div>";
		eval("\$mostthanks_row .= \"".$templates->get("prostats_mostthanks_row")."\";");
		eval("\$column_mostthanks = \"".$templates->get("prostats_mostthanks")."\";");
		return $column_mostthanks;
	}
	
	$query = $db->query("SELECT uid,username,usergroup,displaygroup,thxcount FROM ".TABLE_PREFIX."users ORDER BY thxcount DESC LIMIT 0,".$NumOfRows);

	while ($most_thanks = $db->fetch_array($query))
	{
		$uid = $most_thanks['uid'];
		$username = ps_FormatName(htmlspecialchars_uni($most_thanks['username']), $most_thanks['usergroup'], $most_thanks['displaygroup']);
		$thxnum = $most_thanks['thxcount'];
		$profilelink = get_profile_link($uid);		
		eval("\$mostthanks_row .= \"".$templates->get("prostats_mostthanks_row")."\";");
	}
	eval("\$column_mostthanks = \"".$templates->get("prostats_mostthanks")."\";");

	return $column_mostthanks;
}


function ps_GetNewMembers($NumOfRows)
{
	global $mybb, $db, $templates, $theme, $lang, $ps_align;

	$query = $db->query("SELECT uid,regdate,username,usergroup,displaygroup FROM ".TABLE_PREFIX."users ORDER BY uid DESC LIMIT 0,".$NumOfRows);

	while ($newest_members = $db->fetch_array($query)) {
		$uid = $newest_members['uid'];
		$profilelink = get_profile_link($uid);
		$username = ps_FormatName(htmlspecialchars_uni($newest_members['username']), $newest_members['usergroup'], $newest_members['displaygroup']);
		if($newest_members['regdate']==0 || !$mybb->settings['ps_date_format_ty'])
		{
			$regdate = $lang->prostats_err_undefind;
		}
		else
		{
			$isty = ps_GetTY($mybb->settings['ps_date_format_ty'], $newest_members['regdate'], $offset="", $ty=1);
			if($isty)
			{
				$regdate = $isty;
			}
			else
			{
				$regdate = my_date($mybb->settings['ps_date_format_ty'], $newest_members['regdate'], NULL, 1);
			}
		}

		eval("\$newmembers_row .= \"".$templates->get("prostats_newmembers_row")."\";");
	}
	eval("\$column_newmembers = \"".$templates->get("prostats_newmembers")."\";");

	return $column_newmembers;
}


function ps_GetTopDownloads($NumOfRows)
{
	global $mybb, $db, $templates, $theme, $lang, $parser, $ps_align;
	
	$query = $db->query("
		SELECT p.subject,a.pid,a.downloads,a.filename 
		FROM ".TABLE_PREFIX."attachments a 
		LEFT JOIN ".TABLE_PREFIX."posts p ON (p.pid = a.pid) 
		LEFT JOIN ".TABLE_PREFIX."threads t ON (t.tid = p.tid) 
		WHERE t.visible='1' 
		".ps_GetUnviewable("t")." 
		AND t.closed NOT LIKE 'moved|%'
		AND a.thumbnail = '' 
		GROUP BY p.pid 
		ORDER BY a.downloads DESC 
		LIMIT 0,".$NumOfRows);
		
	$query_icon = $db->query("SELECT extension,icon FROM ".TABLE_PREFIX."attachtypes");
	while ($result_icon = $db->fetch_array($query_icon))
	{
		$mimicon[$result_icon['extension']] = $result_icon['icon'];
	}
	
	while ($top_downloads = $db->fetch_array($query))
	{
		$subject_long = htmlspecialchars_uni($parser->parse_badwords($top_downloads['subject']));
		$pid = $top_downloads['pid'];
		$subject = htmlspecialchars_uni(ps_SubjectLength($parser->parse_badwords($top_downloads['subject']), NULL, true));
		$downloadnum = $top_downloads['downloads'];
		$attach_icon =  $mimicon[get_extension($top_downloads['filename'])];

		$postlink = get_post_link($pid)."#pid".$pid;
		
		eval("\$topdownloads_row .= \"".$templates->get("prostats_topdownloads_row")."\";");
	}
	eval("\$column_topdownloads = \"".$templates->get("prostats_topdownloads")."\";");

	return $column_topdownloads;
}

function ps_MakeTable()
{
	global $mybb, $theme, $lang, $templates, $lightbulb, $unread_forums, $ps_align;
	$lang->load("prostats");
	
	$right_cols = $left_cols = $middle_cols = $extra_content = $prostats_copyright = "";
	$num_columns = 0;
	
	$ps_align = $mybb->settings['ps_direction'] ? "left" : "right";
	
	if($mybb->settings['ps_last_topics'] == 1)
	{
		$middle_cols = ps_GetLastTopics($mybb->settings['ps_num_rows']);
		$num_columns = 4;
	}
	
	for($i=1;$i<7;++$i)
	{
		$extra_cell[$i] = $mybb->settings['ps_cell_'.$i];
	}

	$extra_row[1] = $extra_row[2] = $extra_row[3] = 2;
	$extra_cols = 3;
	
	if($extra_cell[5] > 0)
	{
		$trow = "trow2";
		$extra_cols = 3;
		if($extra_cell[6] == 0)
		{
			$extra_row[3] = 1;
			$single_extra_content = ps_GetExtraData($extra_cell[5],true);
			eval("\$extra_content .= \"".$templates->get("prostats_onerowextra")."\";");
		}
		else
		{
			$extra_content_one = ps_GetExtraData($extra_cell[5]);
			$extra_content_two = ps_GetExtraData($extra_cell[6]);
			eval("\$extra_content .= \"".$templates->get("prostats_tworowextra")."\";");
		}
	}

	
	if($extra_cell[3] > 0)
	{
		$trow = "trow1";
		$extra_cols = 2;
		if($extra_cell[4] == 0)
		{
			$extra_row[2] = 1;
			$single_extra_content = ps_GetExtraData($extra_cell[3],true);
			eval("\$extra_content .= \"".$templates->get("prostats_onerowextra")."\";");
		}
		else
		{
			$extra_content_one = ps_GetExtraData($extra_cell[3]);
			$extra_content_two = ps_GetExtraData($extra_cell[4]);
			eval("\$extra_content .= \"".$templates->get("prostats_tworowextra")."\";");
		}
	}
	
	if($extra_cell[1] > 0)
	{
		$trow = "trow2";
		$extra_cols = 1;
		if($extra_cell[2] == 0)
		{
			$extra_row[1] = 1;
			$single_extra_content = ps_GetExtraData($extra_cell[1],true);
			eval("\$extra_content .= \"".$templates->get("prostats_onerowextra")."\";");
		}
		else
		{
			$extra_content_one = ps_GetExtraData($extra_cell[1]);
			$extra_content_two = ps_GetExtraData($extra_cell[2]);
			eval("\$extra_content .= \"".$templates->get("prostats_tworowextra")."\";");
		}
	}
	
	if($mybb->settings['ps_last_topics_pos'])
	{
		$mybb->settings['ps_direction'] ?  $right_cols = $extra_content : $left_cols = $extra_content;
	}
	else
	{
		$mybb->settings['ps_direction'] ? $left_cols = $extra_content : $right_cols = $extra_content;
	}

	$prostats_content = $left_cols . $middle_cols . $right_cols;
	
	if($mybb->settings['ps_trow_message'] != "") {
		$prostats_message = unhtmlentities(htmlspecialchars_uni($mybb->settings['ps_trow_message']));
		if($mybb->settings['ps_trow_message_pos'] == 0) {
			eval("\$trow_message_top = \"".$templates->get("prostats_message")."\";");
		}
		else
		{
			eval("\$trow_message_down = \"".$templates->get("prostats_message")."\";");
		}
	}
	
	if(!$mybb->settings['ps_rmcopyright'])
	{
		$prostats_copyright = "<div style=\"text-align: right; font-size: 10px;\">ProStats by <a href=\"http://www.mybbhelp.ir\" target=\"_blank\">MybbHelp.ir</a></div>";
	}
	
	eval("\$prostats = \"".$templates->get("prostats")."\";");
	return $prostats;
}


function ps_GetExtraData($cellnum,$fullrows=false)
{
	global $mybb;
	
	if($fullrows)
	{
		$rows = ($mybb->settings['ps_num_rows'] + 1);
	}
	else
	{
		$rows = $mybb->settings['ps_num_rows'];
		$rows = (ceil($rows/2)-1);
		if(!(($mybb->settings['ps_num_rows'])%2) && !($cellnum%2)){++$rows;}
	}

	switch($cellnum)
	{
		case 0: $res = ''; break;
		case 1: $res = ps_GetTopPosters($rows); break;
		case 2: $res = ps_GetTopReferrers($rows); break;
		case 3: $res = ps_GetMostReplies($rows); break;
		case 4: $res = ps_GetMostViewed($rows); break;
		case 5: $res = ps_GetMostThanks($rows); break;
		case 6: $res = ps_GetNewMembers($rows); break;
		case 7: $res = ps_GetTopDownloads($rows); break;
		default: $res = ''; NULL;
	}
	
	return $res;
}


function ps_GetUnviewable($name="") {
	$unviewwhere = "";
	$name ? $name .= "." : NULL;
	$unviewable = get_unviewable_forums();
	if($unviewable) {
		$unviewwhere = "AND ".$name."fid NOT IN (".$unviewable.")";
	}
	return $unviewwhere;
}


function ps_FormatName($username, $usergroup, $displaygroup)
{
	global $mybb;

	if($mybb->settings['ps_format_name'] == "1")
	{
		$username = format_name($username, $usergroup, $displaygroup);
	}
	return $username;
}


function ps_FormatNameDb($uid, $username="")
{
	global $mybb, $db;

	if($mybb->settings['ps_format_name'] == "1")
	{
		$query = $db->query("SELECT username,usergroup,displaygroup FROM ".TABLE_PREFIX."users WHERE uid = '".$uid."'");
		$query_array = $db->fetch_array($query);
		$username = format_name($query_array['username'], $query_array['usergroup'], $query_array['displaygroup']);
	}
	else if($username=="")
	{
		$query = $db->query("SELECT username FROM ".TABLE_PREFIX."users WHERE uid = '".$uid."'");
		$query_array = $db->fetch_array($query);
		$username = $query_array['username'];
	}
	
	return $username;
}


function ps_SubjectLength($subject, $length="", $half=false)
{
	global $mybb;
	$length = $length ? intval($length) : intval($mybb->settings['ps_subject_length']);
	$half ? $length = ceil($length/2) : NULL;
	if ($length != 0)
	{
		if (my_strlen($subject) > $length) 
		{
			$subject = my_substr($subject,0,$length) . "...";
		}
	}
	return $subject;
}

function ps_GetTY($format='m-d', $stamp="", $offset="", $ty=1)
{
	global $mybb, $lang, $mybbadmin, $plugins;

	if(!$offset && $offset != '0')
	{
		if($mybb->user['uid'] != 0 && array_key_exists("timezone", $mybb->user))
		{
			$offset = $mybb->user['timezone'];
			$dstcorrection = $mybb->user['dst'];
		}
		else
		{
			$offset = $mybb->settings['timezoneoffset'];
			$dstcorrection = $mybb->settings['dstcorrection'];
		}

		if($dstcorrection == 1)
		{
			++$offset;
			if(my_substr($offset, 0, 1) != "-")
			{
				$offset = "+".$offset;
			}
		}
	}

	if($offset == "-")
	{
		$offset = 0;
	}
	
	$date = gmdate($format, $stamp + ($offset * 3600));
	
	if($format && $ty)
	{
		$stamp = TIME_NOW;
		
		$todaysdate = gmdate($format, $stamp + ($offset * 3600));
		$yesterdaysdate = gmdate($format, ($stamp - 86400) + ($offset * 3600));

		if($todaysdate == $date)
		{
			$date = $lang->today;
			return $date;
		}
		else if($yesterdaysdate == $date)
		{
			$date = $lang->yesterday;
			return $date;
		}
	}
	return false;
}
?>