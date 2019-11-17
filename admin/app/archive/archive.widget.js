(function() {
  "use strict";

  var STATE = {
    SUCCESS: "SUCCESS",
    WARNING: "WARNING",
    ERROR: "ERROR",
    LOADING: "LOADING"
  };

  angular
    .module("pkg.backup.archive")
    .component("pkgBackupArchiveWidget", {
      require: {},
      bindings: {},
      controller: "PkgBackupArchiveWidgetCtrl as widget",
      transclude: true,
      templateUrl: templateUrl
    })
    .controller("PkgBackupArchiveWidgetCtrl", PkgBackupArchiveWidgetCtrl);

  /**
   * @ngInject
   */
  function templateUrl(RouteHelpers) {
    return RouteHelpers.package("backup").trustedAsset(
      "admin/archive/archive.widget.html"
    );
  }

  /**
   * @ngInject
   * @constructor
   */
  function PkgBackupArchiveWidgetCtrl(Loader, RouteHelpers) {
    var widget = this;
    var $archives = RouteHelpers.package("backup")
      .api()
      .all("archive");

    widget.loader = Loader();
    widget.refresh = refresh;
    widget.$onInit = refresh;
    widget.state = STATE.LOADING;
    widget.STATE = STATE;

    function refresh() {
      return widget.loader.during(
        $archives
          .getList({
            per_page: 2
          })
          .then(storeArchives)
      );
    }

    function storeArchives(result) {
      widget.latest = result[0];
      widget.previous = result[1];
      recomputeState();
    }

    function recomputeState() {
      widget.state = computeState();
    }
    function computeState() {
      return (
        getStateFromBackup(widget.latest) ||
        getStateFromBackup(widget.previous) ||
        STATE.WARNING
      );
    }

    function getStateFromBackup(backup) {
      var status = (backup || {}).status;
      switch (status) {
        case null:
        case undefined:
          return STATE.WARNING;
        case "Failed":
          return STATE.ERROR;
        case "Finished":
          return STATE.SUCCESS;
        default:
          // In progress. Defer to previous backup.
          return null;
      }
    }
  }
})();
