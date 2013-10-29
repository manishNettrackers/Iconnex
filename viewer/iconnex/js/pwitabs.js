    $(function() {
        $( "#mapfiltertabs" ).tabs({
            //event: "mouseover",
            collapsible: true
        });
    });

    var flt_tabs_id = '#mapfiltertabs';
    var add_tab = function (id, name, data) {
        $(flt_tabs_id).append(data).tabs('add', id, name);
    };
    var remove_all_tabs = function () {
        if ( $(flt_tabs_id).tabs )
        {
            var tab_count = $(flt_tabs_id).tabs('length');
            for (i=0; i<tab_count; i++){
                $(flt_tabs_id).tabs( "remove" , 0 )
        }
        }
    };
