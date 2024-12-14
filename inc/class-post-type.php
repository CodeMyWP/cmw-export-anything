<?php

namespace CodeMyWP\Plugins\ExportAnything;

class PostType {
    public static function get_post_types() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cmw_ea_post_types';
        $sql = "SELECT * FROM $table_name";
        $results = $wpdb->get_results($sql);
        return $results;
    }
}

return new PostType();