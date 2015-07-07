jQueryExacomp(function($) {
	
	// want to save?
	var changed = false;
	var saveButton = $('#save-button input').get(0);
	saveButton.originalValue = saveButton.value;
	window.onbeforeunload = function(e) {
		return changed ? 'Du hast deine Änderungen nicht gespeichert. Willst du wirklich diese Seite verlassen?' : null;
	};
	
	
	function update_ui() {
		// leer text anzeigen
		if (!$("#items .item").length) {
			$("#items .empty").show();
		} else {
			$("#items .empty").hide();
		}
		if (!$("#trash .item").length) {
			$("#trash .empty").show();
		} else {
			$("#trash .empty").hide();
		}
	}
	update_ui();
	
	
	$( ".items" ).sortable({
		connectWith: ".items",
		placeholder: "placeholder",
		forcePlaceholderSize: true,
		items: ".item",
		stop: function() {
			update_ui();
		},
		change: function() {
			changed = true;
			saveButton.value = saveButton.originalValue;
		},
	}).disableSelection();
	
	$('.items input').change(function(){
		changed = true;
	});
	
	
	// save button
	$(saveButton).click(function(){
		
		function numberic_id(item) {
			return item.id.replace(/^.*-([0-9])/, '$1');
		}
		
		var data = {
			action: 'save',
			sesskey: M.cfg.sesskey,
			items: [],
			trash: [],
			days: {}
		}
		
		$('#items .item').each(function(){
			data.items.push({
				id: numberic_id(this)
			});
		});
		$('#trash .item').each(function(){
			data.trash.push({
				id: numberic_id(this)
			});
		});
		$('#days .day').each(function(){
			var day = [];
			$('.item', this).each(function(){
				day.push({
					id: numberic_id(this),
					student_evaluation: $('input.student_evaluation', this).is(':checked'),
					teacher_evaluation: $('input.teacher_evaluation', this).is(':checked')
				});
			});
			data.days[numberic_id(this)] = day;
		});
		
		saveButton.value = 'Speichere...';
		saveButton.disabled =  true;
		
		$.post('', data, function(ret) {
			saveButton.disabled =  false;
			
			if (ret !== 'ok') {
				saveButton.value = 'Fehler';
				console.log(ret);
			} else {
				var i = 0;
				(function pulse(){
					if (++i > 3) return;
					$( saveButton ).delay(100).animate({'opacity':0.5},pulse).delay(100).animate({'opacity':1});
				})();
				
				// clear trash
				$("#trash .item").remove();

				saveButton.value = 'Gespeichert!';
				changed = false;
				
				update_ui();
			}
		}).fail(function(ret){
			saveButton.disabled =  false;
			saveButton.value = 'Fehler';
			console.log(ret);
		});
		
		console.log('save', data);
	});
});
