var initChangeListenerQuiz = function() {
    $('.quiz-chapter-form').on('input', 'input, textarea', function(e){
        markCurrentChapterDirty();
    });
};

function resetQuiz() {
    $('.quiz-chapter-form .quiz div.questions div.question:not(:first)').remove();
    var question = $('.quiz-chapter-form .quiz div.questions div.question');
    question.find('.question-number .number').html(1);
    question.find('input.question').val('');
    question.find('.choices .choice:not(:first)').remove();
    question.find('.choices .choice input.text').val('');
    question.find('.choices .choice input.correct').prop('checked', false);

    $('.quiz-chapter-form .connect div.choices div.choice:not(:first)').remove();
    var choice = $('.quiz-chapter-form .connect div.choices div.choice select.connect-choice-type').val('text');
    var choice = $('.quiz-chapter-form .connect div.choices div.choice .field .text').show();
    var choice = $('.quiz-chapter-form .connect div.choices div.choice .field .text input').val('');
    var choice = $('.quiz-chapter-form .connect div.choices div.choice .field .image img').hide().attr('src', '');
    var choice = $('.quiz-chapter-form .connect div.choices div.choice .field .image .file-dropzone').show();
    var choice = $('.quiz-chapter-form .connect div.choices div.choice .field .image .upload-img .new_image_text').show();
    var choice = $('.quiz-chapter-form .connect div.choices div.choice .field .image .upload-img .update_image_text').hide();
}

function loadQuizTypeQuiz(data) {
    var question_tmpl = $('.quiz-chapter-form .quiz .questions>.question').clone();
    $('.quiz-chapter-form .quiz .questions>.question').remove();
    var questions_container = $('.quiz-chapter-form .quiz .questions');
    $.each(data, function (key, el) {
        var tmpl = question_tmpl.clone();
        tmpl.find('input.question').val(el.question);
        tmpl.find('.question-number .number').html(key + 1);
        var choice_tmpl = tmpl.find('.choices .choice:first-child').clone();
        var container = tmpl.find('.choices');
        container.html("");
        $.each(el.choices, function (key, choice) {
            choice_tmpl.find('.text').val(choice.text)
            choice_tmpl.find('input.correct').prop('checked', choice.correct == 1);
            container.append(choice_tmpl.clone());
        });
        questions_container.append(tmpl);
    });
}

function loadQuizTypeConnect(data) {
    var choice_tmpl = $('.quiz-chapter-form .connect .choices .choice:first-child').clone();
    var container = $('.quiz-chapter-form .connect .choices');
    container.html("");
    $.each(data.choices, function (key, choice) {
        choice_tmpl.find('.first .field > *').hide();
        if (choice.first.type == "text") {
            choice_tmpl.find('.first .field .text input').val(choice.first.value);
            choice_tmpl.find('.first .field > .text').show();
        }
        else if (choice.first.type == "image") {
            choice_tmpl.find('.first .field .image img').attr('src', choice.first.value).show();
            choice_tmpl.find('.first .field > .image').show();
            choice_tmpl.find('.first .field > .image .upload-img .new_image_text').hide();
            choice_tmpl.find('.first .field > .image .upload-img .update_image_text').show();
            choice_tmpl.find('.first .field > .image .image-container .file-dropzone').hide();
        }
        choice_tmpl.find('.second .field > *').hide();
        if (choice.second.type == "text") {
            choice_tmpl.find('.second .field .text input').val(choice.second.value);
            choice_tmpl.find('.second .field > .text').show();
        }
        else if (choice.second.type == "image") {
            choice_tmpl.find('.second .field .image img').attr('src', choice.second.value).show();
            choice_tmpl.find('.second .field > .image').show();
            choice_tmpl.find('.second .field > .image .upload-img .new_image_text').hide();
            choice_tmpl.find('.second .field > .image .upload-img .update_image_text').show();
            choice_tmpl.find('.second .field > .image .image-container .file-dropzone').hide();
        }
        var item = choice_tmpl.clone();
        item.find('.first .connect-choice-type').val(choice.first.type);
        item.find('.second .connect-choice-type').val(choice.second.type);
        container.append(item);
    });
}


