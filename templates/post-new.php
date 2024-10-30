	<?php 
	global $wpdb;
	// don't load directly
	if ( !defined('ABSPATH') )
		die('-1');
		
	$this->printHtmlHeader();
	
	/* $categories = get_terms('bookmark_categories', array(
		'hide_empty' => 0
	)); */
	$current_user = wp_get_current_user();
	$table_bookmarks = $wpdb->prefix.'dbookmarks';
	$table_bookmarks_cat = $wpdb->prefix.'dbookmarks_cat';
	
	
	if(!isset($value_title))   $value_title   = isset($_GET['post_title']) ? $_GET['post_title'] : '';
	if(!isset($value_url))     $value_url     = isset($_GET['post_url']) ? $_GET['post_url'] : '';
	if(!isset($value_content)) $value_content = isset($_GET['post_content']) ? $_GET['post_content'] : '';
	if(!isset($value_tags))    $value_tags = '';
	wp_enqueue_script('jquery');
	?>
	<link rel="stylesheet" href="<?php echo plugins_url("css/bookmark-frame.css?".mt_rand(), __FILE__); ?>" type="text/css" media="all">

	
	<script type="text/javascript" src="<?php echo plugins_url("js/jquery.simplyscroll.min.js?".mt_rand(), __FILE__); ?>"></script>
	<link rel="stylesheet" href="<?php echo plugins_url("css/jquery.simplyscroll.css?".mt_rand(), __FILE__); ?>" type="text/css" media="all">
	
	<script type="text/javascript">

		jQuery(document).ready(function() {
		
			jQuery(".scroller").simplyScroll({orientation:'vertical',customClass:'vert',auto: false, speed: 10});
			
			setTimeout(function(){jQuery('.messageBox').slideUp("slow")},3000);
			
			jQuery( ".tree-view" ).hover(
			  function() {
				jQuery(this).parent('ul').parent('.simply-scroll-clip').css('overflow','visible');
				
			  }, function() {
				jQuery(this).parent('ul').parent('.simply-scroll-clip').css('overflow','hidden');
			  }
			);
			
			jQuery( ".edit_bookmark" ).click(function(){
			
				var b_id = jQuery(this).attr('id');
				var mark_array = b_id.split('_');
				var post_id = mark_array[1];
				
				url = '<?php echo get_admin_url(); ?>'+'post.php?post='+post_id+'&post_type=bookmarks&action=browserbookmark&type=edit';
				url = url.replace(/https:\/\/sslsites.de\/kb.conlabz.de/, 'http://kb.conlabz.de');

				jQuery( ".add_edit" ).html('<div id="loading_box"></div>');	
					
				jQuery.get( url, function( data ) {
				  	
				  jQuery( ".add_edit" ).html( data );
				  jQuery( ".wide:eq(0)" ).focus();
				  
				});
			})
			
			jQuery( ".edit_cat" ).click(function(){
			
				var b_id = jQuery(this).attr('id');
				var mark_array = b_id.split('_');
				var cat_id = mark_array[1];
				
				url = '<?php echo get_admin_url(); ?>'+'post.php?category='+cat_id+'&post_type=bookmarks&action=browserbookmark&type=edit';
				url = url.replace(/https:\/\/sslsites.de\/kb.conlabz.de/, 'http://kb.conlabz.de');
				jQuery( ".add_edit" ).html('<div id="loading_box"></div>');	
					
				jQuery.get( url, function( data ) {
				  	
				  jQuery( ".add_edit" ).html( data );
				  jQuery( ".wide:eq(0)" ).focus();
				  
				}); 
			})

			jQuery('#category_ids').change(function() {
				
				if(jQuery(this).val() == -1){
					jQuery('#post_category').show();
				}else{
					jQuery('#post_category').hide();
				}
			});
			
			jQuery('*[jquerydefaultvalue]').each(function(index) {
				// set value to default
				if(jQuery(this).attr('value')=='') {
					this.value=jQuery(this).attr('jquerydefaultvalue');
					jQuery(this).addClass("containsDefaultValue");
				}
				
				// onclick-handler
				jQuery(this).click(function(){
					if(!this.valueChanged && this.value==jQuery(this).attr('jquerydefaultvalue')) this.value='';
					jQuery(this).removeClass("containsDefaultValue");
				});
				
				jQuery(this).keypress(function(){
					this.valueChanged=true;
				});
				
				// blur-handler
				jQuery(this).blur(function(){
					if(this.value=='')  {
					  this.value=jQuery(this).attr('jquerydefaultvalue');
					  jQuery(this).addClass("containsDefaultValue");
					  this.valueChanged=false;
					}
				});
				
				// form-handler
				var form = jQuery(this).parents('form:first');
				if(!form[0].hasJqueryDefaultValueHandler) {
					form.submit(function(){
					
					var myVariable = jQuery('input[name="post_url"]').val();
					if(/^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(myVariable)) {
					 
					} else {
					  alert("invalid url");
					  return false;
					}

						jQuery('*[jquerydefaultvalue]').each(function(index) {
							if(!this.valueChanged && this.value==jQuery(this).attr('jquerydefaultvalue')) {
								this.value='';
								this.valueChanged=true;
								jQuery(this).removeClass("containsDefaultValue");
							}
						});
					});
					form[0].hasJqueryDefaultValueHandler = true;
				}
			});
		});
	</script>
    
    <style type="text/css">
		.category {
			margin:		5px 0px 0px 0px;
			display:	block;
			text-align:	left;
		}
		
		.category input {
			margin-right:	5px;
		}
		
		.button-primary {
			display: inline-block;
			text-decoration: none;
			font-size: 12px;
			line-height: 23px;
			height: 24px;
			margin: 0;
			margin-top:	5px;
			padding: 0px 10px 1px;
			cursor: pointer;
			border-width: 1px;
			border-style: solid;
			-webkit-border-radius: 3px;
			-webkit-appearance: none;
			border-radius: 3px;
			white-space: nowrap;
			-webkit-box-sizing: border-box;
			-moz-box-sizing: border-box;
			box-sizing: border-box;
			
			background-image: linear-gradient(to bottom,#2a95c5,#21759b);
			border-color: #21759b;
			border-bottom-color: #1e6a8d;
			-webkit-box-shadow: inset 0 1px 0 rgba(120,200,230,0.5);
			box-shadow: inset 0 1px 0 rgba(120,200,230,0.5);
			color: #fff;
			text-decoration: none;
			text-shadow: 0 1px 0 rgba(0,0,0,0.1);
		}
	</style>
</head>
<body>
	
	<?php if(isset($_GET['success']) and $_GET['success'] == 1 and $_GET['type'] == 'edit') { ?>
		<div class="messageBox okay"><?php _e('Bookmark was successfully updated', 'd_bookmarks').'!'; ?></div>
	<?php } else if(($_GET['success']) and $_GET['success'] == 1){ ?>
		<div class="messageBox okay"><?php _e('Bookmark was successfully added', 'd_bookmarks').'!'; ?></div>
	<?php } ?>
	<?php 

	$user_global = get_userdatabylogin($global_user);
	$cond = '';
	if(!empty($user_global)){
		$cond = 'or post_author = '.$user_global->ID;
	}

	$bookmarks_list = $wpdb->get_results("SELECT wp_terms.*, wp_posts.* FROM wp_posts LEFT JOIN wp_term_relationships ON ( wp_term_relationships.object_id = wp_posts.ID ) LEFT JOIN wp_term_taxonomy ON ( wp_term_taxonomy.term_taxonomy_id = wp_term_relationships.term_taxonomy_id )LEFT JOIN wp_terms ON ( wp_terms.term_id = wp_term_taxonomy.term_id) where (post_author = $current_user->ID $cond) and post_type = 'bookmarks' ORDER BY wp_terms.name DESC, wp_posts.post_date DESC");
	
	foreach($bookmarks_list as $bookmark){
		if(empty($bookmark->term_id)){
			$formatBookmark[$bookmark->post_title] = $bookmark;
		}
		else{
			$formatBookmark[$bookmark->name][] = $bookmark;
			$author_cat_list[$bookmark->term_id] = $bookmark;
		}
	}
	
	if(isset($message)) echo '<div class="messageBox error">'.$message.'</div>';
	if(isset($success)) echo '<div class="messageBox okay">'.$success.'</div>';
	
	?>
		<h3>Bookmarks</h3>
		<ul class="<?php if(count($formatBookmark) > 7){ echo 'scroller';}else { echo 'no-scroller';} ?>" id="scroller">
			<?php 
			 if(!empty($formatBookmark)){
				foreach($formatBookmark as $catkey=>$catval) {
					
					if(is_array($catval)){
						echo '<li class="main tree-view" style="height:25px;line-height:25px;">'.$catkey;
						$sclass = count($catval)>7 ? "scroller" : "no-scroller" ;
						if($user_global->ID != $catval[0]->post_author or $current_user->ID == $user_global->ID){
							echo '<a onclick="return confirm(\'You are about to permanently delete the selected items. Cancel to stop, OK to delete.\');" href="'.get_admin_url().'post.php?category='.$catval[0]->term_id.'&post_type=bookmarks&action=browserbookmark&type=delete"  title="Delete Category"  id="deletebookmark_'.$catval->ID.'" class="delete"></a>';
							echo '<span title="Edit Category"  id="editcategory_'.$catval[0]->term_id.'" class="edit edit_cat">&nbsp;</span>';
						}
						echo '<ul class="'.$sclass.'">';
						foreach($catval as $key=>$value) {
							$bookmark_url	= get_post_meta($value->ID, '_D_Plugin_Bookmarks-bookmarks-url', true);
							$bookmark_title	= get_the_title($value->ID);
							echo '<li style="height:25px;line-height:25px;"><a target="_blank" href="'.$bookmark_url.'">'.$bookmark_title.'</a>';
							if($user_global->ID != $value->post_author or $current_user->ID == $user_global->ID){
							echo '<a href="'.get_admin_url().'post.php?post='.$value->ID.'&post_type=bookmarks&action=browserbookmark&type=delete"  title="Delete Bookmark" onclick="return confirm(\'You are about to permanently delete the selected items. Cancel to stop, OK to delete.\');"  id="deletebookmark_'.$value->ID.'" class="delete"></a><span title="Edit Bookmark" id="bookmark_'.$value->ID.'" class="edit edit_bookmark">&nbsp;</span></li>';
							}
							
						}
						echo '</ul>';
					}
					else{
						
						$bookmark_url	= get_post_meta($catval->ID, '_D_Plugin_Bookmarks-bookmarks-url', true);
						$bookmark_title	= get_the_title($catval->ID);
						echo '<li class="main" style="height:25px;line-height:25px;"><a href="'.$bookmark_url.'" target="_blank">'.$bookmark_title.'</a>';
						if($user_global->ID != $catval->post_author or $current_user->ID == $user_global->ID){
						echo '<a href="'.get_admin_url().'post.php?post='.$catval->ID.'&post_type=bookmarks&action=browserbookmark&type=delete" onclick="return confirm(\'You are about to permanently delete the selected items. Cancel to stop, OK to delete.\');" title="Delete Bookmark"  id="deletebookmark_'.$catval->ID.'" class="delete"></a>';
						echo '<span title="Edit Bookmark"  id="editbookmark_'.$catval->ID.'" class="edit edit_bookmark">&nbsp;</span>';
						}
						
					}
						echo '</li>';
				} 
			}

			if(empty($formatBookmark)){
				echo '<p>No Bookmarks Added!</p>';
			}
			?>

        			 
		</ul>
		<div class="clear"></div>
	<div class="add_edit">	
	<form name="post" action="" method="post" id="post">
		
		<h3 class="add_head">Add Bookmark</h3>
		<input class="wide" type="text" name="post_title" tabindex="1" value="" jquerydefaultvalue="<?php _e('Title', 'd_bookmarks'); ?>" />
		<input class="wide" type="text" name="post_url"   tabindex="2" value="" jquerydefaultvalue="<?php _e('Copy And Paste URL Here', 'd_bookmarks'); ?>" />
		<!--<textarea class="wide" name="post_content" tabindex="3" jquerydefaultvalue="<?php _e('Description', 'd_bookmarks'); ?>"><?php echo $value_content; ?></textarea>-->
		
		<select class="wide" tabindex="5" name="category_id" id="category_ids">
		<option value="">Select Category</option>
		<?php 
		if(!empty($author_cat_list)){
			foreach($author_cat_list as $catkey=>$catval) {
				/* $tags='';
				if(isset($value_categories) && in_array(($category->term_id), $value_categories))
					$tags.=' checked="checked"'; */
				echo '<option value="'.$author_cat_list[$catkey]->term_id.'">'.$author_cat_list[$catkey]->name.'</option>';
			} 
		}
		?>
		<option value="-1">Add New Category</option>
		</select>
		<input class="wide" type="text" style="display:none;" name="post_category" id="post_category"  tabindex="6" value="<?php echo $value_tags; ?>" maxlength="50" jquerydefaultvalue="<?php _e('Add New Category', 'd_bookmarks'); ?>" />
		<div class="clear"></div>
        <div align="left">
		<input class="button-primary" type="submit" value="<?php _e('Submit', 'd_bookmarks'); ?>" />
		</div>
		
	</form>
	</div>

</body>
</html>
