jQuery(document).ready(function($) {
    var teamList = $('#chillthemes-team-list');
    teamList.sortable({
        update: function( event, ui ) {
            opts = {
                async: true,
                cache: false,
                dataType: 'json',
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'team_sort',
                    order: teamList.sortable( 'toArray' ).toString() 
                },
                success: function( response ) {
                    return;
                },
                error: function( xhr, textStatus, e ) {
                    alert( 'The order of the items could not be saved at this time, please try again.' );
                    return;
                }
            };
        $.ajax(opts);
        }
    });
});