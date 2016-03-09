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
    $feed = rex_yfeed::get($id);
    $feed->fetch('twitter');
    echo rex_view::success($this->i18n('yfeed_fetched', $feed->countAdded(), $feed->countUpdated()));
    $func = '';
}

if ('' == $func) {

    $query = 'SELECT `id`, `title`, `url`, `table` FROM ' . rex_yfeed::table() . ' WHERE `type` = "twitter" ORDER BY `table`, `title`, `url`';
    $list = rex_list::factory($query);
    $list->addTableAttribute('class', 'table-striped');

    $tdIcon = '<i class="rex-icon fa-twitter"></i>';
    $thIcon = '<a href="' . $list->getUrl(['func' => 'add']) . '"' . rex::getAccesskey($this->i18n('add'), 'add') . '><i class="rex-icon rex-icon-add-article"></i></a>';
    $list->addColumn($thIcon, $tdIcon, 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
    $list->setColumnParams($thIcon, ['func' => 'edit', 'id' => '###id###']);

    $list->removeColumn('id');

    $list->setColumnLabel('title', $this->i18n('yfeed_title'));
    $list->setColumnLabel('url', $this->i18n('yfeed_url'));
    $list->setColumnLabel('table', $this->i18n('yfeed_table'));

    $list->addColumn($this->i18n('function'), $this->i18n('edit'));
    $list->setColumnLayout($this->i18n('function'), ['<th class="rex-table-action" colspan="3">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
    $list->setColumnParams($this->i18n('function'), array('func' => 'edit', 'id' => '###id###'));

    $list->addColumn('delete', $this->i18n('yfeed_delete'), -1, ['', '<td style="text-align:center;">###VALUE###</td>']);
    $list->setColumnParams('delete', ['func' => 'delete', 'id' => '###id###']);
    $list->addLinkAttribute('delete', 'onclick', "return confirm('" . $this->i18n('yfeed_delete_question') . "');");

    $list->addColumn('fetch', $this->i18n('yfeed_fetch'), -1, ['', '<td style="text-align:center;">###VALUE###</td>']);
    $list->setColumnParams('fetch', ['func' => 'fetch', 'id' => '###id###']);

    $content = $list->get();

    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('twitter'));
    $fragment->setVar('content', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;

} else {

    $title = $func == 'edit' ? $this->i18n('edit') : $this->i18n('add');

    $form = rex_form::factory(rex_yfeed::table(), $title, 'id = ' . $id, 'post', false);
    $form->addParam('id', $id);
    $form->setApplyUrl(rex_url::currentBackendPage());
    $form->setEditMode($func == 'edit');
    $add = $func != 'edit';

    $field = $form->addHiddenField('type', 'twitter');

    $field = $form->addTextField('title');
    $field->setLabel($this->i18n('yfeed_title'));

    $field = $form->addReadOnlyField('url', 'statuses/user_timeline');
    $field->setLabel($this->i18n('yfeed_url'));

    $fieldContainer = $form->addContainerField('params');
    $group = 'params';
    $type = 'text';
    $name = 'screen_name';
    $value = '';
    $f = $fieldContainer->addGroupedField($group, $type, $name);
    $f->setLabel($this->i18n('twitter_screen_name'));

    $name = 'count';
    $value = '';
    $f = $fieldContainer->addGroupedField($group, $type, $name);
    $f->setLabel($this->i18n('twitter_count'));

    $type = 'select';
    $name = 'exclude_replies';
    $f = $fieldContainer->addGroupedField($group, $type, $name);
    $f->setPrefix('<div class="rex-select-style">');
    $f->setSuffix('</div>');
    $f->setLabel($this->i18n('twitter_exclude_replies'));
    $select = $f->getSelect();
    $select->addOptions(['1' => $this->i18n('yes'), '0' => $this->i18n('no')]);

    $field = $form->addMediaField('image');
    $field->setLabel($this->i18n('yfeed_image'));
    $field->setTypes('jpg,jpeg,gif,png');

    $field = $form->addSelectField('table');
    $field->setLabel($this->i18n('yfeed_table'));
    $select = $field->getSelect();
    foreach (rex_sql::showTables() as $table) {
        $select->addOption($table, $table);
    }

    $form->addFieldset($this->i18n('yfeed_fields'));

    $field = $form->addReadOnlyField('info', $this->i18n('yfeed_fields_info'));
    $field->setLabel(' ');

    foreach (rex_yfeed::fields() as $fieldName) {
        $field = $form->addTextField('field_' . $fieldName, $add ? $fieldName : null);
        $field->setLabel($this->i18n('yfeed_field_' . $fieldName));
    }

    $content = $form->get();

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit');
    $fragment->setVar('title', $this->i18n('twitter'));
    $fragment->setVar('body', $content, false);
    $content = $fragment->parse('core/page/section.php');

    echo $content;

}
