jQuery(document).ready(function($) {
    var table = $('#example').DataTable({
        "scrollY":        "70vh",
        "scrollCollapse": true,
        "paging":         false
    });

    $('#gButton').click( function () {
        var jName =  $('#jname').val();
        // console.log("ccccccccccccccccccc  "+jName);
        var selected = [];
        $('#checkboxes input:checked').each(function() {
            selected.push($(this).attr('name'));
        });
        $.ajax({
            url: jsData.ajaxurl,
            type: 'post',
            data:{
                action: 'journalFiles',
                array: selected,
                journalName: jName+'.pdf'
            },
            beforeSend: function(){
                $('.sp').append('<img src="'+jsData.homepath+'/wp-admin/images/loading.gif">')
            },
            complete: function(){
                $('.sp').html('<h3 style="color:green;">Journal is Created........</h3>');
            },
            success: function(data){
                $('.sp').html('<h5>Note: Enter new name of journal without extension(.pdf) if you want</h5>')
            }
        });
    } );
    
});