var grouphub = (function ($) {
    'use strict';

    var groupSearchReq, userSearchReq;

    var searchGroups = function () {
        var $this = $(this),
            $searchContainer = $('#group_search'),
            $searchResults = $searchContainer.children('ul').first();

        groupSearchReq = $.post({
            url: $searchResults.data('url'),
            data: {query: $this.val()},
            beforeSend: function () {
                groupSearchReq && groupSearchReq.abort();
            },
            success: function (data) {
                $searchContainer.removeClass('hidden');
                $searchResults.html(data);
                initScroll('#group_search');
            }
        });
    };

    var searchUsers = function () {
        var $this = $(this),
            $searchResults = $this.closest('.search_container').next('ul');

        userSearchReq = $.post({
            url: $searchResults.data('url'),
            data: {query: $this.val()},
            beforeSend: function () {
                userSearchReq && userSearchReq.abort();
            },
            success: function (data) {
                $searchResults.html(data);
                initScroll('#add_members_tab ul');
            }
        });
    };

    var initScroll = function (el) {
        var $el = $(el);

        $el.jscroll({
            nextSelector: 'a.groups-next',
            loadingHtml: '<li class="spinner"><i class="fa fa-spinner fa-spin"></li>'
        });
    };

    var init = function () {
        var $editGroup = $('#edit_group'),
            $joinConfirm = $('#join_group'),
            $leaveConfirm = $('#group_leave_confirmation'),
            $groupContainer = $('.groups');

        $('#language_selector_link').on('click', function () {
            $('#language_selector_menu').toggleClass('hidden');
        });

        $('#searchInput').on('keyup', $.debounce(250, searchGroups));

        $('section').on('click', '.close-modal', function () {
            $('body').removeClass('modal-open');
            $(this).closest('section').addClass('hidden');

            return false;
        });

        $groupContainer.on('click', '.group .owned i', function () {
            $(this).toggleClass('fa-angle-down').toggleClass('fa-angle-right');
            $(this).closest('.owned').next('ul').toggleClass('hidden');

            return false;
        });

        $groupContainer.on('click', '#sort_menu_blue, #sort_menu_green, #sort_menu_purple, #sort_menu_grey', function () {
            $(this).next('div').toggleClass('hidden');
        });

        $groupContainer.on('change', '.sort', function () {
            var $this = $(this),
                $container = $this.closest('.group'),
                $sort = $this.find('input:checked'),
                isSearch = $container.is('#group_search'),
                query = isSearch ? $('#searchInput').val() : '';

            $.post($container.data('url'), {query: query, sort: $sort.val()}, function (data) {
                $container.replaceWith(data);
                initScroll('#group_all_groups, #group_search');
            });
        });

        $groupContainer.on('click', '.button_join', function () {
            var url =  $(this).data('url');

            if (!url) {
                return false;
            }

            $joinConfirm.find('.group-name').html($(this).data('name'));
            $joinConfirm.find('.confirm').data('url', url);
            $joinConfirm.toggleClass('hidden');

            return false;
        });

        $joinConfirm.on('click', '.confirm', function () {
            var $this = $(this);

            $.post($this.data('url'), $this.closest('form').serialize(), function () {
                $joinConfirm.toggleClass('hidden');
            });

            return false;
        });

        $joinConfirm.on('click', '.cancel', function () {
            $joinConfirm.toggleClass('hidden');

            return false;
        });

        $groupContainer.on('click', '.button_member', function () {
            var url =  $(this).data('url');

            if (!url) {
                return false;
            }

            $leaveConfirm.find('.confirm').data('url', url);
            $leaveConfirm.toggleClass('hidden');

            return false;
        });

        $leaveConfirm.on('click', '.confirm', function () {
            $.post($(this).data('url'), function () {
                $leaveConfirm.toggleClass('hidden');
                // @todo: Update groups
            });

            return false;
        });

        $leaveConfirm.on('click', '.cancel', function () {
            $leaveConfirm.toggleClass('hidden');

            return false;
        });

        $groupContainer.on('click', '.group_section, .button_edit', function () {
            $('body').addClass('modal-open');

            $editGroup.load($(this).data('url'), function () {
                $(this).removeClass('hidden');
            });

            return false;
        });

        $editGroup.on('click', '#show_group_details', function() {
            if (!$('#group_title').hasClass('hidden')) {
                $('#group_details').toggleClass('hidden');
            }

            return false;
        });

        $editGroup.on('click', '#add_members', function() {
            $('#group_members').removeClass('active');
            $('#group_members_tab').addClass('hidden');

            $('#add_members').addClass('active');
            $('#add_members_tab').removeClass('hidden');

            initScroll('#add_members_tab ul');

            return false;
        });

        $editGroup.on('click', '#group_members', function() {
            $('#group_members').addClass('active');
            $('#group_members_tab').removeClass('hidden');

            $('#add_members').removeClass('active');
            $('#add_members_tab').addClass('hidden');

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

        $editGroup.on('click', '#group_deletion_confirmation a.confirm', function () {
            var $this = $(this);

            $.post($this.data('url'), function () {
                $('body').removeClass('modal-open');
                $this.closest('.edit_group_container').addClass('hidden');
                // @todo: update groups, actual submit?
            });

            return false;
        });

        $editGroup.on('click', '#group_deletion_confirmation a.cancel', function () {
            $('#group_deletion_confirmation').toggleClass('hidden');

            return false;
        });

        $editGroup.on('keyup', '.searchInput', $.debounce(250, searchUsers));

        $editGroup.on('click', '.prospect .confirm, .prospect .cancel', function () {
            var $this = $(this),
                $container = $this.closest('.prospect');

            $.post($this.data('url'), function () {
                var $article = $container.find('article');

                $container.remove();
                // @todo: add membership, update groups, update count

                if (!$article.length) {
                    return;
                }

                $.post($article.data('url'), function () {
                    // @todo: remove notifications from popup
                });
            });
        });

        $('#notifications').on('click', '.confirm, .cancel', function () {
            var $this = $(this);

            $.post($this.data('url'), function () {
                var $article = $this.closest('article');

                $.post($article.data('url'), function () {
                    $article.remove();
                    // @todo: update groups, update count
                });
            });

            return false;
        });

        initScroll('#group_all_groups');
    };

    return {
        init: init
    };

}(window.jQuery));

jQuery().ready(function () {
    'use strict';

    Pace.options.ajax.trackMethods.push('POST');

    grouphub.init();
});
