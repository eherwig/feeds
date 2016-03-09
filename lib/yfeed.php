<?php

use PicoFeed\Parser\Item;
use PicoFeed\PicoFeedException;
use PicoFeed\Reader\Reader;
use TwitterOAuth\Auth\ApplicationOnlyAuth;
use TwitterOAuth\Serializer\ArraySerializer;
use TwitterOAuth\Serializer\ObjectSerializer;

class rex_yfeed
{
    private $id;
    private $title;
    private $url;
    private $image;
    private $table;
    private $fields;
    private $etag;
    private $lastModified;

    private $countAdded = 0;
    private $countUpdated = 0;

    private $debug = false;

    public function __construct(array $data)
    {
        $this->id = (int)$data['id'];
        $this->type = $data['type'];
        $this->title = $data['title'];
        $this->url = $data['url'];
        $params = json_decode($data['params'], true);
        $this->params = $params['params'];
        $this->image = $data['image'];
        $this->table = $data['table'];
        foreach (self::fields() as $field) {
            $this->fields[$field] = $data['field_' . $field];
        }
        $this->etag = $data['etag'];
        $this->lastModified = $data['last_modified'];

        //$this->checkTable();
    }

    /**
     * @param int $id
     * @return self
     */
    public static function get($id)
    {
        $id = (int)$id;
        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM ' . self::table() . ' WHERE `id` = :id LIMIT 1', ['id' => $id]);
        if (!$sql->getRows()) {
            return null;
        }
        return new self($sql->getArray()[0]);
    }

    /**
     * @return self[]
     */
    public static function getAll()
    {
        $sql = rex_sql::factory();
        $array = $sql->getArray('SELECT * FROM ' . self::table());
        $feeds = [];
        foreach ($array as $data) {
            $feeds[$data['id']] = new self($data);
        }
        return $feeds;
    }

    public static function table()
    {
        return rex::getTable('yfeed');
    }

    public static function fields()
    {
        return ['feed_id', 'uid', 'title', 'content', 'content_raw', 'url', 'date', 'author', 'language', 'enclosure_url', 'enclosure_type'];
    }

