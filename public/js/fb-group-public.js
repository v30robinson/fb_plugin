/**
 * Class for work with public groups
 *
 * @link       http://nicktemple.com/
 * @license    http://www.mev.com/license.txt
 * @copyright  2016 by MEV, LLC
 * @since      1.0
 * @author     Stanislav Vysotskyi <stanislav.vysotskyi@mev.com>
 * @author     Nick Temple <nick@intellispire.com>
 */
class GroupsPublic extends GroupsCore {
    
    /**
     * Create object for work with group posts
     * @param {Object} groupContainer
     */
    constructor(groupContainer) {
        super(GroupsPublic.name);
        this.container = groupContainer;
    }

    setInputUrlEvent() {
        this.container.find(this.config.classes.focusInput).on('focusout', () => {
            this.getGroupInfo(this.getGroupIdByUrl(
                this.container.find(this.config.classes.focusInput).val()
            ));
        });
    }
    
    /**
     * Set event for submit search form;
     */
    setSearchFormEvent() {
        this.container.find(this.config.classes.searchForm).on('submit', () => {
            this.setSearchMode(this.getSearchRequest());
            this.clearGroupsList();
            this.getGroups(0, this.getSearchRequest());
            return false;
        });
    }

    /**
     * Set event for reset search form;
     */
    setResetFormEvent() {
        this.container.find(this.config.classes.resetFormButton).on('click', () => {
            this.setSearchMode(null);
            this.clearGroupsList();
            this.getGroups(0);
        });
    }

    /**
     * Set event for loading next posts;
     * Group id and offset options located in the data of container
     */
    setLoadMoreEvent() {
        this.container.find(this.config.classes.loadMoreButton).on('click', () => {
            this.getGroups(this.container.data('groups-offset'), this.container.data('groups-search'));
            return false;
        });
    }

    /**
     * AJAX call to endpoint and get next groups
     * @param {int} offset
     * @param {string} text
     */
    getGroups(offset, text = '') {
        this.jquery.get(this.config.endpoints.getPublicGroups(offset, text), (data) => {
            this.displayData(data);
            this.toggleLoadMoreButton(data.length > this.config.postPerPage);
            this.setOffsetForContainer(offset);
        });
    }

    /**
     * AJAX call to endpoint and get group info (name and description)
     * @param {int} id
     */
    getGroupInfo(id) {
        this.jquery.get(this.config.endpoints.getGroupInfo(id), (data) => {
            this.setGroupFormData(data);
        });
    }

    /**
     * Parse url and return group id form URL
     * @param {string} url
     * @returns {int}
     */
    getGroupIdByUrl(url) {
        return url.length && this.config.groupUrlFormat.test(url)
            ? url.match(this.config.groupUrlFormat)[1]
            : 0;
    }

    /**
     * Set inputs values to new groups form
     * @param {Object} data
     * @param {string} data.name
     * @param {string} data.description
     */
    setGroupFormData(data) {
        if (data.hasOwnProperty('name') && data.hasOwnProperty('description')) {
            this.container.find('input[name="fb-group-name"]').val(data.name);
            this.container.find('input[name="fb-group-description"]').val(data.description);
        }
    }

    /**
     * Set search mode for groups list
     * @param {string} text
     */
    setSearchMode(text) {
        this.container.data('groups-search', text || '');
        this.container.find(this.config.classes.searchForm).toggleClass('search', text ? true : false)
    }

    /**
     * Remove all groups entities
     */
    clearGroupsList() {
        this.container.find(this.config.classes.groupEntity).remove();
    }

    /**
     * get search request field value
     * @returns {*}
     */
    getSearchRequest() {
        return this.container.find(this.config.classes.searchForm).find('input[type="text"]').val()
    }

    /**
     * Create HTML entity of post
     * @param {Object} data
     * @param {string} data.name
     * @param {string} data.url
     * @param {string} data.description
     * @param {int} data.members
     * @returns {string}
     */
    static htmlEntity(data) {
        return `
            <div class="group-container">
                <div class="group-title">
                    <b>Group name</b>: ${data.name}
                </div>\
                <div class="group-url">
                    <b>Group url</b>: <a target="_blank" href="${data.url}">Open ${data.name} group</a>
                </div>\
                <div class="group-description" ${data.description ? '' : 'disabled'}>
                    <b>Group description</b>: ${data.description}
                </div>\
                <div class="group-members" ${data.members ? '' : 'disabled'}>
                    <b>Group members</b>: ${data.members}
                </div>
            </div>
        `;
    }
}