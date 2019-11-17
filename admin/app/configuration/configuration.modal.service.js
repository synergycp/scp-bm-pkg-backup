(function() {
  "use strict";

  angular
    .module("pkg.backup.configuration")
    .service("PkgBackupConfigurationModal", PkgBackupConfigurationModalService);

  /**
   * @ngInject
   * @constructor
   */
  function PkgBackupConfigurationModalService(
    RouteHelpers,
    Modal,
    ApiKey,
    $sce
  ) {
    var PkgBackupConfigurationModal = this;
    var pkg = RouteHelpers.package("backup");
    var $configurations = pkg.api().all("configuration");

    PkgBackupConfigurationModal.save = save;

    function save() {
      return pkg.loadLang("admin:widget").then(function() {
        return Modal.information()
          .templateUrl(
            pkg.trustedAsset(
              "admin/configuration/configuration.create.modal.html"
            )
          )
          .data({
            href: $sce.trustAsResourceUrl($configurations.getRequestedUrl()),
            apiKey: ApiKey.get()
          })
          .open().result;
      });
    }
  }
})();
