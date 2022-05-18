(function() {
  "use strict";

  angular.module("pkg.backup.home", [
    "scp.angle.layout.list",
    "scp.core.api",
    "scp.core.auth",
    "pkg.backup.recurring",
    "pkg.backup.destination",
    "pkg.backup.archive"
  ]);
})();
