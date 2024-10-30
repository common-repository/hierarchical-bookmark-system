<?php
	global $wpdb;
	$cat_id = $_GET['category'];
	
	$term = get_term( $cat_id, 'bookmark_categories' );
	$value_title = $term->name;

	
	
?>
<form name="post" action="" method="post" id="post">
	<h3 class="add_head">Update Category</h3>
	<input type="hidden" name="cat_id" value="<?php echo $cat_id;?>">
	<input class="wide" type="text" name="category_title" tabindex="1" value="<?php echo $value_title; ?>" jquerydefaultvalue="<?php _e('Title', 'd_bookmarks'); ?>" />

	<div class="clear"></div>
	<div align="left">
	<input class="button-primary" type="submit" value="<?php _e('Update Category', 'd_bookmarks'); ?>" />
	</div>
	
</form>