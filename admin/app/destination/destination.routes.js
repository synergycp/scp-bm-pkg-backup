(function () {
  angular
    .module('pkg.backup.destination')
    .config(routeConfig)
  ;

  /**
   * @ngInject
   */
  function routeConfig(RouteHelpersProvider) {
    var helper = RouteHelpersProvider;
    var pkg = helper.package('backup');
    pkg
      .state('destination', {
        url: '/destination',
        abstract: true,
        template: helper.dummyTemplate,
        resolve: helper.resolveFor(pkg.lang('admin:destination')),
      })
      .url('destination/?([0-9]*)', mapDestinationUrl)
      .sso('destination', function ($state, options) {
        return mapDestinationUrl($state, options.id);
      })
    ;

    function mapDestinationUrl($state, id) {
      return $state.href('destination.' + (id ? 'view' : 'list'), {
        id: id,
      });
    }
  }
})();
