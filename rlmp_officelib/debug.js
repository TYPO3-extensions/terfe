var DHTML = 0, DOM = 0, MS = 0, NS = 0, OP = 0;
var xMousePos = 0; // Horizontal position of the mouse on the screen
var yMousePos = 0; // Vertical position of the mouse on the screen
var xMousePosMax = 0; // Width of the page
var yMousePosMax = 0; // Height of the page
var hideDebugTimeout;

function DHTML_init() {

if (window.opera) {
     OP = 1;
 }
 if(document.getElementById) {
   DHTML = 1;
   DOM = 1;
 }
 if(document.all && !OP) {
   DHTML = 1;
   MS = 1;
 }
if(window.netscape && window.screen && !DOM && !OP) {
   DHTML = 1;
   NS = 1;
 }
}

function getElem(p1,p2,p3) {
 var Elem;
 if(DOM) {
   if(p1.toLowerCase()=="id") {
     if (typeof document.getElementById(p2) == "object")
     Elem = document.getElementById(p2);
     else Elem = void(0);
     return(Elem);
   }
   else if(p1.toLowerCase()=="name") {
     if (typeof document.getElementsByName(p2) == "object")
     Elem = document.getElementsByName(p2)[p3];
     else Elem = void(0);
     return(Elem);
   }
   else if(p1.toLowerCase()=="tagname") {
     if (typeof document.getElementsByTagName(p2) == "object" || (OP && typeof document.getElementsByTagName(p2) == "function"))
     Elem = document.getElementsByTagName(p2)[p3];
     else Elem = void(0);
     return(Elem);
   }
   else return void(0);
 }
 else if(MS) {
   if(p1.toLowerCase()=="id") {
     if (typeof document.all[p2] == "object")
     Elem = document.all[p2];
     else Elem = void(0);
     return(Elem);
   }
   else if(p1.toLowerCase()=="tagname") {
     if (typeof document.all.tags(p2) == "object")
     Elem = document.all.tags(p2)[p3];
     else Elem = void(0);
     return(Elem);
   }
   else if(p1.toLowerCase()=="name") {
     if (typeof document[p2] == "object")
     Elem = document[p2];
     else Elem = void(0);
     return(Elem);
   }
   else return void(0);
 }
 else if(NS) {
   if(p1.toLowerCase()=="id" || p1.toLowerCase()=="name") {
   if (typeof document[p2] == "object")
     Elem = document[p2];
     else Elem = void(0);
     return(Elem);
   }
   else if(p1.toLowerCase()=="index") {
    if (typeof document.layers[p2] == "object")
     Elem = document.layers[p2];
    else Elem = void(0);
     return(Elem);
   }
   else return void(0);
 }
}

function getCont(p1,p2,p3) {
   var Cont;
   if(DOM && getElem(p1,p2,p3) && getElem(p1,p2,p3).firstChild) {
     if(getElem(p1,p2,p3).firstChild.nodeType == 3)
       Cont = getElem(p1,p2,p3).firstChild.nodeValue;
     else
       Cont = "";
     return(Cont);
   }
   else if(MS && getElem(p1,p2,p3)) {
     Cont = getElem(p1,p2,p3).innerText;
     return(Cont);
   }
   else return void(0);
}

function getAttr(p1,p2,p3,p4) {
   var Attr;
   if((DOM || MS) && getElem(p1,p2,p3)) {
     Attr = getElem(p1,p2,p3).getAttribute(p4);
     return(Attr);
   }
   else if (NS && getElem(p1,p2)) {
       if (typeof getElem(p1,p2)[p3] == "object")
        Attr=getElem(p1,p2)[p3][p4]
       else
        Attr=getElem(p1,p2)[p4]
         return Attr;
       }
   else return void(0);
}

function setCont(p1,p2,p3,p4) {
	if(DOM && getElem(p1,p2,p3) && getElem(p1,p2,p3).firstChild) {
		getElem(p1,p2,p3).firstChild.nodeValue = p4;
	} else {
		if(MS && getElem(p1,p2,p3)) {
			getElem(p1,p2,p3).innerText = p4;
		} else {
			if (NS && getElem(p1,p2,p3)) {
				getElem(p1,p2,p3).document.open();
				getElem(p1,p2,p3).document.write(p4);
				getElem(p1,p2,p3).document.close();
			}
		}
	}
}

function captureMousePosition(e) {
	if (NS) {
		xMousePos = e.pageX;
		yMousePos = e.pageY;
		xMousePosMax = window.innerWidth+window.pageXOffset;
		yMousePosMax = window.innerHeight+window.pageYOffset;
	} else if (MS) {
		xMousePos = window.event.x+document.body.scrollLeft;
		yMousePos = window.event.y+document.body.scrollTop;
		xMousePosMax = document.body.clientWidth+document.body.scrollLeft;
		yMousePosMax = document.body.clientHeight+document.body.scrollTop;
	} else {
		// Netscape 6 behaves the same as Netscape 4 in this regard
		xMousePos = e.pageX;
		yMousePos = e.pageY;
		xMousePosMax = window.innerWidth+window.pageXOffset;
		yMousePosMax = window.innerHeight+window.pageYOffset;
	}
}





DHTML_init();

if (NS) { // Netscape
    document.captureEvents(Event.MOUSEMOVE);
    document.onmousemove = captureMousePosition;
} else if (MS || DOM) { // Internet Explorer
    document.onmousemove = captureMousePosition;
}

function tx_rlmpofficelib_officedocument_showDebug (msg) {
	if(MS) {
		getElem("id","tx_rlmpofficelib_officedocument_debuglayer",null).style.top = yMousePos+"px";
		getElem("id","tx_rlmpofficelib_officedocument_debuglayer",null).style.left = xMousePos+"px";
	} else {
		getElem("id","tx_rlmpofficelib_officedocument_debuglayer",null).style.top = yMousePos+"px";
		getElem("id","tx_rlmpofficelib_officedocument_debuglayer",null).style.left = xMousePos+"px";
	}
    if(DOM || MS) {
		setCont("id","tx_rlmpofficelib_officedocument_debuglayerpre",null,msg);
		getElem("id","tx_rlmpofficelib_officedocument_debuglayer",null).style.visibility = "visible";
	} else if(NS) {
		if(NS) setCont("id","tx_rlmpofficelib_officedocument_debuglayerpre",null,msg);
		getElem("id","tx_rlmpofficelib_officedocument_debuglayer",null).visibility = "show";
	}
}

function tx_rlmpofficelib_officedocument_hideDebug () {
	if(DOM || MS) {
		getElem("id","tx_rlmpofficelib_officedocument_debuglayer",null).style.visibility = "hidden";
	} else if(NS) {
		getElem("id","tx_rlmpofficelib_officedocument_debuglayer",null).visibility = "hide";
	}
}