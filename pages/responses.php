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

$list->addColumn('icon', '', 0, ['<th class="rex-table-icon">###VALUE###</th>', '<td class="rex-table-icon">###VALUE###</td>']);
$list->setColumnParams('icon', ['func' => 'edit', 'id' => '###id###']);
$list->setColumnFormat('icon', 'custom', function($params) {
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
        return $params['list']->getColumnLink('icon', '<i class="rex-icon ' . $icon . '"></i>');
    }
});

$list->removeColumn('id');

$list->setColumnLabel('namespace', $this->i18n('yfeed_namespace'));
$list->setColumnLabel('type', $this->i18n('yfeed_type'));
$list->setColumnLabel('title', $this->i18n('yfeed_title'));
$list->setColumnLabel('url', $this->i18n('yfeed_url'));
/*
$list->addColumn($this->i18n('function'), $this->i18n('edit'));
$list->setColumnLayout($this->i18n('function'), ['<th class="rex-table-action" colspan="3">###VALUE###</th>', '<td class="rex-table-action">###VALUE###</td>']);
$list->setColumnParams($this->i18n('function'), array('func' => 'edit', 'id' => '###id###'));

$list->addColumn('delete', $this->i18n('yfeed_delete'), -1, ['', '<td class="rex-table-action">###VALUE###</td>']);
$list->setColumnParams('delete', ['func' => 'delete', 'id' => '###id###']);
$list->addLinkAttribute('delete', 'onclick', "return confirm('" . $this->i18n('yfeed_delete_question') . "');");

$list->addColumn('fetch', $this->i18n('yfeed_fetch'), -1, ['', '<td class="rex-table-action">###VALUE###</td>']);
$list->setColumnParams('fetch', ['func' => 'fetch', 'id' => '###id###']);
*/
$content = $list->get();

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('response'));
$fragment->setVar('content', $content, false);
$content = $fragment->parse('core/page/section.php');

echo $content;
