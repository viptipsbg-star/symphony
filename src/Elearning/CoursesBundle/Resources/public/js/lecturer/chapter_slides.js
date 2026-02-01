Dropzone.autoDiscover = false;
var slides_chapter_dropzone = null;

/* Loads files to dropzone containers */
function loadSlidesChapterFiles(chapter_id) {
    $.ajax({
        url: Routing.generate('elearning_courses_get_slides_files'),
        data: {
            chapter_id: chapter_id
        },
        success: function (response) {
            if (response.success) {
                $('.chapter-content .slides-chapter').show();
                $('.dropzone input[name=chapter_id]').val(chapter_id);

                var thisDropzone = slides_chapter_dropzone;
                if (!thisDropzone) return;
                thisDropzone = thisDropzone[0].dropzone;
                $(thisDropzone.options.previewsContainer).html("");
                $.each(response.files, function (key, value) {
                    var mockFile = {name: value.name, size: value.size}; // here we get the file name and size as response
                    thisDropzone.options.addedfile.call(thisDropzone, mockFile);
                    thisDropzone.options.thumbnail.call(thisDropzone, mockFile, value.path);
                    thisDropzone.emit('success', mockFile, {success: true, id: value.id});
                    thisDropzone.options.complete.call(thisDropzone, mockFile);
                });
            }
        }
    });
}

$(function () {
    var dropzoneOptions = {
        maxFilesize: 4000,
        addRemoveLinks: true,
        acceptedFiles: "image/*,application/pdf",
        previewTemplate: document.querySelector('#upload-preview-template').innerHTML,
        dictDefaultMessage: translations["new_course.editor.upload.drop_here"],
        dictRemoveFile: translations["new_course.editor.upload.remove"],
        dictInvalidFileType: translations["new_course.editor.upload.invalid_file_type"],
        init: function () {

            this.on('success', function (file, response) {
                if (response.success) {
                    if (!response.files) {
                        $(file.previewElement).attr('data-id', response.id);
                        $(file.previewElement).find('img.preview').attr('src', response.thumbnail)
                    }
                    else {
                        var thisDropzone = slides_chapter_dropzone;
                        if (!thisDropzone) return;
                        thisDropzone = thisDropzone[0].dropzone;
                        $(file.previewElement).remove();
                        $.each(response.files, function (key, value) {
                            var mockFile = {name: value.name, size: value.size}; // here we get the file name and size as response
                            thisDropzone.options.addedfile.call(thisDropzone, mockFile);
                            thisDropzone.options.thumbnail.call(thisDropzone, mockFile, value.thumbnail);
                            thisDropzone.emit('success', mockFile, {success: true, id: value.id});
                            thisDropzone.options.complete.call(thisDropzone, mockFile);
                        });
                    }
                }
                else {
                    $(file.previewElement).remove();
                    showmessage(translations['new_course.editor.upload.error'] + " " + response.message, 'danger');
                }
            });

            this.on('removedfile', function (file) {
                var id = $(file.previewElement).data('id');
                markCurrentChapterDirty();

                $.ajax({
                    url: Routing.generate('elearning_courses_slides_delete_file'),
                    method: "POST",
                    data: {
                        id: id
                    }
                });
            });

            this.on('addedfile', function (data) {
                console.log('ADDED FILE', data);
            });
            this.on('error', function (data, message, xhr) {
                var preview = $(data.previewElement);
                preview.remove();
                showmessage(translations['new_course.editor.upload.error'] + " " + message, 'danger');
            });

        }
    };

    slides_chapter_dropzone = $('#slides-chapter-files-upload').dropzone($.extend(dropzoneOptions, {
        previewsContainer: "#slides-chapter-files-upload .slides-chapter-files-list"
    }));


    /* File reordering in dropzone containers */
    $('.slides-chapter-files-list').on('click', '.controls .move', function (e) {
        e.preventDefault();
        var id = $(this).parents('.dz-preview').data('id');
        var direction = $(this).hasClass('move-left') ? 'left' : 'right';
        var element = $(this).parents('.dz-preview');
        if ((element.is(':first-child') && direction == 'left') || (element.is(':last-child') && direction == 'right')) {
            return false;
        }
        $.ajax({
            url: Routing.generate('elearning_courses_slides_file_change_order'),
            method: "POST",
            data: {
                'file_id': id,
                'direction': direction
            },
            success: function (response) {
                if (response.success) {
                    if (direction == 'left') {
                        element.prev('.dz-preview').before(element);
                    }
                    else {
                        element.next('.dz-preview').after(element);
                    }
                }
            }
        });
    });

    $('.slides-chapter-form').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: Routing.generate("elearning_courses_save_slides_chapter"),
            method: "post",
            success: function (response) {
                if (response.success) {
                    current_chapter_edited = false;
                    showmessage(translations['new_course.editor.successfully_saved'], 'success');
                }
            }
        });
    });

});


