var DcPoll = {

    init: function() {

        // Register voting buttons
        $('button[data-action^="vote:"]').click(function(e) {

            if($(this).data('disabled') === 'true')
                return;

            var $buttons = $('body > section .container .btn'),
                half     = $(this).data('action').substring(5),
                params   = { vote: half };

            $('.btn').data('disabled', 'true');
            $('.btn.green').html('<img src="img/loading-green.gif" alt="Loading..." />');
            $('.btn.red').html('<img src="img/loading-red.gif" alt="Loading..." />');

            $.post('php/vote.php', params, DcPoll.voteCallback.bind(params))
                .error(DcPoll.voteCallback.bind({
                    error:     true,
                    exception: 'Could not send vote! Please refresh the page.'
                }));

        });

    },

    parse: function(json) {
        try {
            var parsed = JSON.parse(json);
            return parsed;
        } catch (e) {
            return false;
        }
    },

    voteCallback: function(e) {

        var $containers = $('body > section .container'),
            response    = DcPoll.parse(e) || { error: true, exception: 'Bad response, please refresh the page.' },
            half        = this.hasOwnProperty('vote') ? this.vote : 'left',
            otherHalf   = this.hasOwnProperty('vote') ? (this.vote == 'left' ? 'right' : 'left')
                                                      : null;

        $containers.transit({ scale: 0                }, 500, 'easeInBack')
                   .transit({ x: '-100px', opacity: 0 }, 0)
                   .transit({ scale: 1                }, 0, DcPoll.changeHTML.bind({ 

                        $containers: $containers,
                        response:    response,
                        half:        half,
                        otherHalf:   otherHalf,
                        self:        this

                    }));           

    },

    changeHTML: function() {

        var $containers = this.$containers,
            response    = this.response,
            half        = this.half,
            otherHalf   = this.otherHalf,
            self        = this.self;

        if ((self.hasOwnProperty('error') && self.hasOwnProperty('exception') && self.error) ||
            (response.hasOwnProperty('error') && response.hasOwnProperty('exception') && response.error)) {

            $containers.html('<h2><b class="error">Error!</b> ' + (self.hasOwnProperty('exception') ? self.exception : response.exception) + '</h2>');

        } else {

            $('.' +      half + ' .container').html('<h3 class="message">' + response.message +                     '</h3>' +
                                                    '<h2 class="percent">' + response[half + '_percent'] +         '%</h2>' +
                                                    '<h3>' +                 response[half + '_count'] +      ' votes</h3>');

            $('.' + otherHalf + ' .container').html('<h2 class="percent">' + response[otherHalf + '_percent'] +    '%</h2>' +
                                                    '<h3>' +                 response[otherHalf + '_count'] + ' votes</h3>');

            $('.message').transit({ y: -55, opacity: 0, delay: 1500 }, 500, 'easeInBack');
            
        }

        $containers.transit({ x: '0px', opacity: 1, delay: 200 }, 500);

    }

};

DcPoll.init();