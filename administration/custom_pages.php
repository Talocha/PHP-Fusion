<?php
/*-------------------------------------------------------+
| PHP-Fusion Content Management System
| Copyright (C) PHP-Fusion Inc
| https://www.php-fusion.co.uk/
+--------------------------------------------------------+
| Filename: custom_pages.php
| Author: Nick Jones (Digitanium)
+--------------------------------------------------------+
| This program is released as free software under the
| Affero GPL license. You can redistribute it and/or
| modify it under the terms of this license which you
| can read by viewing the included agpl.txt or online
| at www.gnu.org/licenses/agpl.html. Removal of this
| copyright header is strictly prohibited without
| written permission from the original author(s).
+--------------------------------------------------------*/
require_once "../maincore.php";
if (!checkrights("CP") || !defined("iAUTH") || !isset($_GET['aid']) || $_GET['aid'] != iAUTH) { redirect("../index.php"); }
require_once THEMES."templates/admin_header.php";
include LOCALE.LOCALESET."admin/custom_pages.php";

if (isset($_COOKIE['custom_pages_tinymce']) && $_COOKIE['custom_pages_tinymce'] == 1 && $settings['tinymce_enabled']) {
	echo "<script language='javascript' type='text/javascript'>advanced();</script>\n";
} else {
	require_once INCLUDES."html_buttons_include.php";
}

if (isset($_GET['status']) && !isset($message)) {
	if ($_GET['status'] == "sn") {
		$message = $locale['410']."<br />\n".$locale['412']."\n";
		$message .= "<a href='".BASEDIR."viewpage.php?page_id=".intval($_GET['pid'])."'>viewpage.php?page_id=".intval($_GET['pid'])."</a>\n";
	} elseif ($_GET['status'] == "su") {
		$message = $locale['411']."<br />\n".$locale['412']."\n";
		$message .= "<a href='".BASEDIR."viewpage.php?page_id=".intval($_GET['pid'])."'>viewpage.php?page_id=".intval($_GET['pid'])."</a>\n";
	} elseif ($_GET['status'] == "del") {
		$message = $locale['413'];
	} elseif ($_GET['status'] == "pw") {
		$message = $locale['global_182'];
	}
	if ($message) {
		$message = "<div class='admin-message alert alert-info m-t-10'>".$message."</div>";
		if ($_GET['status'] == "sn" || $_GET['status'] == "su") {
			echo $message;
		} else {
			echo "<div id='close-message alert alert-info m-t-10'>".$message."</div>\n";
		}
	}
}

