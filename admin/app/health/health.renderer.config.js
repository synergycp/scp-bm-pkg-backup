(function() {
  "use strict";

  angular.module("pkg.backup.health").config(pkgBackupHealthRendererConfig);

  /**
   * @ngInject
   */
  function pkgBackupHealthRendererConfig(
    HealthStatusRendererProvider,
    PkgSimpleHealthStatusRenderer,
    PkgConstantHealthStatusStateChangeRenderer
  ) {
    HealthStatusRendererProvider.set(
      "pkg.backup.configuration.exists",
      PkgSimpleHealthStatusRenderer(
        "backup",
        /** @ngInject */ function(PkgBackupConfigurationModal, $timeout) {
          return PkgBackupConfigurationModal.save().then(function() {
            // Since this is a download in a new tab, it requires some special attention currently.
            return $timeout(1000);
          });
        }
      )
    );
    HealthStatusRendererProvider.set(
      "pkg.backup.archive.status",
      PkgConstantHealthStatusStateChangeRenderer(
        "backup",
        "app.pkg.backup.archive.list"
      )
    );
    HealthStatusRendererProvider.set(
      "pkg.backup.recurring.exists",
      PkgConstantHealthStatusStateChangeRenderer(
        "backup",
        "app.pkg.backup.recurring.list"
      )
    );
  }
})();
