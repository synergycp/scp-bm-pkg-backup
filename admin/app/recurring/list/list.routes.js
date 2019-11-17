(function() {
  angular.module("pkg.backup.recurring.list").config(routeConfig);

  /**
   * @ngInject
   */
  function routeConfig(RouteHelpersProvider) {
    var pkg = RouteHelpersProvider.package("backup");
    pkg.state("recurring.list", {
      url: "",
      title: "Recurring Backups",
      controller: "PkgBackupRecurringIndexCtrl as vm",
      templateUrl: pkg.asset("admin/recurring/list/list.index.html")
    });
  }
})();
