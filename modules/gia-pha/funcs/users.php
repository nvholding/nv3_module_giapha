<?php

/**
 * @Project NUKEVIET 3.0
 * @Author VINADES.,JSC (contact@vinades.vn)
 * @Copyright (C) 2010 VINADES.,JSC. All rights reserved
 * @Createdate 2-10-2010 18:49
 */

if (!defined('NV_IS_MOD_GIA_PHA'))
{
    die('Stop!!!');
}

if (defined('NV_IS_USER'))
{
    if (defined('NV_EDITOR'))
    {
        require_once (NV_ROOTDIR . '/' . NV_EDITORSDIR . '/' . NV_EDITOR . '/nv.php');
    }
    else
    {
        define('NV_EDITOR', 'ckeditor');
        require_once (NV_ROOTDIR . '/' . NV_EDITORSDIR . '/ckeditor/ckeditor_php5.php');
        function nv_aleditor($textareaname, $width = "100%", $height = '450px', $val = '')
        {
            global $module_name, $client_info;

            $CKEditor = new CKEditor();
            $CKEditor->returnOutput = true;

            $editortoolbar = array( array('Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo', '-', 'Link', 'Unlink', 'Anchor', '-', 'Image', 'Flash', 'Table', 'Font', 'FontSize', 'RemoveFormat', 'Templates', 'Maximize'), array('Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote', 'CreateDiv', '-', 'TextColor', 'BGColor', 'SpecialChar', 'Smiley', 'PageBreak', 'Source', 'About'));

            $CKEditor->config['skin'] = 'v2';
            $CKEditor->config['entities'] = false;
            $CKEditor->config['enterMode'] = 2;
            $CKEditor->config['language'] = NV_LANG_INTERFACE;
            $CKEditor->config['toolbar'] = $editortoolbar;
            $CKEditor->config['pasteFromWordRemoveFontStyles'] = true;

            // Path to CKEditor directory, ideally instead of relative dir, use an absolute path:
            //   $CKEditor->basePath = '/ckeditor/'
            // If not set, CKEditor will try to detect the correct path.
            $CKEditor->basePath = NV_BASE_SITEURL . '' . NV_EDITORSDIR . '/ckeditor/';
            // Set global configuration (will be used by all instances of CKEditor).
            if (!empty($width))
            {
                $CKEditor->config['width'] = strpos($width, '%') ? $width : intval($width);
            }

            if (!empty($height))
            {
                $CKEditor->config['height'] = strpos($height, '%') ? $height : intval($height);
            }

            // Change default textarea attributes
            $CKEditor->textareaAttributes = array("cols" => 80, "rows" => 10);

            $val = nv_unhtmlspecialchars($val);
            return $CKEditor->editor($textareaname, $val);
        }

    }

    $post = array();
    $birthday_hour = $birthday_min = $dieday_hour = $dieday_min = 0;

    $post['id'] = $nv_Request->get_int('id', 'post,get', 0);
    if (!empty($post['id']))
    {
        $post_old = $db->sql_fetchrow($db->sql_query("SELECT * FROM `" . NV_PREFIXLANG . "_" . $module_data . "` WHERE `id`=" . $post['id']));
        $post['gid'] = $post_old['gid'];
    }
    else
    {
        $post['gid'] = $nv_Request->get_int('gid', 'post,get', 0);
    }

    $post_gid = $db->sql_fetchrow($db->sql_query("SELECT * FROM `" . NV_PREFIXLANG . "_" . $module_data . "_genealogy` WHERE gid=" . $post['gid']));

    if (empty($post_gid) or $post_gid['userid'] != $user_info['userid'])
    {
        $redirect = "<meta http-equiv=\"Refresh\" content=\"3;URL=" . nv_url_rewrite(NV_BASE_SITEURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&amp;" . NV_NAME_VARIABLE . "=" . $module_name, true) . "\" />";
        nv_info_die($lang_module['error_who_view_title'], $lang_module['error_who_view_title'], $lang_module['error_who_view_content'] . $redirect);
    }

    $post['parentid'] = $nv_Request->get_int('parentid', 'post,get', 0);
    $post['parentid2'] = $nv_Request->get_int('parentid2', 'post,get', 0);

    $post['relationships'] = $nv_Request->get_int('relationships', 'post,get', 1);
    $post['opanniversary'] = $nv_Request->get_int('opanniversary', 'post,get', 0);

    $post['weight'] = $nv_Request->get_int('weight', 'post,get', 1);
    $post['gender'] = $nv_Request->get_int('gender', 'post,get', 1);
    $post['status'] = $nv_Request->get_int('status', 'post,get', 2);

    $post['full_name'] = filter_text_input('full_name', 'post', '');

    $post['code'] = filter_text_input('code', 'post', '');
    $post['name1'] = filter_text_input('name1', 'post', '');
    $post['name2'] = filter_text_input('name2', 'post', '');

    $content = $nv_Request->get_string('content', 'post', '');
    $post['content'] = defined('NV_EDITOR') ? nv_nl2br($content, '') : nv_nl2br(nv_htmlspecialchars(strip_tags($content)), '<br />');

    $post['life'] = $nv_Request->get_int('life', 'post,get', 0);
    $post['burial'] = filter_text_input('burial', 'post', '');

    $post['image'] = "";

    $birthday_date = filter_text_input('birthday_date', 'post', '');
    $dieday_date = filter_text_input('dieday_date', 'post', '');
    if (!empty($birthday_date) and preg_match("/^([0-9]{1,2})\\/([0-9]{1,2})\/([0-9]{4})$/", $birthday_date, $m))
    {
        $birthday_hour = $nv_Request->get_int('birthday_hour', 'post', 0);
        $birthday_min = $nv_Request->get_int('birthday_min', 'post', 0);
        $life_birthday = $m[3];

        $post['birthday_data'] = $m[3] . "-" . $m[2] . "-" . $m[1] . " " . $birthday_hour . ":" . $birthday_min . ":00";
        $post['birthday_date'] = $birthday_date;
    }
    else
    {
        $post['birthday_data'] = "0000-00-00 00:00:00";
    }

    if (!empty($dieday_date) and preg_match("/^([0-9]{1,2})\\/([0-9]{1,2})\/([0-9]{4})$/", $dieday_date, $m))
    {
        $dieday_hour = $nv_Request->get_int('dieday_hour', 'post', 0);
        $dieday_min = $nv_Request->get_int('dieday_min', 'post', 0);
        $life_dieday = $m[3];

        $post['dieday_data'] = $m[3] . "-" . $m[2] . "-" . $m[1] . " " . $dieday_hour . ":" . $dieday_min . ":00";
        $post['dieday_date'] = $dieday_date;
    }
    else
    {
        $post['dieday_data'] = "0000-00-00 00:00:00";
    }
    $post['life'] = ($post['birthday_data'] != "0000-00-00 00:00:00" and $post['dieday_data'] != "0000-00-00 00:00:00") ? $life_dieday - $life_birthday : 0;

    if ($nv_Request->get_int('save', 'post') == 1 and !empty($post['full_name']) and $post['life'] < 200)
    {
        $post['userid'] = $user_info['userid'];
        if (empty($post['id']))
        {
            if ($post['parentid'])
            {
                list($lev) = $db->sql_fetchrow($db->sql_query("SELECT `lev` FROM `" . NV_PREFIXLANG . "_" . $module_data . "` WHERE `id`=" . $post['parentid']));
                $post['lev'] = intval($lev) + 1;
            }
            else
            {
                $post['lev'] = 1;
                $post['weight'] = 1;
            }
            $post['actanniversary'] = ($post['status'] == 0 and $post['dieday_data'] != '0000-00-00 00:00:00') ? 1 : 0;
            $post['id'] = $db->sql_query_insert_id("INSERT INTO `" . NV_PREFIXLANG . "_" . $module_data . "` 
				(`id`, `gid`, `parentid`, `parentid2`, `weight`, `lev`, `relationships`, `gender`, `status`, `anniversary`, `actanniversary`, 
				`alias`,`full_name`, `code`, `name1`, `name2`, 
				`birthday`, `dieday`, `life`, `burial`, `content`, `image`, `userid`, `add_time`, `edit_time`) VALUES
				(NULL, " . $post['gid'] . ", " . $post['parentid'] . ", " . $post['parentid2'] . ", " . $post['weight'] . ", " . $post['lev'] . ", " . $post['relationships'] . ", " . $post['gender'] . ", " . $post['status'] . ",'', '" . $post['actanniversary'] . "', 
				'',	" . $db->dbescape($post['full_name']) . ", " . $db->dbescape($post['code']) . ", " . $db->dbescape($post['name1']) . ", " . $db->dbescape($post['name2']) . ", 
				'" . $post['birthday_data'] . "', '" . $post['dieday_data'] . "', '" . $post['life'] . "', " . $db->dbescape($post['burial']) . ", " . $db->dbescape($post['content']) . ", " . $db->dbescape($post['image']) . ", '" . $post['userid'] . "', " . NV_CURRENTTIME . ", " . NV_CURRENTTIME . ")");

            if ($post['id'])
            {
                $alias = change_alias($post['full_name']);
                $query = "UPDATE `" . NV_PREFIXLANG . "_" . $module_data . "` SET `alias`=" . $db->dbescape($alias) . " WHERE `id` =" . $post['id'] . "";
                if (!$db->sql_query($query))
                {
                    $query = "UPDATE `" . NV_PREFIXLANG . "_" . $module_data . "` SET `alias`=" . $db->dbescape($alias . "-" . $post['id']) . " WHERE `id` =" . $post['id'] . "";
                    $db->sql_query($query);
                }
                $query = "UPDATE `" . NV_PREFIXLANG . "_" . $module_data . "_genealogy` SET `number`=`number`+1 WHERE `gid` =" . $post['gid'] . "";
                $db->sql_query($query);

                nv_fix_genealogy_user($post['parentid']);
                nv_del_moduleCache($module_name);
                echo '<script type="text/javascript">
					parent.location="' . NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=shows&gid=' . $post['gid'] . '";
    				</script>';
                die();
            }
        }
        else
        {
            $post['actanniversary'] = ($post['status'] == $post_old['status'] and $post['status'] == 0) ? 0 : $post_old['actanniversary'];
            $post['anniversary'] = ($post['dieday_data'] == '0000-00-00 00:00:00') ? $post['anniversary'] : '';
            $query = "UPDATE `" . NV_PREFIXLANG . "_" . $module_data . "` SET `parentid2`=" . $post['parentid2'] . ", `weight`=" . $post['weight'] . ", `relationships` =  " . $post['relationships'] . ", `gender`=" . $post['gender'] . ", `status`= " . $post['status'] . ", `actanniversary`= " . $db->dbescape($post['actanniversary']) . ", 
			`full_name`=" . $db->dbescape($post['full_name']) . ", `code`=" . $db->dbescape($post['code']) . ", `name1`=" . $db->dbescape($post['name1']) . ", `name2`=" . $db->dbescape($post['name2']) . ", 
			`birthday`='" . $post['birthday_data'] . "', `dieday`='" . $post['dieday_data'] . "', `life`='" . $post['life'] . "', `burial`=" . $db->dbescape($post['burial']) . ", `content`=" . $db->dbescape($post['content']) . ",
			`edit_time`=UNIX_TIMESTAMP( ) WHERE `id` =" . $post['id'] . "";
            $db->sql_query($query);
            if ($db->sql_affectedrows() > 0)
            {
                $alias = change_alias($post['full_name']);
                if ($post_old['alias'] != $alias)
                {
                    $query = "UPDATE `" . NV_PREFIXLANG . "_" . $module_data . "` SET `alias`=" . $db->dbescape($alias) . " WHERE `id` =" . $post['id'] . "";
                    if (!$db->sql_query($query))
                    {
                        $query = "UPDATE `" . NV_PREFIXLANG . "_" . $module_data . "` SET `alias`=" . $db->dbescape($alias . "-" . $post['id']) . " WHERE `id` =" . $post['id'] . "";
                        $db->sql_query($query);
                    }
                }
                nv_fix_genealogy_user($post['parentid']);
                nv_del_moduleCache($module_name);

                $op2 = ($post['opanniversary']) ? "anniversary" : "shows";

                echo '<script type="text/javascript">
					parent.location="' . NV_BASE_SITEURL . 'index.php?' . NV_LANG_VARIABLE . '=' . NV_LANG_DATA . '&' . NV_NAME_VARIABLE . '=' . $module_name . '&' . NV_OP_VARIABLE . '=' . $op2 . '&gid=' . $post['gid'] . '";
    				</script>';
                die();
            }
        }
    }
    elseif ($post['id'])
    {
        $post = $db->sql_fetchrow($db->sql_query("SELECT * FROM `" . NV_PREFIXLANG . "_" . $module_data . "` WHERE `id`=" . $post['id'] . ""), 2);

        preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $post['birthday'], $datetime);
        if ($post['birthday'] != '0000-00-00 00:00:00')
        {
            $post['birthday_date'] = $datetime[3] . "/" . $datetime[2] . "/" . $datetime[1];
        }
        else
        {
            $post['birthday_date'] = "";
        }
        $birthday_hour = intval($datetime[4]);
        $birthday_min = intval($datetime[5]);

        preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})/", $post['dieday'], $datetime);
        if ($post['dieday'] != '0000-00-00 00:00:00')
        {
            $post['dieday_date'] = $datetime[3] . "/" . $datetime[2] . "/" . $datetime[1];

        }
        else
        {
            $post['dieday_date'] = "";
        }
        $post['opanniversary'] = $nv_Request->get_int('opanniversary', 'post,get', 0);
        $dieday_hour = intval($datetime[4]);
        $dieday_min = intval($datetime[5]);
    }
    elseif ($post['parentid'] == 0)
    {
        $post['gender'] = 1;
        $post['status'] = 2;
    }
    elseif ($post['parentid'] > 0)
    {
        list($maxweight) = $db->sql_fetchrow($db->sql_query("SELECT max(`weight`) FROM `" . NV_PREFIXLANG . "_" . $module_data . "` WHERE `parentid`=" . $post['parentid'] . " AND `relationships`=" . $post['relationships']));
        $post['weight'] = intval($maxweight) + 1;
    }

    if (!empty($post['content']))
        $post['content'] = nv_htmlspecialchars($post['content']);

    if (!empty($post['image']) and file_exists(NV_UPLOADS_REAL_DIR . "/" . $module_name . "/" . $post['image']))
    {
        $post['image'] = NV_BASE_SITEURL . NV_UPLOADS_DIR . "/" . $module_name . "/" . $post['image'];
    }

    if ($post['relationships'] == 2)
    {
        $post['gender'] = 2;
    }

    $page_title = ($post['id'] == 0) ? $lang_module['u_add'] : $lang_module['u_edit'];

    $lang_module['burial_address'] = ($post['status'] == 0) ? $lang_module['u_burial'] : $lang_module['u_address'];

    $my_head .= "<link rel=\"stylesheet\" href=\"" . NV_BASE_SITEURL . "themes/" . $global_config['module_theme'] . "/css/tab_info.css\" type=\"text/css\" />";
    $my_head .= "<link type=\"text/css\" href=\"" . NV_BASE_SITEURL . "js/ui/jquery.ui.core.css\" rel=\"stylesheet\" />\n";
    $my_head .= "<link type=\"text/css\" href=\"" . NV_BASE_SITEURL . "js/ui/jquery.ui.theme.css\" rel=\"stylesheet\" />\n";
    $my_head .= "<link type=\"text/css\" href=\"" . NV_BASE_SITEURL . "js/ui/jquery.ui.datepicker.css\" rel=\"stylesheet\" />\n";

    $my_head .= "<script type=\"text/javascript\" src=\"" . NV_BASE_SITEURL . "js/jquery/jquery.autocomplete.js\"></script>\n";
    $my_head .= "<script type=\"text/javascript\" src=\"" . NV_BASE_SITEURL . "js/ui/jquery.ui.core.min.js\"></script>\n";
    $my_head .= "<script type=\"text/javascript\" src=\"" . NV_BASE_SITEURL . "js/ui/jquery.ui.datepicker.min.js\"></script>\n";
    $my_head .= "<script type=\"text/javascript\" src=\"" . NV_BASE_SITEURL . "js/language/jquery.ui.datepicker-" . NV_LANG_INTERFACE . ".js\"></script>\n";

    $xtpl = new XTemplate("users.tpl", NV_ROOTDIR . "/themes/" . $module_info['template'] . "/modules/" . $module_file);

    $xtpl->assign('LANG', $lang_module);
    $xtpl->assign('OP', $op);

    $xtpl->assign('NV_SITE_COPYRIGHT', "" . $global_config['site_name'] . " [" . $global_config['site_email'] . "] ");
    $xtpl->assign('NV_SITE_NAME', $global_config['site_name']);
    $xtpl->assign('NV_SITE_TITLE', "" . $global_config['site_name'] . " " . NV_TITLEBAR_DEFIS . " " . $lang_global['admin_page'] . " " . NV_TITLEBAR_DEFIS . " " . $module_info['custom_title'] . "");
    $xtpl->assign('NV_BASE_SITEURL', NV_BASE_SITEURL);
    $xtpl->assign('NV_ADMINDIR', NV_ADMINDIR);
    $xtpl->assign('MODULE_NAME', $module_name);
    $xtpl->assign('MODULE_FILE', $module_file);
    $xtpl->assign('TEMPLATE', $module_info['template']);

    $xtpl->assign('NV_LANG_VARIABLE', NV_LANG_VARIABLE);
    $xtpl->assign('NV_LANG_INTERFACE', NV_LANG_INTERFACE);
    $xtpl->assign('NV_NAME_VARIABLE', NV_NAME_VARIABLE);
    $xtpl->assign('NV_OP_VARIABLE', NV_OP_VARIABLE);
    $xtpl->assign('NV_LANG_VARIABLE', NV_LANG_VARIABLE);
    $xtpl->assign('NV_SITE_TIMEZONE_OFFSET', round(NV_SITE_TIMEZONE_OFFSET / 3600));
    $xtpl->assign('NV_CURRENTTIME', nv_date("T", NV_CURRENTTIME));
    $xtpl->assign('NV_COOKIE_PREFIX', $global_config['cookie_prefix']);

    if ($post['parentid'] > 0 and $post['relationships'] == 1)
    {
        $sql = "SELECT `id`,`full_name` FROM `" . NV_PREFIXLANG . "_" . $module_data . "` WHERE `parentid`=" . $post['parentid'] . " AND `id`!=" . $post['id'] . " AND `relationships`=2 ORDER BY `weight`";
        $result = $db->sql_query($sql);
        while ($row = $db->sql_fetchrow($result))
        {
            $row['selected'] = ($row['id'] == $post['parentid2']) ? ' selected="selected"' : '';
            $xtpl->assign('PARENTID2', $row);
            $xtpl->parse('main.parentid2');
        }
    }

    $array_relationships = array(1 => $lang_module['u_relationships_1'], 2 => $lang_module['u_relationships_2'], 3 => $lang_module['u_relationships_3']);
    foreach ($array_relationships as $value => $title)
    {
        $arrayName = array('value' => $value, 'title' => $title, 'checked' => ($value == $post['relationships']) ? ' checked="checked"' : '');
        $xtpl->assign('RELATIONSHIPS', $arrayName);

        $xtpl->parse('main.root.relationships');
    }
    for ($i = 1; $i <= 100; $i++)
    {
        $arrayName = array('value' => $i, 'title' => $i, 'selected' => ($i == $post['weight']) ? ' selected="selected"' : '');
        $xtpl->assign('WEIGHT', $arrayName);

        $xtpl->parse('main.root.weight');
    }

    $array_gender = array(0 => $lang_module['u_gender_0'], 1 => $lang_module['u_gender_1'], 2 => $lang_module['u_gender_2']);
    foreach ($array_gender as $value => $title)
    {
        $temp = array('value' => $value, 'title' => $title, 'checked' => ($value == $post['gender']) ? ' checked="checked"' : '');
        $xtpl->assign('GENDER', $temp);
        $xtpl->parse('main.gender');
    }

    $array_status = array(0 => $lang_module['u_status_0'], 1 => $lang_module['u_status_1'], 2 => $lang_module['u_status_2']);
    foreach ($array_status as $value => $title)
    {
        $arrayName = array('value' => $value, 'title' => $title, 'checked' => ($value == $post['status']) ? ' checked="checked"' : '');
        $xtpl->assign('STATUS', $arrayName);
        $xtpl->parse('main.status');
    }

    for ($i = 0; $i <= 120; $i++)
    {
        $arrayName = array('value' => $i, 'title' => $i, 'checked' => ($i == $post['life']) ? ' selected="selected"' : '');
        $xtpl->assign('LIFE', $arrayName);

        $xtpl->parse('main.life');
    }

    if (defined('NV_EDITOR') and nv_function_exists('nv_aleditor'))
    {
        $post['content'] = nv_aleditor('content', '100%', '200px', $post['content']);
    }
    else
    {
        $post['content'] = "<textarea style=\"width: 100%\" name=\"content\" id=\"content\" cols=\"20\" rows=\"15\">" . $post['content'] . "</textarea>";
    }

    $post['birthday_hour'] = $post['birthday_min'] = '';
    for ($i = 0; $i <= 23; $i++)
    {
        $post['birthday_hour'] .= "<option value=\"" . $i . "\"" . (($i == $birthday_hour) ? " selected=\"selected\"" : "") . ">" . str_pad($i, 2, "0", STR_PAD_LEFT) . "</option>\n";
    }
    for ($i = 0; $i < 60; $i++)
    {
        $post['birthday_min'] .= "<option value=\"" . $i . "\"" . (($i == $birthday_min) ? " selected=\"selected\"" : "") . ">" . str_pad($i, 2, "0", STR_PAD_LEFT) . "</option>\n";
    }

    $post['dieday_hour'] = $post['dieday_min'] = '';
    for ($i = 0; $i <= 23; $i++)
    {
        $post['dieday_hour'] .= "<option value=\"" . $i . "\"" . (($i == $dieday_hour) ? " selected=\"selected\"" : "") . ">" . str_pad($i, 2, "0", STR_PAD_LEFT) . "</option>\n";
    }
    for ($i = 0; $i < 60; $i++)
    {
        $post['dieday_min'] .= "<option value=\"" . $i . "\"" . (($i == $dieday_min) ? " selected=\"selected\"" : "") . ">" . str_pad($i, 2, "0", STR_PAD_LEFT) . "</option>\n";
    }
    $xtpl->assign('DATA', $post);
    $xtpl->assign('UPLOAD_CURRENT', NV_UPLOADS_DIR . '/' . $module_name);

    if ($post['parentid'] > 0)
    {
        $xtpl->parse('main.root');
        list($full_name_parentid) = $db->sql_fetchrow($db->sql_query("SELECT `full_name` FROM `" . NV_PREFIXLANG . "_" . $module_data . "` WHERE `id`=" . $post['parentid']));
        $page_title .= ": " . $full_name_parentid . " ---> " . $post['full_name'];
    }

    if (!empty($my_head))
    {
        $xtpl->assign('NV_ADD_MY_HEAD', $my_head);
        $xtpl->parse('main.nv_add_my_head');
    }

    if (!empty($page_title))
    {
        $xtpl->assign('PAGE_TITLE', $page_title);
        $xtpl->parse('main.empty_page_title');
    }

    if (NV_LANG_INTERFACE == 'vi' and NV_LANG_DATA == 'vi')
    {
        $xtpl->parse('main.nv_if_mudim');
    }
    $xtpl->assign('NV_GENPASS', nv_genpass());
    $xtpl->parse('main');
    $contents = $xtpl->text('main');

    include (NV_ROOTDIR . "/includes/header.php");
    echo $contents;
    include (NV_ROOTDIR . "/includes/footer.php");
}
else
{
    $redirect = "<meta http-equiv=\"Refresh\" content=\"2;URL=" . nv_url_rewrite(NV_BASE_SITEURL . "index.php?" . NV_LANG_VARIABLE . "=" . NV_LANG_DATA . "&" . NV_NAME_VARIABLE . "=users&" . NV_OP_VARIABLE . "=login&nv_redirect=" . nv_base64_encode($client_info['selfurl']), true) . "\" />";
    nv_info_die($lang_module['error_login_title'], $lang_module['error_login_title'], $lang_module['error_login_content'] . $redirect);
}
?>