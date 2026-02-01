var quizconnect = {

    container: null,
    choices: null,

    init: function() {
        jsPlumb.ready(function(){
            jsPlumb.reset();
            jsPlumb.setContainer(quizconnect.container);
            jsPlumb.Defaults.Anchors = [[0.5, 0.5, 0, 0], [0.5, 0.5, 0, 0]];
            quizconnect.choices.each(function(){
                var handle = $(this).find('.handle');
                jsPlumb.makeSource(handle[0], {
                    maxConnections: 1,
                    parameters: {
                        'correct_id': $(this).data('index')
                    },
                    uniqueEndpoint: true,
                    reattach: true,
                    connector: "Straight",
                    connectorStyle: {lineWidth: 4, strokeStyle: '#fa8f00'},
                    endpoint: ["Blank", {}],
                    filter: function(event, element) {
                        if (jsPlumb.getConnections({target: element.id}).length !== 0 ||
                            jsPlumb.getConnections({source: element.id}).length !== 0)
                        {
                            jsPlumb.detachAllConnections(element);
                        }
                        return true;
                    }
                });
                jsPlumb.makeTarget(handle[0], {
                    maxConnections: 1,
                    parameters: {
                        'correct_id': $(this).data('index')
                    },
                    uniqueEndpoint: true,
                    reattach: true,
                    connector: "Straight",
                    connectorStyle: {lineWidth: 4, strokeStyle: '#fa8f00'},
                    endpoint: ["Blank", {}],
                    beforeDrop: function(info) {
                        var source = $('#' + info.sourceId);
                        var target = $('#' + info.targetId);
                        var haveconnection = jsPlumb.getConnections({target: info.sourceId}).length > 0 ||
                                             jsPlumb.getConnections({source: info.sourceId}).length > 0 ||
                                             jsPlumb.getConnections({source: info.targetId}).length > 0 ||
                                             jsPlumb.getConnections({source: info.targetId}).length > 0;
                        return !haveconnection && 
                            ((source.parents(".items-left-col").length > 0 && target.parents(".items-right-col").length > 0) ||
                             (target.parents(".items-left-col").length > 0 && source.parents(".items-right-col").length > 0));
                    }
                });
            });
            jsPlumb.bind('click', function(connection, e) {
                jsPlumb.detach(connection);
            });
            jsPlumb.bind('endpointclick', function(endpoint, e) {
                jsPlumb.detachAllConnections(endpoint);
            });
        });
    },

    checkAnswer: function() {
        var connections = jsPlumb.getConnections('*');
        var correct = true;

        if (connections.length != quizconnect.choices.length / 2) {
            correct = false;
        }
        else {
            $.each(connections, function(index, connection) {
                params1 = connection.endpoints[0].getParameters();
                params2 = connection.endpoints[1].getParameters();
                if (params1.correct_id != params2.correct_id) {
                    correct = false;
                    return false;
                }
            });
        }

        if (correct) {
            $.magnificPopup.open({
                items: {
                    src: ".correct-answer-popup",
                    type: "inline"
                }
            });
        }
        else {
            $.magnificPopup.open({
                items: {
                    src: ".wrong-answer-popup",
                    type: "inline"
                }
            });
        }
    }

};


