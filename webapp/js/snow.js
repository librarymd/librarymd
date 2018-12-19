var SNOW_Picture = SNOW_Picture_UP;
var SNOW_no = SNOW_no_UP;

var SNOW_Time;
var SNOW_dx, SNOW_xp, SNOW_yp;
var SNOW_am, SNOW_stx, SNOW_sty;
var i, SNOW_Browser_Width, SNOW_Browser_Height;


SNOW_Browser_Width = document.documentElement.clientWidth;
SNOW_Browser_Height = document.documentElement.clientHeight;

SNOW_dx = new Array();
SNOW_xp = new Array();
SNOW_yp = new Array();
SNOW_am = new Array();
SNOW_stx = new Array();
SNOW_sty = new Array();

for (i = 0; i < SNOW_no; ++ i) {
	SNOW_dx[i] = 0;
	SNOW_xp[i] = Math.random()*(SNOW_Browser_Width-50);
	SNOW_yp[i] = Math.random()*SNOW_Browser_Height;
	SNOW_am[i] = Math.random()*20;
	SNOW_stx[i] = 0.02 + Math.random()/10;
	SNOW_sty[i] = 0.7 + Math.random();
	document.write("<\div id=\"SNOW_flake"+ i +"\" class=\"snow_flake\" style=\"position: absolute; z-index: "+ i +"; visibility: visible; top: 15px; left: 15px;\"><\img src=\""+SNOW_Picture+"\" border=\"0\"><\/div>");
}

function SNOW_Weather() {
	for (i = 0; i < SNOW_no; ++ i) {
		SNOW_yp[i] += SNOW_sty[i];

		if (SNOW_yp[i] > SNOW_Browser_Height-50)
		{
			SNOW_xp[i] = Math.random()*(SNOW_Browser_Width-SNOW_am[i]-30);
			SNOW_yp[i] = 0;
			SNOW_stx[i] = 0.02 + Math.random()/10;
			SNOW_sty[i] = 0.7 + Math.random();
		}

		SNOW_dx[i] += SNOW_stx[i];

		document.getElementById("SNOW_flake"+i).style.top=SNOW_yp[i]+"px";
		document.getElementById("SNOW_flake"+i).style.left=SNOW_xp[i] + SNOW_am[i]*Math.sin(SNOW_dx[i])+"px";
	}

	SNOW_Time = setTimeout("SNOW_Weather()", 10);
}

function Stop_SNOW_Weather() {
	clearTimeout(SNOW_Time);
}

function Stop_Snow_After_A_While() {
	var hideAfterSeconds = 5;
	setTimeout("Stop_SNOW_Weather()", 1000 * hideAfterSeconds);
	setTimeout("Remove_All_Snow_Flakes()", 1000 * (hideAfterSeconds - 1));
}

function Remove_All_Snow_Flakes() {
	for (i = 0; i < SNOW_no; ++ i) {
		document.getElementById("SNOW_flake"+i).style.opacity = 0;
	}
}

SNOW_Weather();
Stop_Snow_After_A_While();