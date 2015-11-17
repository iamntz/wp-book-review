jQuery(document).ready(function($){
    var frame = wp.media({
        title : book_review_i18n.uploaderTitle,
        multiple : false,
        library : {
            type : 'image'
        },
        button : {
            text : book_review_i18n.uploaderButton
        }
    });

    $('.js-uploadBookCover').on('click', function(e){
        e.preventDefault();
        frame.open();
    });

    frame.on('close',function() {
        var attachments = frame.state().get('selection').toJSON();
        $('.js-bookCover').val(_.pluck(attachments, 'id')[0]);
    });
});
