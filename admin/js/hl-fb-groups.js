(function($) {
    /**
     * Scope vars
     * @types {null}
     */
    let widgetResultDiv = null,
        loadMoreButton = null,
        widgetInfoDiv = null,
        groupsPerPage = 25,
        resultTable = null,
        widgetForm = null;

    /**
     * Waiting for submit search form and get facebook group from API
     * @param {Object} form - form selector
     */
    function findGroupByName(form) {
        form.on('submit', function() {
            resetResult();
            getGroupList($(this).find('input').val());
            return false;
        });
    }

    /**
     * Waiting for click load more button and get next facebook group from API
     * @param {Object} button
     */
    function loadMoreGroup(button) {
        button.on('click', function() {
            getGroupList(button.data('search'), button.data('next'));
            return false;
        });
    }

    /**
     * add event for AJAX adding new group to local storage
     */
    function addGroupToLocalStorage() {
        $(document).on('click', 'a.addToLocalStorage', function () {
            saveGroupToLocalStorage($(this), getGroupInfo($(this)));
            return false;
        });
    }

    /**
     * AJAX call to endpoint for create new group entity in the local storage
     * @param {Object} selector
     * @param {Array} data
     */
    function saveGroupToLocalStorage(selector, data) {
        $.ajax({
            url: "/wp-admin/admin-ajax.php?action=add_public_group",
            data: data,
            success: () => {
                selector.parent().html(createLocalStorageLink(true));
            }
        });
    }

    /**
     * Get data from HTML row
     * @param {Object} selector
     * @returns {Array}
     */
    function getGroupInfo(selector) {
        let row  = selector.closest('tr');
        return {
            'fb-group-name': row.find('.name').text(),
            'fb-group-description': row.find('.description').text(),
            'fb-group-url': row.find('.link a').attr('href'),
            'fb-group-members': ''
        };
    }

    /**
     * AJAX call to Wordpress Endpoint for get list of groups
     * @param {Object} searchField
     * @param {string|null} more
     */
    function getGroupList(searchField, more = null) {
        $.ajax({
            url: "/wp-admin/admin-ajax.php?action=search_group",
            data: {
                search: searchField,
                after: more
            },
            success: groups => {
                resetTable(groups.data || false);
                createTableResult(resultTable, groups.data);
                toggedLoadMoreButton(searchField, groups.paging, groups.data.length);
            }
        });
    }

    /**
     * @param {string} searchField
     * @param {Object} navigation
     * @param {Object} navigation.cursors
     * @param {string} navigation.after
     * @param {int} resultCount
     */
    function toggedLoadMoreButton(searchField, navigation, resultCount) {
        if (resultCount == groupsPerPage) {
            loadMoreButton.data('search', searchField);
            loadMoreButton.data('next', navigation.cursors.after);
            loadMoreButton.show();
            return;
        }
        loadMoreButton.hide();
    }

    /**
     * reset table; if noData is null - show no-result message
     * @param {bool} noData
     */
    function resetTable(noData = false) {
        return (noData != false)
            ? showResultTable()
            : hideResultTable();
    }

    /**
     * Reset all result for new search
     */
    function resetResult() {
        resultTable.find('tbody').empty();
        resultTable.parent().hide();
    }
    
    /**
     * show result table if it's first load time
     * @returns {boolean}
     */
    function showResultTable() {
        if (!resultTable.find('tbody tr').length) {
            resultTable.parent().show();
            widgetResultDiv.hide();
        }
        return true;
    }

    /**
     * hide result table
     * @returns {boolean}
     */
    function hideResultTable() {
        resetResult();
        widgetResultDiv.show();
        return true;
    }
    
    /**
     * Create table with Facebook groups
     * @param {Object} table
     * @param {Object} groups
     */
    function createTableResult(table, groups) {
        if (groups && !groups['error']) {
            groups.forEach(group => {
                table.find('tbody').append(
                    createGroupRow(group['id'], group['name'], group['description'], group['privacy'], group['localExist'])
                );
            });
        }
    }

    /**
     * Create HTML entity for table with groups
     * @param {int} id
     * @param {string} name
     * @param {string} description
     * @param {string} privacy
     * @param {boolean} exist
     * @returns {string}
     */
    function createGroupRow(id, name, description, privacy, exist = false) {
        return `
            <tr>
                <td class="id">${id}</td>
                <td class="exist">${createLocalStorageLink(exist)}</td>
                <td class="name">${name}</td>
                <td class="description">${description || ''}</td>
                <td class="link">
                    <a href="https://www.facebook.com/groups/${id}/" target="_blank">
                        Open group
                     </a>
                </td>
                <td class="privacy">${privacy}</td>
            </tr>
        `;
    }

    /**
     * Create HTML entity for local storage column
     * @param {boolean} exist
     * @returns {string}
     */
    function createLocalStorageLink(exist) {
        if (!exist) {
            return `
                <a href="#" class="addToLocalStorage" target="_blank">
                    Add to local storage
                 </a>
            `;
        }

        return `<b>Already exist</b>`;
    }
    
    /**
     * run after page was loaded
     */
    $(document).ready(function() {

        widgetResultDiv = $('.widget-results');
        loadMoreButton = $('a.load-more');
        widgetInfoDiv = $('.widget-info');
        resultTable = $('table.wp-list-table');
        widgetForm = $('.widget-info form');

        findGroupByName(widgetForm);
        loadMoreGroup(loadMoreButton);
        addGroupToLocalStorage();
    });
})(jQuery);
