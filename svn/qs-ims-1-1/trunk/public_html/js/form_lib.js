function trim(x)
{
	while (x.value.charCodeAt(0)<=32 && x.value.length) 
		x.value=x.value.substring(1,x.value.length);
	while (x.value.charCodeAt(x.value.length-1)<=32 && x.value.length)
		x.value=x.value.substring(0,x.value.length-2);

	return x.value;
}
function getMonthLength(month,year) 
{
   	var monthlength = new Array(31,28,31,30,31,30,31,31,30,31,30,31)
	if (month>monthlength.length||month<0) return 0;
   	if (month==1 && (year/4==Math.floor(year/4) || year/400==Math.floor(year/400))) { return 29 }
	else return monthlength[month];
}
function checkInt(x,l)
{
	x.value=trim(x);
	if (x.value.search(/^(\d+)$/)>=0||(!l&&x.value==''))
		return true;
	else 
		{ x.focus(); return false; }
}
function checkFloat(x,l)
{
	x.value=trim(x);
	if (x.value.search(/^(\d+\.{0,1}\d*)$/)>=0||(x.value==''&&l==0))
		return true;
	else
		{ x.focus(); return false; }
}
function checkMoney(x,l)
{
	//x.value=trim(x);
	//y=x.value.replace(/,/g,'');
	//if (y.search(/^(\d+\.{0,1}\d*)$/)>=0&&y.length>l)
		return true;
	//else 
		//{ x.focus(); return false; }
}
function checkEMail(x,r)
{
	x.value=trim(x);
	y=x.value.toLowerCase();
	if(y==''&&!r)
		return true;
	if (y.search(/^[a-z0-9]([._-]?[a-z0-9]+)*\@[a-z0-9]+([.-]?[a-z0-9]+)*\.[a-z]{2,4}$/)>=0)
		return true;
	else
		{ x.focus(); return false; }
}
function checkHttp(x)
{
	x.value=trim(x);
	if (x.value.search(/^(http:\/\/[\w+\.\w*]+)$/)>=0)
		return true;
	else
		{ x.focus(); return false; }
}
function checkUrl(x)
{
	x.value=trim(x);
	if (checkText(x,5)&&(x.value.length-(x.value.lastIndexOf('.')+1)==2||x.value.length-(x.value.lastIndexOf('.')+1)==3||x.value.length-(x.value.lastIndexOf('.')+1)==4))
		return true;
	else { x.focus(); return false; }
}
function checkDate(x)
{
	var y;
	x.value=trim(x);
	if(x.value.search(/^(\d\d\d\d)$/)>=0)
		return true;
	if (x.value.search(/^(\d\d)-(\d\d)-(\d\d\d\d)$/)>=0)
	{ 	
		if ((y=getMonthLength(RegExp.$2-1,RegExp.$3))&&RegExp.$1<=y)
		return true;
		else return false;
	}
	else
		{ x.focus(); return false; }
}
function checkCustom(x,w,l)
{
	var u;
	x.value=trim(x);
	if (x.value.length<l) return false;
	for (i=0;i<x.value.length;i++)
	{
		u=0;
		for (j=0;j<w.length;j++)
			if (x.value.charAt(i)==w.charAt(j)) { u=1; break; }
		if (!u) break;
	}
	if (!u) return false; else return true;
}
function checkText(x,l)
{
	x.value=trim(x);
	if (x.value.length<l) { x.focus(); return false; }
	return true;
}
function checkCheckbox(x,l)
{
	var count=0;
	if (x.length)
	{
		for (n=0;n<x.length;n++)
			if (x[n].checked) count++;
	}
	else if (x.checked) count=1;
	return count>0;
}

function checkSelect(x,l)
{
	count=0;
	for (n=0;n<x.options.length;n++)
		if (x.options[n].selected) count++;

	return count>=l;
}
function checkAlphaNumeric(x) {
	if(x.value.search(/\W/) != -1) {
		return false;
	}
	return true;
}

function checkPasswordSecure(x,l)
{
	return x.value.match(/[0-9]/i)&&x.value.match(/[a-z]/i);
}

//function to deactivate all buttons that have the id "btn"
function buttondeact()
{
	for(i=0;i<document.all.btn.length;i++)
	{document.all.btn[i].disabled = true;}
}	