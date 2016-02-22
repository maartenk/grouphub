!function (e) {
    function t(o) {
        if (n[o])return n[o].exports;
        var r = n[o] = {exports: {}, id: o, loaded: !1};
        return e[o].call(r.exports, r, r.exports, t), r.loaded = !0, r.exports
    }

    var n = {};
    return t.m = e, t.c = n, t.p = "", t(0)
}([function (e, t, n) {
    "use strict";
    function o(e) {
        return e && e.__esModule ? e : {"default": e}
    }

    var r = n(1), u = o(r);
    document.addEventListener("DOMContentLoaded", function () {
        return u["default"].start()
    })
}, function (e, t) {
    "use strict";
    function n(e, t) {
        if (!(e instanceof t))throw new TypeError("Cannot call a class as a function")
    }

    var o = function () {
        function e(e, t) {
            for (var n = 0; n < t.length; n++) {
                var o = t[n];
                o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, o.key, o)
            }
        }

        return function (t, n, o) {
            return n && e(t.prototype, n), o && e(t, o), t
        }
    }();
    Object.defineProperty(t, "__esModule", {value: !0});
    var r = function () {
        function e() {
            n(this, e)
        }

        return o(e, [{
            key: "stop", value: function (e) {
                void 0 !== e && null !== e && (e.preventDefault(), e.stopPropagation())
            }
        }, {
            key: "start", value: function () {
                this.groupsSelector(), this.sortingSelector(), this.languageSelector(), this.modals(), this.editGroup()
            }
        }, {
            key: "toggleHidden", value: function (e, t, n) {
                this.stop(e);
                var o = document.querySelector(t);
                void 0 !== n ? o.classList.toggle("hidden", n) : o.classList.toggle("hidden")
            }
        }, {
            key: "sortingSelector", value: function () {
                //var e = this;
                //["blue", "green", "purple", "grey"].forEach(function (t) {
                //    var n = document.querySelector("#sort_menu_" + t);
                //    n.addEventListener("click", function (n) {
                //        return e.toggleHidden(n, "#sort_drop_down_" + t)
                //    });
                //    var o = Array.from(document.querySelectorAll("#sort_drop_down_" + t + " a"));
                //    o.forEach(function (n) {
                //        return n.addEventListener("click", function (n) {
                //            return e.toggleHidden(n, "#sort_drop_down_" + t)
                //        })
                //    })
                //})
            }
        }, {
            key: "groupsSelector", value: function () {
                var e = this;
                ["all_groups", "organisation_groups", "my_groups", "search"].forEach(function (t) {
                    var n = document.getElementById("group_" + t);
                    n.style.height = screen.height - 200 + "px";
                    var o = document.querySelector("#close_" + t);
                    o.addEventListener("click", function (n) {
                        e.toggleHidden(n, "#group_" + t, !0);
                        var o = document.querySelector("#select_" + t);
                        if (o)o.checked = !1; else {
                            var r = document.querySelector("#searchInput");
                            r.value = ""
                        }
                    });
                    var r = document.querySelector("#select_" + t);
                    r && r.addEventListener("change", function (n) {
                        return e.toggleHidden(n, "#group_" + t, !n.target.checked)
                    })
                });
                //var t = document.querySelector("#searchInput");
                //t.addEventListener("keyup", function (t) {
                //    13 === t.keyCode && e.toggleHidden(t, "#group_search", !1)
                //})
            }
        }, {
            key: "modals", value: function () {
                var e = this, t = function (t, n) {
                    e.toggleHidden(t, n), document.querySelector("body").classList.toggle("modal-open")
                };
                ["notifications", "new_group"].forEach(function (e) {
                    document.querySelector("#" + e + "_link").addEventListener("click", function (n) {
                        return t(n, "#" + e)
                    }), document.querySelector("#" + e + "_close").addEventListener("click", function (n) {
                        return t(n, "#" + e)
                    })
                });
                //var n = Array.from(document.querySelectorAll(".button_edit"));
                //n.forEach(function (e) {
                //    return e.addEventListener("click", function (e) {
                //        return t(e, "#edit_group")
                //    })
                //}), document.querySelector("#edit_group_close").addEventListener("click", function (e) {
                //    return t(e, "#edit_group")
                //});
                //var o = Array.from(document.querySelectorAll(".button_join"));
                //o.forEach(function (e) {
                //    return e.addEventListener("click", function (e) {
                //        return t(e, "#join_group")
                //    })
                //}), document.querySelector("#join_group_close").addEventListener("click", function (e) {
                //    return t(e, "#join_group")
                //});
                //var r = Array.from(document.querySelectorAll(".group_section.edit"));
                //r.forEach(function (e) {
                //    return e.addEventListener("click", function (e) {
                //        return t(e, "#edit_group")
                //    })
                //})
            }
        }, {
            key: "editGroup", value: function () {
                //var e = this;
                //document.getElementById("show_group_details").addEventListener("click", function (t) {
                //    e.stop(t), document.getElementById("group_details").classList.toggle("hidden")
                //}), document.getElementById("edit_group_link").addEventListener("click", function (t) {
                //    e.stop(t), document.getElementById("group_title").classList.toggle("hidden"), document.getElementById("edit_group_title").classList.toggle("hidden"), document.getElementById("group_details").classList.toggle("hidden", !0), document.getElementById("edit_group_details").classList.toggle("hidden")
                //}), document.getElementById("delete_group_link").addEventListener("click", function (t) {
                //    e.stop(t), document.getElementById("group_deletion_confirmation").classList.toggle("hidden")
                //}), document.getElementById("add_members").addEventListener("click", function (t) {
                //    e.stop(t), e.toggleTab("group_members", !1), e.toggleTab("add_members", !0)
                //}), document.getElementById("group_members").addEventListener("click", function (t) {
                //    e.stop(t), e.toggleTab("group_members", !0), e.toggleTab("add_members", !1)
                //})
            }
        }, {
            key: "toggleTab", value: function (e, t) {
                //var n = document.getElementById(e).classList, o = document.getElementById(e + "_tab").classList;
                //t ? (n.add("active"), o.remove("hidden")) : (n.remove("active"), o.add("hidden"))
            }
        }, {
            key: "languageSelector", value: function () {
                var e = this;
                ["#language_selector_link", "#language_selector_menu"].forEach(function (t) {
                    return document.querySelector(t).addEventListener("click", function (t) {
                        return e.toggleHidden(t, "#language_selector_menu")
                    })
                })
            }
        }]), e
    }();
    t["default"] = new r
}]);
