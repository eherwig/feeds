<?php

/**
 * This file is part of the Feeds package.
 *
 * @author FriendsOfREDAXO
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Watson\Workflows\Feeds;

use Watson\Foundation\SupportProvider;
use Watson\Foundation\Workflow;

class FeedProvider extends SupportProvider
{
    /**
     * Register the directory to search a translation file.
     *
     * @return string
     */
    public function i18n()
    {
        return __DIR__;
    }

    /**
     * Register the service provider.
     *
     * @return Workflow|array
     */
    public function register()
    {
        if (\rex_addon::get('feeds')->isAvailable()) {
            return $this->registerStoreSearch();
        }
        return [];
    }

    /**
     * Register yform search.
     *
     * @return Workflow
     */
    public function registerStoreSearch()
    {
        return new FeedSearch();
    }
}
