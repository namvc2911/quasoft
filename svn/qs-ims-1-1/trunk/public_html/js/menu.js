var JMenu = {
	initialize : function(el) {
		var elements = el.find('li');
		var nested = null
		elements.each(function() {
			var element = this;

			$(this).click(function() {
				if ($(this).attr('class') == 'hover')
					$(this).removeClass('hover');
				else
					$(this).addClass('hover');
			});
			// $(this).mouseout(function(){ $(this).removeClass('hover'); });

			// find nested UL
			// nested = $(this).children('ul');
			// declare width
			// var offsetWidth = 0;

			// find longest child
			// nested.each(function() {
			// var node = $(this);
			// node.children('LI').each(function(){
			// offsetWidth = (offsetWidth >= $(this).width()) ? offsetWidth :
			// $(this).width();
			// });
			// });

			// match longest child
			// nested.each(function() {
			// var node = $(this);
			//			
			// node.children('LI').each(function(){
			// $(this).width(100);
			// });
			// });

			// $(this).width(offsetWidth);
		});
	}
};