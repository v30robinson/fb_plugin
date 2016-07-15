/**
 * Core class for public part of Facebook Groups plugin
 *
 * @link       http://nicktemple.com/
 * @license    http://www.mev.com/license.txt
 * @copyright  2016 by MEV, LLC
 * @since      1.0
 * @author     Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author     Nick Temple <nick@intellispire.com>
 */
class GroupsCore {

    /**
     * Setup base config for plugin;
     * Will work only on the public part of plugin
     */
    constructor(currentConfig) {
        this.jquery = jQuery || null;
        this.ajaxPath = fbl.ajaxurl || null;
        this.config = eval('this.set' + currentConfig + 'Config()');
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
    displayData(data) {
        data.forEach((post, key) => {
            if (key < this.config.postPerPage) {
                this.container.find(this.config.classes.loadMoreButton).before(
                    this.constructor.htmlEntity(post)
                );
            }
        });
    }

    /**
     * @param {int} currentOffset
     */
    setOffsetForContainer(currentOffset) {
        this.container.data(this.config.name + '-offset', parseInt(currentOffset) + this.config.postPerPage);
    }

    /**
     * Setup config for user groups func
     * @returns {{postPerPage: number, classes: {loadMoreButton: string}, endpoints: {loadMore: (function())}}}
     */
    setGroupsPostConfig() {
        return {
            name: 'posts',
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
     * Setup config for public groups func
     * @returns {{postPerPage: number, classes: {loadMoreButton: string}, endpoints: {getPublicGroups: (function())}}}
     */
    setGroupsPublicConfig() {
        return {
            name: 'groups',
            postPerPage: 5,
            groupUrlFormat: /https:\/\/www.facebook.com\/groups\/(.+?)\/.*/,
            classes: {
                loadMoreButton: '.group-loader .load-more',
                resetFormButton: '.public-groups-search form button[type="reset"]',
                searchForm: '.public-groups-search form',
                groupEntity: '.group-container',
                focusInput: '.group-new-group form input[name="fb-group-url"].hasToken'
            },
            endpoints: {
                getPublicGroups: (offset, text) => {
                    return this.ajaxPath + `?action=get_public_groups&search=${encodeURI(text)}&offset=${offset}`
                },
                getGroupInfo: (id) => {
                    return this.ajaxPath + `?action=get_group_info_by&id=${id}`
                }
            }
        }
    }
}