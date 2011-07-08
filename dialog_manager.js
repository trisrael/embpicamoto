(function(){
    var $ = jQuery;

    Embpicamoto = {Dialog: {}};

    Embpicamoto.Dialog.Manager = {
        
        
        //Some helpful constans 
        Constants: {         
            dialogId: "embpicamoto_dlg",
            albumsContainerId: "albums_container",
            albumClassName: "embicamoto_album",
            albumIdPrefix: this.Constants['albumClassName'] + "_"
        },


        init: function(){
            //Hide each album to begin with
            $.each(this.getAlbumEls(), function(i, el){ 
                $(el).hide();
            })

            //show first album      


        },


        /*
         Return each picamoto album HMTL container found within dialog
         */
        getAlbumEls: function(){
            
        var cssParts = [cssId(this.Constants.dialogId), cssId(this.Constants.albumsContainerId), cssClass(this.Constants.albumClasName)];
            return $(cssParts.join(" "));
        },
        
        
        cssId: function (id){
            return "#" + id;
        },
        
        cssClass: function(className){
            return "." + className;
        }


    }

})(jQuery)