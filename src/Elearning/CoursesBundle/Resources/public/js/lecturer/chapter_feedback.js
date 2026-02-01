var initChangeListenerFeedback = function() {
    $('.feedback-chapter-form').on('input', 'input, textarea', function(e){
        markCurrentChapterDirty();
    });
};

function loadFeedback(chapter_id) {
    $.ajax({
        url: Routing.generate('elearning_courses_get_feedback'),
        data: {
            chapter_id: chapter_id
        },
        success: function (response) {
            $('.chapter-content .feedback-chapter').show();
            var feedback = response.feedback;
            if (!feedback) {
                feedback = {};
            }
            $('.feedback-chapter-form input.feedback_id').val(feedback.id);
            $('.feedback-chapter-form input.chapter_id').val(chapter_id);
            if (feedback.data.length > 0) {
                var questiontmpl = $('.feedback-chapter-form .questions div.question').first().clone();
                $('.feedback-chapter-form .questions div.question').remove();
                $.each(feedback.data, function(index, el) {
                    questiontmpl.find('input.text').val(el.fieldname).attr('name', 'questions['+index+'][text]');
                    questiontmpl.find('select').val(el.type).attr('name', 'questions['+index+'][type]');
                    if (el.type == "select") {
                        questiontmpl.find('.choices').show();
                        var choicetmpl = questiontmpl.find('.choice').first().clone();
                        questiontmpl.find('.choice').remove();
                        $.each(el.choices, function(i, choice) {
                            choicetmpl.find('input[type=text]').val(choice).attr('name', 'questions['+index+'][choice][]');
                            questiontmpl.find('.choices .choices-list').append(choicetmpl.clone());
                        });
                    }
                    else {
                        questiontmpl.find('.choices .choice').slice(1).remove();
                        questiontmpl.find('.choices .choice input[type=text]').val("").attr('name', 'questions['+index+'][choice][]');
                        questiontmpl.find('.choices').hide();
                    }
                    var cloneQuestion = questiontmpl.clone();
                    cloneQuestion.find('select').val(el.type);
                    $('.feedback-chapter-form .questions').append(cloneQuestion);
                });
            }
            initChangeListenerFeedback();
        }
    });
}
$(function () {

    $('.feedback-chapter-form').on('click', '.add-feedback-question', function(e){
        e.preventDefault();
        var question = $('.feedback-chapter-form .questions div.question').first().clone();
        question.find('input[type=text]').val("");
        question.find('select').val("text");
        question.find('.choices').hide();
        question.find('.choices .choice').slice(1).remove();
        var count = $('.feedback-chapter-form .questions div.question').length;
        question.find('input.text').attr('name', 'questions['+count+'][text]');
        question.find('select.type').attr('name', 'questions['+count+'][type]');
        question.find('.choices .choice input[type=text]').attr('name', 'questions['+count+'][choice][]');
        $(this).parents('.feedback-chapter-form').find('.questions').append(question);
        markCurrentChapterDirty();
    });

    $('.feedback-chapter-form').on('click', '.remove-feedback-question', function(e){
        e.preventDefault();
        var questions = $('.feedback-chapter-form .questions div.question');
        if (questions.length > 1) {
            $(this).parents('.question').remove();
            markCurrentChapterDirty();
        }
    });

    $('.feedback-chapter-form').on('click', '.add-feedback-choice', function(e){
        e.preventDefault();
        var choice = $(this).parents('.question').find('.choices .choice').first().clone();
        choice.find('input[type=text]').val("");
        $(this).parents('.question').find('.choices .choices-list').append(choice);
        markCurrentChapterDirty();
    });

    $('.feedback-chapter-form').on('click', '.remove-feedback-choice', function(e){
        e.preventDefault();
        var choices = $(this).parents('.choices').find('.choice');
        if (choices.length > 1) {
            $(this).parents('.choice').remove();
            markCurrentChapterDirty();
        }
    });

    $('.feedback-chapter-form').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: Routing.generate("elearning_courses_save_feedback"),
            data: $(this).serialize(),
            method: "post",
            success: function (response) {
                if (response.success) {
                    current_chapter_edited = false;
                    $('.feedback-chapter-form input.feedback_id').val(response.id);
                    showmessage(translations['new_course.editor.successfully_saved'], 'success');
                }
            }
        });
    });

    $('.feedback-chapter-form').on('change', 'select.type', function(e){
        var value = $(this).val();
        if (value == "select") {
            $(this).parents('.question').find('div.choices').show();
        }
        else {
            $(this).parents('.question').find('div.choices').hide();
        }
    });

    $('.preview-feedback').on('click', function (e) {
        e.preventDefault();
        console.log("PREVIEW");
        $.magnificPopup.open({
            items: {
                src: "#feedback-chapter-preview-popup",
                type: "inline"
            }
        });
    });
});
