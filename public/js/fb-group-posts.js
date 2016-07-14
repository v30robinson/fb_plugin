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
class GroupsPost extends GroupsCore {

    /**
     * Create object for work with group posts
     * @param {Object} postContainer
     */
    constructor(postContainer) {
        super(GroupsPost.name);
        this.container = postContainer;
    }

    /**
     * Set event for loading next posts;
     * Group id and offset options located in the data of container
     */
    setLoadMoreEvent() {
        this.container.find(this.config.classes.loadMoreButton).on('click', () => {
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
        this.jquery.get(this.config.endpoints.loadMore(groupId, offset), (data) => {
            this.displayData(data);
            this.toggleLoadMoreButton(data.length > this.config.postPerPage);
            this.setOffsetForContainer(offset);
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