(function(){
    var $ = jQuery;

    Embpicamoto = {Dialog: {}};

    //Singleton object for controlling dialog in Edit form for Picasa insertions
    Embpicamoto.Dialog.Manager = {
        
        //Member variables
        
        _activeAlbumEl: null,
        _activePhotoEl: null,
        
        //Some helpful constans 
        Constants: {         
            dialogId: "embpicamoto_dlg",
            albumsContainerId: "albums_container",
            albumClassName: "embicamoto_album",
            albumIdPrefix: "embicamoto_album_"
        },


        init: function(){
            //Hide each album to begin with
            var albums = this.getAlbumEls();
            
            $.each(albums, function(i, el){ 
                $(el).hide();
            })

            //show first album      
            
            this.setAlbum(albums[0]);          
            
            
            //initiate select listener with toggle between albums showing
        },
        
        getAlbum: function(){
            return $(this._activeAlbumEl);
        },

        /*
         Return each picamoto album HMTL container found within dialog
         */
        getAlbumEls: function(){            
            var cssParts = [this.cssId(this.Constants.dialogId), this.cssId(this.Constants.albumsContainerId), this.cssClass(this.Constants.albumClassName)];
            return $(cssParts.join(" "));
        },
        
        setAlbum: function(albumEl){
          this.hideAlbum();  
           
          this._activeAlbumEl = albumEl;
          
          this.showAlbum();
        },
        
        
        cssId: function (id){
            return "#" + id;
        },
        
        cssClass: function(className){
            return "." + className;
        },
        
        //Actions 
        hideAlbum: function(){
            this.getAlbum().hide();
        },
        
        showAlbum: function(){
            this.getAlbum().show();
        } 
        
        


    }

})(jQuery)