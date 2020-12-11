$(function(){
	const sp = document.querySelector(".glyphicon-bell");
	if (sp) {
        console.log(sp.parentNode.parentNode);
	    $(sp.parentNode.parentNode).css("visibility", "hidden");
    }

	const settings = document.getElementById("tab_general_settings");
	if (settings.className == "active") {
        const pw = document.getElementById("tab_password");
//        window.location.replace(pw.firstChild.href);
    }
});