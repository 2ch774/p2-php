<?php
/*
    p2 - ���[�U�ݒ�ҏW�C���^�t�F�[�X
*/

include_once './conf/conf.inc.php';  // ��{�ݒ�
require_once (P2_LIBRARY_DIR . '/dataphp.class.php');

$_login->authorize(); // ���[�U�F��

if (!empty($_POST['submit_save']) || !empty($_POST['submit_default'])) {
    if (!isset($_POST['csrfid']) or $_POST['csrfid'] != P2Util::getCsrfId()) {
        die('p2 error: �s���ȃ|�X�g�ł�');
    }
}

//=====================================================================
// �O����
//=====================================================================

// {{{ ���ۑ��{�^����������Ă�����A�ݒ��ۑ�

if (!empty($_POST['submit_save'])) {

    // �l�̓K���`�F�b�N�A����
    
    // �g����
    $_POST['conf_edit'] = array_map('trim', $_POST['conf_edit']);
    
    // �I�����ɂȂ����� �� �f�t�H���g����
    notSelToDef();
    
    // empty �� �f�t�H���g����
    emptyToDef();

    // ���̐��� or 0 �łȂ����� �� �f�t�H���g����
    notIntExceptMinusToDef();

    /**
     * �f�t�H���g�l $conf_user_def �ƕύX�l $_POST['conf_edit'] �̗��������݂��Ă��āA
     * �f�t�H���g�l�ƕύX�l���قȂ�ꍇ�̂ݐݒ�ۑ�����i���̑��̃f�[�^�͕ۑ����ꂸ�A�j�������j
     */
    $conf_save = array();
    foreach ($conf_user_def as $k => $v) {
        if (isset($conf_user_def[$k]) && isset($_POST['conf_edit'][$k])) {
            if ($conf_user_def[$k] != $_POST['conf_edit'][$k]) {
                $conf_save[$k] = $_POST['conf_edit'][$k];
            }
        }
    }

    // �V���A���C�Y���āA�f�[�^PHP�`���ŕۑ�
    $cont = serialize($conf_save);
    if (DataPhp::writeDataPhp($_conf['conf_user_file'], $cont, $_conf['conf_user_perm'])) {
        $_info_msg_ht .= "<p>���ݒ���X�V�ۑ����܂���</p>";
        // �ύX������΁A�����f�[�^���X�V���Ă���
        $_conf = array_merge($_conf, $conf_user_def);
        if (is_array($conf_save)) {
            $_conf = array_merge($_conf, $conf_save);
        }
    } else {
        $_info_msg_ht .= "<p>�~�ݒ���X�V�ۑ��ł��܂���ł���</p>";
    }

// }}}
// {{{ ���f�t�H���g�ɖ߂��{�^����������Ă�����

} elseif (!empty($_POST['submit_default'])) {
    if (@unlink($_conf['conf_user_file'])) {
        $_info_msg_ht .= "<p>���ݒ���f�t�H���g�ɖ߂��܂���</p>";
        // �ύX������΁A�����f�[�^���X�V���Ă���
        $_conf = array_merge($_conf, $conf_user_def);
        if (is_array($conf_save)) {
            $_conf = array_merge($_conf, $conf_save);
        }
    }
}

// }}}

//=====================================================================
// �v�����g�ݒ�
//=====================================================================
$ptitle = '���[�U�ݒ�ҏW';

$csrfid = P2Util::getCsrfId();

//=====================================================================
// �v�����g
//=====================================================================
// �w�b�_HTML���v�����g
P2Util::header_nocache();
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>{$ptitle}</title>\n
EOP;

if (empty($_conf['ktai'])) {
    echo <<<EOP
    <script type="text/javascript" src="js/basic.js"></script>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">\n
EOP;
}

if (empty($_conf['ktai'])) {
    @include("./style/style_css.inc");
    @include("./style/edit_conf_user_css.inc");
}

$body_at = ($_conf['ktai']) ? $_conf['k_colors'] : ' onLoad="top.document.title=self.document.title;"';
echo <<<EOP
</head>
<body{$body_at}>\n
EOP;

// PC�p�\��
if (empty($_conf['ktai'])) {
    echo <<<EOP
<p id="pan_menu"><a href="editpref.php">�ݒ�Ǘ�</a> &gt; {$ptitle}</p>\n
EOP;
}

