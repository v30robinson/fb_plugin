(function($) {
    /**
     * If input with focus or unfocused - need load info about group
     * @param groupUrlSelector
     */
    function getFacebookGroupInfo(groupUrlSelector) {
        groupUrlSelector.on('focusout', function () {
            var facebookUrl = /https\:\/\/www.facebook.com\/groups\/([0-9\/]+)\/.*/,
                value = $(this).val();

            if (value.length && facebookUrl.test(value)) {
                getGroupInfo(value.match(facebookUrl)[1]);
            }
        });
    }

    /**
     * Make request to endpoint and set info into the fields about group
     * @param $groupId
     */
    function getGroupInfo($groupId) {
        $.ajax({
            url: "/wp-admin/admin-ajax.php?action=getGroupInfoBy&id=" + $groupId,
            done: function (data) {
                setInfoToField(null, data);
            }
        });
    }

    /**
     * 
     * @param form
     * @param data
     */
    function setInfoToField(form, data) {
        form.find('input[name="fb-group-name"]').val(data.name);
        form.find('input[name="fb-group-description"]').val(data.description);
        form.find('input[name="fb-group-members"]').val(data.members);
    }

    /**
     * run after page was loaded
     */
    $(document).ready(function() {
        getFacebookGroupInfo($('.group-new-group form input[name="fb-group-url"]'));
    });

})(jQuery);
