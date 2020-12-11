
$(function(){
	document.body.classList.add('freib-psy-student-mail');
	if($("select[name='selected_cmd2']").length) {
		$("select[name='selected_cmd2'] option[value='moveMails_550']").remove();
		$("select[name='selected_cmd2'] option[value='moveMails_551']").remove();
		$("select[name='selected_cmd2'] option[value='moveMails_552']").remove();
		$("select[name='selected_cmd2'] option[value='moveMails_553']").remove();
		$("select[name='selected_cmd2'] option[value='moveMails_554']").remove();
	}

	if($("select[name='selected_cmd']").length) {
		$("select[name='selected_cmd'] option[value='moveMails_550']").remove();
		$("select[name='selected_cmd'] option[value='moveMails_551']").remove();
		$("select[name='selected_cmd'] option[value='moveMails_552']").remove();
		$("select[name='selected_cmd'] option[value='moveMails_553']").remove();
		$("select[name='selected_cmd'] option[value='moveMails_554']").remove();
	}

	if($("div #il_prop_cont_rcp_cc").length) {
		$("div #il_prop_cont_rcp_cc").remove();
	}

	if($("div #il_prop_cont_rcp_bcc").length) {
		$("div #il_prop_cont_rcp_bcc").remove();
	}

	if($("div #il_prop_cont_").length && getUrlParameter("cmd") !== "showMail") {
		$("div #il_prop_cont_").remove();
	}

	if(getUrlParameter("cmd") === "showMail") {
		if($("ul.ilToolbarItems").length) {
			$("ul.ilToolbarItems").remove();
		}

		if($("div.form-horizontal").children().eq(1).length) {
			$("div.form-horizontal").children().eq(1).remove();
		}
	}

});

var getUrlParameter = function getUrlParameter(sParam) {
	var sPageURL = window.location.search.substring(1),
		sURLVariables = sPageURL.split('&'),
		sParameterName,
		i;

	for (i = 0; i < sURLVariables.length; i++) {
		sParameterName = sURLVariables[i].split('=');

		if (sParameterName[0] === sParam) {
			return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
		}
	}
};