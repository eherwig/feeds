<?php
/**
 * This file is part of the Feeds package.
 *
 * @author FriendsOfREDAXO
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class rex_cronjob_feeds extends rex_cronjob
{
    public function execute()
    {
        $streams = rex_feeds_stream::getAllActivated();

        $blacklist_streams = explode('|', $this->getParam('blacklist_streams'));
        $whitelist_streams = array_diff($streams, $blacklist_streams);

        $errors = [];
        $countAdded = 0;
        $countUpdated = 0;
        $countNotUpdatedChangedByUser = 0;
        foreach ($whitelist_streams as $stream) {
            try {
                $stream->fetch();
            } catch (\Exception $e) {
                $errors[] = $stream->getTitle();
            } catch (\Throwable $e) {
                $errors[] = $stream->getTitle();
            }
            $countAdded += $stream->getAddedCount();
            $countUpdated += $stream->getUpdateCount();
            $countNotUpdatedChangedByUser += $stream->getChangedByUserCount();
        }
        $this->setMessage(sprintf(
            '%d errors%s, %d items added, %d items updated, %d items not updated because changed by user',
            count($errors),
            $errors ? ' ('.implode(', ', $errors).')' : '',
            $countAdded,
            $countUpdated,
            $countNotUpdatedChangedByUser
        ));
        return empty($errors);
    }

    public function getTypeName()
    {
        return rex_addon::get('feeds')->i18n('feeds_cronjob');
    }
    public function getParamFields()
    {
        $fields = [
            [
                'label' => rex_i18n::msg('backup_filename'),
                'name' => 'filename',
                'type' => 'text',
                'default' => self::DEFAULT_FILENAME,
                'notice' => rex_i18n::msg('backup_filename_notice'),
            ],
            [
                'name' => 'sendmail',
                'type' => 'checkbox',
                'options' => [1 => rex_i18n::msg('backup_send_mail')],
            ],
        ];

        $tables = rex_backup::getTables();

        $fields[] = [
            'label' => rex_i18n::msg('feeds_blacklist_sources'),
            'name' => 'blacklist_streams',
            'type' => 'select',
            'attributes' => ['multiple' => 'multiple', 'data-live-search' => 'true'],
            'options' => array_combine(rex_feeds_stream::getAllActivated(), rex_feeds_stream::getAllActivated()),
            'notice' => rex_i18n::msg('feeds_blacklist_sources_notice'),
        ];

        return $fields;
    }
}
