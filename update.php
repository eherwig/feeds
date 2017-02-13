<?php

/** @var rex_addon $this */

if (rex_string::versionCompare($this->getVersion(), '1.1.0-beta3', '<')) {
    rex_sql_table::get(rex::getTable('yfeed_item'))
        ->ensureColumn(new rex_sql_column('status', 'tinyint(1)', false, 1))
        ->alter();
}
