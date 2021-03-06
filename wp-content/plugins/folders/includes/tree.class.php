<?php
defined('ABSPATH') or die('Nope, not accessing this');
class WCP_Tree {
    public function __construct() {
        parent::__construct();
    }

    public static function get_full_tree_data($post_type) {
        $isAjax = (!empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ]) == 'xmlhttprequest')?1:0;
        if(isset($_GET[$post_type]) || ! $isAjax) {
            update_option("selected_" . $post_type . "_folder", "");
        }
        $string = self::get_folder_category_data($post_type, 0, 0);
        return $string['string'];
    }

    public static function get_folder_category_data($post_type, $parent = 0, $parentStatus = 0) {
//        echo "<pre>"; print_r($post_type); die;
        $terms = get_terms( $post_type, array(
            'hide_empty' => false,
            'parent'   => $parent,
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'hierarchical' => false,
            'update_count_callback' => '_update_generic_term_count',
            'meta_query' => [[
                'key' => 'wcp_custom_order',
                'type' => 'NUMERIC',
            ]]
        ));
        $string = "";
        $child = 0;
        $isAjax = (!empty( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && strtolower( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ]) == 'xmlhttprequest')?1:0;
        if(!empty($terms)) {
            $child = count($terms);
            foreach($terms as $term) {
                $status = get_term_meta($term->term_id, "is_active", true);
                $return = self::get_folder_category_data($post_type, $term->term_id, $status);
                $class = ($status == 1 && $return['child']>0)?"active":"";
                $class .= ($return['child'])>0?" has-sub-tree":"";
                if($post_type == "attachment") {
                    $class .= (isset($_GET['term']) && $_GET['term'] == $term->slug)?" active-item active-term":"";
                    if(isset($_GET[$post_type]) && $_GET[$post_type] == $term->slug) {
                        update_option("selected_".$post_type."_folder", $term->term_id);
                    }
                    if(!isset($_GET[$post_type]) && $isAjax) {
                        $termId = get_option("selected_".$post_type."_folder");
                        $class .= ($termId == $term->term_id)?" active-item active-term":"";
                    }
                } else {
                    $class .= (isset($_GET[$post_type]) && $_GET[$post_type] == $term->slug)?" active-item active-term":"";
                    if(isset($_GET[$post_type]) && $_GET[$post_type] == $term->slug) {
                        update_option("selected_" . $post_type . "_folder", $term->term_id);
                    }
                    if(!isset($_GET[$post_type]) && $isAjax) {
                        $termId = get_option("selected_".$post_type."_folder");
                        $class .= ($termId == $term->term_id)?" active-item active-term":"";
                    }
                }
                $status = get_term_meta($term->term_id, "is_highlighted", true);
                $class .= ($status == 1)?" is-high":"";
                $count = ($term->count != 0)?"<span class='total-count'>{$term->count}</span>":"";
                $delete_nonce = wp_create_nonce('wcp_folder_delete_term_'.$term->term_id);
                $rename_nonce = wp_create_nonce('wcp_folder_rename_term_'.$term->term_id);
                $highlight_nonce = wp_create_nonce('wcp_folder_highlight_term_'.$term->term_id);
                $term_nonce = wp_create_nonce('wcp_folder_term_'.$term->term_id);
                $string .= "<li data-nonce='{$term_nonce}' data-star='{$highlight_nonce}' data-rename='{$rename_nonce}' data-delete='{$delete_nonce}' data-slug='{$term->slug}' class='ui-state-default route {$class}' id='wcp_folder_{$term->term_id}' data-folder-id='{$term->term_id}'><h3 class='title' title='{$term->name}' id='title_{$term->term_id}'><span class='title-text'>{$term->name}</span> <span class='update-inline-record'></span> {$count} <span class='star-icon'></span></h3><span class='nav-icon'><i class='wcp-icon folder-icon-arrow_right'></i></span><span class='ui-icon'><i class='wcp-icon folder-icon-folder'></i></span>	<ul class='space' id='space_{$term->term_id}'>";
                $string .= $return['string'];
                $string .= "</ul></li>";
            }
        }
        return array(
            'string' =>$string,
            'child' => $child
        );
    }

    public static function get_option_data_for_select($post_type) {
        $string = "<option value='0'>Parent Folder</option>";
        $string .=  self::get_folder_option_data($post_type, 0, '&nbsp;&nbsp;');
        return $string;
    }

    public static function get_folder_option_data($post_type, $parent = 0, $space = "") {
        $terms = get_terms( $post_type, array(
            'hide_empty' => false,
            'parent'   => $parent,
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'hierarchical' => false,
            'meta_query' => [[
                'key' => 'wcp_custom_order',
                'type' => 'NUMERIC',
            ]]
        ) );

        $selected_term = get_option("selected_" . $post_type . "_folder");


        $string = "";
        if(!empty($terms)) {
            foreach($terms as $term) {
                $selected = ($selected_term == $term->term_id)?"selected":"";
                $string .= "<option {$selected} value='{$term->term_id}'>{$space}{$term->name}</option>";
                $string .= self::get_folder_option_data($post_type, $term->term_id, $space."&nbsp&nbsp");
            }
        }
        return $string;
    }
}