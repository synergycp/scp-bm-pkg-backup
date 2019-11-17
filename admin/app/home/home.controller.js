(function() {
  "use strict";

  angular
    .module("pkg.backup.home")
    .controller("PkgBackupHomeCtrl", PkgBackupHomeCtrl);

  /**
   * @ngInject
   */
  function PkgBackupHomeCtrl() {
    var vm = this;

    activate();

    vm.logs = {
      filter: {
        "target_type[]": [
          "pkg.backup.archive",
          "pkg.backup.destination",
          "pkg.backup.recurring",
          "pkg.backup.configuration"
        ]
      }
    };

    ////////////

    function activate() {}
  }
})();
