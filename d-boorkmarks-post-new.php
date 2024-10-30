<?php

class D_Plugin_Bookmarks_Post_New {
	
	
	public function renderPage() {
		// set headers
		//$this->setHeaders();
		// handle post
		if($_SERVER['REQUEST_METHOD'] == 'POST') {
		
			if(isset($_POST['cat_id'])){
				$this->handleCategory();
			}else{
				$this->handlePost();
			}
		}
		else if(isset($_GET['success'])) {
			include dirname(__FILE__).'/templates/post-new.php';
		}
		else if(isset($_GET['post']) and $_GET['type'] == 'edit') {
			include dirname(__FILE__).'/templates/post-edit.php';
		}
		else if(isset($_GET['post']) and $_GET['type'] == 'delete') {
			$this->deletePost();
			
		}
		else if(isset($_GET['category']) and $_GET['type'] == 'edit') {
			include dirname(__FILE__).'/templates/category-edit.php';
		}
		else if(isset($_GET['category']) and $_GET['type'] == 'delete') {
			$this->deleteCategory();
			
		}
		else {
			include dirname(__FILE__).'/templates/post-new.php';
		}
	}
	
	private function handleCategory(){
	
	
		wp_update_term($_POST['cat_id'], 'bookmark_categories', array('name' => $_POST['category_title']));
		if(empty($_POST['category_title'])){
			$message = __("Item not updated.", 'd_bookmarks').'!';
			include dirname(__FILE__).'/templates/post-new.php';
			die;
		}
		$success = __("Category Successfully Updated", 'd_bookmarks').'!';
		include dirname(__FILE__).'/templates/post-new.php';
			die;
	}
	
	private function deleteCategory() {
		
		wp_delete_term( $_GET['category'], 'bookmark_categories' );
		$success= __("Bookmark Category Successfully deleted", 'd_bookmarks').'!';
		include dirname(__FILE__).'/templates/post-new.php';
			die;
	}
	
	
	private function deletePost() {
		wp_delete_post($_GET['post']);
		$success= __("Bookmark Successfully deleted", 'd_bookmarks').'!';
		include dirname(__FILE__).'/templates/post-new.php';
			die;
	}
	
	private function handlePost() {
	
		global $wpdb;
		
		// get values
		$value_id = $id = $_POST['post_id'];
		$value_title = $title = $_POST['post_title'];
		$value_url = $url = $_POST['post_url'];
		$value_content= $content = $_POST['post_content'];
		$value_tags = $tags = $_POST['post_tags'];
		$categories = array();
		$current_user = wp_get_current_user();
		$category_id = '';
		$post_category = $_POST['post_category'];
		$post_category_id = $_POST['category_id'];

		// get categories
/* 		foreach($_POST as $name=>$value) {
			if($value!='1') continue;
			
			$split = explode('category_id_', $name);
			if(count($split)==2 && empty($split[0])) {
				$categories[] = ($split[1]);
			}
		}
		$value_categories = $categories; */
		
		// validate
		if(empty($title)) {
			$message= __("No Title given", 'd_bookmarks').'!';
			include dirname(__FILE__).'/templates/post-new.php';
			die;
		}
		else if(empty($url)) {
			$message= __("No URL given", 'd_bookmarks').'!';
			include dirname(__FILE__).'/templates/post-new.php';
			die;
		}
		/* else if($this->linkExists($url)) {
			$message= __("This Bookmark already exists", 'd_bookmarks').'!';
			include dirname(__FILE__).'/templates/post-new.php';
			die;
		}  */
		
		// add post category
		if(!empty($post_category)){
			
			/* $d_addpostcategory = $wpdb->insert( 
				$wpdb->prefix.'terms', 
				array( 
					'name' => $post_category, 
				), 
				array( 
					'%s', 
				) 
			); */
			
			$term = term_exists($post_category, 'bookmark_categories');
			$cat_slug = $post_category;
			if ($term !== 0 && $term !== null) {
			
				$term_table = $wpdb->prefix.'terms';
				$max_term = $wpdb->get_results("SELECT max(term_id) as max FROM $term_table");
				$max_term_id = $max_term[0]->max; 
				$cat_slug = $cat_slug.'-'.($max_term_id+1); 

			}
			
			$my_cat = array('cat_name' => $post_category, 'category_description' => '', 'category_nicename' => $cat_slug, 'category_parent' => '','taxonomy' => 'bookmark_categories' );

			// Create the category
			$category_id = wp_insert_category($my_cat);

			//$category_id = $wpdb->insert_id;
			
		}else if(!empty($post_category_id)){
		
			$category_id = $post_category_id;
			
		}
		
		// add post
			
		$addpostresult = $this->addPost($title, $content, $url, $tags, $category_id, $id);
		if($addpostresult!=true) {
			if($addpostresult==false) $message = 'Meta: '.__('Invalid Post-ID', 'd_bookmarks').'!';
			elseif(get_class($addpostresult)=='WP_Error') $message= 'Meta: '.__('Invalid Taxonomy', 'd_bookmarks').'!';
			elseif(is_string($addpostresult)) $message='Meta: '.__('Invalid Term', 'd_bookmarks').': '.$addpostresult;
			else $message = __('Unknown Error', 'd_bookmarks').'!';
			
			include dirname(__FILE__).'/templates/post-new.php';
			die;
		}
		if(!empty($id)){
			wp_redirect($_SERVER["PHP_SELF"].'?post_type=bookmarks&action=browserbookmark&success=1&type=edit');
		}else{
			wp_redirect($_SERVER["PHP_SELF"].'?post_type=bookmarks&action=browserbookmark&success=1');
		}	
	}
	
