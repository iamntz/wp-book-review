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

    var previewClassName = 'has-preview';

    var previewContainer = $('.js-previewBookCoverContainer');
    var previewCover = $('.js-previewBookCover');
    var bookCover = $('.js-bookCover');

    $('.js-uploadBookCover').on('click', function(e){
        e.preventDefault();
        frame.open();
    });

    function previewAttachment(attachment) {
        var attachmentPreview = attachment.sizes[previewCover.data('previewSize')];

        var previewImage = $('<img />').attr({
            src : attachmentPreview.url,
            width : attachmentPreview.width,
            height : attachmentPreview.height,
        });

        previewCover.html(previewImage);
    }

    $('.js-deletePreviewBookCover').on('click', function(e){
        e.preventDefault();
        previewCover.empty();
        bookCover.val('');
        previewContainer.removeClass(previewClassName);
    });

    frame.on('open', function(){
        var selection = frame.state().get('selection');
        var id = bookCover.val();
        attachment = wp.media.attachment(id);
        attachment.fetch();
        selection.add( attachment ? [ attachment ] : [] );
    });

    frame.on('close',function() {
        var attachments = frame.state().get('selection').toJSON();
        if(attachments.length){
            bookCover.val(_.pluck(attachments, 'id')[0]);
            previewContainer.addClass(previewClassName);
            previewAttachment(attachments[0]);
        }
    });
});
