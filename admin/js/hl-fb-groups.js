(function($) {
    /**
     * Scope vars
     * @types {null}
     */
    var widgetResultDiv = null,
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
     * show result table if it's first load time
     * @returns {boolean}
     */
    function showResultTable() {
        if (!resultTable.find('tbody tr').length) {
            resultTable.parent().show();
            resultTable.find('tbody').empty();
            widgetResultDiv.hide();
        }
        return true;
    }

    /**
     * hide result table
     * @returns {boolean}
     */
    function hideResultTable() {
        resultTable.parent().hide();
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
                    createGroupRow(group['id'], group['name'], group['privacy'])
                );
            });
        }
    }

    /**
     * Create HTML entity for table with groups
     * @param {int} id
     * @param {string} name
     * @param {string} privacy
     * @returns {HTML}
     */
    function createGroupRow(id, name, privacy) {
        return `
            <tr>
                <td>${id}</td>
                <td>${name}</td>
                <td>
                    <a href="https://facebook.com/groups/${id}/" target="_blank">
                        Join to ${name}
                     </a>
                </td>
                <td>${privacy}</td>
            </tr>
        `;
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
    });
})(jQuery);
