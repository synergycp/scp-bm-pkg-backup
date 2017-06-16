(function () {
  angular
    .module('pkg.backup.source.list')
    .config(routeConfig)
  ;

  /**
   * @ngInject
   */
  function routeConfig(RouteHelpersProvider) {
    var helper = RouteHelpersProvider;
    var pkg = helper.package('backup');
    pkg
      .state('source.list', {
        url: '',
        title: 'Sources',
        controller: 'PkgBackupSourceIndexCtrl as vm',
        templateUrl: pkg.asset('admin/source/list/list.index.html'),
      })
    ;
  }

})();
