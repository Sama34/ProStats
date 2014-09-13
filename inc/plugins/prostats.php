<?php
/*
 ___________________________________________________
|													|
| Plugin ProStats 1.7.5								|
| (c) 2008-2010 by SaeedGh (SaeehGhMail@Gmail.com)	|
| Website: http://www.mybbhelp.ir					|
| Last edit: 2010-05-22								|
|___________________________________________________|

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.

*/

if (!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.");
}


$plugins->add_hook('global_start', 'prostats_run_global');
$plugins->add_hook('pre_output_page', 'prostats_run_pre_output');
$plugins->add_hook('index_start', 'prostats_run_index');
$plugins->add_hook('portal_start', 'prostats_run_portal');
$plugins->add_hook('xmlhttp', 'prostats_run_ajax');


function prostats_info()
{
	global $mybb, $db;
	
	$settings_link = '';
	
	$query = $db->simple_select('settinggroups', '*', "name='ProStats'");

	if (count($db->fetch_array($query)))
	{
		$settings_link = '(<a href="index.php?module=config&action=change&search=prostats" style="color:#FF1493;">Settings</a>)';
	}
	
	return array(
		'name'			=>	'<img border="0" src="../images/MybbHelp_small.gif" align="absbottom" /> <img border="0" src="../images/ProStats.gif" align="absbottom" /> ProStats',
		'title'			=>	'ProStats',
		'description'	=>	'Professional stats for MyBB. ' . $settings_link,
		'website'		=>	'http://www.mybbhelp.ir',
		'author'		=>	'SaeedGh',
		'authorsite'	=>	'http://www.mybbhelp.ir',
		'version'		=>	'1.7.5',
		'guid'			=>	'124b68d05dcdaf6b7971050baddf340f',
		'compatibility'	=>	'14*,16*'
	);
}


