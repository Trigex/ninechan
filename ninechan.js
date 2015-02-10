/*
 * Ninechan JavaScript Backend
 * By Flashwave <http://flash.moe>
 * Distributed under the MIT-License
 */
 
var ninechan = {
    
    // Variables
    cookiePrefix: null,
    
    // Initialise ninechan.js
    init: function() {
        if(this.cookieData('get', this.cookiePrefix + 'style') == "")
            this.setStyle(this.getMainStyle());
        else
            this.setStyle(this.cookieData('get', this.cookiePrefix + 'style'));
    },
    
    // Get or set cookie data
    cookieData: function(action, name, data) {
        switch(action) {
            case 'get':
                return (result = new RegExp('(^|; )' + encodeURIComponent(name) + '=([^;]*)').exec(document.cookie)) ? result[2] : '';
                    
            case 'set':
                document.cookie = name + '=' + data;
                return;
                
            default:
                return;
        }
    },
    
    // Get the main style
    getMainStyle: function() {
        var i, a;
        
        for(i = 0; (a = document.getElementsByTagName('link')[i]); i++) {
            if(
                a.getAttribute('rel').indexOf('style') != -1 &&
                a.getAttribute('rel').indexOf('alt') == -1 &&
                a.getAttribute('title')
            )
                return a.getAttribute('title');
        }
        
        return null;
    },
    
    // Get the current active style
    getActiveStyle: function() {
        var i, a;
        
        for(i = 0; (a = document.getElementsByTagName('link')[i]); i++) {
            if(
                a.getAttribute('rel').indexOf('style') != -1 &&
                a.getAttribute('title') &&
                !a.disabled
            )
                return a.getAttribute('title');
        }
        
        return null;
    },
    
    // Set a style
    setStyle: function(title) {
        var i, a, t;
        
        t = false;
        
        this.cookieData('set', this.cookiePrefix + 'style', title);
        
        for(i = 0; (a = document.getElementsByTagName('link')[i]); i++) {
            if(
                a.getAttribute('rel').indexOf('style') != -1 &&
                a.getAttribute('title')
            ) {
                a.disabled = (a.getAttribute('title') == title) ? false : true;
                t = true;
            }
        }
        
        if(!t && title != null) {
            this.setStyle(this.getMainStyle());
        }
    }
    
}
