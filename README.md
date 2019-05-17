# redaxo_yfeed
YFeed

Mit diesem AddOn kann man unterschiedliche Feeds einlesen.

Im Moment gehen Facebook, Twitter und RSS Feeds.
Diese werden dauerhaft in einer Datenbank gesichert und können
so für die eigene Webseite genutzt werden. (Socialwall oder ähnliches)

Codebeispiel zum Auslesen eines Feeds mit der ID 1:

```
<?php
    $stream_id = 1;
    $media_manager_type = 'd2u_helper_yfeed_small';
	$stream = rex_yfeed_stream::get($stream_id);
	$items = $stream->getPreloadedItems(); // Standard gibt 5 Einträge zurück, sonst gewünschte Anzahl übergeben

    foreach($items as $item) {
		print '<a href="'. $item->getUrl() .'" title="'. $stream->getTitle() .'">';
        print '<img src="index.php?rex_media_type='. $media_manager_type .'&rex_media_file='. $item->getId() .'.yfeed"  alt="'. $item->getTitle() .'" title="'. $item->getTitle() .'">'; 
		print '</a>';
    }
?>```
