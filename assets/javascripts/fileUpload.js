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

    function previewAttachment(attachment) {
        var previewContainer = $('.js-previewBookCover');
        var attachmentPreview = attachment.sizes[previewContainer.data('previewSize')];

        var previewImage = $('<img />').attr({
            src : attachmentPreview.url,
            width : attachmentPreview.width,
            height : attachmentPreview.height,
        });

        previewContainer.html(previewImage);
    }

    frame.on('open', function(){
        var selection = frame.state().get('selection');
        var id = $('.js-bookCover').val();
        attachment = wp.media.attachment(id);
        attachment.fetch();
        selection.add( attachment ? [ attachment ] : [] );
    });

    frame.on('close',function() {
        var attachments = frame.state().get('selection').toJSON();
        if(attachments.length){
            $('.js-bookCover').val(_.pluck(attachments, 'id')[0]);
            previewAttachment(attachments[0]);
        }
    });
});
