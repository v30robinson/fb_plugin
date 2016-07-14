(function($) {
    
    $(document).ready(function() {
        $('.group-container:has(.group-posts)').each(function () {
            var userGroup = new GroupsPost($(this));
            userGroup.setLoadMoreEvent();
        });
        var publicGroup = new GroupsPublic($('.public-groups-list'));
        publicGroup.setLoadMoreEvent();
        publicGroup.setSearchFormEvent();
        publicGroup.setResetFormEvent();
        publicGroup.setInputUrlEvent();
    });
    
})(jQuery);