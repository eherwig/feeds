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

$addon->includeFile(__DIR__ . '/install.php');

// if (rex_string::versionCompare($this->getVersion(), '1.1.0-beta3', '<')) {
//     rex_sql_table::get(rex::getTable('feeds_item'))
//         ->ensureColumn(new rex_sql_column('status', 'tinyint(1)', false, 1))
//         ->alter();
// }

