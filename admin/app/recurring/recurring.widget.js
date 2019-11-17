(function() {
  "use strict";

  var STATE = {
    SUCCESS: "SUCCESS",
    WARNING: "WARNING",
    ERROR: "ERROR",
    LOADING: "LOADING"
  };

  angular
    .module("pkg.backup.recurring")
    .component("pkgBackupRecurringWidget", {
      require: {},
      bindings: {},
      controller: "PkgBackupRecurringWidgetCtrl as widget",
      transclude: true,
      templateUrl: templateUrl
    })
    .controller("PkgBackupRecurringWidgetCtrl", PkgBackupRecurringWidgetCtrl);

  /**
   * @ngInject
   */
  function templateUrl(RouteHelpers) {
    return RouteHelpers.package("backup").trustedAsset(
      "admin/recurring/recurring.widget.html"
    );
  }

  /**
   * @ngInject
   * @constructor
   */
  function PkgBackupRecurringWidgetCtrl(Loader, RouteHelpers, _) {
    var widget = this;
    var $recurrings = RouteHelpers.package("backup")
      .api()
      .all("recurring");

    widget.loader = Loader();
    widget.refresh = refresh;
    widget.$onInit = refresh;
    widget.state = STATE.LOADING;
    widget.STATE = STATE;
    widget.recurrings = [];

    function refresh() {
      return widget.loader.during(
        $recurrings
          .getList({
            per_page: 50
          })
          .then(storeRecurrings)
      );
    }

    function storeRecurrings(result) {
      _.setContents(widget.recurrings, result);
      recomputeState();
    }

    function recomputeState() {
      widget.state = computeState();
    }

    function computeState() {
      return widget.recurrings.length === 0 ? STATE.WARNING : STATE.SUCCESS;
    }
  }
})();
