<?php

use XFran\GTrends\GTrends;

class Autoposting_Generator
{
    public function run()
    {
        $topics = get_option('autoposting_topics', '');
        $api_key = get_option('autoposting_api_key', '');

        // Преобразуем строку в массив
        $topics = array_filter(array_map('trim', explode("\n", $topics)));

        if (empty($topics)) {
            return;
        }

        $gtrends = new GTrends($api_key);

        foreach ($topics as $topic) {
            $results = $gtrends->getRelatedSearchQueries([$topic]);

            $topWords = [];
            if (!empty($results['top'])) {
                foreach (array_slice($results['top'], 0, 10) as $item) {
                    $topWords[] = $item['query'];
                }
            }

            global $wpdb;
            $table_name = $wpdb->prefix . 'autoposter';

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
    }
}