// PC�p�\��
if (empty($_conf['ktai'])) {
    $htm['form_submit'] = <<<EOP
        <tr class="group">
            <td colspan="3" align="center">
                <input type="submit" name="submit_save" value="�ύX��ۑ�����">
                <input type="submit" name="submit_default" value="�f�t�H���g�ɖ߂�" onClick="if (!window.confirm('���[�U�ݒ���f�t�H���g�ɖ߂��Ă���낵���ł����H�i��蒼���͂ł��܂���j')) {return false;}"><br>
            </td>
        </tr>\n
EOP;
// �g�їp�\��
} else {
    $htm['form_submit'] = <<<EOP
        <input type="submit" name="submit_save" value="�ύX��ۑ�����">\n
EOP;
}

// ��񃁃b�Z�[�W�\��
if (!empty($_info_msg_ht)) {
    echo $_info_msg_ht;
    $_info_msg_ht = "";
}

echo <<<EOP
<form method="POST" action="{$_SERVER['PHP_SELF']}" target="_self" accept-charset="{$_conf['accept_charset']}">
    {$_conf['k_input_ht']}
    <input type="hidden" name="detect_hint" value="����">
    <input type="hidden" name="csrfid" value="{$csrfid}">\n
EOP;

// PC�p�\���itable�j
if (empty($_conf['ktai'])) {
    echo '<table id="edit_conf_user" cellspacing="0">'."\n";
}

echo $htm['form_submit'];

// PC�p�\���itable�j
if (empty($_conf['ktai'])) {
    echo <<<EOP
        <tr>
            <td>�ϐ���</td>
            <td>�l</td>
            <td>����</td>
        </tr>\n
EOP;
}

// {{{ be.2ch.net �A�J�E���g

echo getGroupSepaHtml('be.2ch.net �A�J�E���g');

echo getEditConfHtml('be_2ch_code', '<a href="http://be.2ch.net/" target="_blank">be.2ch.net</a>�̔F�؃R�[�h(�p�X���[�h�ł͂Ȃ�)');
echo getEditConfHtml('be_2ch_mail', 'be.2ch.net�̓o�^���[���A�h���X');

// }}}
// {{{ PATH

echo getGroupSepaHtml('PATH');

