jQuery(document).ready(function() {
    if( ! window.location.hash || '#zume_project' === window.location.hash) {
        show_zume_project()
    }
    if('#zume_locations' === window.location.hash) {
        show_zume_locations()
    }

})
function load_static_section_content( id ) {
    "use strict";
    let chartDiv = jQuery('#chart')

    jQuery.ajax({
        type: 'POST',
        contentType: "application/json; charset=utf-8",
        data: JSON.stringify( { 'id': id } ),
        dataType: "json",
        url: dtStatic.root + 'static-section/v1/content',
        beforeSend: function(xhr) {
            xhr.setRequestHeader('X-WP-Nonce', dtStatic.nonce);
        },
    })
        .done( function( response ) {
            chartDiv.empty().append(`
            ${response}
            `)

        }) // end success statement
        .fail(function (err) {
            console.log("error")
            console.log(err)
        })


}