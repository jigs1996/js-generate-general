jQuery(document).ready(function($) { 
    var progressbar= $('.progress-bar');
    $(".upload-file").click(function(){
        console.log("Post id: "+jsAjax.postid);
        console.log("Post id: "+jsAjax.blogid);
        $(".form-horizontal").ajaxForm({
            url: jsAjax.ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: {
                jsNonce: jsAjax.jsFileup,
                postid: jsAjax.postid,
                blogid: jsAjax.blogid,
                action: 'upload_file',
            },
            beforeSend: function() {
                        var val = $('input[type=file]').val().toLowerCase(),
                            regex = new RegExp("(.*?)\.(pdf)$");
                
                        if (!(regex.test(val))) {
                            $('input[type=file]').val('');
                            alert('Please select correct file format');
                            $(".progress").css("display","none");
                        }else{
                            $(".progress").css("display","block");
                            progressbar.width('0%');
                            progressbar.text('0%');
                        }
            },
            uploadProgress: function (event, position, total, percentComplete) {
                progressbar.width(percentComplete + '%');
                progressbar.text(percentComplete + '%');
            },
            complete: function(xhr) {
                console.log(xhr.responseText)
                if(xhr.responseText == "10")
                    $('.msg').html("<p>Successfully uploaed</p>");
                else
                    $('.msg').html("Error while uploading");;
            }
        });
        
    });
}); 