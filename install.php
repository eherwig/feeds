<?php
/**
 * This file is part of the Feeds package.
 *
 * @author FriendsOfREDAXO
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$addon = rex_addon::get('feeds');

// Check if YFeed is installed, geenrate new tables based on YFeed.

if (rex_addon::get('yfeed')->isAvailable() && !$addon->hasConfig('yfeed_migration')) {
    
    $yfeed = rex_addon::get('yfeed');
    
        if ($yfeed->hasConfig('facebook_app_id'))
        {
         $addon->setConfig('facebook_app_id', $yfeed->getConfig('facebook_app_id'));
        }

    if ($yfeed->hasConfig('facebook_app_title'))
        {
         $addon->setConfig('facebook_app_title', $yfeed->getConfig('facebook_app_title'));
        }
    if ($yfeed->hasConfig('facebook_app_secret'))
        {
         $addon->setConfig('facebook_app_secret', $yfeed->getConfig('facebook_app_secret'));
        }
    if ($yfeed->hasConfig('google_key'))
        {
         $addon->setConfig('google_key', $yfeed->getConfig('google_key'));
        }

    if ($yfeed->hasConfig('twitter_consumer_key'))
        {
         $addon->setConfig('twitter_consumer_key', $yfeed->getConfig('twitter_consumer_key'));
        }
    if ($yfeed->hasConfig('twitter_consumer_secret'))
        {
           $addon->setConfig('twitter_consumer_secret', $yfeed->getConfig('twitter_consumer_secret'));
        }

    if ($yfeed->hasConfig('twitter_oauth_token'))
        {
            $addon->setConfig('twitter_oauth_token', $yfeed->getConfig('twitter_oauth_token'));
        }
   if ($yfeed->hasConfig('twitter_oauth_token_secret'))
        {
             $addon->setConfig('twitter_oauth_token_secret', $yfeed->getConfig('twitter_oauth_token_secret'));
        }
 $sql = rex_sql::factory();
 $sql->setQuery('CREATE TABLE IF NOT EXISTS ' . rex::getTable('feeds_stream') . ' LIKE ' . rex::getTable('yfeed_stream'));  
 $sql->setQuery('INSERT ' .  rex::getTable('feeds_stream') . ' SELECT * FROM ' . rex::getTable('yfeed_stream')); 
 $sql->setQuery('CREATE TABLE IF NOT EXISTS '  .  rex::getTable('feeds_item') . ' LIKE ' . rex::getTable('yfeed_item'));  
 $sql->setQuery('INSERT '  .  rex::getTable('feeds_item') . ' SELECT * FROM ' . rex::getTable('yfeed_item')); 
 $addon->setConfig('yfeed_migration','1');   
}


rex_sql_table::get(rex::getTable('feeds_stream'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('namespace', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('type', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('type_params', 'text'))
    ->ensureColumn(new rex_sql_column('title', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('image', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('etag', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('last_modified', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('status', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('default_status', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('updateuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->ensureColumn(new rex_sql_column('updatedate', 'datetime'))
    ->ensure();

rex_sql_table::get(rex::getTable('feeds_item'))
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('stream_id', 'int(10) unsigned'))
    ->ensureColumn(new rex_sql_column('uid', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('type', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('title', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('content', 'text', true))
    ->ensureColumn(new rex_sql_column('content_raw', 'text'))
    ->ensureColumn(new rex_sql_column('url', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('date', 'datetime'))
    ->ensureColumn(new rex_sql_column('author', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('language', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('media', 'longtext'))
    ->ensureColumn(new rex_sql_column('mediasource', 'text'))
    ->ensureColumn(new rex_sql_column('raw', 'text'))
    ->ensureColumn(new rex_sql_column('status', 'tinyint(1)', false, '1'))
    ->ensureColumn(new rex_sql_column('changed_by_user', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('createuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('updateuser', 'varchar(255)'))
    ->ensureColumn(new rex_sql_column('createdate', 'datetime'))
    ->ensureColumn(new rex_sql_column('updatedate', 'datetime'))
    ->ensureIndex(new rex_sql_index('stream_id', ['stream_id']))
    ->ensureForeignKey(new rex_sql_foreign_key('feeds_item_feeds_fk', rex::getTable('feeds_item'), ['stream_id' => 'id'], rex_sql_foreign_key::CASCADE, rex_sql_foreign_key::CASCADE))
 ->ensure();

//CHANGE content to utf8mb4_unicode_ci to display Emoticons
$c = rex_sql::factory();
$c->setQuery('ALTER TABLE `' . rex::getTable('feeds_item') . '` CHANGE `content` `content` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$c->setQuery('ALTER TABLE `' . rex::getTable('feeds_item') . '` CHANGE `title` `title` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

