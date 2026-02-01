var initChangeListenerExam = function() {
    $('.exam-chapter-form').on('input', 'input, textarea', function(e){
        markCurrentChapterDirty();
    });
};

function loadExamOptions(options) {
    var form = $('.exam-chapter-form .exam-options');
    if (!options) {
        return;
    }
    $.each(options, function (key, value) {
        if (key == "maxtime") {
            form.find('input#exammaxtime').val(value);
        }
        else if (key == "numberofretries") {
            form.find('input#numberofretries').val(value);
        }
        else if (key == "numberofquestions") {
            form.find('input#numberofquestions').val(value);
        }
        else if (key == "passcriteria") {
            form.find('input#passcriteria').val(value);
        }
        else if (key == "waittime") {
            form.find('input#waittime').val(value);
        }
        else if (key == "questionsorder") {
            form.find('input[value=' + value + ']').prop("checked", true);
        }
        else if (key == "rules") {
            form.find("textarea#rules").val(value);
            form.find("textarea#rules").summernote('code', value);
        }
    });
}

function loadExamData(data) {
    var question_tmpl = $('.exam-chapter-form .exam-type .questions>.question').first().clone();
    if (data && data.length > 0) {
        $('.exam-chapter-form .exam-type .questions>.question').remove();
        var questions_container = $('.exam-chapter-form .exam-type .questions');
        $('.exam-chapter-form textarea.question').summernote('destroy');
        question_tmpl.find('.note-editor').remove();
        $.each(data, function (key, el) {
            var tmpl = question_tmpl.clone();
            tmpl.find('textarea.question').val(el.question);
            tmpl.find('textarea.question').attr('id', "question-" + key);
            tmpl.find('.question-number .number').html(key + 1);
            var choice_tmpl = tmpl.find('.choices .choice:first-child').clone();
            var container = tmpl.find('.choices').first();
            container.html("");
            $.each(el.choices, function (key, choice) {
                choice_tmpl.find('.text').val(choice.text);
                choice_tmpl.find('input.correct').prop('checked', choice.correct == 1);
                container.append(choice_tmpl.clone());
            });
            questions_container.append(tmpl);
            tmpl.find('textarea.question').summernote({
                height: 100,
                callbacks: {
                    onChange: function () {
                        markCurrentChapterDirty();
                    }
                }
            });
        });
    }
}

function loadExam(chapter_id) {
    $.ajax({
        url: Routing.generate('elearning_courses_get_exam'),
        data: {
            chapter_id: chapter_id
        },
        success: function (response) {
            $('.chapter-content .exam-chapter').show();
            var exam = response.exam;
            if (!exam) {
                exam = {};
            }
            $('.exam-chapter-form input.exam_id').val(exam.id);
            loadExamData(exam.data);
            loadExamOptions(exam.options);
            initChangeListenerExam();
        }
    });
}


function saveExamData() {
    var form = $('.exam-chapter-form .exam-type');
    var data = [];
    form.find('.questions>.question').each(function () {
        var question = $(this).find('textarea.question').val();
        var choices = [];
        $(this).find('.choice').each(function () {
            var text = $(this).find('input.text').val();
            var correct = $(this).find('input.correct').is(":checked") ? 1 : 0;
            choices.push({text: text, correct: correct});
        });
        var item = {question: question, choices: choices};
        data.push(item);
    });

    return data;
}

function saveExamOptions() {
    var options = {};

    $('.exam-chapter-form .exam-options').find('input, textarea').each(function () {
        if ($(this).is('#exammaxtime')) {
            options['maxtime'] = $(this).val();
        }
        else if ($(this).is('#numberofretries')) {
            options['numberofretries'] = $(this).val();
        }
        else if ($(this).is('#numberofquestions')) {
            options['numberofquestions'] = $(this).val();
        }
        else if ($(this).is('#passcriteria')) {
            options['passcriteria'] = $(this).val();
        }
        else if ($(this).is('#waittime')) {
            options['waittime'] = $(this).val();
        }
        else if ($(this).is('[type=radio]') && $(this).is(":checked")) {
            if ($(this).is('.questionsorderselect')) {
                options['questionsorder'] = $(this).val();
            }
        }
        else if ($(this).is('#rules')) {
            options['rules'] = $(this).val();
        }
    });
    return options;
}


$(function () {
    $('.exam-chapter-form').on('click', '.add-exam-choice', function (e) {
        e.preventDefault();
        var choice = $('.exam-chapter-form .exam-type .form-group.choice').first().clone();
        choice.find('input[type=text]').val("");
        choice.find('input[type=checkbox]').prop("checked", false);
        $(this).parents('.question').find('.choices').append(choice);
        markCurrentChapterDirty();
    });
    $('.exam-chapter-form .add-exam-question').on('click', function (e) {
        e.preventDefault();
        var question = $('.exam-chapter-form .exam-type .questions .question:first-child').clone();
        question.find('textarea.question').attr('id', $('.exam-chapter-form .exam-type .questions .question').length);
        question.find('textarea, input[type=text]').val("");
        question.find('input[type=checkbox]').prop("checked", false);
        question.find('.note-editor').remove();
        var number_of_questions = $('.exam-chapter-form .exam-type .questions div.question').length;
        question.find('.question-number .number').html(number_of_questions + 1);
        question.find('.choices>.choice').slice(1).remove();
        $('.exam-chapter-form .exam-type .questions').append(question);
        question.find('textarea').summernote({
            height: 100,
            callbacks: {
                onChange: function () {
                    markCurrentChapterDirty();
                }
            }
        });
        markCurrentChapterDirty();
    });
    $('.exam-chapter-form').on('click', '.remove-exam-choice', function (e) {
        e.preventDefault();
        if ($(this).parents('.choices').find('.choice').length > 1) {
            $(this).parents('.choice').remove();
            markCurrentChapterDirty();
        }
    });

    $('.exam-chapter-form').on('click', '.remove-exam-question', function (e) {
        e.preventDefault();
        if ($('.exam-chapter-form .questions .question').length > 1) {
            $(this).parents('.question').find('textarea.question').summernote('destroy');
            console.log($(this).parents('.question').find('textarea.question'));
            $(this).parents('.question').remove();
            $('.exam-chapter-form .questions .question .question-number .number').each(function (index) {
                $(this).html(index + 1);
            });
            markCurrentChapterDirty();
        }
    });

    $('.exam-chapter-form').submit(function (e) {
        e.preventDefault();
        var exam_id = $(this).find('input.exam_id').val();
        var data = saveExamData();
        var options = saveExamOptions();
        if (!$(this).valid()) {
            return false;
        }

        $.ajax({
            url: Routing.generate("elearning_courses_save_exam"),
            method: 'post',
            data: {
                data: data,
                chapter_id: current_chapter_id,
                id: exam_id,
                options: options
            },
            success: function (response) {
                if (response.success) {
                    current_chapter_edited = false;
                    $('.exam-chapter-form input.exam_id').val(response.id);
                    showmessage(translations['new_course.editor.successfully_saved'], 'success');
                }
            }
        })
    });

    $('.exam-chapter-form').validate({
        errorPlacement: function (error, element) {
            if (element.parents('.radio-inline').length > 0) {
                error.insertAfter(element.parents('.form-control'));
            }
            else if (element.parents('.checkbox-inline').length > 0) {
                error.insertAfter(element.parents('.form-control'));
            }
            else {
                error.insertAfter(element);
            }
        }
    });
    $('.exam-chapter-form textarea#rules').summernote({
        height: 300,
        callbacks: {
            onChange: function () {
                markCurrentChapterDirty();
            }
        }
    });

});

