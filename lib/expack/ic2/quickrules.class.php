<?php
/**
 * rep2expack - HTML_QuickForm�̃J�X�^�����[��
 */

require_once 'HTML/QuickForm/Rule.php';

// {{{ RuleNumericRange

/**
 * QuickForm�̃��[���i���͈̔́AQuickForm�̃T���v�������p�j
 */
class RuleNumericRange extends HTML_QuickForm_Rule
{
    // {{{ validate()

    /**
     * @return bool
     */
    public function validate($value, $options)
    {
        if (isset($options['min']) && floatval($value) < $options['min']) {
            return false;
        }
        if (isset($options['max']) && floatval($value) > $options['max']) {
            return false;
        }
        return true;
    }

    // }}}
    // {{{ getValidationScript()

    /**
     * @return string
     */
    public function getValidationScript($options = null)
    {
        $jsCheck = array();
        if (isset($options['min'])) {
            $jsCheck[] = 'Number({jsVar}) >= ' . $options['min'];
        }
        if (isset($options['max'])) {
            $jsCheck[] = 'Number({jsVar}) <= ' . $options['max'];
        }
        return array('', "{jsVar} != '' && !(" . implode(' && ', $jsCheck) . ')');
    }

    // }}}
}

// }}}
// {{{ RuleInArray

/**
 * QuickForm�̃��[���i�z��ɗv�f�����邩�j
 */
class RuleInArray extends HTML_QuickForm_Rule
{
    // {{{ validate()

    /**
     * @return bool
     */
    public function validate($value, $options)
    {
        return in_array($value, $options);
    }

    // }}}
}

// }}}
// {{{ RuleInArrayKeys

/**
 * QuickForm�̃��[���i�z��ɃL�[�����邩�j
 */
class RuleInArrayKeys extends HTML_QuickForm_Rule
{
    // {{{ validate()

    /**
     * @return bool
     */
    public function validate($value, $options)
    {
        return array_key_exists($value, $options);
    }

    // }}}
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
