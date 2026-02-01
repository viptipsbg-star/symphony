var isTablet = (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) );

var videoplayer = {

    playercontainer: null,
    volumeBar: null,
    volumeBtn: null,
    playpausebtn: null,
    stopbtn: null,
    timercontainer: null,
    fullscreenbtn: null,
    progressbar: null,
    videoSources: [],
    videoParams: null,
    monitor: null,
    currentVideo: null,
    currentDuration: 0,
    monitorControl: null,

    volume: 0.5,
    muted: false,
    isPlaying: false,
    canRewindForward: false,

    init: function() {
        videoplayer.initVideo();
        videoplayer.initControls();
        videoplayer.initVolumeControls();
        videoplayer.initProgressBar();
    },

    initVideo: function() {
        /* Check if html5 video is supported */
        if (!!document.createElement('video').canPlayType) {
            /* TODO find best format for current browser/device */
            
            var videotag = $('<video poster="data:image/svg,AAAA"></video>');
            videoplayer.videoSources.each(function(){
                if ($(this).data('filetype') == "mp4_mobile") {
                    if (isTablet) {
                        videotag.prepend('<source src="'+$(this).attr('src')+'" />')
                    }
                }
                else {
                    videotag.append('<source src="'+$(this).attr('src')+'" />')
                }
            });
            videotag.on('loadstart', function (e) {
                if ($(this)[0].currentSrc !== 'about:blank') {
                    $(this).addClass('loading');
                }
            });
            videotag.on('canplay', function (e) {
                $(this).removeClass('loading');
                $(this).attr('poster', '');
                if (!videoplayer.isPlaying) {
                    videoplayer.monitorControl.show();
                }
            });
            videotag[0].volume = 0.5;
            videoplayer.monitor.append(videotag);
            videoplayer.currentVideo = videotag;
            videoplayer.currentDuration = videoplayer.videoParams.data('duration');
            videoplayer.timer.updateTimerUI(0);
        }
        else {
            /* TODO: show html5 not supported error message */
        }
    },

    initControls: function() {
        this.playpausebtn.on('click', function(e){
            e.preventDefault();
            if (videoplayer.isPlaying) {
                videoplayer.pausevideo();
            }
            else {
                videoplayer.playvideo();
            }
            videoplayer.playpausebtn.toggleClass('playing', videoplayer.isPlaying);
        });

        this.stopbtn.on('click', function(e){
            e.preventDefault();
            videoplayer.stopvideo();
            videoplayer.playpausebtn.toggleClass('playing', videoplayer.isPlaying);
        });

        this.fullscreenbtn.on('click', function(e){
            e.preventDefault();
            if (screenfull.enabled) {
                screenfull.toggle(videoplayer.playercontainer[0]);
            }
        });

        if (screenfull.enabled) {
            document.addEventListener(screenfull.raw.fullscreenchange, function () {
                videoplayer.playercontainer.toggleClass('fullscreen', screenfull.isFullscreen);
            });
            document.addEventListener(screenfull.raw.fullscreenerror, function (event) {
                console.error('Failed to enable fullscreen', event);
                /* TODO fix somehow */
            });
        }
    },

    initVolumeControls: function(){
        var volumeControl = function(e) {
            var x =  (e.pageX - videoplayer.volumeBar.offset().left) / videoplayer.volumeBar.width();
            var handle = videoplayer.volumeBar.find('.handle');
            x = (x < 0) ? 0 : ((x > 1) ? 1 : x);
            handle.css('width', (x * 100) + "%");
            videoplayer.setVolume(x);
            videoplayer.muted = false;

        };

        this.volumeBar.mousedown(function(e){
            volumeControl(e);

            $(window).bind("mousemove", function(e) {
                volumeControl(e);
            });
            $(window).bind("mouseup", function(){
                $(window).unbind("mousemove");
                $(window).unbind("mouseupmousemove");
            });
        });

        this.volumeBtn.on('click', function(e){
            e.preventDefault();
            if ($(this).hasClass('mute')) {
                videoplayer.muted = false;
            }
            else {
                videoplayer.muted = true;
            }
            videoplayer.volumeChanged();
        });
    },


    initProgressBar: function() {
        videoplayer.progressbar.on('click', function(e){
            e.preventDefault();
            var clickXPosition = e.offsetX;
            var barwidth = videoplayer.progressbar.width();
            var time = (clickXPosition * videoplayer.currentDuration) / barwidth;
            videoplayer.skipTo(time);
        });
    },

    setVolume: function(volume) {
        videoplayer.volume = volume;
        videoplayer.volumeChanged();
    },

    volumeChanged: function() {
        this.currentVideo[0].volume = videoplayer.volume;
        this.currentVideo[0].muted = videoplayer.muted;
        if (videoplayer.muted) {
            videoplayer.volumeBtn.addClass('mute').removeClass('low').removeClass('high');
        }
        else if (videoplayer.volume > 0 && videoplayer.volume < 0.5) {
            videoplayer.volumeBtn.removeClass('mute').addClass('low').removeClass('high');
        }
        else if (videoplayer.volume == 0) {
            videoplayer.volumeBtn.addClass('mute').removeClass('low').removeClass('high');
        }
        else {
            videoplayer.volumeBtn.removeClass('mute').removeClass('low').addClass('high');
        }
    },

    skipTo: function(time) {
        var canRewind = videoplayer.canRewindForward ? true : videoplayer.timer.lastListenedTime > time;
        if (canRewind) {
            videoplayer.currentVideo[0].currentTime = time;
            videoplayer.timer.updateUI(time);
        }
    },


    playvideo: function() {
        this.monitorControl.hide();
        this.currentVideo[0].play();
        this.isPlaying = true;
        this.timer.start();
    },

    pausevideo: function() {
        this.currentVideo[0].pause();
        this.monitorControl.show();
        this.isPlaying = false;
        this.timer.stop();
    },

    stopvideo: function() {
        this.currentVideo[0].pause();
        this.monitorControl.show();
        this.currentVideo[0].currentTime = 0;
        this.isPlaying = false;
        this.timer.reset();
    },

    unloadVideo: function() {
        this.currentVideo[0].pause();
        this.currentVideo[0].src = 'about:blank';
        this.currentVideo[0].load();
        videoplayer.monitorControl.hide();
    },

    checkEndedVideo: function() {
        var ended = videoplayer.currentVideo[0].ended;

        if (ended) {
            videoplayer.timer.stop();
            videoplayer.onVideoEnded();
        }
    },

    onVideoEnded: function() {
    },


    timer: {
        timerinterval: null,
        lastListenedTime: 0,
        start: function() {
            this.stop();
            this.timerinterval = window.setInterval(this.intervalAction, 500);
        },

        stop: function() {
            if (this.timerinterval) {
                window.clearInterval(this.timerinterval);
            }
        },

        reset: function() {
            this.stop();
            videoplayer.timercontainer.children().html("00");
            videoplayer.progressbar.find('.bar').css('width', "0%");
            videoplayer.timer.updateTimerUI(0);
        },

        intervalAction: function() {
            var currentTime = videoplayer.currentVideo[0].currentTime;
            if (currentTime > videoplayer.timer.lastListenedTime) {
                videoplayer.timer.lastListenedTime = currentTime;
            }
            videoplayer.timer.updateUI(currentTime);
            videoplayer.checkEndedVideo();
        },

        updateUI: function(time) {
            videoplayer.timer.updateTimerUI(time);
            videoplayer.timer.updateProgressUI(time);
        },

        updateTimerUI: function(time) {
            var totaltime = videoplayer.currentDuration;
            time = parseInt(time, 10);
            time = totaltime - time;
            if (time < 0) {
                time = 0;
            }
            var hours = Math.floor(time / 3600);
            var minutes = Math.floor((time - hours * 3600) / 60);
            var seconds = time - hours * 3600 - minutes * 60;
            videoplayer.timercontainer.find('.hour').html(("0" + hours).substr(-2));
            videoplayer.timercontainer.find('.minute').html(("0" + minutes).substr(-2));
            videoplayer.timercontainer.find('.second').html(("0" + seconds).substr(-2));
        },

        updateProgressUI: function(time) {
            var progress = (time / videoplayer.currentDuration) * 100;
            videoplayer.progressbar.find('.bar').css('width', progress + "%");
        }
    }
};
