<?php
 namespace imwpf\builtin; use imwpf\admin\Options; use imwpf\modules\Cron; class SEO { public function start() { $values = Options::getInstance('imwpf_options')->get('seo'); if (!$values) { return ; } if ($values["auto_post"] == "1") { add_action("wp_insert_post", array($this, 'autoPostToBaidu'), 10, 2); } if ($values['post_hist'] == "1") { add_action('post_hist', array($this, 'postHistory')); } } public function autoPostToBaidu($id, $post = null) { $value = Options::getInstance('imwpf_options')->get('seo'); if (empty($value['bd_token'])) { return false; } $siteURL = trim(str_replace(array('https://','http://'), "", get_bloginfo('url')), '/'); if ($post != null) { $url = get_permalink($post); } else { $url = get_permalink($id); } $res = \imwpf\modules\Baidu::push($siteURL, $value['bd_token'], $url); if ($res) { update_option("imwpf_bdres", $res, 'no') OR add_option("imwpf_bdres", $res, '', 'no'); } } public function postHistory() { global $wpdb; $lastID = get_option("post_hist_id"); if (!$lastID) { $lastID = 0; } $id = $wpdb->get_var("SELECT ID FROM {$wpdb->prefix}posts WHERE ID > $lastID LIMIT 1"); $this->autoPostToBaidu($id); update_option("post_hist_id", $id, 'no') OR add_option("post_hist_id", '', 'no'); } } 