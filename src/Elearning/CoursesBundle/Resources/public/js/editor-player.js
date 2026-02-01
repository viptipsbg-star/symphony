//var console = {log: function () {}};
var editorplayer = {
    mainVideoData: [],
    slidesData: [],
    mainMonitor: null,
    currentSlideMonitor: null,
    nextSlideMonitor: null,
    playPauseBtn: null,
    stopBtn: null,
    nextMainBtn: null,
    nextSlideBtn: null,
    startPreviewBtn: null,
    clearSynchronizationBtn: null,
    timeContainer: null,
    progressbars: null,


    mainMonitorIndex: 0,
    currentSlideIndex: 0,

    videoData: {
        mainvideo: {},
        slidesvideo: {},
        videoduration: 85000
    },

    time: 0,
    timerInterval: null,
    playing: false,

    init: function () {
        console.log("INIT");
        this.changeMain(true);
        this.changeSlide(true);
        if (this.timerInterval) {
            window.clearCorrectingInterval(this.timerInterval);
        }
        this.time = 0;
        this.prepareProgressbars();
        this.progressbars.find('.progressbar .progressbar-content').width(Math.max(this.progressbars.width(), this.progressbars.find(".ruler").width()));

        this.playPauseBtn.unbind('click').on('click', function (e) {
            e.preventDefault();
            if (editorplayer.playing) {
                editorplayer.pause();
            }
            else {
                editorplayer.play();
            }
        });
        this.nextMainBtn.unbind('click').on('click', function (e) {
            e.preventDefault();
            editorplayer.nextMain();
            editorplayer.updateProgressBars();
        });
        this.nextSlideBtn.unbind('click').on('click', function (e) {
            e.preventDefault();
            editorplayer.nextSlide();
            editorplayer.updateProgressBars();
        });

        this.stopBtn.unbind('click').on('click', function (e) {
            e.preventDefault();
            editorplayer.stop();
            editorplayer.updateProgressBars();
        });

        this.startPreviewBtn.unbind('click').on('click', function (e) {
            e.preventDefault();
            editorplayer.startPreview();
        });

        this.clearSynchronizationBtn.unbind('click').on('click', function (e) {
            e.preventDefault();
            editorplayer.stop();
            editorplayer.clearData();
        });

    },
    play: function () {
        console.log("PLAY", this.playing);
        this.playPauseBtn.find('.play-icon').hide();
        this.playPauseBtn.find('.pause-icon').show();
        this.playing = true;
        this.startTimer();
        this.eachContentVideo(function (video) {
            video.play();
        });
    },
    pause: function () {
        console.log("PAUSE", this.playing);
        this.playPauseBtn.find('.play-icon').show();
        this.playPauseBtn.find('.pause-icon').hide();
        this.playing = false;
        this.pauseTimer();
        this.eachContentVideo(function (video) {
            video.pause();
        });
    },
    stop: function () {
        console.log("STOP");
        this.playPauseBtn.find('.play-icon').show();
        this.playPauseBtn.find('.pause-icon').hide();
        this.playing = false;
        this.videoData.videoduration = this.time.toFixed(1) * 1000;
        /* In Millis */
        this.stopTimer();
        this.reset();

        this.prepareProgressbars();
    },

    clearData: function () {
        editorplayer.videoData = {
            mainvideo: {},
            slidesvideo: {},
            videoduration: 85000
        };
        editorplayer.clearProgressBars();
    },

    checkCompletedVideo: function () {
        return (this.mainMonitor.children().length == 0 &&
        this.currentSlideMonitor.children().length == 0);
    },
    eachContentVideo: function (callback) {
        var videos = $('.editor-player .monitor video, .editor-player .monitor audio');
        if (videos.length > 0) {
            videos.each(function () {
                callback.call(undefined, this);
            });
        }
    },

    nextMain: function () {
        if (!this.playing) return false;
        console.log("NEXT MAIN");

        var mainFileId = this.getMainFileId();
        this.videoData.mainvideo[mainFileId] = this.time.toFixed(1) * 1000;
        /* In milliseconds */

        this.mainMonitorIndex++;
        this.changeMain();
        if (this.checkCompletedVideo()) {
            this.stop();
        }
        this.recalculateProgressBars();
    },
    nextSlide: function () {
        if (!this.playing) return false;
        console.log("NEXT SLIDE");

        var slideFileId = this.getSlideFileId();
        this.videoData.slidesvideo[slideFileId] = this.time.toFixed(1) * 1000;
        /* in milliseconds */

        this.currentSlideIndex++;
        this.changeSlide();
        if (this.checkCompletedVideo()) {
            this.stop();
        }
        this.recalculateProgressBars();
    },

    resetMain: function () {
        this.mainMonitorIndex = 0;
        this.changeMain(true);
    },
    resetSlides: function () {
        this.currentSlideIndex = 0;
        this.changeSlide(true);
    },

    reset: function () {
        this.playing = false;
        this.resetMain();
        this.resetSlides();
        this.stopTimer();
        this.generateRuler();
        this.lastTimelinesUpdateTime = 0;
        if (this.playPauseBtn) {
            this.playPauseBtn.find('.play-icon').show();
            this.playPauseBtn.find('.pause-icon').hide();
        }
    },
    clear: function () {
        console.log("CLEARING");
        if (this.mainVideoData.length > 0) {
            this.mainVideoData.remove();
            this.mainVideoData = [];
            this.mainMonitor.html("");
        }
        if (this.slidesData.length > 0) {
            this.slidesData.remove();
            this.slidesData = [];
            this.currentSlideMonitor.html("");
            this.nextSlideMonitor.html("");
        }
    },

    formatMillisDuration: function (millis) {
        var millis_num = parseInt(millis, 10); // don't forget the second param
        var sec_num = Math.floor(millis_num / 1000);
        var hours = Math.floor(sec_num / 3600);
        var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
        var seconds = sec_num - (hours * 3600) - (minutes * 60);
        var milliseconds = parseInt(((millis_num / 1000) - sec_num) * 10, 10);

        if (hours < 10) {
            hours = "0" + hours;
        }
        if (minutes < 10) {
            minutes = "0" + minutes;
        }
        if (seconds < 10) {
            seconds = "0" + seconds;
        }
        var time = hours + ':' + minutes + ':' + seconds + '.' + milliseconds;
        return time;
    },

    /* Progress bar */

    progressrulerscale: 10, /* 1 second equals to 10 px in ruler */

    clearProgressBars: function () {
        if (this.progressbars) {
            this.progressbars.find('.marker').remove();
            this.progressbars.find('.progressbar').css('width', '100%');
        }
    },

    recalculateProgressBars: function () {
        var mainprogressbar = this.progressbars.find('.main-progressbar');
        mainprogressbar.find('.marker').each(function (el) {
            var file_id = $(this).data('file_id');
            var time = editorplayer.videoData.mainvideo[file_id];
            var leftpos = (time / 1000) * editorplayer.progressrulerscale;
            $(this).css('left', leftpos);
        });

        var slidesprogressbar = this.progressbars.find('.slides-progressbar');
        slidesprogressbar.find('.marker').each(function (el) {
            var file_id = $(this).data('file_id');
            var time = editorplayer.videoData.slidesvideo[file_id];
            var leftpos = (time / 1000) * editorplayer.progressrulerscale;
            $(this).css('left', leftpos);
        });
    },
    updateProgressBars: function (widthonly) {
        var markertmpl = '<div class="marker"><div></div></div>';
        var duration = this.videoData.videoduration;
        if (!duration) {
            duration = 85000;
        }

        var mainprogressbar = this.progressbars.find('.main-progressbar .progressbar-content');
        var progressbarwidth = (duration / 1000) * this.progressrulerscale;
        if (progressbarwidth > mainprogressbar.width()) {
            mainprogressbar.width(progressbarwidth);
        }
        else {
            duration = editorplayer.time;
        }
        if (!widthonly) {
            mainprogressbar.html("");
            for (var file_id in this.videoData.mainvideo) {
                var time = this.videoData.mainvideo[file_id];
                var leftpos = (time / 1000 ) * editorplayer.progressrulerscale;
                mainprogressbar.append($(markertmpl).css('left', leftpos).data('file_id', file_id).data('type', 'mainvideo'));
            }
        }
        var slidesprogressbar = this.progressbars.find('.slides-progressbar .progressbar-content');
        if (progressbarwidth > slidesprogressbar.width()) {
            slidesprogressbar.width(progressbarwidth);
        }
        else {
            duration = editorplayer.time / 1000;
        }
        if (!widthonly) {
            slidesprogressbar.html("");
            for (var file_id in this.videoData.slidesvideo) {
                var time = this.videoData.slidesvideo[file_id];
                var leftpos = (time / 1000 ) * editorplayer.progressrulerscale;
                slidesprogressbar.append($(markertmpl).css('left', leftpos).data('file_id', file_id).data('type', 'slidesvideo'));
            }
        }

        if (!widthonly) {
            if (editorplayer.progressbars.find('.marker').data('ui-draggable')) {
                editorplayer.progressbars.find('.marker').draggable('destroy');
            }
            editorplayer.progressbars.find('.marker').draggable(editorplayer.progressbardraggableparams).unbind('collision').bind('collision', function (e, ui) {
                console.log("COLLISION", ui);
            });
        }
    },


    progressbardraggableparams: {
        //containment: "parent",
        axis: "x",
        start: function (e, ui) {
            //ui.helper.append('<div class="time-bubble">00:00:00.0</div>');
        },
        stop: function (e, ui) {
            var leftpos = ui.position.left;


            //ui.helper.find('.time-bubble').remove();
            var markertime = parseInt(leftpos / editorplayer.progressrulerscale, 10) * 1000;
            var file_id = ui.helper.data('file_id');
            var otherprogressbar = null;

            if (ui.helper.data('type') == "mainvideo") {
                editorplayer.videoData.mainvideo[file_id] = markertime;
                otherprogressbar = editorplayer.progressbars.find('.slides-progressbar');
            }
            else if (ui.helper.data('type') == "slidesvideo") {
                editorplayer.videoData.slidesvideo[file_id] = markertime;
                otherprogressbar = editorplayer.progressbars.find('.main-progressbar');
            }
            var mostleftotherbarmarkerleftpos = 0;
            otherprogressbar.find('.marker').each(function(){
                if ($(this).position().left > mostleftotherbarmarkerleftpos) {
                    mostleftotherbarmarkerleftpos = $(this).position().left;
                }
            });
            if (mostleftotherbarmarkerleftpos < leftpos) {
                editorplayer.videoData.videoduration = markertime;
                editorplayer.recalculateProgressBars();
            }
            /* check if other monitor last item will end later */
        },
        drag: function (e, ui) {
            var leftpos = ui.position.left;
            if (leftpos < 0) {
                ui.position.left = 0;
                leftpos = 0;
            }
            var nextmarker = ui.helper.next('.marker');
            if (nextmarker.length > 0 && nextmarker.position().left <= leftpos + 5) {
                console.log("OVERLAP NEXT", ui.helper, nextmarker.position().left - 10);
                ui.position = {'top': 0, 'left': nextmarker.position().left - 10};
                return true;
            }
            var prevmarker = ui.helper.prev('.marker');
            if (prevmarker.length > 0 && prevmarker.position().left >= leftpos - 5) {
                ui.position = {'top': 0, 'left': prevmarker.position().left + 10};
                return true;
            }

            /*
             var bubble = ui.helper.find('.time-bubble');
             var parentwidth = ui.helper.parent().width();
             var duration = editorplayer.videoData.videoduration;
             var markertime = (leftpos * duration) / parentwidth;

             var time = editorplayer.formatMillisDuration(markertime);
             bubble.html(time);
             */

        }
    },


    progressBarsPrepared: false,

    prepareProgressbars: function () {
        if (this.videoData.videoduration === 0 && this.progressBarsPrepared) return false;
        this.progressBarsPrepared = true;

        editorplayer.updateProgressBars();


        this.progressbars.find('.progressbar-marker').draggable({
            axis: 'x',
            containment: 'parent',
            start: function (e, ui) {
                ui.helper.find('.time').show();
            },
            stop: function (e, ui) {
                ui.helper.find('.time').hide();
                //var leftpos = ui.helper.position().left;
                var leftpos = parseInt(ui.helper.css('left'), 10);
                leftpos = !!leftpos ? leftpos : 0;

                var lastmarkerpos = 0;
                $('.progressbar .progressbar-content .marker').each(function () {
                    if ($(this).position().left > lastmarkerpos) {
                        lastmarkerpos = $(this).position().left;
                    }
                });

                console.log(">>> ", leftpos, lastmarkerpos);
                if (leftpos > lastmarkerpos) {
                    leftpos = 0;
                    ui.helper.css('left', 0);
                }

                leftpos = leftpos < 0 ? 0 : leftpos;
                var time = leftpos / editorplayer.progressrulerscale;
                editorplayer.loadContent(time);
            },
            drag: function (e, ui) {
                var timer = ui.helper.find('.time');
                var leftpos = ui.helper.position().left;
                leftpos = leftpos < 0 ? 0 : leftpos;
                var time = leftpos / editorplayer.progressrulerscale;
                this.previewTime = time;
                editorplayer.updateProgressMarker(time, false);
            }
        });
        editorplayer.generateRuler();
    },

    updateProgressMarker: function (time, updateui) {
        var progressmarker = this.progressbars.find('.progressbar-marker');
        var timer = progressmarker.find('.time');
        var minutes = Math.floor(time / 60);
        var seconds = Math.floor(time - minutes * 60);
        timer.html(("0" + minutes).slice(-2) + ":" + ("0" + seconds).slice(-2));
        if (updateui) {
            progressmarker.css('left', time * editorplayer.progressrulerscale);
        }
    },

    generateRuler: function () {
        if (!this.progressbars) return;
        var ruler = this.progressbars.find('.ruler');
        var lasttime = ruler.find('ul li:last-child').html()
        //var singlewidth = ruler.find('ul li:first-child').width();
        var singlewidth = 50;
        var itemscount = ruler.find('ul li').length;
        var progressbarwidth = ruler.siblings('.progressbar').width();
        var totalitems = progressbarwidth / singlewidth;
        var neededitems = totalitems - itemscount;
        console.log("RULER:", totalitems, itemscount, progressbarwidth, singlewidth, neededitems);
        for (var i = 0; i < neededitems; i++) {
            var lasttimeparts = lasttime.split(":");
            var newseconds = parseInt(lasttimeparts[1], 10) + 5;
            var newminutes = parseInt(lasttimeparts[0]);
            if (newseconds > 60) {
                newseconds = 0;
                newminutes++;
            }
            var newtime = ("0" + newminutes).substr(-2) + ":" + ("0" + newseconds).substr(-2);
            ruler.find('ul').append('<li>' + newtime + '</li>');
            lasttime = newtime;
        }
    },


    lastTimelinesUpdateTime: 0,
    updateTimelines: function (time) {
        if (time - editorplayer.lastTimelinesUpdateTime > 5) {
            var markerleftpos = parseInt(this.progressbars.find('.progressbar-marker').css('left'), 10);
            var rulerwidth = editorplayer.progressbars.find('.ruler').width() - 20;
            /* 20px - safe zone to update ruler and progress bars */
            var progressbarwidth = editorplayer.progressbars.find('.progressbar-content').width() - 20;
            if (markerleftpos < rulerwidth && markerleftpos < progressbarwidth) {
                return;
            }
            editorplayer.progressbars.find(".progressbar-content").width(markerleftpos + 100);
            editorplayer.generateRuler();


            var totaltime = (rulerwidth / editorplayer.progressrulerscale) * 1000;
            if (totaltime > editorplayer.videoData.videoduration) {
                editorplayer.videoData.videoduration = this.time.toFixed(1) * 1000;
            }

            editorplayer.lastTimelinesUpdateTime = time;
        }
    },


    showPreloader: function () {
        console.log("SHOW PRELOADER");
    },
    hidePreloader: function () {
        console.log("HIDE PRELOADER");
    },

    isVideoLoaded: function (element) {
        return element.readyState > 3 && element.buffered && element.buffered.length > 0;
    },

    waitForVideoLoad: function (element, preview) {
        console.log("WAITFORELEMENTLOAD:", $(element), element.paused)
        if (this.isVideoLoaded(element)) return true;
        preview ? this.pausePreviewTimer() : this.pause();

        this.showPreloader();
        var loaded = false;
        element.play();
        element.pause();

        var count = 0;
        var checkFunction = function () {
            loaded = editorplayer.isVideoLoaded(element);
            console.log("WAIT FOR LOADED:", loaded, element.readyState, element.buffered, (element.buffered) ? element.buffered.length : 0);
            count++;
            if (count > 500) {
                console.log("CANT LOAD FILE:", element);
                return false;
            }
            if (!loaded) {
                window.setTimeout(checkFunction, 1000);
            }
            else {
                element.currentTime = 0;
                editorplayer.hidePreloader();
                preview ? editorplayer.startPreviewTimer() : editorplayer.play();
            }
        };
        window.setTimeout(checkFunction, 1000);
    },


    /* Editor player content functions */

    changeMain: function (dontplay, starttime) {
        if (!this.mainMonitor) return;
        if (this.mainMonitorIndex < this.mainVideoData.length) {
            var newelement = $(this.mainVideoData[this.mainMonitorIndex]).clone();
            console.log("CHANGING MAIN TO:", this.mainMonitorIndex, newelement);
            this.mainMonitor.html(newelement);
            if (newelement.is('video') || newelement.is('audio')) {
                if (starttime) {
                    newelement[0].currentTime = (starttime / 1000).toFixed(1);
                }
                if (!dontplay) {
                    newelement[0].play();
                    this.waitForVideoLoad(newelement[0]);
                }
            }
            if (newelement.is('audio')) {
                this.mainMonitor.append('<img src="' + newelement.attr('src') + '/thumbnail" class="thumb" />');
            }
        }
        else {
            this.mainMonitor.html("");
        }
    },

    changeSlide: function (dontplay, starttime) {
        console.log("CHANGE SLIDE");
        if (!this.nextSlideMonitor || !this.currentSlideMonitor) return;
        if (this.currentSlideIndex < this.slidesData.length) {
            var newCurrentElement = $(this.slidesData[this.currentSlideIndex]).clone();
            console.log("CHANGING CURRENT SLIDE TO:", this.currentSlideIndex);
            this.currentSlideMonitor.html(newCurrentElement);
            if (newCurrentElement.is('audio') || newCurrentElement.is('video')) {
                if (starttime) {
                    newCurrentElement[0].currentTime = (starttime / 1000).toFixed(1);
                }
                if (!dontplay) {
                    newCurrentElement[0].play();
                    this.waitForVideoLoad(newCurrentElement[0]);
                }
            }
            if (newCurrentElement.is('video') && !dontplay) {

            }
            if (newCurrentElement.is('audio')) {
                this.currentSlideMonitor.append('<img src="' + newCurrentElement.attr('src') + '/thumbnail" class="thumb" />');
            }

            if (this.currentSlideIndex + 1 < this.slidesData.length) {
                var newNextElement = $(this.slidesData[this.currentSlideIndex + 1]).clone();
                if (newNextElement.is('video') || newNextElement.is('audio')) {
                    console.log(newNextElement, newNextElement.attr('src'));
                    var videoSrc = newNextElement.attr('src');
                    newNextElement = '<img src="' + videoSrc + '/thumbnail" class="thumb"/>'
                }
                this.nextSlideMonitor.html(newNextElement);
            }
            else {
                this.nextSlideMonitor.html("");
            }
        }
        else {
            this.currentSlideMonitor.html("");
        }
    },

    loadContent: function (selectedtime) {
        var mainindex = 0;
        var prevmainendtime = 0;
        $.each(this.videoData.mainvideo, function (index, time) {
            if (selectedtime * 1000 < time) {
                editorplayer.mainMonitorIndex = mainindex;
                return false;
            }
            prevmainendtime = time;
            mainindex++;
        });

        var slidesindex = 0;
        var prevslidesendtime = 0;
        $.each(this.videoData.slidesvideo, function (index, time) {
            if (selectedtime * 1000 < time) {
                editorplayer.currentSlideIndex = slidesindex;
                return false;
            }
            prevslidesendtime = time;
            slidesindex++;
        });

        editorplayer.time = selectedtime;
        this.changeMain(true, selectedtime * 1000 - prevmainendtime);
        this.changeSlide(true, selectedtime * 1000 - prevslidesendtime);
    },

    getMainFileId: function () {
        return this.mainMonitor.children().data('id');
    },
    getSlideFileId: function () {
        return this.currentSlideMonitor.children().data('id');
    },


    editorCheckChangeMain: function () {
        var file_id = this.getMainFileId();
        var endtime = this.videoData.mainvideo[file_id];
        var currentTime = this.time.toFixed(1) * 1000;
        console.log("CHECK CHANGE MAIN:", file_id, currentTime, endtime, currentTime >= endtime);
        return (currentTime >= endtime);
    },

    editorCheckChangeSlide: function () {
        var file_id = this.getSlideFileId();
        var endtime = this.videoData.slidesvideo[file_id];
        var currentTime = this.time.toFixed(1) * 1000;
        console.log("CHECK CHANGE SLIDE:", file_id, currentTime, endtime, currentTime >= endtime);
        return (currentTime >= endtime);
    },

    checkCompletedEditorVideo: function () {
        return (this.mainMonitor.children().length == 0 &&
        this.currentSlideMonitor.children().length == 0);
    },


    updateTimerGUI: function () {
        if (!this.timeContainer) return;
        var mins = Math.floor(this.time / 60);
        var seconds = Math.floor(this.time - mins * 60);
        var millis = Math.floor((this.time - mins * 60 - seconds) * 10);
        this.timeContainer.find('.minutes').html(mins);
        this.timeContainer.find('.seconds').html(seconds);
        this.timeContainer.find('.millis').html(millis);
    },
    startTimer: function () {
        console.log("START TIMER", this.playing, this.timerInterval);
        if (this.timerInterval) {
            this.stopTimer();
        }
        this.timerInterval = window.setCorrectingInterval(function () {
            //console.log(editorplayer.time);
            editorplayer.updateTimerGUI();
            editorplayer.time += 0.1;

            if (editorplayer.editorCheckChangeMain()) {
                console.log("CHANGING MAIN");
                editorplayer.mainMonitorIndex += 1;
                editorplayer.changeMain();
            }
            if (editorplayer.editorCheckChangeSlide()) {
                console.log("CHANGING SLIDE");
                editorplayer.currentSlideIndex += 1;
                editorplayer.changeSlide();
            }
            if (editorplayer.checkCompletedEditorVideo()) {
                editorplayer.reset();
            }

            editorplayer.updateProgressMarker(editorplayer.time, true);

            editorplayer.updateTimelines(editorplayer.time);
        }, 100);
    },
    pauseTimer: function () {
        console.log("PAUSE TIMER", this.playing, this.timerInterval);
        window.clearCorrectingInterval(this.timerInterval);
        this.timerInterval = null;
    },
    stopTimer: function () {
        this.pauseTimer();
        this.time = 0;
        editorplayer.updateTimerGUI();
    },


    /* Video data getter/setter */

    getVideoData: function () {
        return this.videoData;
    },
    setVideoData: function (videoData) {
        this.videoData = videoData;
    },


    /* Preview */

    previewMainMonitor: null,
    previewSlideMonitor: null,
    previewMainIndex: 0,
    previewSlideIndex: 0,
    previewTimerInterval: null,
    previewTime: 0,
    previewPlayPauseBtn: null,
    previewPlaying: false,
    previewTimeContainer: null,

    startPreviewTimer: function (callback) {
        if (this.previewTimerInterval) {
            this.stopPreviewTimer();
        }
        this.previewPlaying = true;
        console.log(this.previewPlayPauseBtn);
        this.previewPlayPauseBtn.toggleClass('playing', this.previewPlaying);
        this.eachPreviewContentVideo(function (video) {
            console.log("PAUSING VIDEO:", video);
            video.play();
        });
        this.previewTimerInterval = window.setCorrectingInterval(function () {
            editorplayer.previewTime += 0.1;
            editorplayer.updatePreviewTimerGUI();
            if (callback) {
                callback();
            }

            if (editorplayer.previewCheckChangeMain()) {
                editorplayer.previewMainIndex += 1;
                editorplayer.changePreviewMain();
            }
            if (editorplayer.previewCheckChangeSlide()) {
                editorplayer.previewSlideIndex += 1;
                editorplayer.changePreviewSlide();
            }
            if (editorplayer.checkCompletedPreviewVideo()) {
                editorplayer.stopPreviewTimer();
                editorplayer.previewReset();
            }
        }, 100);
    },
    pausePreviewTimer: function () {
        window.clearCorrectingInterval(this.previewTimerInterval);
        this.previewTimerInterval = null;
        this.previewPlaying = false;
        this.previewPlayPauseBtn.toggleClass('playing', this.previewPlaying);
        editorplayer.updatePreviewTimerGUI();
        this.eachPreviewContentVideo(function (video) {
            console.log("PLAYING VIDEO:", video);
            video.pause();
        });
    },
    stopPreviewTimer: function () {
        this.pausePreviewTimer();
        this.previewTime = 0;
        editorplayer.updatePreviewTimerGUI();
    },
    eachPreviewContentVideo: function (callback) {
        var videos = $('#video-chapter-preview-popup video, #video-chapter-preview-popup audio');
        console.log("EACH PREVIEW", videos);
        if (videos.length > 0) {
            videos.each(function () {
                callback.call(undefined, this);
            });
        }
    },

    updatePreviewTimerGUI: function () {
        if (!this.previewTimeContainer) return;
        var mins = Math.floor(this.previewTime / 60);
        var seconds = Math.floor(this.previewTime - mins * 60);
        var millis = Math.floor((this.previewTime - mins * 60 - seconds) * 10);
        this.previewTimeContainer.find('.minutes').html(("0" + mins).substr(-2));
        this.previewTimeContainer.find('.seconds').html(("0" + seconds).substr(-2));
        this.previewTimeContainer.find('.millis').html(("0" + millis).substr(-2));
    },

    getPreviewMainFileId: function () {
        return this.previewMainMonitor.children().data('id');
    },
    getPreviewSlideFileId: function () {
        return this.previewSlideMonitor.children().data('id');
    },

    previewCheckChangeMain: function () {
        var file_id = this.getPreviewMainFileId();
        var endtime = this.videoData.mainvideo[file_id];
        var currentTime = this.previewTime.toFixed(1) * 1000;
        console.log("CHECK CHANGE MAIN:", file_id, currentTime, endtime, currentTime >= endtime);
        return (currentTime >= endtime);
    },
    previewCheckChangeSlide: function () {
        var file_id = this.getPreviewSlideFileId();
        var endtime = this.videoData.slidesvideo[file_id];
        var currentTime = this.previewTime.toFixed(1) * 1000;
        console.log("CHECK CHANGE SLIDE:", file_id, currentTime, endtime, currentTime >= endtime);
        return (currentTime >= endtime);
    },
    changePreviewMain: function () {
        if (!this.previewMainMonitor) return;
        if (this.previewMainIndex < this.mainVideoData.length) {
            var newElement = $(this.mainVideoData[this.previewMainIndex]).clone();
            this.previewMainMonitor.html(newElement);
            if (newElement.is('video') || newElement.is('audio')) {
                newElement[0].play();
                this.waitForVideoLoad(newElement[0], true);
            }
        }
        else {
            this.previewMainMonitor.html("");
        }
    },
    changePreviewSlide: function () {
        if (!this.previewSlideMonitor) return;
        if (this.previewSlideIndex < this.slidesData.length) {
            var newElement = $(this.slidesData[this.previewSlideIndex]).clone();
            this.previewSlideMonitor.html(newElement);
            if (newElement.is('video') || newElement.is('audio')) {
                newElement[0].play();
                this.waitForVideoLoad(newElement[0], true);
            }
        }
        else {
            this.previewSlideMonitor.html("");
        }
    },
    checkCompletedPreviewVideo: function () {
        return (this.previewMainMonitor.children().length == 0 &&
        this.previewSlideMonitor.children().length == 0);
    },

    previewReset: function () {
        this.previewMainIndex = 0;
        this.previewSlideIndex = 0;
        this.changePreviewMain();
        this.changePreviewSlide();
    },

    startPreview: function () {
        this.stop();
        $.magnificPopup.open({
            items: {
                src: "#video-chapter-preview-popup",
                type: "inline"
            },
            callbacks: {
                close: function () {
                    editorplayer.stopPreviewTimer();
                }
            }
        });

        this.previewPlayPauseBtn.unbind('click').on('click', function (e) {
            e.preventDefault();
            if (editorplayer.previewPlaying) {
                editorplayer.pausePreviewTimer();
            }
            else {
                editorplayer.startPreviewTimer();
            }
        });

        this.previewReset();
    }
};