if (isset($_POST['save'])) {
	$page_title = stripinput($_POST['page_title']);
	$page_access = isnum($_POST['page_access']) ? $_POST['page_access'] : "0";
	$page_content = addslash($_POST['page_content']);
	$page_keywords = stripinput($_POST['page_keywords']);
	$page_language = stripinput($_POST['page_language']);
	$comments = isset($_POST['page_comments']) ? "1" : "0";
	$ratings = isset($_POST['page_ratings']) ? "1" : "0";
	if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
		if (isset($_POST['page_id']) && isnum($_POST['page_id'])) {
			$result = dbquery("UPDATE ".DB_CUSTOM_PAGES." SET
					page_title='".$page_title."',
					page_access='".$page_access."',
					page_content='".$page_content."',
					page_keywords='".$page_keywords."',
					page_allow_comments='".$comments."',
					page_allow_ratings='".$ratings."',
					page_language='".$page_language."'
					WHERE page_id='".$_POST['page_id']."'");
		} else {
			$result = dbquery("INSERT INTO ".DB_CUSTOM_PAGES." (
					page_title, page_access, page_content, page_keywords, page_allow_comments, page_allow_ratings, page_language
				) VALUES (
					'".$page_title."', '".$page_access."', '".$page_content."', '".$page_keywords."', '".$comments."', '".$ratings."', '".$page_language."'
				)");
			$page_id = dblastid();
			if (isset($_POST['add_link'])) {
				$data = dbarray(dbquery("SELECT link_order FROM ".DB_SITE_LINKS." ".(multilang_table("SL") ? "WHERE link_language='".LANGUAGE."'" : "")." ORDER BY link_order DESC LIMIT 1"));
				$link_order = $data['link_order']+1;
				$result = dbquery("INSERT INTO ".DB_SITE_LINKS." (
						link_name, link_url, link_visibility, link_position, link_window, link_order, link_language
					) VALUES (
						'".$page_title."', 'viewpage.php?page_id=".$page_id."', '".$page_access."', '1', '0', '".$link_order."', '".$page_language."'
					)");
			}
		}
		set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
		if (isset($_POST['page_id']) && isnum($_POST['page_id'])) {
			redirect(FUSION_SELF.$aidlink."&status=su&pid=".$_POST['page_id'], TRUE);
		} else {
			redirect(FUSION_SELF.$aidlink."&status=sn&pid=".$page_id, TRUE);
		}
	} else {
		redirect(FUSION_SELF.$aidlink."&status=pw");
	}
} else if (isset($_POST['delete']) && (isset($_POST['page_id']) && isnum($_POST['page_id']))) {
	$result = dbquery("DELETE FROM ".DB_CUSTOM_PAGES." WHERE page_id='".$_POST['page_id']."'");
	$result = dbquery("DELETE FROM ".DB_SITE_LINKS." WHERE link_url='viewpage.php?page_id=".$_POST['page_id']."'");
	redirect(FUSION_SELF.$aidlink."&status=del");
} else {
	if (isset($_POST['preview'])) {
		$addlink = isset($_POST['add_link']) ? " checked='checked'" : "";
		$page_title = stripinput($_POST['page_title']);
		$page_access = $_POST['page_access'];
		$page_content = stripslash($_POST['page_content']);
		$page_keywords = stripinput($_POST['page_keywords']);
		$page_language = stripslash($_POST['page_language']);
		$comments = isset($_POST['page_comments']) ? " checked='checked'" : "";
		$ratings = isset($_POST['page_ratings']) ? " checked='checked'" : "";
		if (check_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "")) {
			opentable($page_title);
			echo "<div class='panel panel-default'>\n";
			echo "<div class='panel-body'>\n";
			eval("?>".$page_content."<?php ");
			echo "</div>\n</div>\n";
			closetable();
			set_admin_pass(isset($_POST['admin_password']) ? stripinput($_POST['admin_password']) : "");
		} else {
			echo "<div id='close-message'><div class='admin-message alert alert-info m-t-10'>".$locale['global_182']."</div></div>\n";
		}
		$page_content = phpentities($page_content);
	}
	$result = dbquery("SELECT page_id, page_title, page_language FROM ".DB_CUSTOM_PAGES." ".(multilang_table("CP") ? "WHERE page_language='".LANGUAGE."'" : "")." ORDER BY page_title");
	if (dbrows($result) != 0) {
		$edit_opts = array();
		while ($data = dbarray($result)) {
			$edit_opts[$data['page_id']] = "[".$data['page_id']."] ".$data['page_title']."";
		}
		opentable($locale['402']);
		echo "<div style='text-align:center'>\n";
		echo openform('selectform', 'selectform', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));
		echo form_select('', 'page_id', 'page_id', $edit_opts, isset($_POST['page_id']) && isnum($_POST['page_id']) ? $_POST['page_id'] : '', array('placeholder' => $locale['choose'], 'class' => 'pull-left'));
		echo form_button($locale['420'], 'edit', 'edit', $locale['420'], array('class' => 'btn-primary pull-left m-l-10 m-r-10'));
		echo form_button($locale['421'], 'delete', 'delete', $locale['421'], array('class' => 'btn-primary pull-left'));
		echo closeform();
		echo "</div>\n";
		closetable();
	}
	if (isset($_POST['edit']) && (isset($_POST['page_id']) && isnum($_POST['page_id']))) {
		$result = dbquery("SELECT page_id, page_title, page_access, page_content, page_keywords, page_allow_comments, page_allow_ratings, page_language
                FROM ".DB_CUSTOM_PAGES." WHERE page_id='".$_POST['page_id']."' LIMIT 1");
		if (dbrows($result)) {
			$data = dbarray($result);
			$page_title = $data['page_title'];
			$page_access = $data['page_access'];
			$page_language = $data['page_language'];
			$page_content = phpentities(stripslashes($data['page_content']));
			$page_keywords = $data['page_keywords'];
			$comments = ($data['page_allow_comments'] == "1" ? " checked='checked'" : "");
			$ratings = ($data['page_allow_ratings'] == "1" ? " checked='checked'" : "");
			$addlink = "";
		} else {
			redirect(FUSION_SELF.$aidlink);
		}
	}
	if (isset($_POST['page_id']) && isnum($_POST['page_id'])) {
		opentable($locale['401'].": [".$_POST['page_id']."] ".$page_title);
	} else {
		if (!isset($_POST['preview'])) {
			$page_title = "";
			$page_access = "";
			$page_content = "";
			$page_keywords = "";
			$page_language = LANGUAGE;
			$comments = " checked='checked'";
			$ratings = " checked='checked'";
			$addlink = "";
		}
		opentable($locale['400']);
	}
	$user_groups = getusergroups();
	$access_opts = "";
	$sel = "";
	while (list($key, $user_group) = each($user_groups)) {
		$access_opts[$user_group['0']] = $user_group['1'];
	}
	echo openform('inputform', 'inputform', 'post', FUSION_SELF.$aidlink, array('downtime' => 0));
	// port to dynamics now.
	echo "<div class='row m-t-20' >\n";
	echo "<div class='col-xs-12 col-sm-8'>\n";
	echo form_text($locale['422'], 'page_title', 'page_title', $page_title, array('required'=>1));
	echo form_select($locale['432'], 'page_keywords', 'page_keywords', array(), $page_keywords, array('max_length' => 320, 'width'=>'100%', 'tags'=>1, 'multiple' => 1));
	echo form_textarea($locale['424'], 'page_content', 'page_content', $page_content, array('autosize'=>1));
	if (!isset($_COOKIE['custom_pages_tinymce']) || !$_COOKIE['custom_pages_tinymce'] || !$settings['tinymce_enabled']) {
		openside();
		echo "<button type='button' class='btn btn-sm btn-default button m-b-10' value='".$locale['431']."' onclick=\"insertText('page_content', '&lt;!--PAGEBREAK--&gt;');\">".$locale['431']."</button>\n";
		echo "<button type='button' class='btn btn-sm btn-default button m-b-10' value='&lt;?php?&gt;' onclick=\"addText('page_content', '&lt;?php\\n', '\\n?&gt;');\">&lt;?php?&gt;</button>\n";
		echo "<button type='button' class='btn btn-sm btn-default button m-b-10' value='&lt;p&gt;' onclick=\"addText('page_content', '&lt;p&gt;', '&lt;/p&gt;');\">&lt;p&gt;</button>\n";
		echo "<button type='button' class='btn btn-default btn-sm button m-b-10' value='&lt;br /&gt;' onclick=\"insertText('page_content', '&lt;br /&gt;');\">&lt;br /&gt;</button>\n";
		echo display_html("inputform", "page_content", TRUE)."</td>\n";
		closeside();
	}

	echo "</div>\n";
	echo "<div class='col-xs-12 col-sm-4'>\n";
	if (fusion_get_settings('tinymce_enabled')) {
		openside('');
		echo "<div class='strong m-b-10'>".$locale['460']."</div>\n";
		$val = !isset($_COOKIE['custom_pages_tinymce']) || $_COOKIE['custom_pages_tinymce'] == 0 ? $locale['461'] : $locale['462'];
		echo form_button($val, 'tinymce_switch', 'tinymce_switch', $val, array('class'=>'btn-default', 'type'=>'button'));
		add_to_jquery("
		$('#tinymce_switch').bind('click', function() {
			SetTinyMCE(".(!isset($_COOKIE['custom_pages_tinymce']) || $_COOKIE['custom_pages_tinymce'] == 0 ? 1 : 0).");
		});
		");
		closeside();
	}

	openside();
	if (multilang_table("CP")) {
		echo form_select($locale['global_ML100'], 'page_language', 'page_language', $language_opts, $page_language);
	} else {
		echo form_hidden('', 'page_language', 'page_language', $page_language);
	}
	echo form_select($locale['423'], 'page_access', 'page_access', $access_opts, $page_access);
	closeside();
	openside();
	if (!isset($_POST['page_id']) || !isnum($_POST['page_id'])) {
		echo "<label><input type='checkbox' name='add_link' value='1'".$addlink." />  ".$locale['426']."</label><br />\n";
	}
	echo "<label><input type='checkbox' name='page_comments' value='1'".$comments." /> ".$locale['427']."</label>";
	if ($settings['comments_enabled'] == "0") {
		echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
	}
	echo "<br/>\n";
	echo "<label><input type='checkbox' name='page_ratings' value='1'".$ratings." /> ".$locale['428']."</label>\n";
	if ($settings['ratings_enabled'] == "0") {
		echo "<span style='color:red;font-weight:bold;margin-left:3px;'>*</span>";
	}
	closeside();

	if ($settings['comments_enabled'] == "0" || $settings['ratings_enabled'] == "0") {
		$sys = "";
		if ($settings['comments_enabled'] == "0" && $settings['ratings_enabled'] == "0") {
			$sys = $locale['457'];
		} elseif ($settings['comments_enabled'] == "0") {
			$sys = $locale['455'];
		} else {
			$sys = $locale['456'];
		}
		echo "<div style='color:red;font-weight:bold;margin-right:5px;'>*</span>".sprintf($locale['454'], $sys)."</div>\n";
	}


	echo "</div>\n</div>\n";

	if (isset($_POST['page_id']) && isnum($_POST['page_id'])) {
		echo form_hidden('', 'page_id', 'page_id', $_POST['page_id']);
	}
	echo form_button($locale['429'], 'preview', 'preview', $locale['429'], array('class' => 'btn-primary m-r-10'));
	echo form_button($locale['430'], 'save', 'save', $locale['430'], array('class' => 'btn-primary m-r-10'));
	echo closeform();
	closetable();

	add_to_jquery("
    $('#delete').bind('click', function() { confirm('".$locale['450']."'); });
    $('#save, #preview').bind('click', function() {
    var page_title = $('#page_title').val();
    var admin_password = $('#admin_password').val();
    if (page_title =='') { alert('".$locale['451']."'); return false; }
    if (admin_password =='') { alert('".$locale['452']."'); return false; }
    });
    ");
	echo "<script type='text/javascript'>\n";
	if ($settings['tinymce_enabled']) {
		echo "function SetTinyMCE(val) {\n";
		echo "now=new Date();\n"."now.setTime(now.getTime()+1000*60*60*24*365);\n";
		echo "expire=(now.toGMTString());\n"."document.cookie=\"custom_pages_tinymce=\"+escape(val)+\";expires=\"+expire;\n";
		echo "location.href='".FUSION_SELF.$aidlink."';\n"."}\n";
	}
	echo "</script>\n";
}
require_once THEMES."templates/footer.php";
?>