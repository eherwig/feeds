<?php
/**
 * This file is part of the Watson package.
 *
 * @author (c) Thomas Blum <thomas@addoff.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Watson\Workflows\Feeds;

use Watson\Foundation\Command;
use Watson\Foundation\Documentation;
use Watson\Foundation\Result;
use Watson\Foundation\ResultEntry;
use Watson\Foundation\Watson;
use Watson\Foundation\Workflow;

class FeedSearch extends Workflow
{
    /**
     * Provide the commands of the search.
     *
     * @return array
     */
    public function commands()
    {
        return ['feed'];
    }

    /**
     * @return Documentation
     */
    public function documentation()
    {
        $documentation = new Documentation();
        $documentation->setDescription(Watson::translate('watson_feeds_documentation_description'));
        $documentation->setUsage('feed keyword');
        $documentation->setExample('feed Phrase');

        return $documentation;
    }

    /**
     * Return array of registered page params.
     *
     * @return array
     */
    public function registerPageParams()
    {
        return [];
    }

    /**
     * Execute the command for the given Command.
     *
     * @param Command $command
     *
     * @return Result
     */
    public function fire(Command $command)
    {
        $result = new Result();

        $fields = ['title', 'content', 'date', 'author', ];

        $sql_query = '
       SELECT      * 
       FROM       ' . Watson::getTable('feeds_item') . ' 
       WHERE       ' . $command->getSqlWhere($fields) . ' 
       ORDER BY   date DESC';

        $items = $this->getDatabaseResults($sql_query);

        if (count($items))
        {
            $counter = 0;

            foreach ($items as $item)
            {

                $url = Watson::getUrl(['page' => 'feeds/items', 'base_path' => 'feeds/items', 'id' => $item['id'], 'func' => 'edit']);

                ++$counter;
                $entry = new ResultEntry();
                if ($counter == 1)
                {
                    $entry->setLegend('Feeds');
                }

                if (isset($item['title']))
                {
                    $entry->setValue($item['title'] . '', '(' . $item['id'] . ')');
                }
                else
                {
                    $entry->setValue($item['id']);
                }

               # $entry->setDescription($item['price'] . ' € | ' . $item['updatedate']);
                $entry->setIcon('watson-icon-yform');
                $entry->setUrl($url);
                $entry->setQuickLookUrl($url);

                $result->addEntry($entry);
            }
        }
        return $result;
    }

}

