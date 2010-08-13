<?php
/**
 * rep2 - �c�[���o�[�p���[�e�B���e�B�iiPhone�j
 */

// {{{ toolbar_i_standard_button()

/**
 * �W���̃c�[���o�[�{�^�� (�����N)
 *
 * @param string $icon
 * @param string $label
 * @param string $uri
 * @return string
 */
function toolbar_i_standard_button($icon, $label, $uri)
{
    return <<<EOS
<a href="{$uri}" ontouchstart="this.className='hover'" ontouchend="this.className=''"><img src="{$icon}" width="48" height="32" alt=""><br>{$label}</a>
EOS;
}

// }}}
// {{{ toolbar_i_badged_button()

/**
 * �o�b�W�t���̃c�[���o�[�{�^�� (�����N)
 *
 * @param string $icon
 * @param string $label
 * @param string $uri
 * @param string $badge
 * @return string
 */
function toolbar_i_badged_button($icon, $label, $uri, $badge)
{
    return <<<EOS
<a href="{$uri}" ontouchstart="this.className='hover'" ontouchend="this.className=''"><img src="{$icon}" width="48" height="32" alt=""><br>{$label}<span class="badge">{$badge}</span></a>
EOS;
}

// }}}
// {{{ toolbar_i_opentab_button()

/**
 * �����N��V�����^�u�ŊJ���c�[���o�[�{�^��
 *
 * @param string $icon
 * @param string $label
 * @param string $uri
 * @return string
 */
function toolbar_i_opentab_button($icon, $label, $uri)
{
    return <<<EOS
<a href="{$uri}" ontouchstart="this.className='hover'" ontouchend="this.className=''" target="_blank"><img src="{$icon}" width="48" height="32" alt=""><br>{$label}</a>
EOS;
}

// }}}
// {{{ toolbar_i_disabled_button()

/**
 * �����ȃc�[���o�[�{�^��
 *
 * @param string $icon
 * @param string $label
 * @param string $uri
 * @return string
 */
function toolbar_i_disabled_button($icon, $label)
{
    return <<<EOS
<span class="unavailable"><img src="{$icon}" width="48" height="32" alt=""><br>{$label}</span>
EOS;
}

// }}}
// {{{ toolbar_i_showhide_button()

/**
 * �^�[�Q�b�g�v�f�̕\���E��\�����g�O������c�[���o�[�{�^��
 *
 * @param string $icon
 * @param string $label
 * @param string $id
 * @return string
 */
function toolbar_i_showhide_button($icon, $label, $id)
{
    return <<<EOS
<a href="#{$id}", onclick="return iutil.toolbarShowHide(this, event);"><img src="{$icon}" width="48" height="32" alt=""><br>{$label}</a>
EOS;
}

// }}}
// {{{ toolbar_i_favita_button()

/**
 * ���C�ɔ̓o�^�E�������g�O������c�[���o�[�{�^��
 *
 * @param string $icon
 * @param string $label (fallback)
 * @param object $info @see lib/get_info.inc.php: get_board_info()
 * @param int $setnum
 * @return string
 */
function toolbar_i_favita_button($icon, $label, $info, $setnum = 0)
{
    if (!array_key_exists($setnum, $info->favs)) {
        return toolbar_i_disabled_button($icon, $label);
    }

    $fav = $info->favs[$setnum];
    $attrs = $fav['set'] ? '' : ' class="inactive"';
    $query = http_build_query(array(
        'cmd'       => 'setfavita',
        'host'      => $info->host,
        'bbs'       => $info->bbs,
        'itaj_en'   => UrlSafeBase64::encode($info->itaj),
        'setnum'    => $setnum,
        'setfavita' => -1,
    ), '', '&amp;');

    return <<<EOS
<a href="httpcmd.php?{$query}", onclick="return iutil.toolbarSetFavIta(this, event);"{$attrs}><img src="{$icon}" width="48" height="32" alt=""><br>{$fav['title']}</a>
EOS;
}

// }}}
// {{{ toolbar_i_fav_button()

/**
 * ���C�ɃX���̓o�^�E�������g�O������c�[���o�[�{�^��
 *
 * @param string $icon
 * @param string $label (fallback)
 * @param object $info @see lib/get_info.inc.php: get_thread_info()
 * @param int $setnum
 * @return string
 */
function toolbar_i_fav_button($icon, $label, $info, $setnum = 0)
{
    if (!array_key_exists($setnum, $info->favs)) {
        return toolbar_i_disabled_button($icon, $label);
    }

    $fav = $info->favs[$setnum];
    $attrs = $fav['set'] ? '' : ' class="inactive"';
    $query = http_build_query(array(
        'cmd'       => 'setfav',
        'host'      => $info->host,
        'bbs'       => $info->bbs,
        'key'       => $info->key,
        'ttitle_en' => UrlSafeBase64::encode($info->ttitle),
        'setnum'    => $setnum,
        'setfav'    => -1,
    ), '', '&amp;');

    return <<<EOS
<a href="httpcmd.php?{$query}", onclick="return iutil.toolbarSetFav(this, event);"{$attrs}><img src="{$icon}" width="48" height="32" alt=""><br>{$fav['title']}</a>
EOS;
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
