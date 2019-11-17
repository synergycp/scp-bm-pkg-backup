(function() {
  "use strict";

  angular.module("pkg.backup.health", [
    "pkg.backup.archive",
    "pkg.backup.configuration",
    "pkg.backup.destination",
    "pkg.backup.recurring"
  ]);
})();
