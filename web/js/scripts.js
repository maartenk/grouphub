var grouphub = (function ($) {
    'use strict';

    var groupSearchReq, userSearchReq,
        userId = $('body').data('user-id');

    var searchGroups = function () {
        var $this = $(this),
            $searchContainer = $('#group_search'),
            $searchResults = $searchContainer.children('ul').first();

        groupSearchReq = $.get({
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

        userSearchReq = $.get({
            url: $searchResults.data('url'),
            data: {query: $this.val()},
            beforeSend: function () {
                userSearchReq && userSearchReq.abort();
            },
            success: function (data) {
                $searchResults.html(data);
                initScroll($searchResults);
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

    var lowerGroupCount = function (groupId) {
        var $counts = $('.group-' + groupId).find('.count');

        $counts.each(function () {
            var $this = $(this);

            $this.html(parseInt($this.text(), 10) - 1);
        });
    };

    var raiseGroupCount = function (groupId) {
        var $counts = $('.group-' + groupId).find('.count');

        $counts.each(function () {
            var $this = $(this);

            $this.html(parseInt($this.text(), 10) + 1);
        });
    };

    var updateGroups = function () {
        var $groups = $('#groups');

        $.get($groups.data('url'), {query: $('#searchInput').val()}, function (data) {
            $groups.html(data);

            initScroll('#group_all_groups, #group_search');
        });
    };

    var userAddMode = function (groupId, userId) {
        var $member = $('.edit_group #group_members_tab .users').find('.user-' + userId),
            $user = $('.edit_group #add_members_tab .users').find('.user-' + userId),
            $tpl = $('.edit_group .user-add-tpl').clone();

        $member.remove();
        $user.find('.actions').html(function () {
            return $tpl.html().replace(/%25gid%25/g, groupId).replace(/%25uid%25/g, userId);
        });
    };

    var userEditMode = function (groupId, userId, value) {
        var $members = $('.edit_group #group_members_tab .users'),
            $member = $members.find('.user-' + userId),
            $user = $('.edit_group #add_members_tab .users').find('.user-' + userId),
            $tpl = $('.edit_group .user-edit-tpl').clone();

        value = typeof value !== 'undefined' ? value : 'member';

        $tpl.find('select option[value=' + value + ']').attr('selected', 'selected');
        $tpl = $tpl.html().replace(/%25gid%25/g, groupId).replace(/%25uid%25/g, userId);

        $user.find('.actions').html($tpl);

        if ($member.length == 0) {
            $members.append($user.clone());
        } else {
            $member.find('.actions').html($tpl);
        }
    };

    var removeNotification = function (id) {
        var $count = $('#notifications_link').find('span');

        $('#notifications').find('.notification-' + id).remove();

        $count.html(parseInt($count.text(), 10) - 1);
    };

    var init = function () {
        var $editGroup = $('#edit_group'),
            $joinConfirm = $('#join_group'),
            $leaveConfirm = $('#group_leave_confirmation'),
            $groupContainer = $('#groups');

        $('#language_selector_link').on('click', function () {
            $('#language_selector_menu').toggleClass('hidden');

            return false;
        });

        $('#searchInput').on('keyup', $.debounce(250, searchGroups));

        $('section').on('click', '.close-modal', function () {
            $('body').removeClass('modal-open');
            $(this).closest('section').addClass('hidden');

            return false;
        });

        $groupContainer.on('click', '.spinner a', function () {
            var $this = $(this),
                $container = $this.closest('.spinner');

            $container.html('<i class="fa fa-spinner fa-spin">');

            $.get($this.attr('href'), function (data) {
                $container.replaceWith(data);
            });

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

            $.get($container.data('url'), {query: query, sort: $sort.val()}, function (data) {
                $container.replaceWith(data);
                initScroll('#' + $container.attr('id'));
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
            var $this = $(this),
                $form = $this.closest('form');

            $form.attr('action', $this.data('url'));
            $form.submit();

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
            var $form = $('<form>', {
                action: $(this).data('url'),
                method: 'post'
            });

            $form.submit();

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

                initScroll('#group_members_tab ul');
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

            initScroll('#group_members_tab ul');

            return false;
        });

        $editGroup.on('click', '.add', function () {
            var $this = $(this);

            $.post($this.data('url'), function () {
                var id = $editGroup.find('.edit_group').data('id'),
                    user = $this.closest('li').data('user-id');

                raiseGroupCount(id);

                userEditMode(id, user);

                if (user == userId) {
                    updateGroups();
                }
            });

            return false;
        });

        $editGroup.on('change', '.roles', function () {
            var $this = $(this);

            $.post($this.data('url'), {'role': $this.val()}, function () {
                var id = $editGroup.find('.edit_group').data('id'),
                    user = $this.closest('li').data('user-id');

                userEditMode(id, user, $this.val());

                if (user == userId) {
                    updateGroups();
                }
            });
        });

        $editGroup.on('click', '.delete', function () {
            var $this = $(this);

            $.post($this.data('url'), function () {
                var id = $editGroup.find('.edit_group').data('id'),
                    user = $this.closest('li').data('user-id');

                lowerGroupCount(id);

                userAddMode(id, user);

                if (user == userId) {
                    updateGroups();
                }
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
                var id = $editGroup.find('.edit_group').data('id'),
                    $group = $('.group-' + id),
                    title = $form.find('input').val(),
                    descr = $form.find('textarea').val();

                $('#group_title').html(title);
                $('#group_details').html(descr);

                $group.find('.name').html(title);
                $group.find('.description').html(descr);
            });
        });

        $editGroup.on('click', '#delete_group_link', function () {
            $('#group_deletion_confirmation').toggleClass('hidden');

            return false;
        });

        $editGroup.on('click', '#group_deletion_confirmation a.confirm', function () {
            var $this = $(this),
                $form = $this.closest('form');

            $form.attr('action', $this.data('url'));
            $form.submit();

            return false;
        });

        $editGroup.on('click', '#group_deletion_confirmation a.cancel', function () {
            $('#group_deletion_confirmation').toggleClass('hidden');

            return false;
        });

        $editGroup.on('keyup', '.searchInput', $.debounce(250, searchUsers));

        $editGroup.on('click', '.prospect_details', function () {
            $(this).next('div').find('div.details').toggleClass('hidden');

            return false;
        });

        $editGroup.on('click', '.prospect .confirm, .prospect .cancel', function () {
            var $this = $(this),
                $container = $this.closest('.prospect');

            $.post($this.data('url'), function () {
                var id = $editGroup.find('.edit_group').data('id'),
                    user = $this.closest('li').data('user-id'),
                    $article = $container.find('article');

                if ($this.hasClass('confirm')) {

                    raiseGroupCount(id);

                    if (user == userId) {
                        updateGroups();
                    }

                    userEditMode(id, user);
                } else {
                    userAddMode(id, user);
                }

                if (!$article.length) {
                    return;
                }

                $.post($article.data('url'), function () {
                    removeNotification($article.data('id'));
                });
            });

            return false;
        });

        $('#notifications').on('click', '.confirm, .cancel', function () {
            var $this = $(this);

            $.post($this.data('url'), function () {
                var $article = $this.closest('article');

                $.post($article.data('url'), function () {
                    removeNotification($article.data('id'));
                });

                if (!$this.hasClass('confirm')) {
                    return;
                }

                raiseGroupCount($article.data('group-id'));

                if ($article.data('from-id') == userId) {
                    updateGroups();
                }
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

    $('.tooltip').tooltipster();
});