    public function fetch($type)
    {
        $sql = rex_sql::factory();
        $sql->setDebug($this->debug);

        $query = 'SELECT ' . $sql->escapeIdentifier($this->fields['uid']) . ' AS id FROM ' . $sql->escapeIdentifier($this->table);
        $params = [];
        if ($this->fields['feed_id']) {
            $query .= ' WHERE ' . $sql->escapeIdentifier($this->fields['feed_id']) . ' = :feed_id';
            $params = ['feed_id' => $this->id];
        }

        $existing = [];
        $array = $sql->getArray($query, $params);
        foreach ($array as $item) {
            $existing[$item['id']] = $item['id'];
        }

        $this->countAdded = 0;
        $this->countUpdated = 0;

        if ($type == 'rss') {

            try {
                $reader = new Reader();
                $resource = $reader->download($this->url, $this->lastModified, $this->etag);
                if (!$resource->isModified()) {
                    return;
                }
                $parser = $reader->getParser(
                    $resource->getUrl(),
                    $resource->getContent(),
                    $resource->getEncoding()
                );
                $parser->disableContentFiltering();
                $feed = $parser->execute();

                /** @type Item $item */
                foreach ($feed->getItems() as $item) {
                    $sql->setTable($this->table);
                    if ($this->fields['title']) {
                        $sql->setValue($this->fields['title'], $item->getTitle());
                    }
                    if ($this->fields['content_raw']) {
                        $sql->setValue($this->fields['content_raw'], $item->getContent());
                    }
                    $parser->filterItemContent($feed, $item);
                    if ($this->fields['content']) {
                        $sql->setValue($this->fields['content'], strip_tags($item->getContent()));
                    }
                    if ($this->fields['url']) {
                        $sql->setValue($this->fields['url'], $item->getUrl());
                    }
                    if ($this->fields['date']) {
                        $sql->setValue($this->fields['date'], date('Y-m-d H:i:s', $item->getDate()->format('U')));
                    }
                    if ($this->fields['author']) {
                        $sql->setValue($this->fields['author'], $item->getAuthor());
                    }
                    if ($this->fields['language']) {
                        $sql->setValue($this->fields['language'], $item->getLanguage());
                    }
                    if ($this->fields['enclosure_url']) {
                        $sql->setValue($this->fields['enclosure_url'], $item->getEnclosureUrl());
                    }
                    if ($this->fields['enclosure_type']) {
                        $sql->setValue($this->fields['enclosure_type'], $item->getEnclosureType());
                    }

                    if (isset($existing[$item->getId()])) {
                        $where = $sql->escapeIdentifier($this->fields['uid']) . ' = :uid';
                        $params = ['uid' => $sql->escapeIdentifier($item->getId())];
                        if ($this->fields['feed_id']) {
                            $where .= ' AND' . $sql->escapeIdentifier($this->fields['feed_id']) . ' = :feed_id';
                            $params['feed_id'] = $this->id;
                        }
                        $sql->setWhere($where, $params);
                        $sql->update();
                        $this->countUpdated++;
                    } else {
                        $sql->setValue($this->fields['uid'], $item->getId());
                        if ($this->fields['feed_id']) {
                            $sql->setValue($this->fields['feed_id'], $this->id);
                        }
                        $sql->insert();
                        $this->countAdded++;
                    }
                }

                $sql->setTable(self::table());
                $sql->setWhere('id = ' . $this->id);
                $sql->setValue('etag', $resource->getEtag());
                $sql->setValue('last_modified', $resource->getLastModified());
                $sql->update();

                return true;
            } catch (PicoFeedException $e) {
                // Do Something...
            }
        } elseif ($type == 'twitter') {
            $credentials = array(
                'consumer_key' => rex_config::get('yfeed', 'twitter_consumer_key'),
                'consumer_secret' => rex_config::get('yfeed', 'twitter_consumer_secret'),
                'oauth_token' => rex_config::get('yfeed', 'twitter_oauth_token'),
                'oauth_token_secret' => rex_config::get('yfeed', 'twitter_oauth_token_secret'),
            );
            $auth = new ApplicationOnlyAuth($credentials, new ObjectSerializer());

            /*
            $params = [
                'screen_name' => 'redaxo',
                'count' => 3,
                'exclude_replies' => true
            ];
            */
            $response = $auth->get($this->url, $this->params);
            /*
            echo '<pre>'; print_r($this->fields); echo '</pre><hr />';
            echo '<pre>'; print_r($auth->getHeaders()); echo '</pre>';
            echo '<pre>'; print_r($response); echo '</pre><hr />';
            exit();
            */
            foreach($response as $key => $item) {
                $sql->setTable($this->table);
                if ($this->fields['content_raw']) {
                    $sql->setValue($this->fields['content_raw'], $item->text);
                }
                if ($this->fields['content']) {
                    $sql->setValue($this->fields['content'], strip_tags($item->text));
                }
                if ($this->fields['date']) {
                    $date = new DateTime($item->created_at);
                    $sql->setValue($this->fields['date'], date('Y-m-d H:i:s', $date->format('U')));
                }
                if ($this->fields['url']) {
                    if (isset($item->urls) && isset($item->urls->url)) {
                        $sql->setValue($this->fields['url'], $item->urls->url);
                    }
                }
                if ($this->fields['author']) {
                    $sql->setValue($this->fields['author'], $item->user->name);
                }
                if ($this->fields['language']) {
                    $sql->setValue($this->fields['language'], $item->lang);
                }

                if (isset($existing[$item->id])) {
                    $where = $sql->escapeIdentifier($this->fields['uid']) . ' = :uid';
                    $params = ['uid' => $sql->escapeIdentifier($item->id)];
                    if ($this->fields['feed_id']) {
                        $where .= ' AND' . $sql->escapeIdentifier($this->fields['feed_id']) . ' = :feed_id';
                        $params['feed_id'] = $this->id;
                    }
                    $sql->setWhere($where, $params);
                    $sql->update();
                    $this->countUpdated++;
                } else {
                    $sql->setValue($this->fields['uid'], $item->id);
                    if ($this->fields['feed_id']) {
                        $sql->setValue($this->fields['feed_id'], $this->id);
                    }
                    $sql->insert();
                    $this->countAdded++;
                }
            }
        }

        return false;
    }

    public function countAdded()
    {
        return $this->countAdded;
    }

    public function countUpdated()
    {
        return $this->countUpdated;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getEtag()
    {
        return $this->etag;
    }

    public function getLastModified()
    {
        return $this->lastModified;
    }

    protected function checkTable()
    {
        $table = rex_sql_table::get($this->table);
        $yformFields = [];
        foreach ($this->fields as $fieldKey => $field) {
            if ($field != '' && !$table->hasColumn($field)) {
                $yformField = [
                    'table_name' => $this->table,
                    'name' => $field,
                    'type_id' => 'value',
                    'label' => $field,
                    'list_hidden' => "0",
                    'not_required' => "",
                    'search' => "1",
                ];
                switch ($fieldKey) {
                    case 'feed_id':
                        $yformField['type_name'] = 'select_sql';
                        $yformField['query'] = 'SELECT `id`, IF(`title` != "", `title`, `url`) AS name FROM ' . self::table() . ' ORDER BY name';
                        $yformField['multiple'] = "0";
                        $yformField['prio'] = "1";
                        $yformField['relation_table'] = "";
                        break;
                    case 'date':
                        $yformField['type_name'] = 'datetime';
                        $yformField['year_start'] = (date('Y') - 1);
                        $yformField['format'] = '###D###.###M###.###Y### - ###H###h ###I###m';
                        break;
                    case 'content':
                    case 'content_raw':
                        $yformField['type_name'] = 'textarea';
                        break;
                    default:
                        $yformField['type_name'] = 'text';
                        break;
                }
                $yformFields[] = $yformField;
            }
        }

        if (count($yformFields) && rex_yform_manager_table::get($this->table)) {
            rex_yform_manager_table_api::setTable(['table_name' => $this->table], $yformFields);
        }
    }
}