function loadQuiz(chapter_id) {
    $.ajax({
        url: Routing.generate('elearning_courses_get_quiz'),
        data: {
            chapter_id: chapter_id
        },
        success: function (response) {
            $('.chapter-content .quiz-chapter').show();
            var quiz = response.quiz;
            if (!quiz) {
                quiz = {};
            }
            $('.quiz-chapter-form input.quiz_id').val(quiz.id);
            $('.quiz-chapter-form #quiz-type-select').val(quiz.type).trigger('change');
            if (quiz.type == "quiz") {
                loadQuizTypeQuiz(quiz.data);
            }
            else if (quiz.type == "connect") {
                loadQuizTypeConnect(quiz.data);
            }
            initChangeListenerQuiz();
        }
    });
}


function saveQuizTypeQuiz() {
    var form = $('.quiz-chapter-form .quiz');
    var data = [];
    form.find('.questions>.question').each(function () {
        var question = $(this).find('input.question').val();
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


function saveQuizTypeConnect() {
    var form = $('.quiz-chapter-form .connect');
    var choices = [];

    function getSingleTypeData(choiceitem) {
        var type = choiceitem.find('select.connect-choice-type').val();
        var value = "";
        if (type == "text") {
            value = choiceitem.find('.field .text input').val();
        }
        else if (type == "image") {
            value = choiceitem.find('.field .file-upload img').attr('src');
        }
        return {type: type, value: value};
    }

    form.find('.choice').each(function () {
        var first = getSingleTypeData($(this).find('.choice-item.first'));
        var second = getSingleTypeData($(this).find('.choice-item.second'));
        choices.push({first: first, second: second});
    });
    var data = {choices: choices};

    return data;
}


function previewQuizTypeQuiz(popup) {
    popup.find('.content>.quiz').show();
    popup.find('.content>.connect').hide();
    var root = popup.find('.content>.quiz');
    var questiontmpl = root.find('.question-item').first().clone();
    root.find('.questions').html("");
    var choicetmpl = questiontmpl.find('.choices>.choice').first().clone();
    questiontmpl.find('.choices').html("");


    $('.quiz-chapter-form .quiz .questions>.question').each(function (question_index) {
        var question = $(this).find('.question').val();
        var choices = $(this).find('.choices .choice');
        questiontmpl.find('h1.question').html(question);
        questiontmpl.find('.choices').html("");
        var correct_answers = [];
        choices.each(function (index) {
            choicetmpl.find('input.check').val(index)
            choicetmpl.find('.text').html($(this).find('input.text').val());
            questiontmpl.find('.choices').append(choicetmpl.clone());
            if ($(this).find('input.correct').is(":checked")) {
                correct_answers.push(index);
            }
        });
        questiontmpl.find('input.check').attr('name', 'check-' + question_index);
        questiontmpl.find('input.correct').val(JSON.stringify(correct_answers));
        if (correct_answers.length > 1) {
            questiontmpl.find('input.check').attr('type', 'checkbox');
        }
        else {
            questiontmpl.find('input.check').attr('type', 'radio');
        }
        questiontmpl.find('.choice').shuffle();
        questiontmpl.clone().appendTo(root.find('.questions'));
    });
}


function previewQuizTypeConnect(popup) {
    popup.find('.content>.connect').show();
    popup.find('.content>.quiz').hide();
    var choices = $('.quiz-chapter-form .connect .choices .choice');
    var root = popup.find('.content>.connect');
    var choice_tmpl = root.find('.choices .choice:first-child').clone();
    var connect_type = null;
    root.find('.choices').html("");
    choices.each(function (index) {
        choice_tmpl.find('.handle').removeAttr('id');
        connect_type = $(this).find('.first .connect-choice-type').val();
        choice_tmpl.find('.first').attr('data-index', index);
        if (connect_type == 'text') {
            choice_tmpl.find('.first .item .text').html($(this).find('.first .field .text input').val());
            choice_tmpl.find('.first .item .image').hide();
            choice_tmpl.find('.first .item .text').show();
        }
        else if (connect_type == 'image') {
            choice_tmpl.find('.first .item .image img').attr('src', $(this).find('.first .field .image img').attr('src'));
            choice_tmpl.find('.first .item .image').show();
            choice_tmpl.find('.first .item .text').hide();
        }
        connect_type = $(this).find('.second .connect-choice-type').val();
        choice_tmpl.find('.second').attr('data-index', index);
        if (connect_type == 'text') {
            choice_tmpl.find('.second .item .text').html($(this).find('.second .field .text input').val());
            choice_tmpl.find('.second .item .image').hide();
            choice_tmpl.find('.second .item .text').show();
        }
        else if (connect_type == 'image') {
            choice_tmpl.find('.second .item .image img').attr('src', $(this).find('.second .field .image img').attr('src'));
            choice_tmpl.find('.second .item .image').show();
            choice_tmpl.find('.second .item .text').hide();
        }
        root.find('.choices').append(choice_tmpl.clone());
    });
    root.find('.first').shuffle();
    root.find('.second').shuffle();
    jsPlumb.ready(function () {
        jsPlumb.reset();
        jsPlumb.Defaults.Container = popup;
        jsPlumb.Defaults.Anchors = [[0.5, 0.5, 0, 0], [0.5, 0.5, 0, 0]];
        root.find('.choice .handle').each(function () {
            jsPlumb.makeSource(this, {
                maxConnections: 1,
                parameters: {
                    'correct_id': $(this).parent().data('index')
                },
                reattach: true,
                connector: "Straight",
                connectorStyle: {lineWidth: 4, strokeStyle: '#fa8f00'},
                endpoint: ["Blank", {}],
                filter: function (event, element) {
                    if (jsPlumb.getConnections({target: element.id}).length !== 0 ||
                        jsPlumb.getConnections({source: element.id}).length !== 0) {
                        jsPlumb.detachAllConnections(element);
                    }
                    return true;
                }
            });
            jsPlumb.makeTarget(this, {
                maxConnections: 1,
                parameters: {
                    'correct_id': $(this).parent().data('index')
                },
                reattach: true,
                connector: "Straight",
                connectorStyle: {lineWidth: 4, strokeStyle: '#fa8f00'},
                endpoint: ["Blank", {}],
                beforeDrop: function (info) {
                    var source = $('#' + info.sourceId);
                    var target = $('#' + info.targetId);
                    var haveconnection = jsPlumb.getConnections({target: info.sourceId}).length > 0 ||
                        jsPlumb.getConnections({source: info.sourceId}).length > 0 ||
                        jsPlumb.getConnections({source: info.targetId}).length > 0 ||
                        jsPlumb.getConnections({source: info.targetId}).length > 0;
                    return !haveconnection &&
                        (source.parent().is(".first") && target.parent().is(".second")) ||
                        (target.parent().is(".first") && source.parent().is(".second"));
                }
            });
        });
        jsPlumb.bind('click', function (connection, e) {
            jsPlumb.detach(connection);
        });
        jsPlumb.bind('endpointclick', function (endpoint, e) {
            jsPlumb.detachAllConnections(endpoint);
        });
    });

}


$(function () {


    /* Quiz type --START */
    $('.quiz-chapter-form').on('click', '.add-quiz-choice', function (e) {
        e.preventDefault();
        var choice = $('.quiz-chapter-form .quiz .form-group.choice').first().clone();
        choice.find('input[type=text]').val("");
        choice.find('input[type=checkbox]').prop("checked", false);
        $(this).parents('.question').find('.choices').append(choice);
        markCurrentChapterDirty();
    });
    $('.quiz-chapter-form .add-quiz-question').on('click', function (e) {
        e.preventDefault();
        var question = $('.quiz-chapter-form .quiz .questions .question:first-child').clone();
        question.find('input[type=text]').val("");
        question.find('input[type=checkbox]').prop("checked", false);
        var number_of_questions = $('.quiz-chapter-form .quiz .questions div.question').length;
        console.log($('.quiz-chapter-form .quiz .questions div.question'), number_of_questions);
        question.find('.question-number .number').html(number_of_questions + 1);
        question.find('.choices>.choice').slice(1).remove();
        $('.quiz-chapter-form .quiz .questions').append(question);
        markCurrentChapterDirty();
    });
    $('.quiz-chapter-form').on('click', '.remove-quiz-choice', function (e) {
        e.preventDefault();
        if ($('.quiz-chapter-form .quiz .choices .choice').length > 1) {
            $(this).parents('.choice').remove();
        }
        markCurrentChapterDirty();
    });
    $('.quiz-chapter-form').on('click', '.remove-quiz-question', function (e) {
        e.preventDefault();
        if ($(this).parents('.questions').find('div.question').length > 1) {
            var questions = $(this).parents('.questions');
            $(this).parents('.question').remove();
            questions.find('div.question').each(function(index){
                $(this).find('.number').html(index + 1);
            });
        }
        markCurrentChapterDirty();
    });
    /* Quiz type --END */

    /* Connect type --START */
    $('.quiz-chapter-form .add-connect-choice').on('click', function (e) {
        e.preventDefault();
        var choice = $('.quiz-chapter-form .connect .form-group.choice:first-child').clone();
        choice.find('input[type=text]').val("");
        choice.find('select').val("text");
        choice.find('.file-upload img').attr('src', "").hide();
        choice.find('.field .image').hide();
        choice.find('.field .text').show();
        $('.quiz-chapter-form .connect .choices').append(choice);
        markCurrentChapterDirty();
    });
    $('.quiz-chapter-form').on('click', '.remove-connect-choice', function (e) {
        e.preventDefault();
        if ($('.quiz-chapter-form .connect .choices .choice').length > 1) {
            $(this).parents('.choice').remove();
        }
        markCurrentChapterDirty();
    });

    $('.quiz-chapter-form .connect').on('change', '.connect-choice-type', function (e) {
        var type = $(this).val();
        $(this).parents(".choice-item").find('.field > *').hide();
        if (type == "text") {
            $(this).parents(".choice-item").find('.field .text').show();
        }
        else if (type == "image") {
            $(this).parents(".choice-item").find('.field .image').show();
        }
        markCurrentChapterDirty();
    });

    function readimage(files, image) {
        if (files && files[0]) {
            var FR = new FileReader();
            FR.onload = function (e) {
                image.attr('src', e.target.result);
                image.show();
            };
            FR.readAsDataURL(files[0]);
            var fileuploadparent = image.parents('.file-upload');
            fileuploadparent.find('.upload-img .new_image_text').hide();
            fileuploadparent.find('.upload-img .update_image_text').show();
            fileuploadparent.find('.image-container .file-dropzone').hide();
        }
    }

    $('.quiz-chapter-form').on('change', '.file-upload input[type=file]', function () {
        var image = $(this).parents('.file-upload').find('img');
        readimage(this.files, image);
    });
    $('.quiz-chapter-form').on('click', '.image .file-upload a.upload-img', function (e) {
        e.preventDefault();
        $(this).siblings('input[type=file]').trigger('click');
    });
    $('.quiz-chapter-form').on('click', '.image .file-upload .file-dropzone', function (e) {
        e.preventDefault();
        $(this).parent().siblings('input[type=file]').trigger('click');
    });
    $('.quiz-chapter-form').on('dragenter', '.image .file-upload .image-container', function (e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('dragenter');
    });
    $('.quiz-chapter-form').on('dragover', '.image .file-upload .image-container', function (e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('dragover');
    });
    $('.quiz-chapter-form').on('drop', '.image .file-upload .file-dropzone', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var image = $(this).parents('.file-upload').find('img');
        var files = e.originalEvent.dataTransfer.files;
        readimage(files, image);
    });
    /* Connect type --END */


    $('#quiz-type-select').change(function (e) {
        var type = $(this).val();
        $('.quiz-chapter-form .quiz-type').hide();
        $('.quiz-chapter-form .' + type).show();
        resetQuiz();
    });


    $('.quiz-chapter-form').submit(function (e) {
        e.preventDefault();
        var type = $(this).find('#quiz-type-select').val();
        var quiz_id = $(this).find('input.quiz_id').val();
        var data = {};
        if (type == "quiz") {
            data = saveQuizTypeQuiz();
        }
        else if (type == "connect") {
            data = saveQuizTypeConnect();
        }

        $.ajax({
            url: Routing.generate("elearning_courses_save_quiz"),
            method: 'post',
            data: {
                data: data,
                type: type,
                chapter_id: current_chapter_id,
                id: quiz_id
            },
            success: function (response) {
                if (response.success) {
                    current_chapter_edited = false;
                    $('.quiz-chapter-form input.quiz_id').val(response.id);
                    showmessage(translations['new_course.editor.successfully_saved'], 'success');
                }
            }
        })
    });


    $('.preview-quiz').on('click', function (e) {
        e.preventDefault();
        var popup = $('#quiz-chapter-preview-popup');
        popup.find('.result .alert').hide();
        var type = $('.quiz-chapter-form #quiz-type-select').val();
        if (type == "quiz") {
            previewQuizTypeQuiz(popup);
        }
        else if (type == "connect") {
            previewQuizTypeConnect(popup);
        }
        $.magnificPopup.open({
            items: {
                src: "#quiz-chapter-preview-popup",
                type: "inline"
            }
        });
    });

    /* Quiz type --START */
    $('#quiz-chapter-preview-popup .quiz .answer-quiz').on('click', function (e) {
        e.preventDefault();

        var questions = $('#quiz-chapter-preview-popup .quiz .questions .question-item');

        var allcorrect = true;
        questions.each(function () {
            var correct_count = 0;
            var correct = JSON.parse($(this).find('input.correct').val());
            $(this).find('.choices .choice input.check').each(function () {
                console.log($(this).is(":checked"), correct, $(this).val(), correct.indexOf(parseInt($(this).val())));
                if ($(this).is(':checked')) {
                    if (correct.indexOf(parseInt($(this).val())) > -1) {
                        correct_count++;
                    }
                }
            });
            if (correct_count != correct.length) {
                $('#quiz-chapter-preview-popup .result .correct').hide();
                $('#quiz-chapter-preview-popup .result .incorrect').show();
                allcorrect = false;
                return false;
            }
        });
        if (allcorrect) {
            $('#quiz-chapter-preview-popup .result .correct').show();
            $('#quiz-chapter-preview-popup .result .incorrect').hide();
        }
    });

    /* Quiz type --END */

    /* Connect type --START */
    $('#quiz-chapter-preview-popup .connect .answer-connect').on('click', function (e) {
        e.preventDefault();
        var connections = jsPlumb.getConnections('*');
        var correct = true;

        if (connections.length != $('#quiz-chapter-preview-popup .connect .choices .choice').length) {
            correct = false;
        }
        else {
            $.each(connections, function (index, connection) {
                params1 = connection.endpoints[0].getParameters();
                params2 = connection.endpoints[1].getParameters();
                if (params1.correct_id != params2.correct_id) {
                    correct = false;
                    return false;
                }
            });
        }
        if (correct) {
            $('#quiz-chapter-preview-popup .result .correct').show();
            $('#quiz-chapter-preview-popup .result .incorrect').hide();
        }
        else {
            $('#quiz-chapter-preview-popup .result .correct').hide();
            $('#quiz-chapter-preview-popup .result .incorrect').show();
        }

    });
    /* Connect type --END */

});

