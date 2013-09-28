var DcPollAdmin = {
    
    init: function() {

        // Register forms
        $('form.polls').on('submit', function(event) {

            event.preventDefault();
            var form      = event.target,
                $form     = $(form),
                params    = $form.find('input[name]'),
                paramsMap = {},
                action    = $form.attr('action') !== null ? $form.attr('action') : 'index.php',
                $button   = $form.find('input[type="submit"]'),
                toJoin    = [];

            //
            //  Map all of the parameters to an object for POST
            //
            params.each(function(i, v) {
                if ($(v).attr('type') === 'checkbox') {
                    if (!v.checked) return;
                    if (!paramsMap.hasOwnProperty(v.name)){
                        paramsMap[v.name] = [];
                        toJoin.push(v.name);
                    }
                    paramsMap[v.name].push(v.value);
                    return;
                }
                if ($(v).attr('type') === 'radio' && !v.checked) return;
                paramsMap[v.name] = v.value;
            });

            for (var i = 0; i < toJoin.length; i++) {
                paramsMap[toJoin[i]] = paramsMap[toJoin[i]].join(',');   
            }
            console.log(paramsMap);

            //
            // Send POST request
            //
            $.post(action, paramsMap, function(e) {

                var json = e;

                // Parse the JSON
                if (typeof json == 'string')
                    try {
                        json = JSON.parse(e);
                    } catch(exception) {
                        DcPollAdmin.changeButton($button, 'Invalid Response! See console.', false);
                        console.log('[RESPONSE] See below.');
                        console.log(e);
                        return;
                    }
                
                // If the response was in the right format
                if (json.hasOwnProperty('success') && typeof json.success === 'boolean') {
                    // If there was no success
                    if(!json.success
                        && json.hasOwnProperty('error')
                        && json.hasOwnProperty('errorCode')) {

                        DcPollAdmin.changeButton($button, 'Error! See console.', false);
                        console.log('[ERROR] Code: ' + json.errorCode + ', Message: ' + json.error);

                    // If there was success
                    } else if (json.success) {

                        DcPollAdmin.changeButton($button, 'Success!', true, function() { setTimeout(function(){window.location.reload()}, 1000); });
                        console.log(json);

                    // If there was no success but no error
                    } else {

                        DcPollAdmin.changeButton($button, 'No success but no error :(', false);

                    }

                // Invalid response format
                } else {
                    
                    DcPollAdmin.changeButton($button, 'Invalid Response! See console.', false);
                    console.log('[RESPONSE] See below.');
                    console.log(json);

                }

            }).error(function() {

                DcPollAdmin.changeButton($button, 'Could not send request :(', false);

            });

        });

    },

    changeButton: function(btn, text, success, callback) {

        if (arguments.length == 3) callback = function(){}
        var color = success ? 'green' : 'red';

        btn.transit({ scale: 0 }, 500, 'easeInBack', function() {

            btn.addClass(color);
            btn.attr('value', text);

            btn.transit({ scale: 1 }, 500, 'easeOutBack', callback);

        });

    }

};

DcPollAdmin.init();