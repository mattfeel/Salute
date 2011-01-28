/* Functions needed by the application */

$(document).ready(function() {			// Wait for the document to be able to be manipulated
	
	// First thing... hide everything... then show it slowly
	$("#wrapper").hide().animate({"height": "toggle", "opacity": "toggle"}, 1000);
	
	// Get the current page
	var curpage = window.location.pathname;
	
	$("#rightcolumn > a").click(function(event) {		// Do something on click on a specific tag <a/>
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
	});
	
	layout_bindings();
});

function layout_bindings() {
	$(".ajaxlink").live("click", function(event) {
		event.preventDefault();
		var href = $(this).attr('href');
		execute_ajax(href);
	});
	
	/*$("#mypatients-table tr").hover(
	function() {
		$(this).children().toggleClass("tables-1-selected-tds");
	},
	function() {
		$(this).children().toggleClass("tables-1-selected-tds");
	});
	
	$("#mypatients-table tr").live("click", function(event) {
		var href = $(this).find("a:first").attr('href');
		alert(href);
		execute_ajax(href);
	});*/
}

function show_patient_form() {
	//$("#type-selection").fadeOut(400);
	$("#registration-hcp-form").hide();
	$("#registration-patient-form").fadeIn(400);
}

function show_hcp_form() {
	//$("#type-selection").fadeOut(400);
	$("#registration-patient-form").hide();
	$("#registration-hcp-form").fadeIn(400);
}

function execute_ajax(href) {
	//if (href == curpage) return;
	$("#leftcolumn").slideUp(50, function() {
		//var beenslow = false;
		//$("#leftcolumn").empty();
		//$("#leftcolumn").append("<center>Loading...</center>").delay(600, "beenslow").slideToggle(1000, function() {
		//	beenslow = true;
		//});
		$.get(href, function(data, status, request) {
			alert(request.getResponseHeader(1));
		});
		$.get(href, function(data, status) {
			//$("#leftcolumn").dequeue("beenslow");
			//$("#leftcolumn").stop();
			//$("#leftcolumn").hide();
			//$("#leftcolumn").empty();
			//if (beenslow) {
				//$("#leftcolumn").slideToggle(1);
			//}
			alert(data);
			//alert(data.redirect);
			
			if (data.redirect) {
				// data.redirect contains the string URL to redirect to
				window.location.href = data.redirect;
				return;
			}
			
			if (data.sidepane != "") {
				$("#rightcolumn").slideUp(20).empty().append(data.sidepane);
				$("#rightcolumn").animate({"height": "toggle", "opacity": "toggle"}, 200);
			}
			if (data.mainpane != "") {
				$("#leftcolumn").empty().append(data.mainpane);
				$("#leftcolumn").animate({"height": "toggle", "opacity": "toggle"}, 200);
			} else {
				$("#leftcolumn").animate({"height": "toggle", "opacity": "toggle"}, 200);
			}
			//$("#leftcolumn").append(data.mainpane);
			//$("#leftcolumn").animate({"height": "toggle", "opacity": "toggle"}, 200);
		}, 'json');
	});
}
