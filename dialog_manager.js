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
            albumSelectId: "embpicamoto_dlg_album_id",
            albumsContainerId: "albums_container",
            albumClassName: "embpicamoto_album",
            albumIdPrefix: "embpicamoto_album_"
        },

        init: function(){
            //Hide each album to begin with
            var albums = this.getAlbumEls();
            
            $.each(albums, function(i, el){ 
                $(el).hide();
            })

            //show first album      
            
            this.setAlbum(albums[0]);   
            
            //Switch between which album is showing when select is switched
            $(this.cssId(this.Constants.albumSelectId)).change(function(){
                var that = Embpicamoto.Dialog.Manager;
                that.setAlbum( $(that.cssId( that.buildAlbumId(this.value)) ));
            });
            
            
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
        
        getInsertPhotoButton: function(){
          return this.getInsertButtonEls()[0];  
        },
        
        getInsertAlbumButton: function(){
          return this.getInsertButtonEls()[0];  
        },
                
        getInsertButtonEls: function(){
            return $(this.cssId(this.Constants.dialogId)+" button");
        },
        
        
        setAlbum: function(albumEl){
          this.hideAlbum();  
           
          this._activeAlbumEl = albumEl;
          
          this.showAlbum();
        },
        
        //Helpers
        
        
        cssId: function (id){
            return "#" + id;
        },
        
        cssClass: function(className){
            return "." + className;
        },
        
        buildAlbumId: function(id){
            return this.Constants.albumIdPrefix + id;
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