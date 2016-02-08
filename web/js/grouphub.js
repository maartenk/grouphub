!function (e) {
    function t(r) {
        if (n[r])return n[r].exports;
        var o = n[r] = {exports: {}, id: r, loaded: !1};
        return e[r].call(o.exports, o, o.exports, t), o.loaded = !0, o.exports
    }

    var n = {};
    return t.m = e, t.c = n, t.p = "", t(0)
}([function (e, t, n) {
    "use strict";
    function r(e) {
        return e && e.__esModule ? e : {"default": e}
    }

    var o = n(1), u = r(o);
    document.addEventListener("DOMContentLoaded", function () {
        return u["default"].start()
    })
}, function (e, t) {
    "use strict";
    function n(e, t) {
        if (!(e instanceof t))throw new TypeError("Cannot call a class as a function")
    }

    var r = function () {
        function e(e, t) {
            for (var n = 0; n < t.length; n++) {
                var r = t[n];
                r.enumerable = r.enumerable || !1, r.configurable = !0, "value" in r && (r.writable = !0), Object.defineProperty(e, r.key, r)
            }
        }

        return function (t, n, r) {
            return n && e(t.prototype, n), r && e(t, r), t
        }
    }();
    Object.defineProperty(t, "__esModule", {value: !0});
    var o = function () {
        function e() {
            n(this, e)
        }

        return r(e, [{
            key: "stop", value: function (e) {
                void 0 !== e && null !== e && (e.preventDefault(), e.stopPropagation())
            }
        }, {
            key: "start", value: function () {
                this.groupsSelector(), this.sortingSelector(), this.languageSelector(), this.modals()
            }
        }, {
            key: "toggleHidden", value: function (e, t, n) {
                this.stop(e);
                var r = document.querySelector(t);
                void 0 !== n ? r.classList.toggle("hidden", n) : r.classList.toggle("hidden")
            }
        }, {
            key: "sortingSelector", value: function () {
                var e = this;
                ["blue", "green", "purple", "grey"].forEach(function (t) {
                    var n = document.querySelector("#sort_menu_" + t);
                    n.addEventListener("click", function (n) {
                        return e.toggleHidden(n, "#sort_drop_down_" + t)
                    });
                    var r = Array.from(document.querySelectorAll("#sort_drop_down_" + t + " a"));
                    r.forEach(function (n) {
                        return n.addEventListener("click", function (n) {
                            return e.toggleHidden(n, "#sort_drop_down_" + t)
                        })
                    })
                })
            }
        }, {
            key: "groupsSelector", value: function () {
                var e = this;
                ["all_groups", "organisation_groups", "my_groups", "search"].forEach(function (t) {
                    var n = document.querySelector("#close_" + t);
                    n.addEventListener("click", function (n) {
                        e.toggleHidden(n, "#group_" + t, !0);
                        var r = document.querySelector("#select_" + t);
                        if (r)r.checked = !1; else {
                            var o = document.querySelector("#searchInput");
                            o.value = ""
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
                //})
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
    t["default"] = new o
}]);
