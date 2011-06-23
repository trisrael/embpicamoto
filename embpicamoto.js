(function() {
    tinymce.create('tinymce.plugins.embpicamoto', {
        init : function(ed, url) {
            if ( typeof embpicamoto_dlg_open == 'undefined' ) return;
			
			ed.addButton('embpicamoto', {
                title : 'Picasa',
                image : url+'/embpicasa.gif',
                onclick : function() {
                    embpicamoto_dlg_open();
                }
            });
        },
        createControl : function(n, cm) {
            return null;
        },
        getInfo : function() {
            return {
                longname : "Embed picasa album",
                author : 'Marchenko Alexandr',
                authorurl : 'http://mac-blog.org.ua/',
                infourl : 'http://mac-blog.org.ua/',
                version : "1.0"
            };
        }
    });
    tinymce.PluginManager.add('embpicamoto', tinymce.plugins.embpicamoto);
})();
