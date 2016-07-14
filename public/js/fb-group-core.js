class groupsCore {

    /**
     * Setup base config for plugin;
     * Will work only on the public part of plugin
     */
    constructor(currentConfig) {
        this.jquery = jQuery || null;
        this.ajaxPath = fbl.ajaxurl || null;
        this.config = eval('this.' + currentConfig + 'Config()');
    }

    /**
     * Setup config for user groups func
     * @returns {{postPerPage: number, classes: {loadMoreButton: string}, endpoints: {loadMore: (function())}}}
     */
    groupsPostConfig() {
        return {
            postPerPage: 5,
            classes: { loadMoreButton: '.load-more-posts' },
            endpoints: {
                loadMore: (group, offset) => {
                    return this.ajaxPath + `?action=get_posts&groupId=${group}&offset=${offset}`
                }
            }
        };
    }

    /**
     *
     * @returns {{postPerPage: number, classes: {loadMoreButton: string}, endpoints: {getPublicGroups: (function())}}}
     */
    publicGroupsConfig() {
        return {
            postPerPage: 5,
            classes: { loadMoreButton: '.group-loader .load-more' },
            endpoints: {
                getPublicGroups: (offset, text) => {
                    return this.ajaxPath + `?action=search_local_group_by&text=${encodeURI(text)}&offset=${offset}`
                }
            }
        }
    }

    /**
     * Show or hide load more button
     * @param {boolean} show
     */
    toggleLoadMoreButton(show = false) {
        this.container.find(this.config.classes.loadMoreButton).toggle(show);
    }

    /**
     * Display posts to the container
     * @param {Array} data
     */
    displayPosts(data) {
        data.forEach((post, key) => {
            if (key < this.config.postPerPage) {
                this.container.find(this.config.classes.loadMoreButton).before(
                    this.constructor.htmlEntity(post)
                );
            }
        });
    }
}