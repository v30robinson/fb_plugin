(function($) {

    let config = {
        loadMoreGroupEndpoint: '?action=get_group_list_from',
        groupInfoEndpoint: '?action=get_group_info_by',
        facebookGroupFormat: /https\:\/\/www.facebook.com\/groups\/(.+?)\/.*/
    };

    /**
     * Added public ajax url for endpoint
     * @param url
     */
    function addAjaxLinkToConfig(url) {
        config.loadMoreGroupEndpoint = url + config.loadMoreGroupEndpoint;
        config.groupInfoEndpoint = url + config.groupInfoEndpoint;
    }

    /**
     * If input with focus or unfocused - need load info about group
     * @param {Object} groupUrlSelector
     */
    function getFacebookGroupInfo(groupUrlSelector) {
        groupUrlSelector.on('focusout', function () {
            var value = $(this).val();

            if (value.length && config.facebookGroupFormat.test(value)) {
                getGroupInfo(value.match(config.facebookGroupFormat)[1]);
            }
        });
    }
    
    function loadMorePublicGroup(selector) {
        selector.find('.load-more').on('click', function () {
            getPublicGroupFrom(
                selector,
                countOfDisplayedGroups(selector)
            );
        });
    }

    /**
     * Get list of groups from number
     * @param {Object} selector
     * @param {int} number
     */
    function getPublicGroupFrom(selector, number) {
        $.ajax({
            url: config.loadMoreGroupEndpoint + '&number=' + number,
            success: function (groups) {
                groups.forEach(function (group) {
                    selector.find('.group-loader').before(
                        createGroupEntity(group['name'], group['url'], group['description'], group['members'])
                    );
                });

                if (countOfAllGroups(selector) <= countOfDisplayedGroups(selector)) {
                    selector.find('.load-more').hide();
                }
            }
        });
    }

    /**
     * Create HTML entity of facebook group
     * @param {string} name
     * @param {string} url
     * @param {string} description
     * @param {int} members
     * @returns {string}
     */
    function createGroupEntity(name, url, description, members) {
        return '\
            <div class="group-container">\
                <div class="group-title">\
                    <b>Group name</b>: ' + name + '\
                </div>\
                <div class="group-url">\
                    <b>Group url</b>: <a target="_blank" href="' + url + '">Open ' + name + ' group</a>\
                </div>\
                <div class="group-description" ' + (description ? '' : 'disabled') + '>\
                    <b>Group description</b>: ' + description + '\
                </div>\
                <div class="group-members" ' + (members ? '' : 'disabled') + '>\
                    <b>Group members</b>: ' + members + '\
                </div>\
            </div>\
        ';
    }

    /**
     * Get count of displayed groups
     * @param {Object} selector
     * @returns {*}
     */
    function countOfDisplayedGroups(selector) {
        return selector.find('.group-container').length;
    }

    /**
     * Get count of all groups
     * @returns {int}
     */
    function countOfAllGroups(selector) {
        return selector.find('.group-loader').data('count');
    }

    /**
     * Make request to endpoint and set info into the fields about group
     * @param {int} $groupId
     */
    function getGroupInfo($groupId) {
        $.ajax({
            url: config.groupInfoEndpoint + "&id=" + $groupId,
            success: function (data) {
                setInfoToField($('form[name="fb-group"]'), data);
            }
        });
    }

    /**
     * Set group data to the form, if form don't have error
     * @param {Object} form - selector of form
     * @param {Object} data - ajax data about FB groups
     * @param {string} data.name
     * @param {string} data.description
     */
    function setInfoToField(form, data) {
        if (form.length) {            
            form.find('input[name="fb-group-name"]').val(data.name || '');
            form.find('input[name="fb-group-description"]').val(data.description || '');
        }
    }

    /**
     * run after page was loaded
     */
    $(document).ready(function() {
        addAjaxLinkToConfig(fbl.ajaxurl);
        getFacebookGroupInfo($('.group-new-group form input[name="fb-group-url"].hasToken'));
        loadMorePublicGroup($('.public-groups-list'));
    });
})(jQuery);
