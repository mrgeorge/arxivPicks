// ==UserScript==
// @name           arxiv discussion
// @namespace      arxiv
// @description    Add bookmark button to arxiv website
// @include        http://*.arxiv.org/abs/*
// @include        http://*.arxiv.org/list/*
// @include        http://arxiv.org/abs/*
// @include        http://arxiv.org/list/*
// @include        http://xxx.lanl.gov/abs/*
// @include        http://xxx.lanl.gov/list/*
// ==/UserScript==

bookmark_uri = "http://example.com/arxiv.php";

function getElementsByClassName(className, tag, elm){
	var testClass = new RegExp("(^|\\\\s)" + className + "(\\\\s|$)");
	var tag = tag || "*";
	var elm = elm || document;
	var elements = (tag == "*" && elm.all)? elm.all : elm.getElementsByTagName(tag);
	var returnElements = [];
	var current;
	var length = elements.length;
	for(var i=0; i<length; i++){
		current = elements[i];
		if(testClass.test(current.className)){
			returnElements.push(current);
		}
	}
	return returnElements;
}

if (!document.getElementsByClassName) {
    document.getElementsByClassName = getElementsByClassName;
}


function location_parse_article_id() {
    var article_id = null;
    var split_location = String(document.location).split("/");
    var len = split_location.length
    
    for (var i=1; i < 3; i++) {
        if (split_location[len - i]) {
            article_id = split_location[len - i];
            break;
        }
    }

    // truncate version number, if it exists (v2 problem)
    var v_location = article_id.indexOf('v')
    if (v_location != -1) {
	article_id = article_id.substring(0,v_location);
    }

    return article_id
}

function form_encode(parameters) {
    var req = "";
    for (var i=0; i < parameters.length; i++) {
        var param = parameters[i];
        req += encodeURIComponent(param[0]) + "=" + encodeURIComponent(param[1]);
        if (i != parameters.length - 1) {
            req += "&";   
        }
    }
    return req;
}

function add_ui() {
    var loc = document.location;
    if (/\/list/.test(loc)) {
        add_listing_ui();
    } else if (/\/abs/.test(loc)) {
        add_absract_ui();
    }
}

function add_absract_ui() {
    var article_id = location_parse_article_id();
    
    var form = create_form(article_id);
    
    var container = document.createElement("div");
    var header = document.createElement("div");
    container.setAttribute("class", "citebase");
    header.setAttribute("class", "heading");
    header.textContent = "Arxiv Discussion"
    
    container.appendChild(header);
    container.appendChild(form);
    
    next_element = document.getElementsByClassName("full-text")[0];
    next_element.parentNode.insertBefore(container, next_element);
};


function add_listing_ui() {
    var listings = document.getElementsByClassName("list-identifier");
    for (var i=0; i< listings.length; i++) {
        var container = listings[i];
        var article_id = container.childNodes[0].textContent.split(":")[1];
        var form = create_form(article_id);
        form.style.display = "inline-block";
        form.style.marginLeft ="2em";
        form.style.fontSize = "80%";
        container.appendChild(form);
    }
}

function create_form(article_id) {
    var form = document.createElement("form");
    form.setAttribute("method", "POST")
    form.setAttribute("action", bookmark_uri)
    var button = document.createElement("input");
    button.setAttribute("type", "button");
    button.setAttribute("value", "List for discussion");
    var article_input = document.createElement("input");
    article_input.setAttribute("type", "hidden");
    article_input.setAttribute("name", "article_id");
    article_input.setAttribute("class", "article_id");
    article_input.setAttribute("value", article_id);
    
    button.addEventListener("click", submit_bookmark, false)

    form.appendChild(article_input);
    form.appendChild(button);
    
    return form;
}

function request_callback(response, button) {
    if (response.readyState == 4) {
        if (response.status == 200) {
            make_info_bar("Bookmark added");
            button.disabled = true;
        }
        else {
            make_info_bar("Adding bookmark failed; response " + response.status + " " + response.statusText);
        }
    }
}

function submit_bookmark(event) {
    id_input = event.currentTarget.form.elements.namedItem("article_id");
    post_body = form_encode([["article_id", id_input.value]]);
    var button = event.currentTarget;
    GM_xmlhttpRequest({
        method:"POST",
        url:bookmark_uri,
        headers:{
            "Content-Type":"application/x-www-form-urlencoded"
        },
        data:post_body,
        onload:function (response) {request_callback(response, button)}
        })
    return false;
}

function make_info_bar(text) {
    var bar = document.createElement("div");
    bar.style.color = "black";
    bar.style.backgroundColor = "lightyellow";
    bar.style.position = "fixed";
    bar.style.top = "0";
    bar.style.left = "0";
    bar.style.width = "100%";
    bar.style.paddingLeft = "1em";
    bar.style.paddingTop = "0.2em";
    bar.style.paddingBottom = "0.2em";
    bar.textContent = text;
    bar.style.opacity = 0;
    
    
    var timeout = 15 //ms
    function fade(object, direction, time) {
        var opacity = object.style.opacity;
        delta_opacity = 1/(time/timeout);
        if ((direction == 1 && opacity < 1) || (direction == -1 && opacity > 0)) {
            object.style.opacity = parseFloat(object.style.opacity) + direction * delta_opacity;
            window.setTimeout(fade, timeout, object, direction, time)
        }       
    }
    
    document.body.appendChild(bar);
    fade(bar, 1, 500);
    window.setTimeout(function() {fade(bar, -1, 500); document.removeChild(bar)}, 3000);
}

add_ui();
