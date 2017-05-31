(function () {
  angular
    .module('pkg.backup.destination.list')
    .config(routeConfig)
  ;

  /**
   * @ngInject
   */
  function routeConfig(RouteHelpersProvider) {
    var helper = RouteHelpersProvider;
    var pkg = helper.package('backup');
    pkg
      .state('destination.list', {
        url: '',
        title: 'Destinations',
        controller: 'PkgBackupDestinationIndexCtrl as vm',
        templateUrl: pkg.asset('admin/destination/list/list.index.html'),
      })
    ;
  }

})();
