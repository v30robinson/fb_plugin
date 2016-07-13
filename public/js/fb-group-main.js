(function($) {

    let config = {
        loadMoreGroupEndpoint: '?action=get_group_list_from',
        groupInfoEndpoint: '?action=get_group_info_by',
        facebookGroupFormat: /https\:\/\/www.facebook.com\/groups\/(.+?)\/.*/,
        searchEndpoint: '?action=search_local_group_by'
    };

    /**
     * Added public ajax url for endpoint
     * @param url
     */
    function addAjaxLinkToConfig(url) {
        config.loadMoreGroupEndpoint = url + config.loadMoreGroupEndpoint;
        config.groupInfoEndpoint = url + config.groupInfoEndpoint;
        config.searchEndpoint = url + config.searchEndpoint;
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

    /**
     * Load more groups
     * @param selector
     */
    function loadMorePublicGroup(selector) {
        $(document).on('click', '.load-more',function () {
            if ($(this).hasClass('search')) {
                let searchForm = $('.public-groups-search form'),
                    searchValue = searchForm.find('input[type="text"]').val(),
                    searchPage = searchForm.find('input[type="hidden"]').val();

                loadLocalGroupByTitle(searchPage, searchValue);

            } else {
                getPublicGroupFrom(selector, countOfDisplayedGroups(selector));
            }
        });
    }

    /**
     * Get list of groups from number
     * @param {Object} selector
     * @param {int} page
     */
    function getPublicGroupFrom(selector, page) {
        $.ajax({
            url: config.loadMoreGroupEndpoint,
            data: { page: page },
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
     * Clear groups list
     */
    function clearGroupsList() {
        $('.public-groups-list').find('.group-container').remove();
    }

    /**
     * insert first 5 item to page
     * @param {Array} groups
     */
    function insertToList(groups) {
        groups.forEach(function (group, key) {
            if (key < 5) {
                $('.public-groups-list').find('.group-loader').before(
                    createGroupEntity(group['name'], group['url'], group['description'], group['members'])
                );
            }
        });
    }

    /**
     * Show/hide load more button
     * @param {boolean} status
     * @param {boolean} search
     */
    function toggleLoadMoreButton(status, search = false) {
        $('.group-loader button')
            .toggle(status)
            .toggleClass('search', search);
    }

    /**
     * Edit page counter in the search func
     * @param {int} page
     */
    function editPageCounter(page) {
        $('.public-groups-search form').find('input[type="hidden"]').val(page);
    }

    /**
     * AJAX search by title of local group
     * @param {int} page
     * @param {string} text
     */
    function loadLocalGroupByTitle(page, text = '') {
        $.ajax({
            type: 'get',
            url: config.searchEndpoint,
            data: {
                text: text,
                page: page
            },
            success: (groups) => {
                insertToList(groups);
                toggleLoadMoreButton(groups.length > 5, text.length > 0);
                editPageCounter(parseInt(page) + 1);
            }
        });
    }

    /**
     * Do search by local group title
     * @param {Object} form
     */
    function searchPublicGroup(form) {
        form.on('submit', function () {
            let searchValue = $(this).find('input[type="text"]').val();
            clearGroupsList();
            form.addClass('search');
            loadLocalGroupByTitle(0, searchValue);
            return false;
        });
    }

    /**
     * Reset search func
     * @param {Object} form
     */
    function resetSearchForm(form) {
        form.find('button[type="reset"]').on('click', function () {
            form.removeClass('search');
            clearGroupsList();
            loadLocalGroupByTitle(0, '');
        });
    }

    /**
     * run after page was loaded
     */
    $(document).ready(function() {
        addAjaxLinkToConfig(fbl.ajaxurl);
        getFacebookGroupInfo($('.group-new-group form input[name="fb-group-url"].hasToken'));
        loadMorePublicGroup($('.public-groups-list'));
        searchPublicGroup($('.public-groups-search form'));
        resetSearchForm($('.public-groups-search form'));

        $('.group-container:has(.group-posts)').each(function () {
            var post = new groupsPost(jQuery, $(this), {
                button: '.load-more-posts'
            });
            post.setLoadMoreEvent();
        });

    });
})(jQuery);
