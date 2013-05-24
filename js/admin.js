jQuery(document).ready(function($){
	$("#ptdw-new").change(function(){
		window.location.href    = "post-new.php?post_type=" + $(this).find(":selected").attr("class");
	});
	
	$("#ptdw-viewposts").change(function(){
		window.location.href    = "edit.php?post_type=" + $(this).find(":selected").attr("class");
	});
});