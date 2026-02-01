function loadLesson(chapter_id) {
    $.ajax({
        url: Routing.generate('elearning_courses_get_lesson'),
        data: {
            chapter_id: chapter_id
        },
        success: function(response) {
            if (response.success) {
                $('.chapter-content .lesson-chapter').show();
                $('.lesson-chapter-form input.chapter_id').val(chapter_id);
                $('.lesson-chapter-form input.lesson_id').val(response.lesson.id);
                $('#lesson-content').summernote('code', response.lesson.content);
            }
        }
    });
}
$(function() {
    $('#lesson-content').summernote({
        height:"500px",
        callbacks: {
            onChange: function () {
                markCurrentChapterDirty();
            }
        }
    });

    $('.lesson-chapter-form').on('submit', function(e){
        e.preventDefault();
        $.ajax({
            url: Routing.generate("elearning_courses_save_lesson"),
            data: $(this).serialize(),
            method: "post",
            success: function(response) {
                if (response.success) {
                    current_chapter_edited = false;
                    $('.lesson-chapter-form input.lesson_id').val(response.id);
                    showmessage(translations['new_course.editor.successfully_saved'], 'success');
                }
            }
        });
    });

    $('.preview-lesson').on('click', function(e){
        e.preventDefault();
        var content = $('#lesson-content').summernote('code');
        $('#lesson-chapter-preview-popup .content').html(content);
        $.magnificPopup.open({
            items: {
                src: "#lesson-chapter-preview-popup",
                type: "inline"
            }
        });
    });
});
