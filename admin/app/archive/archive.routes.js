(function () {
  angular
    .module('pkg.backup.archive')
    .config(routeConfig)
  ;

  /**
   * @ngInject
   */
  function routeConfig(RouteHelpersProvider) {
    var helper = RouteHelpersProvider;
    var pkg = helper.package('backup');
    pkg
      .state('archive', {
        url: '/archive',
        abstract: true,
        template: helper.dummyTemplate,
        resolve: helper.resolveFor(pkg.lang('admin:archive')),
      })
      .url('archive/?([0-9]*)', mapArchiveUrl)
      .sso('archive', function ($state, options) {
        return mapArchiveUrl($state, options.id);
      })
    ;

    function mapArchiveUrl($state) {
      // note: there is no archive view route, so ID is not needed.
      return $state.href('archive.list');
    }
  }
})();
