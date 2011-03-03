/* Functions needed by the application */

var AJAX_ACTIVE = true;

const HISTORY_MAX = 30;
var hist = new Array(HISTORY_MAX);
var hist_i;

// Initialize the history
for (i=0; i<HISTORY_MAX; i++) hist[i] = null;
hist_i = 0;
hist[hist_i] = window.location.href;


function show_history() {
	var hist = '';
	for (i=0; i<HISTORY_MAX; i++) {
		if (hist_i == i) hist += '->'; else hist += '   ';
		hist += hist[i] + '\n';
	}
	hist += '\n';
	alert(hist);
}


//function callback(href) {
//	alert(href);
//	execute_ajax(href);
//}

function upload_history(href) {
	// New page, new history element
	// Upload history and history index
	hist_i = (hist_i + 1) % HISTORY_MAX;
	hist[hist_i] = href;
}

$(document).ready(function() {			// Wait for the document to be able to be manipulated
	
	// First thing... hide everything... then show it slowly
	$("#wrapper").hide().animate({"height": "toggle", "opacity": "toggle"}, 1000);
	
	if ($("#message").html() != '') {
		$("#message").show();
	}
	
	// Get the current page
	//var curpage = window.location.pathname;
	
	/*$("#rightcolumn > a").click(function(event) {		// Do something on click on a specific tag <a/>
		event.preventDefault(); 						// Don't use <a/> as usual, stay here
		alert("Good job! Now enjoy the magic");
		$(this).hide("slow");
		$("#lorem-ipsum").delay(800).show("slow");
	});
	
	$("#lorem-ipsum > a").click(function(event) {
		event.preventDefault();
		$(this).parent().hide(2500, function() {	// very slow fade out!
			$("#rightcolumn > a").show();			// this is a callback example
		});
	});*/
	
	// History plugin
	//$.hist.init(execute_ajax);
	/*$("a[rel|='hist']").click(function(event) {
		event.preventDefault();
		$.hist.load(this.href.replace(/^.*#/, ''));
		return false;
	});*/
	
	layout_bindings();
});

function layout_bindings() {
	$("a").live("click", function(event) {
		
		if ($(this).hasClass('noajax')) {
			return;
		}
		
		if ($(this).hasClass('confirm')) {
			if (! confirm('Please confirm')) {
				event.preventDefault();
				return;
			}
		}
		
		if ($(this).attr('href').charAt(0) == '/') {
			if (! AJAX_ACTIVE) return;
			event.preventDefault();
			
			var href = $(this).attr('href');
			
			upload_history(href);
		}
		
		// If it's a history.back() request
		else if ($(this).hasClass('history_back')) {
			if (! AJAX_ACTIVE) return;
			event.preventDefault();
			
			var i = (hist_i + HISTORY_MAX - 1) % HISTORY_MAX;
			if (hist[i] == null) {
				// No history
				history.back();
				return;
			} else {
				href = hist[i];
				hist_i = i;
			}
		}
		
		// If it's a history.forward() request
		else if ($(this).hasClass('history_forth')) {
			if (! AJAX_ACTIVE) return;
			event.preventDefault();
			
			var i = (hist_i + 1) % HISTORY_MAX;
			if (hist[i] == null) {
				// No history
				history.forward();
				return;
			} else {
				href = hist[i];
				hist_i = i;
			}
		}
		
		else return;
		
		//show_history();
		execute_ajax(href);
	});
	
	// Form submit bindings
	$('form').live('submit', function(event) {
		//alert($(this).serialize());
		if ($(this).hasClass('noajax')) return;
		if (! AJAX_ACTIVE) return;
		event.preventDefault();
		href = $(this).attr('action');
		upload_history(href);
		execute_ajax(href, $(this).serialize());
		return false;
	});
	
	/*$("a.ajax").live("click", function(event) {
		if (! AJAX_ACTIVE) return;
		event.preventDefault();
		var href = $(this).attr('href');
		execute_ajax(href);
	});
	
	$("a.confirm").live("click", function(event) {
		if (! confirm('Please confirm')) {
			event.preventDefault();
			return;
		}
		/*if (! AJAX_ACTIVE) return;
		event.preventDefault();
		var href = $(this).attr('href');
		execute_ajax(href);*
	});*/
	
	$("#mypatients-table tr, #mydoctors-table tr").live("hover",
	function() {
		$(this).children().toggleClass("tables-1-selected-tds");
	},
	function() {
		$(this).children().toggleClass("tables-1-selected-tds");
	});
	
	$("#message a").live('click', function(event) {
		event.preventDefault();
		$('#message').slideUp(150);
	});
	
	/*$("#mypatients-table tr, #mydoctors-table tr").live("click", function(event) {
		//var href = $(this).find("a:first").attr('href');
		var account_id = $(this).find('.id_keeper:first').html();
		alert(account_id);
		execute_ajax('/profile/user/' + account_id);
	});*/
	
	$('.checkAll').live('click', function() {
		$("input[type='checkbox'][name!='checkAll']")
			.attr('checked', $('.checkAll input').is(':checked'));
	}
)
}

