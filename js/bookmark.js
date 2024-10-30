function wpBookmarksClose() {
	if(window.wpBookmarksDebug) console.log('wpBookmarksClose');
	if($!='undefined') {
		if(jQuery('#wpBookmarks_box')!='undefined') jQuery('#wpBookmarks_box').slideUp('fast', function(){
			if(jQuery('#wpBookmarks_iframe')!='undefined') jQuery('#wpBookmarks_iframe').hide();
		});
	}
}

function wpBookmarksInit() {
	if(window.wpBookmarksDebug) {
        console.log('wpBookmarksInit');
    }
	if(window.wpBookmarksInitialized!=true) {
		if(window.wpBookmarksDebug) {
            console.log('doInit');
        }
		// load stylesheets
		jQuery('head').append('<link rel="stylesheet" href="'+window.wpBookmarksPluginPath+'/css/bookmark.css?'+Math.random()+'" type="text/css" />');
		jQuery('head').append('<link rel="stylesheet" href="'+window.wpBookmarksPluginPath+'/css/jquery-ui.css" type="text/css" />');
		
		// build box-html
		var box = jQuery('<div id="wpBookmarks_box" style="display:none;"><a id="wpBookmarks_moveBar" href="#" title="Drag Window"></a><a id="wpBookmarks_newwindowBar" href="#" onclick="onNewWindowClicked();return false;" title="open in new window"></a><a id="wpBookmarks_titleBar" href="#" onclick="wpBookmarksClose();return false;" title="'+window.wpBookmarksCloseButtonLabel+'"></a><iframe id="wpBookmarks_iframe" src="javascript:" style="display:none;" onreadystatechange="onWpBookmarkFrameLoaded();" onload="onWpBookmarkFrameLoaded();"></iframe></div>');
		jQuery(document.body).append(box);
		
		// set init-status
		window.wpBookmarksInitialized=true;

	}
}

function getSelectionText() {
    var text = "";
    if (window.getSelection) {
        text = window.getSelection().toString();
    } else if (document.selection && document.selection.type != 'Control') {
        text = document.selection.createRange().text;
    }
    if(window.wpBookmarksDebug) console.log('getSelectionText: '+text);
    return text;
}

function onNewWindowClicked(){

	wpBookmarksClose();
	
	url = window.wpBookmarksAdminPath+'post-new.php?post_type=bookmarks&action=browserbookmark';
	url+='&post_title='+encodeURIComponent(jQuery(document).attr('title'));
	url+='&post_content='+encodeURIComponent(getSelectionText());
	url+='&post_url='+encodeURIComponent(jQuery(location).attr('href'));
	url = url.replace(/https:\/\/sslsites.de\/kb.conlabz.de/, 'http://kb.conlabz.de');
	var width = 500;
	var height = 550;
	var left = (screen.width/2)-(width/2);
	var top = (screen.height/2)-(height/2);
	window.open(url, '_blank', 'width='+width+',height='+height+',left='+left+',top='+top);
	window.focus();
}

function showWpBookmarks_box() {
	if(window.wpBookmarksDebug) console.log('showWpBookmarks_box');
	
	// build url
	url = window.wpBookmarksAdminPath+'post-new.php?post_type=bookmarks&action=browserbookmark';
	url+='&post_title='+encodeURIComponent(jQuery(document).attr('title'));
	url+='&post_content='+encodeURIComponent(getSelectionText());
	url+='&post_url='+encodeURIComponent(jQuery(location).attr('href'));
	url = url.replace(/https:\/\/sslsites.de\/kb.conlabz.de/, 'http://kb.conlabz.de');
	
	// frameset-check
	if(jQuery("body").is("body")!=true) {
		if(confirm(unescape("Diese Seite benutzt Framesets. Soll die Seite in einem neuen Fenster ge%F6ffnet werden%3F \nFalls ein Popup-Blocker verwendet wird%2C muss das %D6ffnen des Fensters best%E4tigt werden."))) {
			var width = 300;
			var height = 250;
			var left = (screen.width/2)-(width/2);
			var top = (screen.height/2)-(height/2);
			window.open(url, '_blank', 'width='+width+',height='+height+',left='+left+',top='+top);
			window.focus();
		}
	}
	else {
		// close previously opened box(so it slides up)
		//wpBookmarksClose();
		
		// build html-box
		wpBookmarksInit();
		
		// show box 
		jQuery('#wpBookmarks_box').slideDown('fast');

		// set source
		jQuery('#wpBookmarks_iframe').attr('src', url);
	}
};

function onBookmarkClicked() {
	if(window.wpBookmarksDebug) console.log('onBookmarkClicked');
	// load jquery
	if(window.wpBookmarksInitialized!=true) {
		// callback
		window.DOMLoaded = function() {
			showWpBookmarks_box();
		}
		loadScript(window.wpBookmarksIncludePath+'/js/jquery/jquery.js');
		loadScript(window.wpBookmarksIncludePath+'/js/jquery/jquery-migrate.min.js');
		loadScript(window.wpBookmarksIncludePath+'/js/jquery/ui/jquery.ui.core.min.js');
		loadScript(window.wpBookmarksIncludePath+'/js/jquery/ui/jquery.ui.widget.min.js');
		loadScript(window.wpBookmarksIncludePath+'/js/jquery/ui/jquery.ui.mouse.min.js');
		loadScript(window.wpBookmarksIncludePath+'/js/jquery/ui/jquery.ui.resizable.min.js');
		loadScript(window.wpBookmarksIncludePath+'/js/jquery/ui/jquery.ui.draggable.min.js');
		
		
		//loadScript(window.wpBookmarksPluginPath+'/js/jquery-ui.js')
		

	}
	else {
		showWpBookmarks_box();
	}
}

function onWpBookmarkFrameLoaded() {
	// show iframe
	jQuery('#wpBookmarks_iframe').show();

	if(!jQuery.ui)
	{
	   loadScript(window.wpBookmarksIncludePath+'/js/jquery/ui/jquery.ui.draggable.min.js');
	}
	jQuery(function($){ 
		$( "#wpBookmarks_box" ).draggable();
		$( "#wpBookmarks_box" ).resizable({minHeight: 400,minWidth: 500});
	});
	
}
