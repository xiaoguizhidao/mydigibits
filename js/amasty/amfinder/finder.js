/**
* @author Amasty Team
* @copyright Copyright (c) 2010-2011 Amasty (http://www.amasty.com)
* @package Amasty_Finder
*/
var amFinder = new Class.create();

amFinder.prototype = {
    initialize: function(containerId, ajaxUrl, loadingText, isNeedLast, autoSubmit)
    {
        this.containerId = containerId;
        this.ajaxUrl	 = ajaxUrl;
        this.autoSubmit  = Number(autoSubmit);
        this.loadingText = loadingText;
        this.isNeedLast  = Number(isNeedLast);
        this.selects     = new Array();
        
        //possible bug if select order in the HTML will be different
    	$$('#' + this.containerId + ' select').each(function(select){
    		select.observe('change', this.onChange.bindAsEventListener(this));
    		this.selects[this.selects.length] = select;
    	}.bind(this));
        
        
    },
        
    onChange: function(event)
    {
    	var select     = Event.element(event);
    	var parentId   = select.value;
    	var dropdownId = 0;
    	
    	/* should load next element's options only if current is not the last one */
    	for (var i = 0; i < this.selects.length; i++){
    		if (this.selects[i].id == select.id && i != this.selects.length-1){
    			var selectToReload = this.selects[i + 1];
    			if (selectToReload){
    				dropdownId = selectToReload.id.substr(selectToReload.id.search('--') + 2);
    			}
    			break;
    		}
    	}
    	
    	this.clearAllBelow(select);
    	
    	if (0 != parentId && dropdownId){
    		var postData = 'dropdown_id=' + dropdownId + '&parent_id=' + parentId + '&box_id=' + selectToReload.id + '&type=' + $$('#' + this.containerId).first().className;
    		new Ajax.Request(this.ajaxUrl, {
                method: 'post',
                postBody : postData,
                evalJSON : 'force',
                
                onLoading: function(){
                	this.showLoading(selectToReload);
                }.bind(this),
                
                onSuccess: function(transport) {
                    if (transport.responseJSON){
                        this.clearSelectOptions(selectToReload);
                    	var itemsFound = false;
                    	transport.responseJSON.each(function(item){
                    		itemsFound = true;
                    		var option = document.createElement('option');
                    		option.value = item.value;
                    		option.text  = item.label;
                    		option.label = item.label;
                    		$(selectToReload).appendChild(option);
                    	});
                    	
                    	if (itemsFound){
                    		$(selectToReload).disabled = false;
                    	}
                    }
                }.bind(this)
            });
    	}
    },
    
    isLast: function(select)
    {
        return (this.selects[this.selects.length-1].id == select.id);    
    },
    
    isFirst: function(select)
    {
        return (this.selects[0].id == select.id);         
    },
    
    clearSelectOptions: function(select)
    {
    	$(select).descendants().each(function(option){
    		option.remove();
    	});
    },
    
    clearAllBelow: function(select)
    {
    	var startClearing = false;
    	for (var i = 0; i < this.selects.length; i++){
    		if (startClearing){
				this.clearSelectOptions(this.selects[i]);
				$(this.selects[i]).disabled = true;
			}
    		if (this.selects[i].id == select.id){
    			startClearing = true;
    		}
    	}
    	var type = (((this.isLast(select) && !this.isNeedLast) && select.value > 0) || ((this.isNeedLast) && ((select.value > 0) || (!this.isFirst(select))))) ? 'block' : 'none';
        
        if ('block' == type && this.autoSubmit && this.isLast(select))
        {
            $$('#' + this.containerId + ' .amfinder-buttons button')[0].form.submit();
        } else {
            $$('#' + this.containerId + ' .amfinder-buttons')[0].style.display = type;            
        }
        

    },
    
    showLoading: function(selectToReload)
    {
        var option = document.createElement('option');
        option.value = 0;
        option.text  = this.loadingText;
        option.label = this.loadingText;
        $(selectToReload).appendChild(option);        
    }
};


var amFinderHorizontal = new Class.create();

