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

if ('' == $func) {
    $query = 'SELECT
                r.id,
                s.namespace,
                s.type,
                (CASE WHEN (r.title IS NULL or r.title = "")
                    THEN r.content
                    ELSE r.title
                END) as title,
                r.url
            FROM
                ' . rex_yfeed_response::table() . ' AS r
                LEFT JOIN
                    ' . rex_yfeed_stream::table() . ' AS s
                    ON  r.stream_id = s.id
            ';

    $list = rex_list::factory($query);
    $list->addTableAttribute('class', 'table-striped');

    $list->addColumn('', '', 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams('', ['func' => 'edit', 'id' => '###id###']);
    $list->setColumnFormat('', 'custom', function($params) {
        $type = explode('_', $params['list']->getValue('s.type'));
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
            return $params['list']->getColumnLink('', '<i class="rex-icon ' . $icon . '"></i>');
        }
    });

    $list->removeColumn('id');
    $list->removeColumn('url');

    $list->setColumnLabel('namespace', $this->i18n('yfeed_stream_namespace'));
    $list->setColumnLabel('type', $this->i18n('yfeed_stream_type'));

    $list->setColumnLabel('title', $this->i18n('yfeed_response_title'));
    $list->setColumnFormat('title', 'custom', function($params) {
        $title = $params['list']->getValue('title');
        $title .= ($params['list']->getValue('url') != '') ? '<br /><small><a href="' . $params['list']->getValue('url') . '" target="_blank">' . $params['list']->getValue('url') . '</a></small>' : '';
        return $title;
    });

    $list->addColumn($this->i18n('function'), $this->i18n('edit'));
    $list->setColumnLayout($this->i18n('function'), ['<th class="rex-table-action" colspan="3">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams($this->i18n('function'), array('func' => 'edit', 'id' => '###id###'));

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('response'));
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;

} else {

    $title = $func == 'edit' ? $this->i18n('response_edit') : $this->i18n('response_add');

    $form = rex_form::factory(rex_yfeed_response::table(), '', 'id = ' . $id, 'post', false);
    $form->addParam('id', $id);
    $form->setApplyUrl(rex_url::currentBackendPage());
    $form->setEditMode($func == 'edit');
    $add = $func != 'edit';

    $field = $form->addHiddenField('changed_by_user', 1);

    $field = $form->addTextField('uid');
    $field->setLabel($this->i18n('yfeed_response_uid'));

    $field = $form->addTextField('title');
    $field->setLabel($this->i18n('yfeed_response_title'));

    $field = $form->addTextareaField('content');
    $field->setLabel($this->i18n('yfeed_response_content'));

    $field = $form->addTextareaField('content_raw');
    $field->setLabel($this->i18n('yfeed_response_content_raw'));

    $field = $form->addTextField('url');
    $field->setLabel($this->i18n('yfeed_response_url'));

    $field = $form->addReadOnlyField('date');
    $field->setLabel($this->i18n('yfeed_response_date'));

    $field = $form->addTextField('author');
    $field->setLabel($this->i18n('yfeed_response_author'));

    $field = $form->addTextField('language');
    $field->setLabel($this->i18n('yfeed_response_language'));

    $field = $form->addTextareaField('media');
    $field->setLabel($this->i18n('yfeed_response_media'));

    $field = $form->addTextareaField('raw');
    $field->setLabel($this->i18n('yfeed_response_raw'));

    $content = $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit');
    $fragment->setVar('title', $title);
    $fragment->setVar('body', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;

}
