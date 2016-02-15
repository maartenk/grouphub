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

            $editGroup.load($(this).data('url'), function () {
                $(this).removeClass('hidden');
            });

            return false;
        });

        $editGroup.on('click', '.add', function () {
            var $this = $(this);

            $.post($this.data('url'), function () {
                // @todo: update counts, groups could need an update
            });

            return false;
        });

        $editGroup.on('change', '.roles', function () {
            var $this = $(this);

            $.post($this.data('url'), {'role': $this.val()});

            // @todo: groups could need an update
        });

        $editGroup.on('click', '.delete', function () {
            var $this = $(this);

            $.post($this.data('url'), function () {
                $this.closest('li').remove();

                // @todo: update counts, groups could need an update
            });

            return false;
        });

        $editGroup.on('click', '#edit', function () {
            var $this = $(this),
                $form = $this.closest('.edit_group').find('form');

            $form.toggleClass('hidden');

            return false;
        });

        $editGroup.on('change', 'header form :input', function () {
            var $this = $(this),
                $form = $this.closest('form');

            $.post($form.attr('action'), $form.serialize(), function () {
                // @todo: update groups/description
            });
        });

        $editGroup.on('click', '#delete_group', function () {
            var $this = $(this);

            // @todo: confirm popup
            $.post($this.data('url'), function () {
                // @todo: close modal, update groups, actual submit?
            });

            return false;
        });

        $editGroup.on('keyup', '.searchInput', function (e) {
            var $this = $(this),
                $searchResults = $this.closest('.search_container').next('ul');

            if (e.which != 13) {
                return;
            }

            $.post($searchResults.data('url'), {query: $this.val()}, function (data) {
                $searchResults.html(data);
            });
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
