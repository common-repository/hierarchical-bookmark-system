<?php
	global $wpdb;
	$post_id = $_GET['post'];
	$post_categories = get_the_terms( $post_id, 'bookmark_categories' );
	$get_post = get_post($post_id);
	$value_title	= $get_post->post_title;
	$value_url	= get_post_meta($post_id, '_D_Plugin_Bookmarks-bookmarks-url', true);
	$value_content	= $get_post->post_content;

	$current_user = wp_get_current_user();
	$bookmarks_list = $wpdb->get_results("SELECT wp_terms.*, wp_posts.* FROM wp_posts LEFT JOIN wp_term_relationships ON ( wp_term_relationships.object_id = wp_posts.ID ) LEFT JOIN wp_term_taxonomy ON ( wp_term_taxonomy.term_taxonomy_id = wp_term_relationships.term_taxonomy_id )LEFT JOIN wp_terms ON ( wp_terms.term_id = wp_term_taxonomy.term_id) where post_author = $current_user->ID and post_type = 'bookmarks' ORDER BY wp_terms.name DESC, wp_posts.post_date DESC");
	
	foreach($bookmarks_list as $bookmark){
		if(empty($bookmark->term_id)){
			$formatBookmark[$bookmark->post_title] = $bookmark;
		}
		else{
			$formatBookmark[$bookmark->name][] = $bookmark;
			$author_cat_list[$bookmark->term_id] = $bookmark;
		}
	}
	
	
?>
<script type="text/javascript">

		jQuery(document).ready(function() {

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
<form name="post" action="" method="post" id="post">
	<h3 class="add_head">Edit Bookmark</h3>
	<input type="hidden" name="post_id" value="<?php echo $post_id;?>">
	<input class="wide" type="text" name="post_title" tabindex="1" value="<?php echo $value_title; ?>" jquerydefaultvalue="<?php _e('Title', 'd_bookmarks'); ?>" />
	<input class="wide" type="text" name="post_url"   tabindex="2" value="<?php echo $value_url; ?>" jquerydefaultvalue="<?php _e('Copy And Paste URL Here', 'd_bookmarks'); ?>" />
	<!--<textarea class="wide" name="post_content" tabindex="3" jquerydefaultvalue="<?php _e('Description', 'd_bookmarks'); ?>"><?php echo $value_content; ?></textarea>-->
	
	<select class="wide" tabindex="4" name="category_id" id="category_ids">
	<option value="">Select Category</option>
	<?php 
	if(!empty($author_cat_list)){
		foreach($author_cat_list as $catkey=>$catval) {
		
			$selected = ($author_cat_list[$catkey]->term_id == $post_categories[0]->term_id )?'selected="selected"': "";
			echo '<option '.$selected.' value="'.$author_cat_list[$catkey]->term_id.'">'.$author_cat_list[$catkey]->name.'</option>';
		} 
	}
	?>
	<!-- <option value="-1">Add New Category</option> -->
	</select>
	<input class="wide" type="text" style="display:none;" name="post_category" id="post_category"  tabindex="6" value="<?php echo $value_tags; ?>" maxlength="50" jquerydefaultvalue="<?php _e('Add New Category', 'd_bookmarks'); ?>" />
	<div class="clear"></div>
	<div align="left">
	<input class="button-primary" type="submit" value="<?php _e('Update', 'd_bookmarks'); ?>" />
	</div>
	
</form>