function prostats_activate()
{
	global $db;
	
	require MYBB_ROOT.'inc/adminfunctions_templates.php';
	find_replace_templatesets('index', '#{\$header}(\r?)\n#', "{\$header}\n{\$ps_header_index}\n");
	find_replace_templatesets('index', '#{\$forums}(\r?)\n#', "{\$forums}\n{\$ps_footer_index}\n");
	find_replace_templatesets('portal', '#{\$header}(\r?)\n#', "{\$header}\n{\$ps_header_portal}\n");
	find_replace_templatesets('portal', '#{\$footer}(\r?)\n#', "{\$ps_footer_portal}\n{\$footer}\n");
	
	$extra_cells = "select\n0=--\n1=Top posters\n2=Top referrers\n3=Most replies\n4=Most viewed\n5=Most thanks\n6=New members\n7=Top downloads";

	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats",
		"template" => "
<script type=\"text/javascript\">
<!--

var spinner=null;

function prostats_reload()
{
	if(spinner){return false;}
	this.spinner = new ActivityIndicator(\"body\", {image: \"images/spinner_big.gif\"});
	new Ajax.Request(\'xmlhttp.php?action=prostats_reload&my_post_key=\'+my_post_key, {method: \'post\',postBody:\"\", onComplete:prostats_done});
	return false;
}

function prostats_done(request)
{
	if(this.spinner)
	{
		this.spinner.destroy();
		this.spinner = \'\';
	}
	if(request.responseText.match(/<error>(.*)<\\\/error>/))
	{
		message = request.responseText.match(/<error>(.*)<\\\/error>/);
		alert(message[1]);
	}
	else if(request.responseText)
	{
		$(\"prostats_table\").innerHTML = request.responseText;
	}
}
-->
</script>

		<div id=\"prostats_table\">		
		<table width=\"100%\" border=\"0\" cellspacing=\"{\$theme[borderwidth]}\" cellpadding=\"0\" class=\"tborder\">
		<thead>
		<tr><td colspan=\"{\$num_columns}\">
			<table border=\"0\" cellspacing=\"0\" cellpadding=\"{\$theme[tablespace]}\" width=\"100%\">
			<tr class=\"thead\">
			<td><strong>{\$lang->prostats_prostats}</strong></td>
			<td style=\"text-align:{\$ps_ralign};\"><a href=\"\" onclick=\"return prostats_reload();\">{\$lang->prostats_reload} <img src=\"{\$mybb->settings[\'bburl\']}/images/ps_reload.gif\" style=\"vertical-align:middle;\" alt=\"\" /></a></td>
			</tr>
			</table>
		</td>
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
		<!-- You can hide the copyright link by changing settings of the plugin. -->
		{\$prostats_copyright}
		<br />
		</div>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_readstate_icon",
		"template" => "<img src=\"{\$mybb->settings[\'bburl\']}/images/ps_mini{\$lightbulb[\'folder\']}.gif\" style=\"vertical-align:middle;\" alt=\"\" />&nbsp;",
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
		"template" => "<td class=\"{\$trow}\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr>{\$single_extra_content}</tr></table></td>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
		"tid" => "NULL",
		"title" => "prostats_tworowextra",
		"template" => "<td class=\"{\$trow}\"><table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\"><tr>{\$extra_content_one}</tr><tr>{\$extra_content_two}</tr></table></td>",
		"sid" => "-1",
		);
	$db->insert_query("templates", $templatearray);
	
	$ps_group = array(
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
		"name"			=> "ps_active",
		"title"			=> "Activate",
		"description"	=> "Do you want to activate the plugin?",
		"optionscode"	=> "yesno",
		"value"			=> '1',
		"disporder"		=> '1',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_ignoreforums",
		"title"			=> "Ignore list",
		"description"	=> "Forums not to be shown on ProStats. Seperate with comma. (e.g. 1,3,12)",
		"optionscode"	=> "text",
		"value"			=> '',
		"disporder"		=> '3',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_index",
		"title"			=> "Show in index",
		"description"	=> "Show the ProStats table in the index page.",
		"optionscode"	=> "yesno",
		"value"			=> '1',
		"disporder"		=> '4',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_portal",
		"title"			=> "Show in portal",
		"description"	=> "Show the ProStats table in the portal page.",
		"optionscode"	=> "yesno",
		"value"			=> '0',
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
		"description"	=> "Maximum length of topic/post subjects. (Input 0 to remove the limitation)",
		"optionscode"	=> "text",
		"value"			=> '25',
		"disporder"		=> '30',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_num_rows",
		"title"			=> "Number of rows",
		"description"	=> "How much items must be shown? (Input an odd number greater than or equal to 3)",
		"optionscode"	=> "text",
		"value"			=> '11',
		"disporder"		=> '41',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_date_format",
		"title"			=> "Date and Time format",
		"description"	=> "The format of Date and Time in the ProStats table (<a href=\"http://php.net/manual/en/function.date.php\">More details</a>).",
		"optionscode"	=> "text",
		"value"			=> 'm-d, H:i',
		"disporder"		=> '42',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_date_format_ty",
		"title"			=> "Replace format",
		"description"	=> "A part of Date and Time format that can be replaced with \"Yesterday\" or \"Today\".",
		"optionscode"	=> "text",
		"value"			=> 'm-d',
		"disporder"		=> '43',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_trow_message",
		"title"			=> "Message block",
		"description"	=> "This is a block on top/bottom of the ProStats table that you can put your HTML contents in it.",
		"optionscode"	=> "textarea",
		"value"			=> '',
		"disporder"		=> '45',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_trow_message_pos",
		"title"			=> "Message block position",
		"description"	=> "The position of message block in the ProStats table.",
		"optionscode"	=> "select\n0=Top\n1=Down (Default)",
		"value"			=> '1',
		"disporder"		=> '46',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_last_topics",
		"title"			=> "Show last topics",
		"description"	=> "Show last topics in the ProStats table.",
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
		"description"	=> "The position of the Last topics field.",
		"optionscode"	=> "select\n0=Left\n1=Right",
		"value"			=> '0',
		"disporder"		=> '60',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_cell_1",
		"title"			=> "Extra cell 1 (Top-Left)",
		"description"	=> "<div style=\"width:98px;height:43px;overflow:hidden;text-direction:rtl;margin-top:5px;\"><img style=\"float:left;\" src=\"../images/ps_cells.gif\" /><img style=\"float:left;margin-top:-178px;margin-left:-28px;\" src=\"../images/ps_cells.gif\" /></div>",
		"optionscode"	=> $extra_cells,
		"value"			=> '4',
		"disporder"		=> '62',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_cell_2",
		"title"			=> "Extra cell 2 (Bottom-Left)",
		"description"	=> "<div style=\"width:98px;height:43px;overflow:hidden;text-direction:rtl;margin-top:5px;\"><img style=\"float:left;\" src=\"../images/ps_cells.gif\" /><img style=\"float:left;margin-top:-159px;margin-left:-28px;\" src=\"../images/ps_cells.gif\" /></div>",
		"optionscode"	=> $extra_cells,
		"value"			=> '2',
		"disporder"		=> '64',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_cell_3",
		"title"			=> "Extra cell 3 (Top-Middle)",
		"description"	=> "<div style=\"width:98px;height:43px;overflow:hidden;text-direction:rtl;margin-top:5px;\"><img style=\"float:left;\" src=\"../images/ps_cells.gif\" /><img style=\"float:left;margin-top:-178px;margin-left:-14px;\" src=\"../images/ps_cells.gif\" /></div>",
		"optionscode"	=> $extra_cells,
		"value"			=> '3',
		"disporder"		=> '66',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_cell_4",
		"title"			=> "Extra cell 4 (Bottom-Middle)",
		"description"	=> "<div style=\"width:98px;height:43px;overflow:hidden;text-direction:rtl;margin-top:5px;\"><img style=\"float:left;\" src=\"../images/ps_cells.gif\" /><img style=\"float:left;margin-top:-159px;margin-left:-14px;\" src=\"../images/ps_cells.gif\" /></div>",
		"optionscode"	=> $extra_cells,
		"value"			=> '1',
		"disporder"		=> '68',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_cell_5",
		"title"			=> "Extra cell 5 (Top-Right)",
		"description"	=> "<div style=\"width:98px;height:43px;overflow:hidden;text-direction:rtl;margin-top:5px;\"><img style=\"float:left;\" src=\"../images/ps_cells.gif\" /><img style=\"float:left;margin-top:-178px;margin-left:0px;\" src=\"../images/ps_cells.gif\" /></div>",
		"optionscode"	=> $extra_cells,
		"value"			=> '5',
		"disporder"		=> '70',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_cell_6",
		"title"			=> "Extra cell 6 (Bottom-Right)",
		"description"	=> "<div style=\"width:98px;height:43px;overflow:hidden;text-direction:rtl;margin-top:5px;\"><img style=\"float:left;\" src=\"../images/ps_cells.gif\" /><img style=\"float:left;margin-top:-159px;margin-left:0px;\" src=\"../images/ps_cells.gif\" /></div>",
		"optionscode"	=> $extra_cells,
		"value"			=> '6',
		"disporder"		=> '72',
		"gid"			=> intval($gid),
	);

	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_global_tag",
		"title"			=> "Active global tag",
		"description"	=> "So you can edit themes and insert &lt;ProStats&gt; tag wherever you want to show the stats",
		"optionscode"	=> "yesno",
		"value"			=> '0',
		"disporder"		=> '75',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_xml_feed",
		"title"			=> "Active XML feed",
		"description"	=> "Output the stats in XML format ( index.php?stats=xml )",
		"optionscode"	=> "yesno",
		"value"			=> '0',
		"disporder"		=> '77',
		"gid"			=> intval($gid),
	);
	
	$ps[]= array(
		"sid"			=> "NULL",
		"name"			=> "ps_show_copyright",
		"title"			=> "Show Copyright link",
		"description"	=> "Select Yes to show copyright link or No to hide it. Also you can delete {\$prostats_copyright} to totally disable this feature.",
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
	
	$db->delete_query("settings","name IN ('ps_active','ps_ignoreforums','ps_index','ps_portal','ps_position','ps_format_name','ps_subject_length','ps_num_rows','ps_date_format','ps_date_format_ty','ps_trow_message','ps_trow_message_pos','ps_last_topics','ps_last_topics_cells','ps_last_topics_pos','ps_cell_1','ps_cell_2','ps_cell_3','ps_cell_4','ps_cell_5','ps_cell_6','ps_global_tag','ps_xml_feed','ps_show_copyright')");
	$db->delete_query("settinggroups","name='ProStats'");
	
	rebuild_settings();
}


function prostats_run_global()
{
	global $mybb;
	
	if (isset($GLOBALS['templatelist']))
	{
		if ($mybb->settings['ps_active'] && defined('THIS_SCRIPT'))
		{
			if (($mybb->settings['ps_index'] && THIS_SCRIPT == 'index.php')
				|| ($mybb->settings['ps_portal'] && THIS_SCRIPT == 'portal.php')
				|| $mybb->settings['ps_global_tag'])
			{
				$GLOBALS['templatelist'] .= ",prostats,prostats_readstate_icon,prostats_newmembers,prostats_newmembers_row,prostats_topposters,prostats_topposters_row,prostats_topreferrers,prostats_topreferrers_row,prostats_mostthanks,prostats_mostthanks_row,prostats_newthreads,prostats_newthreads_row,prostats_newthreads_specialchar,prostats_mostreplies,prostats_mostreplies_row,prostats_mostviews,prostats_mostviews_row,prostats_newposts,prostats_newposts_row,prostats_topdownloads,prostats_topdownloads_row,prostats_message,prostats_onerowextra,prostats_tworowextra";
			}
		}
	}
}


function prostats_run_index($force = false)
{
	global $mybb, $parser, $prostats_tbl, $ps_header_index, $ps_footer_index, $ps_header_portal, $ps_footer_portal;

	if (!$mybb->settings['ps_active']) {return false;}

	if (!is_object($parser))
	{
		require_once MYBB_ROOT.'inc/class_parser.php';
		$parser = new postParser;
	}
	
	if (ceil($mybb->settings['ps_num_rows']) != $mybb->settings['ps_num_rows'] || ceil($mybb->settings['ps_subject_length']) != $mybb->settings['ps_subject_length']){return false;}
	if (intval($mybb->settings['ps_num_rows']) < 3) {return false;}
	
	if (strtolower($mybb->input['stats'])=='xml' && $mybb->settings['ps_xml_feed'])
	{
		prostats_run_feed();
		exit;
	}
	
	if (!$mybb->settings['ps_index'] && !$force) {return false;}
	
	$numofrows = $mybb->settings['ps_num_rows'];
	$prostats_tbl = "";
	
	$prostats_tbl = ps_MakeTable();

	if ($mybb->settings['ps_position'] == 0)
	{
		$ps_header_index = $prostats_tbl;
	}
	else if ($mybb->settings['ps_position'] == 1)
	{
		$ps_footer_index = $prostats_tbl;
	}
}


function prostats_run_portal()
{
	global $mybb, $parser, $ps_header_index, $ps_footer_index, $ps_header_portal, $ps_footer_portal;
	
	if (!$mybb->settings['ps_active']) {return false;}

	if (!is_object($parser))
	{
		require_once MYBB_ROOT.'inc/class_parser.php';
		$parser = new postParser;
	}
	
	if (ceil($mybb->settings['ps_num_rows']) != $mybb->settings['ps_num_rows'] || ceil($mybb->settings['ps_subject_length']) != $mybb->settings['ps_subject_length']){return false;}
	
	if (!$mybb->settings['ps_portal']) {return false;}
	if (intval($mybb->settings['ps_num_rows']) < 3) {return false;}
	
	$numofrows = $mybb->settings['ps_num_rows'];
	$prostats_tbl = "";
	
	$prostats_tbl = ps_MakeTable();

	if ($mybb->settings['ps_position'] == 0)
	{
		$ps_header_portal = $prostats_tbl;
	}
	else if ($mybb->settings['ps_position'] == 1)
	{
		$ps_footer_portal = $prostats_tbl;
	}
}


function prostats_run_pre_output($contents)
{
	global $mybb, $parser, $prostats_tbl, $ps_header_index, $ps_footer_index, $ps_header_portal, $ps_footer_portal;

	if (!$mybb->settings['ps_active']) {return false;}
	
	if (!is_object($parser))
	{
		require_once MYBB_ROOT.'inc/class_parser.php';
		$parser = new postParser;
	}
	
	if (ceil($mybb->settings['ps_num_rows']) != $mybb->settings['ps_num_rows'] || ceil($mybb->settings['ps_subject_length']) != $mybb->settings['ps_subject_length']){return false;}
	if (intval($mybb->settings['ps_num_rows']) < 3) {return false;}
	
	if (!$mybb->settings['ps_global_tag']){
		$contents = str_replace('<ProStats>', '', $contents);
		return false;
	}
	
	$numofrows = $mybb->settings['ps_num_rows'];
	$prostats_tbl = "";
	
	$prostats_tbl = ps_MakeTable();

	$contents = str_replace('<ProStats>', $prostats_tbl, $contents);
}


function ps_GetLastTopics($NumOfRows, $feed=false)
{
	global $mybb, $db, $templates, $theme, $lang, $unviewwhere, $parser, $lightbulb, $trow, $newthreads_cols_name, $newthreads_cols, $colspan, $feeditem;

	if (!is_object($parser))
	{
		require_once MYBB_ROOT.'inc/class_parser.php';
		$parser = new postParser;
	}
	
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
					$newthreads_cols_name .= "<td>".$lang->prostats_datetime."&nbsp;</td>";
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
	
	$loop_counter = 0;
	
	while ($newest_threads = $db->fetch_array($query))
	{
		$tid = $newest_threads['tid'];
		$fuid = $newest_threads['uid'];
		$fid = $newest_threads['fid'];
		$lightbulb['folder'] = "off";
		$newthreads_cols = "";
		
		if ($mybb->user['uid'])
		{
			if ($newest_threads['dateline'] && $newest_threads['truid'] == $mybb->user['uid'])
			{
				if ($newest_threads['lastpost'] > $newest_threads['dateline'])
				{
					$lightbulb['folder'] = "on";
				}
			}
			else
			{
				if ($newest_threads['lastpost'] > $mybb->user['lastvisit'])
				{
					$lightbulb['folder'] = "on";
				}
			}
		}
		
		$dateformat = $mybb->settings['ps_date_format'];
		
		if ($active_cells['Date'])
		{
			$isty = ps_GetTY($mybb->settings['ps_date_format_ty'], $newest_threads['lastpost'], $offset="", $ty=1);
			if ($isty)
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
		
		if ($active_cells['New_threads'])
		{
			$parsed_subject = $parser->parse_badwords($newest_threads['subject']);
			$subject = htmlspecialchars_uni(ps_SubjectLength($parsed_subject));
			$subject_long = htmlspecialchars_uni($parsed_subject);
			$threadlink = get_thread_link($tid,NULL,"lastpost");
			eval("\$readstate_icon = \"".$templates->get("prostats_readstate_icon")."\";");
			eval("\$newthreads_specialchar = \"".$templates->get("prostats_newthreads_specialchar")."\";");
		}
		
		if ($active_cells['Starter'])
		{
			$username = ps_FormatNameDb($fuid, htmlspecialchars_uni($newest_threads['username']));
			$profilelink = get_profile_link($fuid);
		}
		
		if ($active_cells['Last_sender'])
		{
			$lastposter_uname = ps_FormatNameDb($newest_threads['lastposteruid'], htmlspecialchars_uni($newest_threads['lastposter']));
			$lastposter_profile = get_profile_link($newest_threads['lastposteruid']);
		}
		
		if ($active_cells['Forum'])
		{
			$forumlink = get_forum_link($fid);
			$forumname_long = $parser->parse_badwords(strip_tags($newest_threads['name']));
			$forumname = htmlspecialchars_uni(ps_SubjectLength($forumname_long, NULL, true));		
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
					$newthreads_cols.= "<td><a href=\"".$forumlink."\" title=\"".$forumname_long."\">".$forumname."</a></td>";
					break;
				default: NULL;
			}
		}

		eval("\$newthreads_row .= \"".$templates->get("prostats_newthreads_row")."\";");
		
		
		if ($feed)
		{
			$feeditem[$loop_counter]['tid'] = $tid;
			$feeditem[$loop_counter]['fuid'] = $fuid;
			$feeditem[$loop_counter]['fid'] = $fid;
			$feeditem[$loop_counter]['bulb'] = $lightbulb['folder'];
			$feeditem[$loop_counter]['lasttime'] = $newest_threads['lastpost'];
			$feeditem[$loop_counter]['datetime'] = $datetime;
			
			if ($active_cells['New_threads'])
			{
				$feeditem[$loop_counter]['subject'] = $subject;
				$feeditem[$loop_counter]['subject_long'] = $subject_long;
			}
			
			if ($active_cells['Starter'])
			{
				$feeditem[$loop_counter]['username'] = htmlspecialchars_uni($newest_threads['username']);
				$feeditem[$loop_counter]['username_formed'] = $username;
			}
			
			if ($active_cells['Last_sender'])
			{
				$feeditem[$loop_counter]['lastposter_uid'] = $newest_threads['lastposteruid'];
				$feeditem[$loop_counter]['lastposter_uname'] = htmlspecialchars_uni($newest_threads['lastposter']);
				$feeditem[$loop_counter]['lastposter_uname_formed'] = $lastposter_uname;
			}
			
			if ($active_cells['Forum'])
			{
				$feeditem[$loop_counter]['forumname'] = $forumname;
				$feeditem[$loop_counter]['forumname_long'] = $forumname_long;
			}
		}
		
		++$loop_counter;
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

	if (!is_object($parser))
	{
		require_once MYBB_ROOT.'inc/class_parser.php';
		$parser = new postParser;
	}
	
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

		if ($mybb->user['uid'])
		{
			if ($most_replies['dateline'] && $most_replies['truid'] == $mybb->user['uid'])
			{
				if ($most_replies['lastpost'] > $most_replies['dateline'])
				{
					$lightbulb['folder'] = "on";
				}
			}
			else
			{
				if ($most_replies['lastpost'] > $mybb->user['lastvisit'])
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
	
	if (!is_object($parser))
	{
		require_once MYBB_ROOT.'inc/class_parser.php';
		$parser = new postParser;
	}
	
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

		if ($mybb->user['uid'])
		{
			if ($most_views['dateline'] && $most_views['truid'] == $mybb->user['uid'])
			{
				if ($most_views['lastpost'] > $most_views['dateline'])
				{
					$lightbulb['folder'] = "on";
				}
			}
			else
			{
				if ($most_views['lastpost'] > $mybb->user['lastvisit'])
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
		$mostthanks_row .= "<tr class=\"smalltext\"><td colspan=\"2\" align=\"center\"><small>".$lang->prostats_err_thxplugin."</small></td></tr>";
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
		if ($newest_members['regdate']==0 || !$mybb->settings['ps_date_format_ty'])
		{
			$regdate = $lang->prostats_err_undefind;
		}
		else
		{
			$isty = ps_GetTY($mybb->settings['ps_date_format_ty'], $newest_members['regdate'], $offset="", $ty=1);
			if ($isty)
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
	
	if (!is_object($parser))
	{
		require_once MYBB_ROOT.'inc/class_parser.php';
		$parser = new postParser;
	}
	
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
	global $mybb, $theme, $lang, $templates, $parser, $lightbulb, $unread_forums, $ps_align;
	$lang->load("prostats");
	
	$right_cols = $left_cols = $middle_cols = $extra_content = $extra_content_1_2 = $extra_content_3_4 = $extra_content_5_6 = $prostats_copyright = "";
	$num_columns = 0;
	
	$ps_align = $lang->settings['rtl'] ? "right" : "left";
	$ps_ralign = $lang->settings['rtl'] ? "left" : "right";
	
	if ($mybb->settings['ps_last_topics'] == 1)
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
	
	if ($extra_cell[5] > 0)
	{
		$trow = "trow2";
		$extra_cols = 3;
		if ($extra_cell[6] == 0)
		{
			$extra_row[3] = 1;
			$single_extra_content = ps_GetExtraData($extra_cell[5],true);
			eval("\$extra_content_5_6 = \"".$templates->get("prostats_onerowextra")."\";");
		}
		else
		{
			$extra_content_one = ps_GetExtraData($extra_cell[5]);
			$extra_content_two = ps_GetExtraData($extra_cell[6]);
			eval("\$extra_content_5_6 = \"".$templates->get("prostats_tworowextra")."\";");
		}
	}

	
	if ($extra_cell[3] > 0)
	{
		$trow = "trow1";
		$extra_cols = 2;
		if ($extra_cell[4] == 0)
		{
			$extra_row[2] = 1;
			$single_extra_content = ps_GetExtraData($extra_cell[3],true);
			eval("\$extra_content_3_4 = \"".$templates->get("prostats_onerowextra")."\";");
		}
		else
		{
			$extra_content_one = ps_GetExtraData($extra_cell[3]);
			$extra_content_two = ps_GetExtraData($extra_cell[4]);
			eval("\$extra_content_3_4 = \"".$templates->get("prostats_tworowextra")."\";");
		}
	}
	
	if ($extra_cell[1] > 0)
	{
		$trow = "trow2";
		$extra_cols = 1;
		if ($extra_cell[2] == 0)
		{
			$extra_row[1] = 1;
			$single_extra_content = ps_GetExtraData($extra_cell[1],true);
			eval("\$extra_content_1_2 = \"".$templates->get("prostats_onerowextra")."\";");
		}
		else
		{
			$extra_content_one = ps_GetExtraData($extra_cell[1]);
			$extra_content_two = ps_GetExtraData($extra_cell[2]);
			eval("\$extra_content_1_2 = \"".$templates->get("prostats_tworowextra")."\";");
		}
	}
	
	if ($lang->settings['rtl'])
	{
		$extra_content = $extra_content_5_6 . $extra_content_3_4 . $extra_content_1_2;
		$mybb->settings['ps_last_topics_pos'] ? $right_cols = $extra_content : $left_cols = $extra_content;
	}
	else
	{
		$extra_content = $extra_content_1_2 . $extra_content_3_4 . $extra_content_5_6;
		$mybb->settings['ps_last_topics_pos'] ? $left_cols = $extra_content : $right_cols = $extra_content;
	}

	$prostats_content = $left_cols . $middle_cols . $right_cols;
	
	if ($mybb->settings['ps_trow_message'] != "") {
		$prostats_message = unhtmlentities(htmlspecialchars_uni($mybb->settings['ps_trow_message']));
		if ($mybb->settings['ps_trow_message_pos'] == 0) {
			eval("\$trow_message_top = \"".$templates->get("prostats_message")."\";");
		}
		else
		{
			eval("\$trow_message_down = \"".$templates->get("prostats_message")."\";");
		}
	}
	
	$ps_cr_dyn = "<div id=\"prostats_cpr\" style=\"text-align:right;font-family:tahoma;font-size:9px;\"><a href=\"http://www.mybbhelp.ir/\" target=\"_blank\">پشتیبانی فارسی MyBB</a></div><script type=\"text/javascript\">/*<!--*/$(\"prostats_cpr\").update(\"ProStats by <a href=\\\"http://www.mybbhelp.ir/\\\" target=\\\"_blank\\\">MybbHelp.ir</a>\");/*-->*/</script>";
	$ps_cr_stk = "<div id=\"prostats_cpr\" style=\"text-align:right;font-family:tahoma;font-size:9px;\">ProStats by  <a href=\"http://www.mybbhelp.ir/\" target=\"_blank\">MybbHelp.ir</a></div>";
	
	if ($mybb->settings['ps_show_copyright']){
		$prostats_copyright = (THIS_SCRIPT == 'xmlhttp.php' ? $ps_cr_stk : $ps_cr_dyn);
	}else{
		$prostats_copyright = "<div id=\"prostats_cpr\" style=\"position:absolute;top:-500px;\"><h1><strong><a href=\"http://www.mybbhelp.ir/\" target=\"_blank\">پشتیبانی فارسی MyBB</a></strong></h1></div><script type=\"text/javascript\">/*<!--*/$(\"prostats_cpr\").update(\"\");/*-->*/</script>";
	}
	
	eval("\$prostats = \"".$templates->get("prostats")."\";");
	return $prostats;
}


function ps_GetExtraData($cellnum,$fullrows=false)
{
	global $mybb;
	
	if ($fullrows)
	{
		$rows = ($mybb->settings['ps_num_rows'] + 1);
	}
	else
	{
		$rows = $mybb->settings['ps_num_rows'];
		$rows = (ceil($rows/2)-1);
		if (!(($mybb->settings['ps_num_rows'])%2) && !($cellnum%2)){++$rows;}
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


function ps_GetUnviewable($name="")
{
	global $mybb;
	$unviewwhere = $comma = '';
	$name ? $name .= '.' : NULL;
	$unviewable = get_unviewable_forums();
	
	if ($mybb->settings['ps_ignoreforums'])
	{
		$ignoreforums = explode(',', $mybb->settings['ps_ignoreforums']);
		
		if (count($ignoreforums))
		{
			$unviewable ? $unviewable .= ',' : NULL;
			
			foreach($ignoreforums as $fid)
			{
				$unviewable .= $comma."'".intval($fid)."'";
				$comma = ',';
			}
		}
	}
	
	if ($unviewable)
	{
		$unviewwhere = "AND ".$name."fid NOT IN (".$unviewable.")";
	}

	return $unviewwhere;
}


function ps_FormatName($username, $usergroup, $displaygroup)
{
	global $mybb;

	if ($mybb->settings['ps_format_name'] == '1')
	{
		$username = format_name($username, $usergroup, $displaygroup);
	}
	return $username;
}


function ps_FormatNameDb($uid, $username="")
{
	global $mybb, $db;

	if ($mybb->settings['ps_format_name'] == "1")
	{
		$query = $db->query("SELECT username,usergroup,displaygroup FROM ".TABLE_PREFIX."users WHERE uid = '".$uid."'");
		$query_array = $db->fetch_array($query);
		$username = format_name($query_array['username'], $query_array['usergroup'], $query_array['displaygroup']);
	}
	else if ($username=="")
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
			$subject = my_substr($subject,0,$length) . '...';
		}
	}
	return $subject;
}


function ps_GetTY($format='m-d', $stamp='', $offset='', $ty=1)
{
	global $mybb, $lang, $mybbadmin, $plugins;

	if (!$offset && $offset != '0')
	{
		if ($mybb->user['uid'] != 0 && array_key_exists('timezone', $mybb->user))
		{
			$offset = $mybb->user['timezone'];
			$dstcorrection = $mybb->user['dst'];
		}
		else
		{
			$offset = $mybb->settings['timezoneoffset'];
			$dstcorrection = $mybb->settings['dstcorrection'];
		}

		if ($dstcorrection == 1)
		{
			++$offset;
			if (my_substr($offset, 0, 1) != '-')
			{
				$offset = '+'.$offset;
			}
		}
	}

	if ($offset == '-')
	{
		$offset = 0;
	}
	
	$date = gmdate($format, $stamp + ($offset * 3600));
	
	if ($format && $ty)
	{
		$stamp = TIME_NOW;
		
		$todaysdate = gmdate($format, $stamp + ($offset * 3600));
		$yesterdaysdate = gmdate($format, ($stamp - 86400) + ($offset * 3600));

		if ($todaysdate == $date)
		{
			$date = $lang->today;
			return $date;
		}
		else if ($yesterdaysdate == $date)
		{
			$date = $lang->yesterday;
			return $date;
		}
	}
	return false;
}


function prostats_run_ajax()
{
	global $mybb, $lang, $parser, $prostats_tbl;
	
	if (!$mybb->settings['ps_active']) {return false;}
	
	if (!is_object($parser))
	{
		require_once MYBB_ROOT.'inc/class_parser.php';
		$parser = new postParser;
	}
	
	if ($mybb->input['action'] != "prostats_reload" || $mybb->request_method != "post"){return false;exit;}

	if (!verify_post_check($mybb->input['my_post_key'], true))
	{
		xmlhttp_error($lang->invalid_post_code);
	}	
	
	prostats_run_index(true);
	
	header('Content-Type: text/xml');
	echo $prostats_tbl;
}


function prostats_run_feed()
{
	global $mybb, $db, $templates, $theme, $lang, $unviewwhere, $parser, $lightbulb, $trow, $newthreads_cols_name, $newthreads_cols, $colspan, $feeditem;
	
	if (!$mybb->settings['ps_active'] || !$mybb->settings['ps_xml_feed']) {return false;}
	
	if (!is_object($parser))
	{
		require_once MYBB_ROOT.'inc/class_parser.php';
		$parser = new postParser;
	}
	
	$seo = 0;
	
	if ($mybb->settings['seourls'] == "yes" || ($mybb->settings['seourls'] == "auto" && $_SERVER['SEO_SUPPORT'] == 1))
	{
		$seo = 1;
	}
	
	ps_GetLastTopics($mybb->settings['ps_num_rows'], true);
	
	//echo '<pre>';print_r($feeditem);echo '</pre>';exit;//just for test! ;-)

	/*
	$feeditem
	{
		[tid]
		[fuid]
		[fid]
		[bulb]
		[lasttime]
		[datetime]
		[subject]
		[username]
		[username_formed]
		[lastposter_uid]
		[lastposter_uname]
		[lastposter_uname_formed]
		[lastposter_profile]
		[forumname]
		[forumname_long]
	}
	*/
	
	$xml_feed = '<?xml version="1.0" encoding="UTF-8"?>';
	$xml_feed .= '<ProStats>';
	$xml_feed .= '<bburl>'.$mybb->settings['bburl'].'</bburl>';
	$xml_feed .= '<seo>'.intval($seo).'</seo>';
	
	foreach($feeditem as $key => $value)
	{
		$xml_feed .= '<record num="'.($key+1).'">';
		$xml_feed .= '<tid>'.$feeditem[$key]['tid'].'</tid>';
		$xml_feed .= '<fuid>'.$feeditem[$key]['fuid'].'</fuid>';
		$xml_feed .= '<fid>'.$feeditem[$key]['fid'].'</fid>';
		$xml_feed .= '<bulb>'.$feeditem[$key]['bulb'].'</bulb>';
		$xml_feed .= '<lasttime>'.$feeditem[$key]['lasttime'].'</lasttime>';
		$xml_feed .= '<datetime>'.htmlspecialchars_uni($feeditem[$key]['datetime']).'</datetime>';
		$xml_feed .= '<subject>'.htmlspecialchars_uni($feeditem[$key]['subject']).'</subject>';
		$xml_feed .= '<longsubject>'.htmlspecialchars_uni($feeditem[$key]['subject_long']).'</longsubject>';
		$xml_feed .= '<uname>'.htmlspecialchars_uni($feeditem[$key]['username']).'</uname>';
		$xml_feed .= '<uname2>'.htmlspecialchars_uni($feeditem[$key]['username_formed']).'</uname2>';
		$xml_feed .= '<luid>'.$feeditem[$key]['lastposter_uid'].'</luid>';
		$xml_feed .= '<luname>'.htmlspecialchars_uni($feeditem[$key]['lastposter_uname']).'</luname>';
		$xml_feed .= '<luname2>'.htmlspecialchars_uni($feeditem[$key]['lastposter_uname_formed']).'</luname2>';
		$xml_feed .= '<fname>'.htmlspecialchars_uni($feeditem[$key]['forumname']).'</fname>';
		$xml_feed .= '<ffullname>'.htmlspecialchars_uni($feeditem[$key]['forumname_long']).'</ffullname>';
		$xml_feed .= '</record>';
	}

	$xml_feed .= '</ProStats>';
	
	
	if ($mybb->settings['gzipoutput'] == 1)
	{
		if (version_compare(PHP_VERSION, '4.2.0', '>='))
		{
			$xml_feed = gzip_encode($xml_feed, $mybb->settings['gziplevel']);
		}
		else
		{
			$xml_feed = gzip_encode($xml_feed);
		}
	}
	
	header("content-type: text/xml");
	echo $xml_feed;
}


?>