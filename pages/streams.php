<?php

/**
 * This file is part of the YFeed package.
 *
 * @author (c) Yakamara Media GmbH & Co. KG
 * @author thomas.blum@redaxo.org
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$func = rex_request('func', 'string');
$id = rex_request('id', 'integer');

if ('fetch' === $func) {
    $stream = rex_yfeed_stream::get($id);
    $stream->fetch();
    echo rex_view::success($this->i18n('stream_fetched', $stream->getAddedCount(), $stream->getUpdateCount(), $stream->getChangedByUserCount()));
    $func = '';
}

if ('' == $func) {
    $query = 'SELECT `id`, `namespace`, `type`, `title` FROM ' . rex_yfeed_stream::table() . ' ORDER BY `type`, `namespace`';
    $list = rex_list::factory($query);
    $list->addTableAttribute('class', 'table-striped');

    $tdIcon = '<i class="rex-icon fa-twitter"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey($this->i18n('add'), 'add') . '><i class="rex-icon rex-icon-add-article"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnFormat($thIcon, 'custom', function ($params) use ($thIcon) {
        /** @var rex_list $list */
        $list = $params['list'];
        $type = explode('_', $list->getValue('type'));
        $icon = 'fa-paper-plane-o';
        if (isset($type[0])) {
            switch ($type[0]) {
                case 'rss':
                    $icon = 'fa-rss';
                    break;
                case 'twitter':
                    $icon = 'fa-twitter';
                    break;
                case 'facebook':
                    $icon = 'fa-facebook';
                    break;
            }
            return $list->getColumnLink($thIcon, '<i class="rex-icon ' . $icon . '"></i>');
        }
    });
    $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);

    $list->removeColumn('id');

    $list->setColumnLabel('namespace', $this->i18n('stream_namespace'));
    $list->setColumnLabel('type', $this->i18n('stream_type'));
    $list->setColumnLabel('title', $this->i18n('stream_title'));

    $list->addColumn($this->i18n('function'), $this->i18n('edit'));
    $list->setColumnLayout($this->i18n('function'), ['<th class="rex-table-action" colspan="3">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams($this->i18n('function'), ['func' => 'edit', 'id' => '###id###']);

    $list->addColumn('delete', $this->i18n('delete'), -1, ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('delete', ['func' => 'delete', 'id' => '###id###']);
    $list->addLinkAttribute('delete', 'onclick', "return confirm('" . $this->i18n('stream_delete_question') . "');");

    $list->addColumn('fetch', $this->i18n('stream_fetch'), -1, ['', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams('fetch', ['func' => 'fetch', 'id' => '###id###']);

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('streams'));
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;
} else {
    $streams = rex_yfeed_stream::getSupportedStreams();

    $title = $func == 'edit' ? $this->i18n('stream_edit') : $this->i18n('stream_add');

    $form = rex_form::factory(rex_yfeed_stream::table(), '', 'id = ' . $id, 'post', false);
    $form->addParam('id', $id);
    $form->setApplyUrl(rex_url::currentBackendPage());
    $form->setEditMode($func == 'edit');
    $add = $func != 'edit';

    $form->addFieldset($this->i18n('stream_general'));

    $field = $form->addTextField('namespace');
    $field->setLabel($this->i18n('stream_namespace'));
    $field->setNotice($this->i18n('stream_namespace_notice'));
    $field->getValidator()
        ->add('notEmpty', $this->i18n('stream_namespace_error'))
        ->add('match', $this->i18n('stream_namespace_error'), '/^[a-z0-9_]*$/');

    $field = $form->addTextField('title');
    $field->setLabel($this->i18n('stream_title'));

    $field = $form->addMediaField('image');
    $field->setLabel($this->i18n('stream_image'));
    $field->setTypes('jpg,jpeg,gif,png');

    $form->addFieldset($this->i18n('stream_select_type'));

    $field = $form->addSelectField('type');
    $field->setPrefix('<div class="rex-select-style">');
    $field->setSuffix('</div>');
    $field->setLabel($this->i18n('stream_type'));
    $fieldSelect = $field->getSelect();

    $script = '
    <script type="text/javascript">
    <!--

    (function($) {
        var currentShown = null;
        $("#' . $field->getAttribute('id') . '").change(function(){
            if(currentShown) currentShown.hide();

            var streamParamsId = "#rex-"+ jQuery(this).val();
            currentShown = $(streamParamsId);
            currentShown.show();
        }).change();
    })(jQuery);

    //--></script>';

    $fieldContainer = $form->addContainerField('type_params');
    $fieldContainer->setAttribute('style', 'display: none');
    $fieldContainer->setSuffix($script);
    $fieldContainer->setMultiple(false);
    $fieldContainer->setActive($field->getValue());

    foreach ($streams as $streamType => $streamClass) {
        /** @var rex_yfeed_stream_abstract $stream */
        $stream = new $streamClass();

        $fieldSelect->addOption($stream->getTypeName(), $streamType);

        $streamParams = $stream->getTypeParams();
        $group = $streamType;

        if (empty($streamParams)) {
            continue;
        }

        foreach ($streamParams as $param) {
            $name = $param['name'];
            $value = isset($param['default']) ? $param['default'] : null;
            $attributes = [];
            if (isset($param['attributes'])) {
                $attributes = $param['attributes'];
            }

            switch ($param['type']) {
                case 'int':
                case 'float':
                case 'string':
                    $type = 'text';
                    $field = $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes);
                    $field->setLabel($param['label']);
                    $field->setAttribute('id', "media_manager $name $type");
                    if (!empty($param['notice'])) {
                        $field->setNotice($param['notice']);
                    }
                    if (!empty($param['prefix'])) {
                        $field->setPrefix($param['prefix']);
                    }
                    if (!empty($param['suffix'])) {
                        $field->setSuffix($param['suffix']);
                    }
                    break;
                case 'select':
                    $type = $param['type'];
                    /** @var rex_form_select_element $field */
                    $field = $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes);
                    $field->setLabel($param['label']);
                    $field->setAttribute('id', "media_manager $name $type");
                    if (!empty($param['notice'])) {
                        $field->setNotice($param['notice']);
                    }
                    if (!empty($param['prefix'])) {
                        $field->setPrefix($param['prefix']);
                    }
                    if (!empty($param['suffix'])) {
                        $field->setSuffix($param['suffix']);
                    }

                    $select = $field->getSelect();
                    if (isset($attributes['multiple'])) {
                        $select->setMultiple();
                    }
                    $select->addOptions($param['options'], true);
                    break;
                case 'media':
                    $type = $param['type'];
                    $field = $fieldContainer->addGroupedField($group, $type, $name, $value, $attributes);
                    $field->setLabel($param['label']);
                    $field->setAttribute('id', "media_manager $name $type");
                    if (!empty($param['notice'])) {
                        $field->setNotice($param['notice']);
                    }
                    if (!empty($param['prefix'])) {
                        $field->setPrefix($param['prefix']);
                    }
                    if (!empty($param['suffix'])) {
                        $field->setSuffix($param['suffix']);
                    }
                    break;
                default:
                    throw new rex_exception('Unexpected param type "' . $param['type'] . '"');
            }
        }
    }

    $content = $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit');
    $fragment->setVar('title', $title);
    $fragment->setVar('body', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;
}
