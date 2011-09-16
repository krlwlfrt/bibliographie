function bibliographie_search_simple (category, limit, query, noQueryExpansion, highlightTerms) {
	$.ajax({
		url: bibliographie_web_root+'/search/ajax.php',
		data: {
			'task': 'simpleSearch',
			'category': category,
			'q': query,
			'limit': limit,
			'noQueryExpansion': noQueryExpansion
		},
		success: function (html) {
			$('#bibliographie_search_'+category+'_container').html(html);
			$('#bibliographie_search_'+category+'_container').highlight(highlightTerms);

			if($('#bibliographie_search_'+category+'_result').length == 0){
				$('#bibliographie_search_'+category+'_title').remove();
				$('#bibliographie_search_'+category+'_container').remove();
				$('#bibliographie_search_'+category+'_link').remove();
			}else{
				if($('#bibliographie_search_'+category+'_link').parent().is(':visible') == false)
					$('#bibliographie_search_'+category+'_link').parent().show();

				$('#bibliographie_search_'+category+'_link').show('slow').append(' ('+$('#bibliographie_search_'+category+'_results_count').html()+' results)');
				$('#bibliographie_search_'+category+'_title').show('slow');
				$('#bibliographie_search_'+category+'_container').show('slow');
				$('#bibliographie_search_'+category+'_link').show();
			}
		}
	})
}