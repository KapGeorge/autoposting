<?php
// include Google Trends API library

require_once __DIR__ . '../../../vendor/autoload.php';
 
use XFran\GTrends\GTrends;

// recieve settings WordPress
$topics = get_option('autoposting_topics', []);
$api_key = get_option('autoposting_api_key', '');

// Если тем нет, ничего не делаем
if (empty($topics) || !is_array($topics)) {
    return;
}


$gtrends = new GTrends($api_key);

foreach ($topics as $topic) {
    // get related queries
    $results = $gtrends->getRelatedSearchQueries([$topic]);

    // get top words
    $topWords = [];
    if (!empty($results['top'])) {
        foreach (array_slice($results['top'], 0, 10) as $item) {
            $topWords[] = $item['query'];
        }
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'autoposter';

    // save top words to the database
    foreach ($topWords as $word) {
        $wpdb->insert(
            $table_name,
            [
                'topic' => $topic,
                'word' => $word,
                'created_at' => current_time('mysql')
            ]
        );
    }
}
