(function() {
  "use strict";

  angular.module("pkg.backup", [
    "pkg.backup.archive",
    "pkg.backup.configuration",
    "pkg.backup.destination",
    "pkg.backup.home",
    "pkg.backup.health",
    "pkg.backup.recurring"
  ]);
})();
