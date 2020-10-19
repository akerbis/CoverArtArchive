# Cover Art Archive Web Service PHP library

This PHP library that allows you to easily access the Cover Art Archive API. Visit the [Cover Art Archive page](https://musicbrainz.org/doc/Cover_Art_Archive) for more information.

## Usage Example


```php
<?php
use CoverArtArchive\CoverArt;
use GuzzleHttp\Client;

require __DIR__ . '/vendor/autoload.php';

try {
    $coverArt = new CoverArt(CoverArt::TYPE_RELEASE, '1e477f68-c407-4eae-ad01-518528cedc2c', new Client());
    $front    = $coverArt->getFrontImage();
    ?>
    <img src="<?=$front->getThumbnail('small')?>" />
    <img src="<?=$front->getThumbnail('large')?>" />
    <img src="<?=$front->getImage()?>" /><br />
    <?php

} catch (Exception $e) {
    print $e->getMessage();    
}
```

## Requirements
PHP 7.2 and [cURL extension](https://php.net/manual/en/book.curl.php).