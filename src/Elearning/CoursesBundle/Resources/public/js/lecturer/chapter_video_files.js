$(function () {
    var dropzoneOptions = {
        maxFilesize: 4000,
        maxFiles: 1,
        addRemoveLinks: true,
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
                        var thisDropzone;
                        if (response.type == 'instructor')
                            thisDropzone = video_full_dropzone;
                        if (response.type == 'webm')
                            thisDropzone = video_webm_dropzone;
                        if (response.type == 'mobile')
                            thisDropzone = video_mobile_dropzone;
                        if (!thisDropzone) return;
                        thisDropzone = thisDropzone[0].dropzone;
                        $(file.previewElement).remove();
                        $.each(response.files, function (key, value) {
                            var mockFile = {name: value.name, size: value.size}; // here we get the file name and size as response
                            thisDropzone.options.addedfile.call(thisDropzone, mockFile);
                            thisDropzone.options.thumbnail.call(thisDropzone, mockFile, value.thumbnail);
                            thisDropzone.emit('success', mockFile, {success: true, id: value.id});
                            thisDropzone.options.complete.call(thisDropzone, mockFile);
                            thisDropzone.options.maxFiles = thisDropzone.options.maxFiles - 1;
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
                    url: Routing.generate('elearning_courses_delete_file'),
                    method: "POST",
                    data: {
                        id: id
                    }
                });
                this.options.maxFiles = this.options.maxFiles + 1;
                this.files = [];
            });

            this.on('error', function (data, message, xhr) {
                var preview = $(data.previewElement);
                preview.remove();
                showmessage(translations['new_course.editor.upload.error'] + " " + message, 'danger');
            });

        }
    };


    video_full_dropzone = $('#video-full-upload').dropzone($.extend(dropzoneOptions, {
        previewsContainer: "#video-full-upload .files-list",
        acceptedFiles: ".mp4",
    }));

    video_webm_dropzone = $('#video-webm-upload').dropzone($.extend(dropzoneOptions, {
        previewsContainer: "#video-webm-upload .files-list",
        acceptedFiles: ".webm",
    }));

    video_mobile_dropzone = $('#video-mobile-upload').dropzone($.extend(dropzoneOptions, {
        previewsContainer: "#video-mobile-upload .files-list",
        acceptedFiles: ".mp4",
    }));

});

function loadVideoFilesType(chapter_id, type) {
    $.ajax({
        url: Routing.generate('elearning_courses_get_chapter_files'),
        data: {
            chapter_id: chapter_id,
            type: type,
        },
        success: function (response) {
            if (response.success) {
                $('.video-files').show();
                $('.dropzone input[name=chapter_id]').val(chapter_id);
                var thisDropzone;
                if (type == 'instructor')
                    thisDropzone = video_full_dropzone;
                if (type == 'webm')
                    thisDropzone = video_webm_dropzone;
                if (type == 'mobile')
                    thisDropzone = video_mobile_dropzone;
                if (!thisDropzone) return;
                thisDropzone = thisDropzone[0].dropzone;
                thisDropzone.files = [];
                thisDropzone.options.maxFiles = 1;
                $(thisDropzone.options.previewsContainer).html("");
                $.each(response.files, function (key, value) {
                    var mockFile = {name: value.name, size: value.size}; // here we get the file name and size as response
                    thisDropzone.options.addedfile.call(thisDropzone, mockFile);
                    thisDropzone.options.thumbnail.call(thisDropzone, mockFile, value.path);
                    thisDropzone.emit('success', mockFile, {success: true, id: value.id});
                    thisDropzone.options.complete.call(thisDropzone, mockFile);
                    thisDropzone.options.maxFiles = thisDropzone.options.maxFiles - 1;
                });
            }
        }
    });
}