<?php
/*
Plugin Name: Hierarchical Bookmark System
Plugin URI: http://damiestechnologies.com/
Description: Hierarchical Bookmark System avails an eye catching interface to save and categorize bookmarks in a hierarchical fashion.
Version: 1.0
Author: Amit Tekwani
Author URI: http://damiestechnologies.com/
*/
require_once dirname(__FILE__)."/lib/Minifier.php";
class D_Plugin_Bookmarks {

	private static $_instance = null;
	private static $_fields_bookmarks = array("url"=>  "Link");
	private static $_debug = false;
	private static $_classname = null;

	/**
	 * @return WP_Plugin_References
	 */
	public static function getInstance() {
		if(is_null(self::$_instance) || !is_obj(self::$_instance)) {
			self::$_instance = new D_Plugin_Bookmarks();
		}

		return self::$_instance;
	}

	public function __construct() {
		load_plugin_textdomain('d_bookmarks', false, basename( dirname( __FILE__ ) ).'/languages/');
		// get classname
		self::$_classname = get_class($this);

		// enable debug
		if(self::$_debug) {
			error_reporting(E_ALL);
		}
	}

	/**
	 * main-method which registers everything via the WP-api
	 */
	public function register() {

		// register methods
		register_activation_hook(__FILE__, array($this,'onEnable'));
		register_deactivation_hook(__FILE__, array($this,'onDisable'));
		add_action('init', array($this,'onInit'));
		add_action('admin_init', array($this,'onAdminInit'));
		add_action('add_meta_boxes', array($this,'onAddMetaBoxes'));
		add_action('save_post', array($this,'onSavePost'));
		add_action('admin_action_browserbookmark', array($this,'onActionBrowserbookmark'));
		/* Filter the single_template with our custom function*/
		add_filter('the_content', array( $this, 'bookmarkTemplate' ));
		add_filter('the_title', array($this, 'bookmarkTitle'));
		add_filter('wp_nav_menu', array($this,'menu_shortcodes'));
		add_shortcode('button_bookmark', array($this, 'Bookmark_Button_Link'));

	}
	
	public function bookmarkNiceUrl($bookmark_url) {
		if (substr($bookmark_url, 0, 7) == 'http://') :
			$bookmark_url_nice	= substr($bookmark_url, 7);
		elseif (substr($bookmark_url, 0, 8) == 'https://') :
			$bookmark_url_nice	= substr($bookmark_url, 8);
		else :
			$bookmark_url_nice	= $bookmark_url;
		endif;
		
		if (substr($bookmark_url_nice, -1) == '/') : $bookmark_url_nice = substr($bookmark_url_nice, 0, -1); endif;
		
		return $bookmark_url_nice;
	}
	
	public function bookmarkTitle($title) {
		global $post;
		
		$bookmark_url	= get_post_meta($post->ID, '_D_Plugin_Bookmarks-bookmarks-url', true);
		$bookmark_url_nice	= $this->bookmarkNiceUrl($bookmark_url);
		
		if (get_post_type() == 'bookmarks' && is_single()) {
			return '<a href="'.$bookmark_url.'" target="_blank" title="'.$bookmark_url_nice.'">'.$title.'</a>';
		}
		
		return $title;
	}
	
