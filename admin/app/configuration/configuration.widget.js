(function() {
  "use strict";

  var STATE = {
    SUCCESS: "SUCCESS",
    WARNING: "WARNING",
    ERROR: "ERROR",
    LOADING: "LOADING"
  };

  angular
    .module("pkg.backup.configuration")
    .component("pkgBackupConfigurationWidget", {
      require: {},
      bindings: {},
      controller: "PkgBackupConfigurationWidgetCtrl as widget",
      transclude: true,
      templateUrl: templateUrl
    })
    .controller(
      "PkgBackupConfigurationWidgetCtrl",
      PkgBackupConfigurationWidgetCtrl
    );

  /**
   * @ngInject
   */
  function templateUrl(RouteHelpers) {
    return RouteHelpers.package("backup").trustedAsset(
      "admin/configuration/configuration.widget.html"
    );
  }

  /**
   * @ngInject
   * @constructor
   */
  function PkgBackupConfigurationWidgetCtrl(
    Loader,
    RouteHelpers,
    _,
    Modal,
    ApiKey,
    $sce
  ) {
    var widget = this;
    var pkg = RouteHelpers.package("backup");
    var $configurations = pkg.api().all("configuration");

    widget.loader = Loader();
    widget.refresh = refresh;
    widget.$onInit = refresh;
    widget.saveConfigurationModal = saveConfigurationModal;
    widget.state = STATE.LOADING;
    widget.STATE = STATE;

    function saveConfigurationModal() {
      return widget.loader.during(
        Modal.information()
          .templateUrl(
            pkg.trustedAsset(
              "admin/configuration/configuration.create.modal.html"
            )
          )
          .data({
            href: $sce.trustAsResourceUrl($configurations.getRequestedUrl()),
            apiKey: ApiKey.get()
          })
          .open().result
      );
    }

    function refresh() {
      return widget.loader.during(
        $configurations
          .getList({
            per_page: 1
          })
          .then(storeConfigurations)
      );
    }

    function storeConfigurations(result) {
      widget.latest = result[0];
      recomputeState();
    }

    function recomputeState() {
      widget.state = computeState();
    }

    function computeState() {
      return widget.latest ? STATE.SUCCESS : STATE.WARNING;
    }
  }
})();