//echo getEditConfHtml('first_page', '�E�������ɍŏ��ɕ\�������y�[�W�B�I�����C��URL���B');
echo getEditConfHtml('brdfile_online', 
    '���X�g�̎w��i�I�����C��URL�j<br>
    ���X�g���I�����C��URL���玩���œǂݍ��ށB
    �w���� menu.html �`���A2channel.brd �`���̂ǂ���ł��悢�B
    <!-- �K�v�Ȃ���΁A�󔒂ɁB --><br>

    2ch��{ <a href="http://menu.2ch.net/bbsmenu.html" target="_blank">http://menu.2ch.net/bbsmenu.html</a><br>
    2ch + �O��BBS <a href="http://azlucky.s25.xrea.com/2chboard/bbsmenu.html" target="_blank">http://azlucky.s25.xrea.com/2chboard/bbsmenu.html</a><br>
    ');


// }}}
// {{{ subject

echo getGroupSepaHtml('subject');

echo getEditConfHtml('refresh_time', '�X���b�h�ꗗ�̎����X�V�Ԋu (���w��B0�Ȃ玩���X�V���Ȃ�)');

echo getEditConfHtml('sb_show_motothre', '�X���b�h�ꗗ�Ŗ��擾�X���ɑ΂��Č��X���ւ̃����N�i�E�j��\�� (����, ���Ȃ�)');
echo getEditConfHtml('sb_show_one', '�X���b�h�ꗗ�i�\���j��>>1��\�� (����, ���Ȃ�, �j���[�X�n�̂�)');
echo getEditConfHtml('sb_show_spd', '�X���b�h�ꗗ�ł��΂₳�i���X�Ԋu�j��\�� (����, ���Ȃ�)');
echo getEditConfHtml('sb_show_ikioi', '�X���b�h�ꗗ�Ő����i1��������̃��X���j��\�� (����, ���Ȃ�)');
echo getEditConfHtml('sb_show_fav', '�X���b�h�ꗗ�ł��C�ɃX���}�[�N����\�� (����, ���Ȃ�)');
echo getEditConfHtml('sb_sort_ita', '�\���̃X���b�h�ꗗ�ł̃f�t�H���g�̃\�[�g�w��');
echo getEditConfHtml('sort_zero_adjust', '�V���\�[�g�ł́u�����Ȃ��v�́u�V�����[���v�ɑ΂���\�[�g�D�揇�� (���, ����, ����)');
echo getEditConfHtml('cmp_dayres_midoku', '�����\�[�g���ɐV�����X�̂���X����D�� (����, ���Ȃ�)');
echo getEditConfHtml('k_sb_disp_range', '�g�щ{�����A��x�ɕ\������X���̐�');
echo getEditConfHtml('viewall_kitoku', '�����X���͕\�������Ɋւ�炸�\�� (����, ���Ȃ�)');

echo getEditConfHtml('sb_ttitle_max_len', '�X���b�h�ꗗ�ŕ\������X���b�h�^�C�g���̒����̏�� (0�Ŗ�����)');
echo getEditConfHtml('sb_ttitle_trim_len', '�X���b�h�^�C�g���������̏�����z�����Ƃ��A���̒����܂Ő؂�l�߂�');
echo getEditConfHtml('sb_ttitle_trim_pos', '�X���b�h�^�C�g����؂�l�߂�ʒu (�擪, ����, ����)');

// }}}
// {{{ read

echo getGroupSepaHtml('read');

echo getEditConfHtml('respointer', '�X�����e�\�����A���ǂ̉��R�O�̃��X�Ƀ|�C���^�����킹�邩');
echo getEditConfHtml('before_respointer', 'PC�{�����A�|�C���^�̉��R�O�̃��X����\�����邩');
echo getEditConfHtml('before_respointer_new', '�V���܂Ƃߓǂ݂̎��A�|�C���^�̉��R�O�̃��X����\�����邩');
echo getEditConfHtml('rnum_all_range', '�V���܂Ƃߓǂ݂ň�x�ɕ\�����郌�X��');
echo getEditConfHtml('preview_thumbnail', '�摜URL�̐�ǂ݃T���l�C����\���i����, ���Ȃ�)');
echo getEditConfHtml('pre_thumb_limit', '�摜URL�̐�ǂ݃T���l�C������x�ɕ\�����鐧���� (0�Ŗ�����)');
//echo getEditConfHtml('preview_thumbnail', '�摜�T���l�C���̏c�̑傫�����w�� (�s�N�Z��)');
////echo getEditConfHtml('pre_thumb_width', '�摜�T���l�C���̉��̑傫�����w�� (�s�N�Z��)');
echo getEditConfHtml('iframe_popup', 'HTML�|�b�v�A�b�v (����, ���Ȃ�, p�ł���, �摜�ł���)');
//echo getEditConfHtml('iframe_popup_delay', 'HTML�|�b�v�A�b�v�̕\���x������ (�b)');
echo getEditConfHtml('ext_win_target', '�O���T�C�g���փW�����v���鎞�ɊJ���E�B���h�E�̃^�[�Q�b�g�� (����:&quot;&quot;, �V��:&quot;_blank&quot;)');
echo getEditConfHtml('bbs_win_target', 'p2�Ή�BBS�T�C�g���ŃW�����v���鎞�ɊJ���E�B���h�E�̃^�[�Q�b�g�� (����:&quot;&quot;, �V��:&quot;_blank&quot;)');
echo getEditConfHtml('bottom_res_form', '�X���b�h�����ɏ������݃t�H�[����\�� (����, ���Ȃ�)');
echo getEditConfHtml('quote_res_view', '���p���X��\�� (����, ���Ȃ�)');

echo getEditConfHtml('k_rnum_range', '�g�щ{�����A��x�ɕ\�����郌�X�̐�');
echo getEditConfHtml('ktai_res_size', '�g�щ{�����A��̃��X�̍ő�\���T�C�Y');
echo getEditConfHtml('ktai_ryaku_size', '�g�щ{�����A���X���ȗ������Ƃ��̕\���T�C�Y');
echo getEditConfHtml('before_respointer_k', '�g�щ{�����A�|�C���^�̉��R�O�̃��X����\�����邩');
echo getEditConfHtml('k_use_tsukin', '�g�щ{�����A�O�������N�ɒʋ΃u���E�U(��)�𗘗p(����, ���Ȃ�)');
echo getEditConfHtml('k_use_picto', '�g�щ{�����A�摜�����N��pic.to(��)�𗘗p(����, ���Ȃ�)');

// }}}
// {{{ NG/���ځ[��

echo getGroupSepaHtml('NG/���ځ[��');

echo getEditConfHtml('ngaborn_frequent', '&gt;&gt;1 �ȊO�̕p�oID�����ځ[�񂷂�(����, ���Ȃ�, NG�ɂ���)');
echo getEditConfHtml('ngaborn_frequent_one', '&gt;&gt;1 ���p�oID���ځ[��̑ΏۊO�ɂ���(����, ���Ȃ�)');
echo getEditConfHtml('ngaborn_frequent_num', '�p�oID���ځ[��̂������l�i�o���񐔂�����ȏ��ID�����ځ[��j');
echo getEditConfHtml('ngaborn_frequent_dayres', '�����̑����X���ł͕p�oID���ځ[�񂵂Ȃ��i�����X��/�X�����Ă���̓����A0�Ȃ疳���j');
echo getEditConfHtml('ngaborn_chain', '�A��NG���ځ[��(����, ���Ȃ�, ���ځ[�񃌃X�ւ̃��X��NG�ɂ���) <br>�������y�����邽�߁A�\���͈͂̃��X�ɂ����A�����Ȃ�');
echo getEditConfHtml('ngaborn_daylimit', '���̊��ԁANG���ځ[���HIT���Ȃ���΁A�o�^���[�h�������I�ɊO���i�����j');
// }}}
// {{{ ETC

echo getGroupSepaHtml('ETC');

echo getEditConfHtml('my_FROM', '���X�������ݎ��̃f�t�H���g�̖��O');
echo getEditConfHtml('my_mail', '���X�������ݎ��̃f�t�H���g��mail');

echo getEditConfHtml('editor_srcfix', 'PC�{�����A�\�[�X�R�[�h�̃R�s�y�ɓK�����␳������`�F�b�N�{�b�N�X��\���i����, ���Ȃ�, pc�I�̂݁j');

echo getEditConfHtml('get_new_res', '�V�����X���b�h���擾�������ɕ\�����郌�X��(�S�ĕ\������ꍇ:&quot;all&quot;)');
echo getEditConfHtml('rct_rec_num', '�ŋߓǂ񂾃X���̋L�^��');
echo getEditConfHtml('res_hist_rec_num', '�������ݗ����̋L�^��');
echo getEditConfHtml('res_write_rec', '�������ݓ��e���O���L�^(����, ���Ȃ�)');
echo getEditConfHtml('through_ime', '�O��URL�W�����v����ۂɒʂ��Q�[�g (����, p2 ime(�����]��), p2 ime(�蓮�]��), p2 ime(p�̂ݎ蓮�]��), r.p(�����]��1�b), r.p(�����]��0�b), r.p(�蓮�]��), r.p(p�̂ݎ蓮�]��))');
echo getEditConfHtml('ime_manual_ext', '�Q�[�g�Ŏ����]�����Ȃ��g���q�i�J���}��؂�ŁA�g���q�̑O�̃s���I�h�͕s�v�j');
echo getEditConfHtml('join_favrank', '<a href="http://akid.s17.xrea.com:8080/favrank/favrank.html" target="_blank">���C�ɃX�����L</a>�ɎQ��(����, ���Ȃ�)');
echo getEditConfHtml('enable_menu_new', '���j���[�ɐV������\�� (����, ���Ȃ�, ���C�ɔ̂�)');
echo getEditConfHtml('menu_refresh_time', '���j���[�����̎����X�V�Ԋu (���w��B0�Ȃ玩���X�V���Ȃ��B)');
echo getEditConfHtml('menu_hide_brds', '�J�e�S���ꗗ�������Ԃɂ���(����, ���Ȃ�)');
echo getEditConfHtml('k_save_packet', '�g�щ{�����A�p�P�b�g�ʂ����炷���߁A�S�p�p���E�J�i�E�X�y�[�X�𔼊p�ɕϊ� (����, ���Ȃ�)');
echo getEditConfHtml('enable_exfilter', '�t�B���^�����O��AND/OR�������\�ɂ��� (off, ���X�̂�, �T�u�W�F�N�g��)');
echo getEditConfHtml('flex_idpopup', 'ID:xxxxxxxx��ID�t�B���^�����O�̃����N�ɕϊ� (����, ���Ȃ�)');
echo getEditConfHtml('precede_openssl', '�����O�C�����A�܂���openssl�Ŏ��݂�B��PHP 4.3.0�ȍ~�ŁAOpenSSL���ÓI�Ƀ����N����Ă���K�v������B');
echo getEditConfHtml('precede_phpcurl', 'curl���g�����A�R�}���h���C���ł�PHP�֐��łǂ����D�悷�邩 (�R�}���h���C����, PHP�֐���)');

// }}}
// {{{ Mobile Color

echo getGroupSepaHtml('Mobile Color');
echo getEditConfHtml('mobile.background_color', '�w�i');
echo getEditConfHtml('mobile.text_color', '��{�����F');
echo getEditConfHtml('mobile.link_color', '�����N');
echo getEditConfHtml('mobile.vlink_color', '�K��ς݃����N');
echo getEditConfHtml('mobile.newthre_color', '�V���X���b�h�}�[�N');
echo getEditConfHtml('mobile.ttitle_color', '�X���b�h�^�C�g��');
echo getEditConfHtml('mobile.newres_color', '�V�����X�ԍ�');
echo getEditConfHtml('mobile.ngword_color', 'NG���[�h');
echo getEditConfHtml('mobile.onthefly_color', '�I���U�t���C���X�ԍ�');
echo getEditConfHtml('mobile.match_color', '�t�B���^�����O�Ń}�b�`�����L�[���[�h');

// }}}
// {{{ expack
// {{{ expack - tGrep

echo getGroupSepaHtml('expack - tGrep');
echo getEditConfHtml('expack.tgrep.quicksearch', '�ꔭ�����i�\��, ��\���j');
echo getEditConfHtml('expack.tgrep.recent_num', '�����������L�^���鐔�i�L�^���Ȃ�:0�j');

// }}}
// {{{ expack - �X�}�[�g�|�b�v�A�b�v���j���[

if ($_conf['expack.spm.enabled']) {
    echo getGroupSepaHtml('expack - �X�}�[�g�|�b�v�A�b�v���j���[');
} else {
    echo getGroupSepaHtml('<s>expack - �X�}�[�g�|�b�v�A�b�v���j���[</s> (����: see conf_admin_ex.inc.php)');
}
if ($_conf['disable_res']) {
    echo getEditConfHtml('expack.spm.kokores', '�����Ƀ��X');
    echo getEditConfHtml('expack.spm.kokores_orig', '�����Ƀ��X�ŊJ���t�H�[���Ɍ����X�̓��e��\������');
}
echo getEditConfHtml('expack.spm.ngaborn', '���ځ[�񃏁[�h�ENG���[�h�o�^');
echo getEditConfHtml('expack.spm.ngaborn_confirm', '���ځ[�񃏁[�h�ENG���[�h�o�^���Ɋm�F����');
echo getEditConfHtml('expack.spm.filter', '�t�B���^�����O');
echo getEditConfHtml('expack.spm.filter_target', '�t�B���^�����O���ʂ��J���t���[���܂��̓E�C���h�E');

// }}}
// {{{ expack - �A�N�e�B�u���i�[

if ($_conf['expack.am.enabled']) {
    echo getGroupSepaHtml('expack - �A�N�e�B�u���i�[');
} else {
    echo getGroupSepaHtml('<s>expack - �A�N�e�B�u���i�[</s> (����: see conf_admin_ex.inc.php)');
}
if (isset($_conf['expack.am.fontfamily.orig'])) {
    $_conf['expack.am.fontfamily'] = $_conf['expack.am.fontfamily.orig'];
}
echo getEditConfHtml('expack.am.fontfamily', 'AA�p�̃t�H���g');
echo getEditConfHtml('expack.am.fontsize', 'AA�p�̕����̑傫��');
echo getEditConfHtml('expack.am.display', '�X�C�b�`��\������ʒu');
echo getEditConfHtml('expack.am.autodetect', '�����Ŕ��肵�AAA�p�\��������iPC�j');
echo getEditConfHtml('expack.am.autong_k', '�����Ŕ��肵�ANG���[�h�ɂ���BAAS ���L���Ȃ� AAS �̃����N���쐬�i�g�сj');

// }}}
// {{{ expack - RSS���[�_

if ($_conf['expack.rss.enabled']) {
    echo getGroupSepaHtml('expack - RSS���[�_');
} else {
    echo getGroupSepaHtml('<s>expack - RSS���[�_</s> (����: see conf_admin_ex.inc.php)');
}
echo getEditConfHtml('expack.rss.check_interval', 'RSS���X�V���ꂽ���ǂ����m�F����Ԋu�i���w��j');
echo getEditConfHtml('expack.rss.target_frame', 'RSS�̊O�������N���J���t���[���܂��̓E�C���h�E');
echo getEditConfHtml('expack.rss.desc_target_frame', '�T�v���J���t���[���܂��̓E�C���h�E');

// }}}
// {{{ expack - ImageCache2

if ($_conf['expack.ic2.enabled']) {
    echo getGroupSepaHtml('expack - ImageCache2');
} else {
    echo getGroupSepaHtml('<s>expack - ImageCache2</s> (����: see conf_admin_ex.inc.php)');
}
echo getEditConfHtml('expack.ic2.through_ime', '�L���b�V���Ɏ��s�����Ƃ��̊m�F�p��ime�o�R�Ń\�[�X�ւ̃����N���쐬 (����, ���Ȃ�)');
echo getEditConfHtml('expack.ic2.fitimage', '�|�b�v�A�b�v�摜�̑傫�����E�C���h�E�̑傫���ɍ��킹�� (����, ���Ȃ�, �����傫���Ƃ���������, �������傫���Ƃ���������, �蓮�ł���)');
echo getEditConfHtml('expack.ic2.pre_thumb_limit_k', '�g�тŃC�����C���E�T���l�C�����L���̂Ƃ��̕\�����鐧���� (0�Ŗ�����)');
echo getEditConfHtml('expack.ic2.newres_ignore_limit', '�V�����X�̉摜�� pre_thumb_limit �𖳎����đS�ĕ\�� (����, ���Ȃ�)');
echo getEditConfHtml('expack.ic2.newres_ignore_limit_k', '�g�тŐV�����X�̉摜�� pre_thumb_limit_k �𖳎����đS�ĕ\�� (����, ���Ȃ�)');

// }}}
// {{{ expack - Google����

if ($_conf['expack.google.enabled']) {
    echo getGroupSepaHtml('expack - Google����');
} else {
    echo getGroupSepaHtml('<s>expack - Google����</s> (����: see conf_admin_ex.inc.php)');
}
echo getEditConfHtml('expack.google.key', 'Google Web APIs �̓o�^�L�[');

// }}}
// {{{ expack - AAS

if ($_conf['expack.aas.enabled']) {
    echo getGroupSepaHtml('expack - AAS');
} else {
    echo getGroupSepaHtml('<s>expack - AAS</s> (����: see conf_admin_ex.inc.php)');
}
echo getEditConfHtml('expack.aas.inline', '���� AA ����ƘA�����A�C�����C���\�� (����, ���Ȃ�)');
echo getEditConfHtml('expack.aas.image_type', '�摜�`�� (PNG, JPEG, GIF)');
echo getEditConfHtml('expack.aas.image_width', '�g�їp�̉摜�̉��� (�s�N�Z��)');
echo getEditConfHtml('expack.aas.image_height', '�g�їp�̉摜�̍��� (�s�N�Z��)');
echo getEditConfHtml('expack.aas.image_width_pc', 'PC�p�̉摜�̉��� (�s�N�Z��)');
echo getEditConfHtml('expack.aas.image_height_pc', 'PC�p�̉摜�̍��� (�s�N�Z��)');
echo getEditConfHtml('expack.aas.image_width_il', '�C�����C���摜�̉��� (�s�N�Z��)');
echo getEditConfHtml('expack.aas.image_height_in', '�C�����C���摜�̍��� (�s�N�Z��)');
echo getEditConfHtml('expack.aas.jpeg_quality', 'JPEG�̕i�� (0-100)');
echo getEditConfHtml('expack.aas.trim', '�摜�̗]�����g���~���O (����, ���Ȃ�)');
echo getEditConfHtml('expack.aas.max_fontsize', '�ő�̕����T�C�Y (�|�C���g)');
echo getEditConfHtml('expack.aas.min_fontsize', '�ŏ��̕����T�C�Y (�|�C���g)');

// }}}
// }}}

echo $htm['form_submit'];

if (empty($_conf['ktai'])) {
    echo '</table>'."\n";
}

echo '</form>'."\n";


// �g�тȂ�
if ($_conf['ktai']) {
    echo <<<EOP
<hr>
<a {$_conf['accesskey']}="{$_conf['k_accesskey']['up']}" href="editpref.php{$_conf['k_at_q']}">{$_conf['k_accesskey']['up']}.�ݒ�ҏW</a>
{$_conf['k_to_index_ht']}
EOP;
}

echo '</body></html>';

// �������܂�
exit;

//=====================================================================
// �֐�
//=====================================================================

/**
 * ���[���ݒ�i$conf_user_rules�j�Ɋ�Â��āA
 * �w���name�ɂ����āAPOST�w�肪empty�̎��́A�f�t�H���g�Z�b�g����
 */
function emptyToDef()
{
    global $conf_user_def, $conf_user_rules;
    
    $rule = 'NotEmpty';
    
    if (is_array($conf_user_rules)) {
        foreach ($conf_user_rules as $n => $va) {
            if (in_array($rule, $va)) {
                if (isset($_POST['conf_edit'][$n])) {
                    if (empty($_POST['conf_edit'][$n])) {
                        $_POST['conf_edit'][$n] = $conf_user_def[$n];
                    }
                }
            }
        } // foreach
    }
    return true;
}

/**
 * ���[���ݒ�i$conf_user_rules�j�Ɋ�Â��āA
 * POST�w��𐳂̐������ł��鎞�͐��̐������i0���܂ށj���A
 * �ł��Ȃ����́A�f�t�H���g�Z�b�g����
 */
function notIntExceptMinusToDef()
{
    global $conf_user_def, $conf_user_rules;
    
    $rule = 'IntExceptMinus';
    
    if (is_array($conf_user_rules)) {
        foreach ($conf_user_rules as $n => $va) {
            if (in_array($rule, $va)) {
                if (isset($_POST['conf_edit'][$n])) {
                    // �S�p�����p ����
                    $_POST['conf_edit'][$n] = mb_convert_kana($_POST['conf_edit'][$n], 'a');
                    // �������ł���Ȃ�
                    if (is_numeric($_POST['conf_edit'][$n])) {
                        // ����������
                        $_POST['conf_edit'][$n] = intval($_POST['conf_edit'][$n]);
                        // ���̐��̓f�t�H���g��
                        if ($_POST['conf_edit'][$n] < 0) {
                            $_POST['conf_edit'][$n] = intval($conf_user_def[$n]);
                        }
                    // �������ł��Ȃ����̂́A�f�t�H���g��
                    } else {
                        $_POST['conf_edit'][$n] = intval($conf_user_def[$n]);
                    }
                }
            }
        } // foreach
    }
    return true;
}

/**
 * �I�����ɂȂ��l�̓f�t�H���g�Z�b�g����
 */
function notSelToDef()
{
    global $conf_user_def, $conf_user_sel, $conf_user_rad;
    
    $conf_user_list = array_merge($conf_user_sel, $conf_user_rad);
    $names = array_keys($conf_user_list);
    
    if (is_array($names)) {
        foreach ($names as $n) {
            if (isset($_POST['conf_edit'][$n])) {
                if (!array_key_exists($_POST['conf_edit'][$n], $conf_user_list[$n])) {
                    $_POST['conf_edit'][$n] = $conf_user_def[$n];
                }
            }
        } // foreach
    }
    return true;
}

/**
 * �O���[�v�����p��HTML�𓾂�i�֐�����PC�A�g�їp�\����U�蕪���j
 */
function getGroupSepaHtml($title)
{
    global $_conf;
    
    // PC�p
    if (empty($_conf['ktai'])) {
        $ht = <<<EOP
        <tr class="group">
            <td colspan="4"><h4 style="display:inline;">{$title}</h4></td>
        </tr>\n
EOP;
    // �g�їp
    } else {
        $ht = "<hr><h4>{$title}</h4>"."\n";
    }
    return $ht;
}

/**
 * �ҏW�t�H�[��input�pHTML�𓾂�i�֐�����PC�A�g�їp�\����U�蕪���j
 */
function getEditConfHtml($name, $description_ht)
{
    global $_conf, $conf_user_def, $conf_user_sel, $conf_user_rad;

    // �f�t�H���g�l�̋K�肪�Ȃ���΁A�󔒂�Ԃ�
    if (!isset($conf_user_def[$name])) {
        return '';
    }

    $name_view = htmlspecialchars($_conf[$name], ENT_QUOTES);
    
    if (empty($_conf['ktai'])) {
        $input_size_at = ' size="38"';
    } else {
        $input_size_at = '';
    }
    
    // select �I���`���Ȃ�
    if ($conf_user_sel[$name]) {
        $form_ht = getEditConfSelHtml($name);
        $key = $conf_user_def[$name];
        $def_views[$name] = htmlspecialchars($conf_user_sel[$name][$key], ENT_QUOTES);
    // select �I���`���Ȃ�
    } elseif ($conf_user_rad[$name]) {
        $form_ht = getEditConfRadHtml($name);
        $key = $conf_user_def[$name];
        $def_views[$name] = htmlspecialchars($conf_user_rad[$name][$key], ENT_QUOTES);
    // input ���͎��Ȃ�
    } else {
        $form_ht = <<<EOP
<input type="text" name="conf_edit[{$name}]" value="{$name_view}"{$input_size_at}>\n
EOP;
        if (is_string($conf_user_def[$name])) {
            $def_views[$name] = htmlspecialchars($conf_user_def[$name], ENT_QUOTES);
        } else {
            $def_views[$name] = $conf_user_def[$name];
        }
    }
    
    // PC�p
    if (empty($_conf['ktai'])) {
        $r = <<<EOP
<tr title="�f�t�H���g�l: {$def_views[$name]}">
    <td>{$name}</td>
    <td>{$form_ht}</td>
    <td>{$description_ht}</td>
</tr>\n
EOP;
    // �g�їp
    } else {
        $r = <<<EOP
[{$name}]<br>
{$description_ht}<br>
{$form_ht}<br>
<br>\n
EOP;
    }
    
    return $r;
}

/**
 * �ҏW�t�H�[��select�pHTML�𓾂�
 */
function getEditConfSelHtml($name)
{
    global $_conf, $conf_user_def, $conf_user_sel;

    foreach ($conf_user_sel[$name] as $key => $value) {
        /*
        if ($value == "") {
            continue;
        }
        */
        $selected = "";
        if ($_conf[$name] == $key) {
            $selected = " selected";
        }
        $key_ht = htmlspecialchars($key, ENT_QUOTES);
        $value_ht = htmlspecialchars($value, ENT_QUOTES);
        $options_ht .= "\t<option value=\"{$key_ht}\"{$selected}>{$value_ht}</option>\n";
    } // foreach
    
    $form_ht = <<<EOP
        <select name="conf_edit[{$name}]">
        {$options_ht}
        </select>\n
EOP;
    return $form_ht;
}

/**
 * �ҏW�t�H�[��radio�pHTML�𓾂�
 */
function getEditConfRadHtml($name)
{
    global $_conf, $conf_user_def, $conf_user_rad;

    $form_ht = '';

    foreach ($conf_user_rad[$name] as $key => $value) {
        /*
        if ($value == "") {
            continue;
        }
        */
        $checked = "";
        if ($_conf[$name] == $key) {
            $checked = " checked";
        }
        $key_ht = htmlspecialchars($key, ENT_QUOTES);
        $value_ht = htmlspecialchars($value, ENT_QUOTES);
        $form_ht .= "<label><input type=\"radio\" name=\"conf_edit[{$name}]\" value=\"{$key_ht}\"{$checked}>{$value_ht}</label>\n";
    } // foreach
    
    return $form_ht;
}

?>
