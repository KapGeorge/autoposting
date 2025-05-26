<?php

use XFran\GTrends\GTrends;

class Autoposting_Generator
{
    public function run()
    {
        $topics = get_option('autoposting_topics', '');
        $api_key = get_option('autoposting_api_key', '');

        // reformatting topics
        $topics = array_filter(array_map('trim', explode("\n", $topics)));

        if (empty($topics)) {
            return;
        }
$options = [
    'hl' => 'en-US',
    'tz' => 0,
    'geo' => 'US',
    'time' => 'all',
    'category' => 0,
];
try {
    $gtrends = new GTrends($options);
    // Set proxy configs
    $gtrends->setProxyConfigs([
        'proxy_host' => '194.60.201.8',
        'proxy_port' => 3128,
        // 'proxy_user' => '', 
        // 'proxy_pass' => '',
    ]);
    echo '<div style="color:green;">GTrends connected!</div>';
} catch (\Throwable $e) {
    echo '<div style="color:red;">Error connection with GTrends: ' . $e->getMessage() . '</div>';
    return;
}
        echo implode(" and ", $topics);
        foreach ($topics as $topic) {
            // get tranding topics
            $categories = $gtrends->getCategories($topic);

            if (!empty($categories)) {
                foreach ($categories as $category) {
                    // for each category, get related search queries
                    $results = $gtrends->getRelatedSearchQueries($topics);
                    echo '<pre>';
                    print_r($results);
                    echo '</pre>';
                    $topWords = [];
                    if (!empty($results['top'])) {
                        foreach (array_slice($results['top'], 0, 3) as $item) {
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
                                'category' => $category['name'],
                                'word' => $word,
                                'created_at' => current_time('mysql')
                            ]
                        );
                    }
                }
            }
        }
    }
}