function show_patient_form() {
	//$("#type-selection").fadeOut(400);
	$("#registration-hcp-fieldset").hide();
	$("#registration-patient-fieldset").fadeIn(400);
}

function show_hcp_form() {
	//$("#type-selection").fadeOut(400);
	$("#registration-patient-fieldset").hide();
	$("#registration-hcp-fieldset").fadeIn(400);
}

function execute_ajax(href, postdata) {
	//if (href == curpage) return;
	$("#message").fadeOut(100);
	$("#leftcolumn_content").slideUp(50, function() {
		//var beenslow = false;
		//$("#leftcolumn").empty();
		//$("#leftcolumn").append("<center>Loading...</center>").delay(600, "beenslow").slideToggle(1000, function() {
		//	beenslow = true;
		//});
		/*$.ajax({
			type: "GET",
			url: href,
			complete: function(request, status) {
					//alert(request.getResponseHeader('Last-Modified'));
					alert(request.status + ' ' + status);
				},
			dataType: 'json'
		});
		$.get(href, function(data, status, request) {
			//alert(request.getResponseHeader('Last-Modified'));
			alert(request.status);
		});*/
		
		$.post(href, postdata, function(data, status, request) {
			//$("#leftcolumn").dequeue("beenslow");
			//$("#leftcolumn").stop();
			//$("#leftcolumn").hide();
			//$("#leftcolumn").empty();
			//if (beenslow) {
				//$("#leftcolumn").slideToggle(1);
			//}
			//alert(data.redirect);
			
			if (data.redirect != "") {
				// data.redirect contains the string URL to redirect to
				window.location.href = data.redirect;
				//$(window.location).attr('href', data.redirect);
				return;
			}
			
			if (data.mainpane != null) {
				$("#leftcolumn_content").empty().append(data.mainpane);
				$("#leftcolumn_content").animate({"height": "toggle", "opacity": "toggle"}, 200);
			} else {
				$("#leftcolumn_content").animate({"height": "toggle", "opacity": "toggle"}, 200);
			}
			
			if (data.sidepane != null) {
				$("#rightcolumn").slideUp(20).empty().append(data.sidepane);
				$("#rightcolumn").animate({"height": "toggle", "opacity": "toggle"}, 200);
			}
			
			if (data.header != null) {
				$("#header").slideUp(20).empty().append(data.header);
				$("#header").animate({"height": "toggle", "opacity": "toggle"}, 200);
			}
			
			if (data.footer != null) {
				$("#footer").slideUp(20).empty().append(data.header);
				$("#footer").animate({"height": "toggle", "opacity": "toggle"}, 200);
			}
			
			if (data.curr_url != '') {
				$("#curr_url").html(data.curr_url);
			}
			
			if (data.message != '') {
				$("#message").html(data.message).show();
			}
			
		}, 'json');
	});
}
