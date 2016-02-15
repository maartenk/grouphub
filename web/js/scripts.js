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

        $editGroup.on('click', '#show_group_details', function() {
            $('#group_details').toggleClass('hidden');
        });

        $editGroup.on('click', '#add_members', function() {
            $('#group_members').removeClass('active');
            $('#group_members_tab').addClass('hidden');

            $('#add_members').addClass('active');
            $('#add_members_tab').removeClass('hidden');
        });

        $editGroup.on('click', '#group_members', function() {
            $('#group_members').addClass('active');
            $('#group_members_tab').removeClass('hidden');

            $('#add_members').removeClass('active');
            $('#add_members_tab').addClass('hidden');
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

        $editGroup.on('click', '#edit_group_link', function () {
            $('#group_details').addClass('hidden');
            $('#group_title, #group_name, #edit_group_details').toggleClass('hidden');

            return false;
        });

        $editGroup.on('change', 'header form :input', function () {
            var $this = $(this),
                $form = $this.closest('form');

            $.post($form.attr('action'), $form.serialize(), function () {
                // @todo: update groups/description
            });
        });

        $editGroup.on('click', '#delete_group_link', function () {
            $('#group_deletion_confirmation').toggleClass('hidden');

            return false;
        });

        $editGroup.on('click', 'a.confirm', function () {
            var $this = $(this);

            $.post($this.data('url'), function () {
                $('body').removeClass('modal-open');
                $this.closest('.edit_group_container').addClass('hidden');
                // @todo: update groups, actual submit?
            });

            return false;
        });

        $editGroup.on('click', 'a.cancel', function () {
            $('#group_deletion_confirmation').toggleClass('hidden');

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
