Embpicamoto = {Dialog: {}};

Embpicamoto.Dialog.Manager = {

    
    init: function(){
        //Hide each album to begin with
        jQuery.each(this.getAlbumEls(), function(el){ 
            el.hide();
        })
        
        //show first album      
        
        
    },
    
    
    /*
     Return each picamoto album HMTL container found within dialog
     */
    getAlbumEls: function(){
        return jQuery("#embpicamoto_dlg #album_container .picamoto_album");
    }
    
    
}

