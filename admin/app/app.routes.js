(function () {
  angular
    .module('pkg.backup')
    .config(routeConfig)
    ;

  /**
   * @ngInject
   */
  function routeConfig(RouteHelpersProvider) {
    var helper = RouteHelpersProvider;
    var pkg = helper.package('backup');

    pkg.state('');
  }
})();
