(function() {
  "use strict";

  var STATE = {
    SUCCESS: "SUCCESS",
    WARNING: "WARNING",
    ERROR: "ERROR",
    LOADING: "LOADING"
  };

  angular
    .module("pkg.backup.destination")
    .component("pkgBackupDestinationWidget", {
      require: {},
      bindings: {},
      controller: "PkgBackupDestinationWidgetCtrl as widget",
      transclude: true,
      templateUrl: templateUrl
    })
    .controller(
      "PkgBackupDestinationWidgetCtrl",
      PkgBackupDestinationWidgetCtrl
    );

  /**
   * @ngInject
   */
  function templateUrl(RouteHelpers) {
    return RouteHelpers.package("backup").trustedAsset(
      "admin/destination/destination.widget.html"
    );
  }

  /**
   * @ngInject
   * @constructor
   */
  function PkgBackupDestinationWidgetCtrl(Loader, RouteHelpers, _) {
    var widget = this;
    var $destinations = RouteHelpers.package("backup")
      .api()
      .all("destination");

    widget.loader = Loader();
    widget.refresh = refresh;
    widget.$onInit = refresh;
    widget.state = STATE.LOADING;
    widget.STATE = STATE;
    widget.destinations = [];

    function refresh() {
      return widget.loader.during(
        $destinations
          .getList({
            per_page: 50
          })
          .then(storeDestinations)
      );
    }

    function storeDestinations(result) {
      _.setContents(widget.destinations, result);
      recomputeState();
    }

    function recomputeState() {
      widget.state = computeState();
    }

    function computeState() {
      return widget.destinations.length === 0 ? STATE.WARNING : STATE.SUCCESS;
    }
  }
})();
