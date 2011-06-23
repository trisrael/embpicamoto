(function() {
    tinymce.create('tinymce.plugins.embpicamoto', {
        init : function(ed, url) {
            if ( typeof embpicamoto_dlg_open == 'undefined' ) return;
			
			ed.addButton('embpicamoto', {
                title : 'Picasa',
                image : url+'/embpicamoto.gif',
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
                longname : "Simple Picasa Albums",
                author : 'Tristan Goffman',
                authorurl : 'http://github.com/trisrael/',
                infourl : 'http://github.com/trisrael/Embed-Picasa-Plugin',
                version : "1.0"
            };
        }
    });
    tinymce.PluginManager.add('embpicamoto', tinymce.plugins.embpicamoto);
})();
