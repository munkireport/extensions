<div class="col-lg-4 col-md-6">
	<div class="card" id="extensions-teamid-widget">
		<div class="card-header" data-container="body" >
			<i class="fa fa-puzzle-piece"></i>
			    <span data-i18n="extensions.teamid"></span>
			    <a href="/show/listing/extensions/extensions" class="pull-right"><i class="fa fa-list"></i></a>
			
		</div>
		<div class="list-group scroll-box"></div>
	</div><!-- /panel -->
</div><!-- /col -->

<script>
$(document).on('appUpdate', function(e, lang) {
	
	var box = $('#extensions-teamid-widget div.scroll-box');
	
	$.getJSON( appUrl + '/module/extensions/get_teamid', function( data ) {
		
		box.empty();
		if(data.length){
			$.each(data, function(i,d){
				var badge = '<span class="badge badge-light pull-right">'+d.count+'</span>';
                box.append('<a href="'+appUrl+'/show/listing/extensions/extensions/#'+d.teamid+'" class="list-group-item">'+d.teamid+badge+'</a>')
			});
		}
		else{
			box.append('<span class="list-group-item">'+i18n.t('extensions.noextensions')+'</span>');
		}
	});
});	
</script>
