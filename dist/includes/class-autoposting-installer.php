<?php

class Autoposting_Installer {
    public static function install() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'autoposter';

        // check if the table already exists
        // If the table already exists, we don't need to create it again
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name) {
            return;
        }

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            topic VARCHAR(255) NOT NULL,
            word VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
      public static function uninstall() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'autoposter';

        // drop the table if it exists
        $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
    }
}


  