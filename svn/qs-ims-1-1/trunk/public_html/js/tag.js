/*
  @author: remy sharp / http://remysharp.com
  @url: http://remysharp.com/2007/12/28/jquery-tag-suggestion/
  @usage: setGlobalTags(['javascript', 'jquery', 'java', 'json']); // applied tags to be used for all implementations
          $('input.tags').tagSuggest(options);
          
          The selector is the element that the user enters their tag list
  @params:
    matchClass - class applied to the suggestions, defaults to 'tagMatches'
    tagContainer - the type of element uses to contain the suggestions, defaults to 'span'
    tagWrap - the type of element the suggestions a wrapped in, defaults to 'span'
    sort - boolean to force the sorted order of suggestions, defaults to false
    url - optional url to get suggestions if setGlobalTags isn't used.  Must return array of suggested tags
    tags - optional array of tags specific to this instance of element matches
    delay - optional sets the delay between keyup and the request - can help throttle ajax requests, defaults to zero delay
    separator - optional separator string, defaults to ' ' (Brian J. Cardiff)
  @license: Creative Commons License - ShareAlike http://creativecommons.org/licenses/by-sa/3.0/
  @version: 1.4
  @changes: fixed filtering to ajax hits
*/

(function ($) {
    var globalTags = [];

    // creates a public function within our private code.
    // tags can either be an array of strings OR
    // array of objects containing a 'tag' attribute
    window.setGlobalTags = function(tags /* array */) {
        globalTags = getTags(tags);
    };
    
    function getTags(tags) {
        var tag, i, goodTags = [];
        for (i = 0; i < tags.length; i++) {
            tag = tags[i];
            if (typeof tags[i] == 'object') {
                tag = tags[i].tag;
            } 
            goodTags.push(tag);
        }
        
        return goodTags;
    }
    
    $.fn.tagSuggest = function (options) {
        var defaults = { 
            'matchClass' : 'tag-container', 
            'tagContainer' : 'div', 
            'tagWrap' : 'li', 
            'sort' : false,
            'icon' : true,//nút bên cạnh
            'tags' : null,
            'url' : null,
            'data' : '1=1',
            'delay' : 1,
            'separator' : ' ',
            'callback' : '',
            'element' : '#lookup',
            'placeHolder': Language.translate('SEARCH_PLACEHOLDER'),
            'currentTags' : ''
        };
        var i, tag, userTags = [], settings = $.extend({}, defaults, options);
        $(this).attr("placeholder", settings.placeHolder);
        if(settings.placeHolder == ''){
        	$(this).attr("placeholder", Language.translate('SEARCH_PLACEHOLDER'));
        }
        $(this).addClass('listbox');
        $(this).parent().find('.icon-down').remove();
        if(settings.icon){
        	$('<span id="'+ $(this).attr('id') +'_select" class="icon-down">&nbsp;</span>').insertAfter(this);
        }
        settings.delay = 1;
        if (settings.tags) {
            userTags = getTags(settings.tags);
        } else {
            userTags = globalTags;
        }

        return this.each(function () {
        	var tagsElm = $(this);
            var elm = this;
            var selected = null;
            var matches, fromTab = false;
            var suggestionsShow = false;
            var workingTags = [];
            var currentTag = {"position": 0, tag: ""};
            var tagMatches = document.createElement(settings.tagContainer);
            $(tagMatches).hide();
            function showSuggestionsDelayed(el, key) {
                if (settings.delay) {
                    if (elm.timer) clearTimeout(elm.timer);
                    elm.timer = setTimeout(function () {
                        showSuggestions(el, key);
                    }, settings.delay);
                } else {
                    showSuggestions(el, key);
                }
            }

            function showSuggestions(el, key) {
            	selected = null;
            	workingTags = el.value.split(settings.separator);
                matches = [];
                var i, html = '', chosenTags = {}, tagSelected = false;
                // we're looking to complete the tag on currentTag.position (to start with)
                currentTag = { position: settings.currentTags.length-1, tag: '' };
                
                for (i = 0; i < settings.currentTags.length && i < workingTags.length; i++) {
                    if (!tagSelected && 
                    		settings.currentTags[i] != workingTags[i]) {
                        currentTag = { position: i, tag: workingTags[i] };
                        tagSelected = true;
                    }
                    // lookup for filtering out chosen tags
                    chosenTags[settings.currentTags[i]] = true;
                }
                if (1) {
                    // collect potential tags
                	var postdata = settings.data + '&tag=' + currentTag.tag.replace("&","%26");
                    if (settings.url) {
                    	qssAjax.call(settings.url,postdata,function(m){
                                matches = m;
                            },null,false);
                    } else {
                        for (i = 0; i < userTags.length; i++) {
                            if (userTags[i].indexOf(currentTag.tag) === 0) {
                                matches.push(userTags[i]);
                            }
                        }                        
                    }

                    matches = $.grep(matches, function (v, i) {
                        return !chosenTags[v];
                    });

                    if (settings.sort) {
                        matches = matches.sort();
                    }                    
                    html += '<ul>';
                    $(matches).map(function(){
                    	if(this.extra == 'disabled'){
                    		html += '<' + settings.tagWrap + ' id="' + this.id + '" class="disabled">' + this.value + '</' + settings.tagWrap + '>';
                    	}
                    	else{
                    		html += '<' + settings.tagWrap + ' id="' + this.id + '" ' + this.extra + ' class="tag-suggestion">' + this.value + '</' + settings.tagWrap + '>';
                    	}
                    });
                    //html += '<' + settings.tagWrap + ' id="" ' + this.extra + ' class="tag-suggestion">No records found!</' + settings.tagWrap + '>';
                    html += '</ul>';
                    
                    if(settings.element != '#lookup'){
                    	var par = $(settings.element).parent().offset().top;
                    	var parenttop = ($(document).height()-par);
                    }
                    
                    tagMatches.html(html);
                    tagMatches.show();
                    //prevent hide for end of screen 
                    $tagheight = $(tagMatches).height();
                    if(settings.element != '#lookup'){
	                    if(parenttop < $tagheight){
	        				$(tagMatches).css('top',(par - $tagheight)+'px');
	        			}
                    }
                    
                    suggestionsShow = !!(matches.length);
                    var totalCount = matches.length;
                    if(selected == null){
            			selected = $('.'+settings.matchClass).find('li').first();
            			if($(selected).attr('id') == ''){
            				selected = $('.'+settings.matchClass).find('li:nth-child(2)');
            				totalCount--;
            			}
            			$(selected).addClass('tag-selected');
            			if(totalCount==1 && el.value != ''){
            				$(selected).click();
            			}
            		}
                } else {
                    hideSuggestions();
                }
            }

            function hideSuggestions() {
            	tagMatches.hide();
                tagMatches.empty();
                matches = [];
                suggestionsShow = false;
            }

            function setSelection() {
                var v = tagsElm.val();
                // tweak for hintted elements
                // http://remysharp.com/2007/01/25/jquery-tutorial-text-box-hints/
                if (v == tagsElm.attr('title') && tagsElm.is('.hint')) v = '';

                settings.currentTags = v.split(settings.separator);
                hideSuggestions();
            }

            function chooseTag(tag,id) {
                var i, index;
                for (i = 0; i < settings.currentTags.length; i++) {
                    if (settings.currentTags[i] != workingTags[i]) {
                        index = i;
                        break;
                    }
                }
                if(settings.callback){
                	workingTags[i] = tag;
                	settings.callback(tag,id,settings.element);
                }
                else{
                	if (index == workingTags.length - 1) tag = tag + settings.separator;
                	workingTags[i] = tag;
                	tagsElm.val(workingTags.join(settings.separator));
                	tagsElm.blur().focus();
                }
                
//                if (index == workingTags.length - 1 && !settings.element) tag = tag + settings.separator;
//
//                workingTags[i] = tag;
//
//                if(settings.element){
//                	var ctrl = $('<div>').addClass('tag-select')
//                						 .html(workingTags.join(settings.separator) + '<input type="hidden" name="members[]" value="'+id+'"><a href="#!" onclick="closeThis(this)" class="tag-close">');
//                	$('#'+settings.element).before(ctrl);
//                	tagsElm.val('');
//                	tagsElm.blur().focus();
//                }
//                else{
//                	tagsElm.val(workingTags.join(settings.separator));
//                    tagsElm.blur().focus();
//                }
          
                setSelection();
               
            }

            function handleKeys(ev) {
                fromTab = false;
                var type = ev.type;
                var resetSelection = false;

                switch (ev.keyCode) {
                    case 38: {
                    	if (suggestionsShow && type == 'keyup') {
                            // complete
                    		if(selected == null){
                    			selected = $('.'+settings.matchClass).find('li').last();
                    		}
                    		else{
                    			var newone = $(selected).prev();
                    			if(newone.length){
                    				$(selected).removeClass('tag-selected');
                    				selected = newone;
                    			}
                    		}
                    		$(selected).addClass('tag-selected');
                        }
                    	return true;
                    }
                    case 40: {
                    	if (suggestionsShow && type == 'keyup') {
                            // complete
                    		if(selected == null){
                    			selected = $('.'+settings.matchClass).find('li').first();
                    		}
                    		else{
                    			var newone = $(selected).next();
                    			if(newone.length){
                    				$(selected).removeClass('tag-selected');
                    				selected = newone;
                    			}
                    		}
                    		$(selected).addClass('tag-selected');
                        }
                    	return true;
                    }
                    case 13: {
                        if (type == 'keyup' && suggestionsShow && selected != null) {
                            // complete
                            chooseTag($(selected).text(),$(selected).attr('id'));
                            fromTab = true;
                            return false;
                        } else {
                        	 if (resetSelection) { 
                                 setSelection();
                             }
                        	 if(type == 'keyup'){
                             	showSuggestionsDelayed(this, ev.charCode);	
                             }
                             return false;
                        }
                    }
                    default: {
                    	if (this.value == ''){
                    		hideSuggestions();
                    		if(settings.callback){//keypress để phân biệt thằng unikey
                            	settings.callback('',0,settings.element);
                            }
                    		return true;
                    	} 
                    	else{
                    		hideSuggestions();
                            //setSelection();
                            return true;	
                    	}
                        
                    }
 
                }

                /*if (type == 'keypress' || type == 'keyup') {//keyup not run
                    switch (ev.charCode) {
                        case 9:
                        case 13: {
                            return true;
                        }
                    }
                    if (resetSelection) { 
                        setSelection();
                    }
                    if(ev.charCode || type == 'keyup'){
                    	showSuggestionsDelayed(this, ev.charCode);	
                    }
                }*/
            }

            tagsElm.after(tagMatches).keypress(handleKeys).keyup(handleKeys).blur(function () {
                if (fromTab == true || suggestionsShow) { // tweak to support tab selection for Opera & IE
                    fromTab = false;
                    tagsElm.focus();
                }
            });

            // replace with jQuery version
            tagMatches = $(tagMatches).click(function (ev) {
                if (ev.target.nodeName == settings.tagWrap.toUpperCase() && $(ev.target).is('.tag-suggestion')) {
                    chooseTag(ev.target.innerHTML,ev.target.id);
                }             
            }).addClass(settings.matchClass);
            /*$(tagsElm).click(function(){
            		showSuggestionsDelayed(this);
            });*/
            var self = this;
            $('#'+$(this).attr('id')+'_select').click(function(){
            	if(self.disabled){//Hủy nếu text disable
            		return;
            	}
            	if(suggestionsShow){
            		hideSuggestions();
            	}
            	else{
            		showSuggestionsDelayed(self);            		
            	}
            });
            $(document).click(function(ev){
            	if (ev.target.nodeName == settings.tagWrap.toUpperCase() && $(ev.target).is('.disabled')){
            		return;
            	}
            	hideSuggestions();	
            });
			/*var parentleft = ($(document).width()-$('.'+settings.matchClass).parent().offset().left);
			if(parentleft < 302){
				newleft = parentleft - 302;
				$('.'+settings.matchClass).css('right', newleft+'px');
			}
			else{
				$('.'+settings.matchClass).css('left', $('.'+settings.matchClass).parent().offset().left+'px');
			}
			var parenttop = ($(document).height()-$('.'+settings.matchClass).parent().offset().top);
			*/
			/*if(parenttop < 180){
				var bottom = $(document).height()-$('.'+settings.matchClass).parent().offset().top;
				$('.'+settings.matchClass).css('bottom',bottom+'px');
			}*/
			
            // initialise
            setSelection();
        });
    };
})(jQuery);