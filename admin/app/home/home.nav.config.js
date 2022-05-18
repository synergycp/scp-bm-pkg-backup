(function() {
  "use strict";

  angular
    .module("pkg.backup.home")
    .constant("PkgBackupHomeNav", {
      text: "Backups",
      sref: "app.pkg.backup.home"
    })
    .run(NavConfig);

  /**
   * @ngInject
   */
  function NavConfig(Nav, PkgBackupHomeNav, Auth, Permission) {
    var group = Nav.group("system");

    Auth.whileLoggedIn(show, hide);

    function show() {
      Permission.map().then(showPermitted);
    }

    function showPermitted(map) {
      console.log(map);
      if (map.pkg.backup.read) {
        group.item(PkgBackupHomeNav);
      }
    }
    function hide() {
      group.remove(PkgBackupHomeNav);
    }
  }
})();
