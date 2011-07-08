(function(){
    var $ = jQuery;

    Embpicamoto = {Dialog: {}};

    Embpicamoto.Dialog.Manager = {


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
            return $("#embpicamoto_dlg #albums_container .picamoto_album");
        }


    }

})(jQuery)