<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */
/*
    p2 - �{�[�h���j���[��\������ �N���X
*/

class ShowBrdMenuPc {

    var $cate_id; // �J�e�S���[ID

    /**
     * �R���X�g���N�^
     */
    function ShowBrdMenuPc(){
        $this->cate_id = 1;
    }

    /**
     * ���j���[���v�����g����
     */
    function printBrdMenu(&$categories)
    {
        global $_conf, $_info_msg_ht;

        if ($categories) {

            $menu_php_url = $_SERVER['PHP_SELF'];

            foreach ($categories as $cate) {
                if ($cate->num > 0) {
                    echo "<div class=\"menu_cate\">\n";
                    echo "\t<b class=\"menu_cate\" onclick=\"showHide('c{$this->cate_id}');\">{$cate->name}</b>\n";
                    if ($cate->is_open or $cate->match_attayo) {
                        echo "\t<div class=\"itas\" id=\"c{$this->cate_id}\">\n";
                    } else {
                        echo "\t<div class=\"itas_hide\" id=\"c{$this->cate_id}\">\n";
                    }
                    foreach ($cate->menuitas as $mita) {
                        // �t�B���^�����O����itaj_ht���g���Ă͂����Ȃ�
                        $mita_itaj_js = htmlspecialchars($mita->itaj);
                        echo <<<EOP
        <span class="fav" onclick="setFavIta('{$menu_php_url}','{$mita_itaj_js}','{$mita->host}','{$mita->bbs}','{$mita->itaj_en}',1);">+</span> <a href="{$_conf['subject_php']}?host={$mita->host}&amp;bbs={$mita->bbs}&amp;itaj_en={$mita->itaj_en}">{$mita->itaj_ht}</a><br>\n
EOP;
                    }
                    echo "\t</div>\n";
                    echo "</div>\n";
                }
                $this->cate_id++;
            }
        }

    }

    /**
     * ���C�ɔ��v�����g����
     */
    function print_favIta()
    {
        global $_conf, $_exconf, $matome_i, $STYLE;

        echo "<div class=\"menu_cate\">\n";
        echo "<b class=\"menu_cate\" onclick=\"showHide('c_favita');\">���C�ɔ�</b>\n";
        echo "[<a href=\"editfavita.php\" target=\"subject\">�ҏW</a>]\n";

        // ���C�ɔ؂�ւ�
        if ($_exconf['etc']['multi_favs']) {
            echo "<br>\n";
            echo FavSetManager::makeFavSetSwitchElem('m_favita_set', '���C�ɔ�', TRUE, "replaceMenuItem('c_favita', 'm_favita_set', this.options[this.selectedIndex].value);");
        }

        echo "\t<div class=\"itas\" id=\"c_favita\">\n";

        $lines = @file($_conf['favita_path']); // favita�ǂݍ���

        if ($lines) {
            $menu_php_url = (isset($_GET['PHP_SELF'])) ? $_GET['PHP_SELF'] : $_SERVER['PHP_SELF'];

            foreach ($lines as $l) {
                $l = rtrim($l);
                if (preg_match("/^\t?(.+)\t(.+)\t(.+)$/", $l, $matches)) {
                    $host = $matches[1];
                    $bbs = $matches[2];
                    $itaj = rtrim($matches[3]);
                    $itaj_view = htmlspecialchars($itaj);
                    $itaj_en = rawurlencode(base64_encode($itaj));
                    //$itaj_js = str_replace("'", "\\'", str_replace("\\", "\\\\", $itaj_view));

                    // ���C�ɔ���������Ƃ���JavaScript�Ń_�C�A���O��\�����A�m�F����B
                    $p_htm['star'] = <<<EOP
<span class="fav" onclick="unSetFavIta('{$menu_php_url}','{$itaj_view}','{$host}','{$bbs}',0);">��</span>
EOP;

                    // �V������\������ꍇ
                    if ($_conf['enable_menu_new'] && !empty($_GET['new'])) {
                        $matome_i++;
                        $spmode = '';
                        $shinchaku_num = 0;
                        $shinokini_num = 0;
                        $_newthre_num = 0;
                        $newthre_ht = '';
                        $matome_url_ht = "{$_conf['read_new_php']}?host={$host}&amp;bbs={$bbs}";
                        // $shinchaku_num, $_newthre_num ���Z�b�g
                        include (P2_LIBRARY_DIR . '/subject_new.inc.php');
                        if ($shinchaku_num > 0) {
                            $class_newres_num = ' class="newres_num"';
                        } else {
                            $class_newres_num = ' class="newres_num_zero"';
                        }
                        if ($shinokini_num > 0) {
                            $class_newfav_num = ' class="newres_num"';
                        } else {
                            $class_newfav_num = ' class="newres_num_zero"';
                        }
                        if ($_newthre_num) {
                            $newthre_ht = $_newthre_num;
                        }
                        echo <<<EOP
        {$p_htm['star']} <a href="{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}&amp;itaj_en={$itaj_en}" onclick="chMenuColor({$matome_i});">{$itaj}</a> <span id="newthre{$matome_i}" class="newthre_num">{$newthre_ht}</span> (<a href="{$matome_url_ht}&amp;fav=1" target="read" id="unf{$matome_i}"{$class_newfav_num}>{$shinokini_num}</a>/<a href="{$matome_url_ht}" target="read" id="un{$matome_i}" onclick="chUnColor({$matome_i});"{$class_newres_num}>{$shinchaku_num}</a>)<br>\n
EOP;

                    // �V������\�����Ȃ��ꍇ
                    } else {
                        echo <<<EOP
        {$p_htm['star']} <a href="{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}&amp;itaj_en={$itaj_en}">{$itaj}</a><br>\n
EOP;

                    }

                }

                flush();

            } // foreach

        // ����ۂȂ�
        } else {
            echo "\t\t�@�i����ہj\n";
        }

        echo "\t</div>\n";
        echo "</div>\n";
        flush();

    }

}
?>
