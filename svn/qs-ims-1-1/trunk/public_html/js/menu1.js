function menuAction(el)
{
	if(item_active)
		{
			item_active.className=' item';
			item_active.active='0';
			if(item_active.childNodes[0].tagName=='IMG')
				{
				item_active.childNodes[0].className =' ';
				item_active.childNodes[1].className =' hidden';
				}
		}
		item_active = el;
		el.className='active';
		el.active='1';
		
	var href=el.getAttribute('href');
	if (href) 
	{
		var url=href;
		var target=el.getAttribute('target');
		if (target)
			if(target == '_blank')
				window.open(url,"Pop","width=800,height=600,status=0;menu=0");
			else
				window.open(url);			
		else
				parent.main.window.location.href=url;
	}
}
/*
function searchRecord(search)
{
	var url='search.cfm?'+top.getUrltoken()+'&search='+escape(search);
	if (event.shiftKey||parent.main.windowManager.windows.length==0)
		parent.main.windowManager.open(url);
	else
		parent.main.windowManager.getActiveWindow().replace(url);
	return false;
}*/