amFinderHorizontal.prototype = {
    initialize: function(containerId, ajaxUrl, loadingText, isNeedLast, autoSubmit)
    {
        this.containerId = containerId;
        this.ajaxUrl	 = ajaxUrl;
        this.autoSubmit  = Number(autoSubmit);
        this.loadingText = loadingText;
        this.isNeedLast  = Number(isNeedLast);
        this.selects     = new Array();

        //possible bug if select order in the HTML will be different
        $$('#' + this.containerId + ' dl').each(function(select){
            select.observe('click', this.onClick.bindAsEventListener(this));
            this.selects[this.selects.length] = select;
        }.bind(this));

        window.scrollTo(0, 0);
    },

    onClick: function(event, isObject)
    {
        var select;

        if (isObject) {
            select     = event;
        } else {
            select     = Event.element(event);
        }
//        var select     = Event.element(event);
        var parentId   = select.readAttribute('data-item-value');
        var dropdownId = 0;

        $$('#' + this.containerId + ' .finder_' + select.readAttribute('finder-id')).first().value = parentId;

        /* should load next element's options only if current is not the last one */
        for (var i = 0; i < this.selects.length; i++){
            if (this.selects[i].id.substr(this.selects[i].id.search('--') + 2) == select.readAttribute('finder-id')){
                select = this.selects[i];
                if((i != this.selects.length-1)){
                    var selectToReload = this.selects[i + 1];
                    if (selectToReload){
                        dropdownId = selectToReload.id.substr(selectToReload.id.search('--') + 2);
                    }
                    break;
                }
            }
        }

        this.clearAllBelow(select);

        if (parentId && dropdownId){
            var postData = 'dropdown_id=' + dropdownId + '&parent_id=' + parentId + '&box_id=' + selectToReload.id + '&type=' + $$('#' + this.containerId).first().className;
            new Ajax.Request(this.ajaxUrl, {
                method: 'post',
                postBody : postData,
                evalJSON : 'force',

                onLoading: function(){
                    this.showLoading(selectToReload);
                }.bind(this),

                onSuccess: function(transport) {
                    if (transport.responseJSON){
                        this.clearSelectOptions(selectToReload);
                        var itemsFound = false;

                        selectToReload.innerHTML="";
                        transport.responseJSON.each(function(item){
                            var obj = document.createElement('dd');
                            obj.innerHTML=item;
                            selectToReload.appendChild(obj);
                        });

                        selectToReload.parentNode.classList.add('active');

                        window.scrollTo(0,selectToReload.parentNode.positionedOffset().top - 10);
                    }
                }.bind(this)
            });
        }
    },

    isLast: function(select)
    {
        return (this.selects.last().id == select.id);
    },

    isFirst: function(select)
    {
        return (this.selects.first().id == select.id);
    },

    clearSelectOptions: function(select)
    {
        $(select).descendants().each(function(option){
            option.remove();
        });
    },

    clearAllBelow: function(select)
    {
        var startClearing = false;
        for (var i = 0; i < this.selects.length; i++){
            if (startClearing){
                this.clearSelectOptions(this.selects[i]);
                $(this.selects[i]).disabled = true;
            }
            if (this.selects[i].id == select.id){
                startClearing = true;
            }
        }
        var type = (((this.isLast(select) && !this.isNeedLast) && select.value > 0) || ((this.isNeedLast) && ((select.value > 0) || (!this.isFirst(select))))) ? 'block' : 'none';

        if ('block' == type && this.autoSubmit && this.isLast(select))
        {
            $$('#' + this.containerId + ' .amfinder-buttons button')[0].form.submit();
        } else {
            if(this.isLast(select)){
                $$('#' + this.containerId + ' .amfinder-buttons')[0].style.display = type;
                $$('#' + this.containerId + ' .amfinder-buttons button')[0].form.submit();
            }
        }
    },

    showLoading: function(selectToReload)
    {
        var option = document.createElement('option');
        option.value = 0;
        option.text  = this.loadingText;
        option.label = this.loadingText;
        $(selectToReload).appendChild(option);
    }
};
