var grouphub = (function ($) {
    'use strict';

    var init = function () {
        var $editGroup = $('#edit_group');

        $("#searchInput").on('keyup', function (e) {
            var $this = $(this),
                $searchContainer = $('#group_search'),
                $searchResults = $searchContainer.children('ul').first();

            if (e.which != 13) {
                return;
            }

            $.post($searchResults.data('url'), {query: $this.val()}, function (data) {
                $searchContainer.removeClass('hidden');
                $searchResults.html(data);
            });
        });

        $('section').on('click', '.close-modal', function () {
            $('body').removeClass('modal-open');
            $(this).closest('section').addClass('hidden');

            return false;
        });

        $('.button_edit').on('click', function () {
            $('body').addClass('modal-open');

            $('#edit_group').load($(this).data('url'), function () {
                $(this).removeClass('hidden');
            });

            return false;
        });

        $editGroup.on('change', '.roles', function () {
            var $this = $(this);

            $.post($this.data('url'), {'role': $this.val()});
        });

        $editGroup.on('click', '.delete', function () {
            var $this = $(this);

            $.post($this.data('url'), function () {
                $this.closest('li').remove();
            });

            return false;
        });
    };

    return {
        init: init
    };

}(window.jQuery));

jQuery().ready(function () {
    'use strict';

    grouphub.init();
});
