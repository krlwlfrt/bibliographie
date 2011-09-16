function bibliographie_topics_toggle_visibility_of_subtopics (topic_id, repeat_id) {
	if($('#topic_'+topic_id+'_'+repeat_id+'_subtopics').is(':visible')){
		$('#topic_'+topic_id+'_'+repeat_id+'_subtopics').hide();
		$('#topic_'+topic_id+'_'+repeat_id+' span').removeClass('silk-icon-bullet-toggle-minus').addClass('silk-icon-bullet-toggle-plus');
	}else{
		$('#topic_'+topic_id+'_'+repeat_id+'_subtopics').show();
		$('#topic_'+topic_id+'_'+repeat_id+' span').removeClass('silk-icon-bullet-toggle-plus').addClass('silk-icon-bullet-toggle-minus');
	}
}

function bibliographie_topics_toggle_visiblity_of_all (expand) {
	if(expand == true){
		$('.topic_subtopics').show();
		$('.topic span').removeClass('silk-icon-bullet-toggle-plus').addClass('silk-icon-bullet-toggle-minus');
	}else{
		$('.topic_subtopics').hide();
		$('.topic span').removeClass('silk-icon-bullet-toggle-minus').addClass('silk-icon-bullet-toggle-plus');
	}
}