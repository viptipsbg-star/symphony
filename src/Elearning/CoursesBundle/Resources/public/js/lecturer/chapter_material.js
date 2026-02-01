function loadMaterial(chapter_id) {
    $.ajax({
        url: Routing.generate('elearning_courses_get_material'),
        data: {
            chapter_id: chapter_id
        },
        success: function (response) {
            if (response.success) {
                $('.chapter-content .material-chapter').show();
                $('.material-chapter-form input.chapter_id').val(chapter_id);
                if (response.files) {
                    var rowtmpl = $('.material-chapter div.template-file').clone();
                    var container = $('.material-chapter-form .files');
                    container.html("");
                    $.each(response.files, function (index, file) {
                        var filetmpl = rowtmpl.clone();
                        filetmpl.find('input.material_id').val(file.id);
                        filetmpl.find('input.title').val(file.title);
                        filetmpl.find('input[type=file]').hide()
                            .after('<a href="' + file.link + '" class="btn btn-orange material-preview" target="_blank">' +
                                translations["new_course.editor.material.preview"] +
                                '</a>');
                        filetmpl.find('a.remove-material-file').attr('data-id', file.id);
                        filetmpl.show();
                        filetmpl.removeClass('template-file');
                        container.append(filetmpl);
                    });
                }
            }
        }
    });
}
$(function () {
    $('.material-chapter-form').on('submit', function (e) {
        e.preventDefault();
        var formData = new FormData($(this)[0]);
        var error = false;
        $('.material-chapter-form .files div.file').each(function (index) {
            if (!$(this).find('input.title').val() || (!$(this).find('input.fileinput').val() && !$(this).find('input.material_id').val())) {
                showmessage(translations["new_course.editor.material.fill_all_fields"], "danger");
                error = true;
                return false;
            }
        });
        if (error) {
            return false;
        }
        $.ajax({
            url: Routing.generate("elearning_courses_save_material"),
            data: formData,
            type: "post",
            async: false,
            cache: false,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success) {
                    current_chapter_edited = false;
                    showmessage(translations['new_course.editor.successfully_saved'], 'success');
                    var ids = response.ids;
                    $('.material-chapter-form .files div.file').each(function (index) {
                        var link = Routing.generate("elearning_courses_material_file", {material_id: ids[index]});
                        $(this).find('input.material_id').val(ids[index]);
                        $(this).find('a.remove-material-file').attr('data-id', ids[index]);
                        if ($(this).find('.material-preview').length === 0) {
                            $(this).find('input[type=file]').val("").hide()
                                .after('<a href="' + link + '" class="btn btn-orange material-preview" target="_blank">' +
                                    translations["new_course.editor.material.preview"] +
                                    '</a>');
                        }
                    });
                }
                else {
                    showmessage(response.message, 'danger');
                }
            }
        });
    });

    $('.material-chapter-form').on('click', '.add-material-file', function (e) {
        e.preventDefault();
        var file = $('.material-chapter div.template-file').clone();
        file.find('a.material-preview').remove();
        file.find('input[type=text]').val("");
        file.find('input[type=file]').val("").show();
        file.find('input.material_id').val("").show();
        file.show();
        file.removeClass('template-file');
        $(this).parents('.material-chapter-form').find('.files').append(file);
        markCurrentChapterDirty();
    });

    $('.material-chapter-form').on('click', '.remove-material-file', function (e) {
        e.preventDefault();
        var material_id = $(this).data('id');
        var button = $(this);
        if (!material_id) {
            button.parents('.file').remove();
        }
        else {
            $.ajax({
                url: Routing.generate('elearning_courses_material_delete', {material_id: material_id}),
                type: 'post',
                success: function(response) {
                    if (response.success) {
                        button.parents('.file').remove();
                    }
                }
            })
        }
        markCurrentChapterDirty();
    });

});
