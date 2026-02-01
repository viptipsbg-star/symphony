Dropzone.autoDiscover = false;
var instructor_dropzone = null;
var slides_dropzone = null;

/* Loads files to dropzone containers */
function loadVideoFiles(chapter_id, type) {
    $.ajax({
        url: Routing.generate('elearning_courses_get_chapter_files'),
        data: {
            chapter_id: chapter_id,
            type: type
        },
        success: function (response) {
            if (response.success) {
                $('.chapter-content .video-chapter').show();
                $('.dropzone input[name=chapter_id]').val(chapter_id);

                var thisDropzone = (type == "instructor") ? instructor_dropzone : slides_dropzone;
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
        acceptedFiles: "video/*,image/*,audio/*,application/pdf",
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
                        var thisDropzone = (response.type == "instructor") ? instructor_dropzone : slides_dropzone;
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
                    url: Routing.generate('elearning_courses_delete_file'),
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

    instructor_dropzone = $('#instructor-files-upload').dropzone($.extend(dropzoneOptions, {
        previewsContainer: "#instructor-files-upload .files-list",
    }));
    slides_dropzone = $('#slides-files-upload').dropzone($.extend(dropzoneOptions, {
        previewsContainer: "#slides-files-upload .files-list",
    }));


    /* File reordering in dropzone containers */
    $('.files-list').on('click', '.controls .move', function (e) {
        e.preventDefault();
        var id = $(this).parents('.dz-preview').data('id');
        var direction = $(this).hasClass('move-left') ? 'left' : 'right';
        var element = $(this).parents('.dz-preview');
        if ((element.is(':first-child') && direction == 'left') || (element.is(':last-child') && direction == 'right')) {
            return false;
        }
        $.ajax({
            url: Routing.generate('elearning_courses_file_change_order'),
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


    /* Load files into editor */
    function loadEditorFiles(chapter_id) {
        $.ajax({
            url: Routing.generate('elearning_courses_get_chapter_editor_content'),
            data: {
                'chapter_id': chapter_id
            },
            success: function (response) {
                if (response.success) {
                    var instructor_container = $('.hidden-data .instructor-data');
                    var slides_container = $('.hidden-data .slides-data');
                    instructor_container.html("");
                    slides_container.html("");
                    $.each(response.instructor, function (key, file) {
                        var element = "";
                        if (file.type == "image") {
                            element = '<img src="' + file.path + '" data-id="' + file.id + '"/>';
                        }
                        else if (file.type == "video") {
                            element = '<video src="' + file.path + '" preload="none" data-id="' + file.id + '" ></video>'
                        }
                        else if (file.type == "audio") {
                            element = '<audio src="' + file.path + '" preload="none" data-id="' + file.id + '" ></audio>'
                        }
                        instructor_container.append(element);
                    });
                    $.each(response.slides, function (key, file) {
                        var element = "";
                        if (file.type == "image") {
                            element = '<img src="' + file.path + '" data-id="' + file.id + '"/>';
                        }
                        else if (file.type == "video") {
                            element = '<video src="' + file.path + '" preload="none" data-id="' + file.id + '" ></video>'
                        }
                        else if (file.type == "audio") {
                            element = '<audio src="' + file.path + '" preload="none" data-id="' + file.id + '" ></audio>'
                        }

                        slides_container.append(element);
                    });
                    editorplayer.mainVideoData = $('.hidden-data .instructor-data > *');
                    editorplayer.slidesData = $('.hidden-data .slides-data > *');
                    editorplayer.mainMonitor = $('.editor-player .main-monitor .content');
                    editorplayer.currentSlideMonitor = $('.editor-player .current-slide .content');
                    editorplayer.nextSlideMonitor = $('.editor-player .next-slide .content');
                    editorplayer.playPauseBtn = $('.editor-player .controls .playpause');
                    editorplayer.stopBtn = $('.editor-player .controls .stop');
                    editorplayer.nextMainBtn = $('.editor-player .controls .next-instructor');
                    editorplayer.nextSlideBtn = $('.editor-player .controls .next-slide');
                    editorplayer.startPreviewBtn = $('.start-video-chapter-preview');
                    editorplayer.clearSynchronizationBtn = $('.clear-synchronization');
                    editorplayer.timeContainer = $('.editor-player .time');
                    editorplayer.progressbars = $('.editor-player .progressbars');
                    editorplayer.previewMainMonitor = $('#video-chapter-preview-popup .main-monitor');
                    editorplayer.previewSlideMonitor = $('#video-chapter-preview-popup .slide-monitor');
                    editorplayer.previewPlayPauseBtn = $('#video-chapter-preview-popup .controls .playpause');
                    editorplayer.previewTimeContainer = $('#video-chapter-preview-popup .time');
                    editorplayer.setVideoData(response.times);
                    editorplayer.init();
                }
            }
        });
    }

    $('.update-editor-files').on('click', function (e) {
        e.preventDefault();
        markCurrentChapterDirty();
        loadEditorFiles(current_chapter_id);
    });

    $('.save-video-chapter').on('click', function (e) {
        e.preventDefault();
        var data = editorplayer.getVideoData();

        var mainLargestNum = 0;
        for (var id in data.mainvideo) {
            if (data.mainvideo[id] > mainLargestNum) {
                mainLargestNum = data.mainvideo[id];
            }
            else {
                data.mainvideo[id] = parseInt(mainLargestNum, 10) + 5000;
                mainLargestNum = parseInt(mainLargestNum, 10) + 5000;
            }
        }

        var slidesLargestNum = 0;
        for (var id in data.slidesvideo) {
            if (data.slidesvideo[id] > slidesLargestNum) {
                slidesLargestNum = data.slidesvideo[id];
            }
            else {
                data.slidesvideo[id] = parseInt(slidesLargestNum, 10) + 5000;
                slidesLargestNum = parseInt(slidesLargestNum, 10) + 5000;
            }
        }

        if (data.mainvideo.length === 0 && editorplayer.mainVideoData.length > 0) {
            var slideslasttime = 0;
            for (var file in data.slidesvideo) {
                if (data.slidesvideo[file] > slideslasttime) {
                    slideslasttime = data.slidesvideo[file];
                }
            }
            var index = 0;
            editorplayer.mainVideoData.each(function(){
                data.mainvideo[$(this).data('id')] = slideslasttime + index * 5;
                index++;
            });
        }

        if (data.slidesvideo.length === 0 && editorplayer.slidesData.length > 0) {
            var mainlasttime = 0;
            for (var file in data.mainvideo) {
                if (data.mainvideo[file] > mainlasttime) {
                    mainlasttime = data.mainvideo[file];
                }
            }
            var index = 0;
            editorplayer.slidesData.each(function(){
                data.slidesvideo[$(this).data('id')] = mainlasttime;
                index++;
            });
        }

        var times = {'mainvideo': {}, 'slidesvideo': {}};
        for (var file in data.mainvideo) {
            times.mainvideo[file.toString()] = data.mainvideo[file];
        }
        for (var file in data.slidesvideo) {
            times.slidesvideo[file.toString()] = data.slidesvideo[file];
        }

        $.ajax({
            url: Routing.generate("elearning_courses_save_video"),
            method: "post",
            data: {
                times: times
            },
            success: function (response) {
                if (response.success) {
                    current_chapter_edited = false;
                    showmessage(translations['new_course.editor.successfully_saved'], 'success');
                }
            }
        });
    });

    $('.change-step a').on('click', function (e) {
        e.preventDefault();
        $(this).hide();
        $(this).siblings('a').show();
        if ($(this).hasClass('open-uploads')) {
            $('.video-chapter .video-sync').hide('slide', {direction: 'down'});
            $('.video-chapter .file-uploads').show('slide', {direction: 'up'});
        }
        else {
            $('.video-chapter .video-sync').show('slide', {direction: 'down'});
            $('.video-chapter .file-uploads').hide('slide', {direction: 'up'});
        }
    });

});


