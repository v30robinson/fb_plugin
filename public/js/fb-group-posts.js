/**
 * Class for work with group posts
 *
 * @link       http://nicktemple.com/
 * @license    http://www.mev.com/license.txt
 * @copyright  2016 by MEV, LLC
 * @since      1.0
 * @author     Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author     Nick Temple <nick@intellispire.com>
 */
class groupsPost {

    /**
     * Create object for work with group posts
     * @param {Object} jquery
     * @param {Object} postContainer
     * @param {Object} config
     */
    constructor(jquery, postContainer, config = {}) {
        this.jquery = jquery;
        this.container = postContainer;
        this.config = config;
        this.endpoints = {
            loadMore: (group, offset) => {
                return `/wp-admin/admin-ajax.php?action=get_posts&groupId=${group}&offset=${offset}`
            }
        }
    }

    /**
     * Set event for loading next posts;
     * Group id and offset options located in the data of container
     */
    setLoadMoreEvent() {
        this.container.find(this.config.button).on('click', () => {
            this.getGroupPosts(this.container.data('group-id'), this.container.data('posts-offset'));
            return false;
        });
    }

    /**
     * AJAX call to endpoint and get next posts
     * @param {int} groupId
     * @param {int} offset
     */
    getGroupPosts(groupId, offset) {
        this.jquery.get(this.endpoints.loadMore(groupId, offset), (data) => {
            this.displayPosts(data);
            this.toggleLoadMoreButton(data.length > 5);
            this.container.data('posts-offset', parseInt(offset) + 5);
        });
    }

    /**
     * Show or hide load more button
     * @param {boolean} show
     */
    toggleLoadMoreButton(show = false) {
        this.container.find(this.config.button).toggle(show);
    }

    /**
     * Display posts to the container
     * @param {Array} data
     */
    displayPosts(data) {
        data.forEach((post, key) => {
            if (key < 5) {
                this.container.find(this.config.button).before(
                    this.constructor.htmlEntity(post)
                );
            }
        });
    }

    /**
     * Create HTML entity of post
     * @param {Object} data
     * @param {string} data.name
     * @param {string} data.message
     * @param {string} data.story
     * @returns {string}
     */
    static htmlEntity(data) {
        return `
            <div class="post-container">
                <div class="post-title">
                    <b>Post name</b>: ${ data.message ? 'User Post' : 'Group story' }
                </div>
                <div class="post-description">
                    <b>Post message</b>: ${ data.message || data.story }
                </div>
            </div>
        `;
    }
}