	function bookmarkTemplate($content) {
	global $post;
	
	if(get_post_type() == 'bookmarks') {
		
		$bookmark_url	= get_post_meta($post->ID, '_D_Plugin_Bookmarks-bookmarks-url', true);
		
		if (substr($bookmark_url, 0, 7) == 'http://') :
			$bookmark_url_nice	= substr($bookmark_url, 7);
		elseif (substr($bookmark_url, 0, 8) == 'https://') :
			$bookmark_url_nice	= substr($bookmark_url, 8);
		else :
			$bookmark_url_nice	= $bookmark_url;
			$bookmark_url		= 'http://'.$bookmark_url;
		endif;
		
		$bookmark_title	= get_the_title($post->ID);
		
		if (substr($bookmark_url_nice, -1) == '/') : $bookmark_url_nice = substr($bookmark_url_nice, 0, -1); endif;
		
		$content .= '
		<style type="text/css">
		#add-to-bookmarks {
			display: inline-block;
			position: relative;
			cursor: move;
			color: #333;
			background: #e6e6e6;
			background-image: -webkit-gradient(linear,left bottom,left top,color-stop(7%,#e6e6e6),color-stop(77%,#d8d8d8));
			background-image: -webkit-linear-gradient(bottom,#e6e6e6 7%,#d8d8d8 77%);
			background-image: -moz-linear-gradient(bottom,#e6e6e6 7%,#d8d8d8 77%);
			background-image: -o-linear-gradient(bottom,#e6e6e6 7%,#d8d8d8 77%);
			background-image: linear-gradient(to top,#e6e6e6 7%,#d8d8d8 77%);
			-webkit-border-radius: 5px;
			border-radius: 5px;
			border: 1px solid #b4b4b4;
			font-style: normal;
			line-height: 16px;
			font-size: 12px;
			text-decoration: none;
			text-shadow: 0 1px 0 #fff;
		}
		
		#add-to-bookmarks span {
			padding: 4px 7px 4px 7px;
			margin: 0 5px;
			display: inline-block;
		}
		
		#add-to-bookmarks:after {
			content: "";
			width: 70%;
			height: 55%;
			z-index: -1;
			position: absolute;
			right: 10px;
			bottom: 9px;
			background: transparent;
			-webkit-transform: skew(20deg) rotate(6deg);
			-moz-transform: skew(20deg) rotate(6deg);
			transform: skew(20deg) rotate(6deg);
			-webkit-box-shadow: 0 10px 8px rgba(0,0,0,0.6);
			box-shadow: 0 10px 8px rgba(0,0,0,0.6);
		}
		</style>
		
		<div class="footer-bookmark">
		'.__('Link to Bookmark', 'd_bookmarks').': <a href="'.$bookmark_url.'" target="_blank" id="add-to-bookmarks"><span>'.$bookmark_url_nice.'</span></a>
		</div>
				';
		$content	= str_replace($bookmark_title, '<a href="'.$bookmark_url.'" target="_blank">'.$bookmark_title.'</a>', $content);
	}
	return $content;
}
			
	public function onInit() {
		$this->addStylesheets();
		$this->registerPostTypes();
	}	
	
	public function onAdminInit() {
		
	}

	public function onActionBrowserbookmark() {
		global $typenow;
		
		// allow bookmark-page to run inside a frame
		if(isset($typenow) && $typenow=="bookmarks") {
			require_once dirname(__FILE__).'/d-boorkmarks-post-new.php';
			
			$newPostObject = new D_Plugin_Bookmarks_Post_New();
			$newPostObject->renderPage();
			die;
		}
	}
	

	/**
	 * register client-stylesheets
	 */
	private function addStylesheets() {
		wp_register_style(self::$_classname."-frontend-style", plugin_dir_url( __FILE__ ) .'css/frontend.css' );
		wp_enqueue_style(self::$_classname."-frontend-style");
	}

	/**
	 * registers required posttypes
	 */
	private function registerPostTypes() {
		// define post-types ...
		$bookmargs_args = array(
			"public" => true,
			"query_var" => "bookmarks",
			"labels" => array(
				"name" => __("Bookmarks", 'd_bookmarks'),
				"singular_name" => __("Bookmark", 'd_bookmarks'),
				"add_new" => __("Add", 'd_bookmarks'),
				"add_new_item" => __("Add New Bookmark", 'd_bookmarks'),
				"edit_item" => __("Edit Bookmark", 'd_bookmarks'),
				"new_item" => __("New Bookmark", 'd_bookmarks'),
				"view_item" => __("View Bookmark", 'd_bookmarks'),
				"search_items" => __("Search Bookmarks", 'd_bookmarks'),
				"not_found" => __("Bookmark Not Found", 'd_bookmarks'),
				"not_found_in_trash" => __("Not Found in Trash", 'd_bookmarks'),
			),
			"supports" => array(
				"title",
				"thumbnail",
				"editor",
				"author",
				"post_tag",
				"post_meta"
			),
			'capabilities' => array(
				'edit_post' => 'read',
				'edit_posts' => 'read',
				'edit_others_posts' => 'edit_others_posts',
				'publish_posts' => 'publish_posts',
				'read_post' => 'read_post',
				'read_private_posts' => 'read_private_posts',
				'delete_post' => 'delete_post',
			),
			"capability_type" => "post"
		);

		$category_args = array(
			"hierarchical" => true,
			"query_var" => "bookmark_categories",
			"labels" => array(
				"name" => __('Categories', 'd_bookmarks'),
				"singular_name" => __('Category', 'd_bookmarks'),
				"edit_item" => __('Edit Category', 'd_bookmarks'),
				"update_item" => __('Update Category', 'd_bookmarks'),
				"add_new_item" => __('Add New Category', 'd_bookmarks'),
				"new_item_name" => __("New Category's Name", 'd_bookmarks'),
				"all_items" => __("All Categories", 'd_bookmarks'),
				"search_items" => __("Search Categories", 'd_bookmarks'),
				"parent_item" => __("Parent Category", 'd_bookmarks'),
				"parent_item_colon" => __("Parent Category", 'd_bookmarks')
			)
		);

		// ... and register them
		register_post_type("bookmarks", $bookmargs_args);
		register_taxonomy("bookmark_categories", "bookmarks", $category_args);
		
		//register_taxonomy_for_object_type('category', 'product');
		register_taxonomy_for_object_type('post_tag', 'bookmarks');
		
		
	}

	/**
	 * called when post wants to be saved
	 * @param int $post_id
	 */
	public function onSavePost($post_id) {
		$post = get_post($post_id);
		if($post->post_type == "bookmarks") {
			$inputs = self::$_fields_bookmarks;
			foreach($inputs as $input => $text) {
				$input = self::$_classname."-".$post->post_type."-".$input;
				if(isset($_POST[$input])) {
					if (substr($_POST[$input], 0, 7) != 'http://' and substr($_POST[$input], 0, 8) != 'https://') :
						$_POST[$input]	= 'http://'.$_POST[$input];
					endif;
					update_post_meta($post_id,'_'.$input,strip_tags($_POST[$input]));
				}
			}
		}
	}


	/**
	 * adds all the metaboxes needed for this plugin
	 */
	public function onAddMetaBoxes(){
		self::_addMetaBox("general", __("General", 'd_bookmarks'), "bookmarks", self::$_fields_bookmarks);
	}


	/**
	 * adds a meta-box with given fields. this will automatically register a callback-method which builds a form in backend
	 * @param string $id
	 * @param string $title
	 * @param string $postType
	 * @param Array $fields
	 * @param string $context
	 * @param string $priority
	 * @param Array $callbackArgs
	 */
	private static function _addMetaBox($id, $title, $postType, $fields, $context="advanced", $priority="default", $callbackArgs=null) {

		// patch ids with unique name
		$fields_patched = array();
		foreach($fields as $id => $description) {
			$fields_patched[self::$_classname."-".$postType."-".$id] = $description;
		}

		// create args
		$args = array("fields"=>$fields_patched);
		array_push($args, $callbackArgs);

		// add meta-box
		add_meta_box($id, $title, self::$_classname."::_metaBoxCallback", $postType, $context, $priority, $args);
	}


	/**
	 * internal callbox-method used to build a formular with the fields given on registration
	 * @param Array $post
	 * @param Array $data
	 */
	public static function _metaBoxCallback($post, $data){
		?>
		<table class="form-table">
			<?php foreach($data["args"]["fields"] as $id => $description) {
				$value = get_post_meta($post->ID,"_".$id,true);
				if(isset($_GET[$id]) && strlen($value)<=0) $value=$_GET[$id]; ?>
				<tr valign="top">
					<th scope="row"><label for="<?php echo $id ?>"><?php echo $description ?>:</label>
					</th>
					<td><input id="<?php echo $id ?>"
						value="<?php echo $value; ?>"
						name="<?php echo $id; ?>" type="text" />
					</td>
				</tr>
			<?php } ?>
		</table>
		<?php 
	}

	/**
	 * will be called on plugin-activation
	 */
	public function onEnable() {

        $this->registerPostTypes();
		//$this->create_bookmarks_table();
        global $wp_rewrite;
        $wp_rewrite->flush_rules();
	}

	/**
	 * will be called on plugin-disable
	 */
	public function onDisable() {
        global $wp_rewrite;
		//$this->drop_bookmarks_table();
        $wp_rewrite->flush_rules();
	}
	
	public function menu_shortcodes( $menu ){ 
	
	
			$jsCode = file_get_contents(dirname(__FILE__)."/js/bookmarkscript-loader.js");
			$minifiedCode = Minifier::minify($jsCode);
			$minifiedCode.="window.wpBookmarksAdminPath = '".get_admin_url()."';";
			$minifiedCode.="window.wpBookmarksIncludePath = '".includes_url()."';";
			$minifiedCode.="window.wpBookmarksCloseButtonLabel = '". __('Close','d_bookmarks') ."';";
			$minifiedCode.="window.wpBookmarksPluginPath = '".plugins_url('', __FILE__)."';";
			$minifiedCode.="loadScript('".plugins_url('js/bookmark.js?'.mt_rand(), __FILE__)."')";
			return str_replace( array('http://button_bookmark','button_bookmark'), array('javascript:{'.$minifiedCode.'};','javascript:{'.$minifiedCode.'};'), do_shortcode( $menu ) ); 
			
	} 
	
	public function Bookmark_Button_Link($atts, $content = null) {
	   extract(shortcode_atts(array('link' => '#'), $atts));
	   
		$jsCode = file_get_contents(dirname(__FILE__)."/js/bookmarkscript-loader.js");
		$minifiedCode = Minifier::minify($jsCode);
		$minifiedCode.="window.wpBookmarksAdminPath = '".get_admin_url()."';";
		$minifiedCode.="window.wpBookmarksIncludePath = '".includes_url()."';";
		$minifiedCode.="window.wpBookmarksCloseButtonLabel = '". __('Close','d_bookmarks') ."';";
		$minifiedCode.="window.wpBookmarksPluginPath = '".plugins_url('', __FILE__)."';";
		$minifiedCode.="loadScript('".plugins_url('js/bookmark.js?'.mt_rand(), __FILE__)."')";
		if(isset($atts['icon'])){
			$ret.='<a title="Bookmark" href="javascript:{'.$minifiedCode.'};" style=""><img src="'.$atts["icon"].'" /></a>';
		}else if(isset($atts['text'])){
			$ret.='<a title="Bookmark" href="javascript:{'.$minifiedCode.'};" style="">'.$atts["text"].'</a>';
		}else{
			$ret.='<a title="Bookmark" href="javascript:{'.$minifiedCode.'};" style="">Bookmark</a>';
		}  
		
		
		return $ret;
	}
	
}
D_Plugin_Bookmarks::getInstance()->register();
		
?>