	private function linkExists($url) {
		$current_user = wp_get_current_user();
		
		$query = new WP_Query(array(
				'post_type' => 'bookmarks',
				'post_status' => 'publish',
				'post_author' => $current_user->ID,
				'posts_per_page' => -1,
				'caller_get_posts'=> 1
		));
		//echo '<pre>';print_r($query->posts);echo '</pre>';
		foreach($query->posts as $post) {
			$meta_url = get_post_meta($post->ID, '_D_Plugin_Bookmarks-bookmarks-url', true);
			if($meta_url==$url) return true;
		}
		wp_reset_query();
		
		return false;
	}
	
	private function addPost($title, $content, $link, $tags, $categories, $id) {
		// get user-info
		
		$current_user = wp_get_current_user();
		
		// construct entry
		$new_entry = array();
		
		$new_entry['post_title'] = esc_html($title);
		$new_entry['post_content'] = ($content);
		$new_entry['post_status'] = 'publish';
		$new_entry['post_type'] = 'bookmarks';
		$new_entry['post_author'] = $current_user->ID;
		$new_entry['tags_input'] = esc_html($tags);

		// insert the post into the database
		if(!empty($id)){
			$new_entry['ID'] = $post_id = $id;
			wp_update_post( $new_entry );
			update_post_meta($id, '_D_Plugin_Bookmarks-bookmarks-url', strip_tags($link));
		}else{
			$post_id = wp_insert_post( $new_entry );
			add_post_meta($post_id, '_D_Plugin_Bookmarks-bookmarks-url', strip_tags($link), true);
		}
		if($post_id==0 || is_object($post_id)) return false;
		
		wp_set_post_categories($post_id, $categories);
		// add meta
		
		
		// set categories
		$metaresult=wp_set_post_terms($post_id, $categories, 'bookmark_categories', false);
		if(!is_array($metaresult)) {
			return $metaresult;
		}
		
		return true;
	}
	
	private function setHeaders() {
		//header_remove('X-Frame-Options');
	}
	
	private function printHtmlHeader() {
		global $title, $hook_suffix, $current_screen, $wp_locale, $pagenow, $wp_version, $current_site, $update_title, $total_update_count, $parent_file;
		
		_wp_admin_html_begin();
		wp_enqueue_style( 'colors' );
		wp_enqueue_style( 'ie' );
		wp_enqueue_script('utils');
		do_action('admin_enqueue_scripts', $hook_suffix);
		do_action("admin_print_styles-$hook_suffix");
		do_action('admin_print_styles');
		do_action("admin_print_scripts-$hook_suffix");
		do_action('admin_print_scripts');
		do_action("admin_head-$hook_suffix");
		do_action('admin_head');
	}
}